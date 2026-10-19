<?php
namespace App\Models;
use CodeIgniter\Model;

/**
 * Payee transaction flow:
 *   Obligation (ORS / BURS)  --serialno-->  Disbursement (DV)
 *   DV  --dvno-->  Payment (LDDAP-ADA line or ADA)  --dvno-->  RCI
 *
 * Obligation amounts live in the six direct/indirect PS/MOOE/CO detail
 * tables (project_id = header recid); the header net_amount is unused.
 * tbl_ada used utf8mb4_unicode_ci (aligned to utf8mb4_general_ci by
 * migration-lddapada-ada-approval-09302026.sql); the COLLATE on its join
 * columns is kept so older databases without that migration still work.
 * Dates are never compared to a '0000-00-00' literal: MySQL 8 strict mode
 * (NO_ZERO_DATE) rejects it, so unset dates are caught with > '1000-01-01'.
 */
class MyDashboardModel extends Model
{
    protected $db;

    public function __construct(){
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /**
     * Amount per ORS / BURS (prefix 'ors' or 'burs') as a derived table
     * (project_id, amt): the sum of its six detail tables. The header's
     * gross_amount is empty on most older obligations, so use this instead:
     * LEFT JOIN <this> a ON a.project_id = h.recid.
     */
    public static function obligationAmounts(string $prefix): string
    {
        $prefix = $prefix === 'burs' ? 'burs' : 'ors';
        $parts = [];
        foreach (['direct', 'indirect'] as $kind) {
            foreach (['ps', 'mooe', 'co'] as $cls) {
                $parts[] = "SELECT project_id, amount FROM tbl_{$prefix}_{$kind}_{$cls}_dt";
            }
        }
        return '(SELECT project_id, SUM(amount) amt FROM (' . implode(' UNION ALL ', $parts) . ') x GROUP BY project_id)';
    }

    /** Shared CTEs: obl, pay, rci, dv_agg, flow (one row per obligation). */
    private function cte(): string
    {
        $dt = function ($prefix) {
            $parts = [];
            foreach (['direct', 'indirect'] as $kind) {
                foreach (['ps', 'mooe', 'co'] as $cls) {
                    $parts[] = "SELECT project_id, amount FROM tbl_{$prefix}_{$kind}_{$cls}_dt";
                }
            }
            return implode(' UNION ALL ', $parts);
        };

        return "
        WITH
        ors_amt AS (SELECT project_id, SUM(amount) amt FROM ({$dt('ors')}) x GROUP BY project_id),
        burs_amt AS (SELECT project_id, SUM(amount) amt FROM ({$dt('burs')}) x GROUP BY project_id),
        obl AS (
            SELECT 'ORS' src, o.recid, o.serialno, o.payee_name,
                   CASE WHEN o.ors_date > '1000-01-01' THEN o.ors_date ELSE DATE(o.added_at) END obl_date,
                   COALESCE(a.amt, 0) amount
            FROM tbl_ors_hd o LEFT JOIN ors_amt a ON a.project_id = o.recid
            UNION ALL
            SELECT 'BURS', b.recid, b.serialno, b.payee_name,
                   CASE WHEN b.burs_date > '1000-01-01' THEN b.burs_date ELSE DATE(b.added_at) END,
                   COALESCE(a.amt, 0)
            FROM tbl_burs_hd b LEFT JOIN burs_amt a ON a.project_id = b.recid
        ),
        pay AS (
            SELECT l.dvno, 'LDDAP-ADA' channel, h.lddapadano ref_no, h.lddapada_date pay_date, l.net_amount amount
            FROM tbl_lddapada_dt l JOIN tbl_lddapada_hd h ON h.recid = l.lddapada_id
            UNION ALL
            SELECT a.dvno COLLATE utf8mb4_general_ci, 'ADA', a.adano COLLATE utf8mb4_general_ci, a.ada_date, a.net_amount
            FROM tbl_ada a
        ),
        rci AS (
            SELECT r.dvno, h.reportno, DATE(h.created_at) rci_date, r.net_amount amount
            FROM tbl_rci_dt r JOIN tbl_rci_hd h ON h.recid = r.hd_rid
        ),
        dv_agg AS (
            SELECT d.serialno,
                   COUNT(*) dv_n, SUM(d.net_amount) dv_amt, MAX(d.disbursement_date) dv_date,
                   SUM(p.dvno IS NOT NULL) paid_dv_n, SUM(COALESCE(p.amt, 0)) pay_amt, MAX(p.pay_date) pay_date,
                   SUM(r.dvno IS NOT NULL) rci_dv_n, MAX(r.rci_date) rci_date
            FROM tbl_disbursement_hd d
            LEFT JOIN (SELECT dvno, SUM(amount) amt, MAX(pay_date) pay_date FROM pay GROUP BY dvno) p ON p.dvno = d.dvno
            LEFT JOIN (SELECT dvno, MAX(rci_date) rci_date FROM rci GROUP BY dvno) r ON r.dvno = d.dvno
            GROUP BY d.serialno
        ),
        flow AS (
            SELECT o.*,
                   COALESCE(a.dv_n, 0) dv_n, COALESCE(a.dv_amt, 0) dv_amt, a.dv_date,
                   COALESCE(a.paid_dv_n, 0) paid_dv_n, COALESCE(a.pay_amt, 0) pay_amt, a.pay_date,
                   COALESCE(a.rci_dv_n, 0) rci_dv_n, a.rci_date,
                   CASE WHEN a.rci_dv_n > 0 THEN 4 WHEN a.paid_dv_n > 0 THEN 3 WHEN a.dv_n > 0 THEN 2 ELSE 1 END stage
            FROM obl o LEFT JOIN dv_agg a ON a.serialno = o.serialno
        )";
    }

    /** "AND YEAR(col) = ?" when a year is selected, with its binding. */
    private function yearFilter(?int $year, string $col): array
    {
        return $year ? [" AND YEAR({$col}) = ? ", [$year]] : ['', []];
    }

    public function years(): array
    {
        $rows = $this->db->query($this->cte() . "
            SELECT DISTINCT YEAR(obl_date) y FROM obl WHERE obl_date IS NOT NULL AND YEAR(obl_date) > 2000 ORDER BY y DESC
        ")->getResultArray();
        return array_map('intval', array_column($rows, 'y'));
    }

    /** Stage totals, conversion and waiting queues. */
    public function summary(?int $year): array
    {
        [$fo, $bo] = $this->yearFilter($year, 'obl_date');
        [$fd, $bd] = $this->yearFilter($year, 'd.disbursement_date');
        [$fp, $bp] = $this->yearFilter($year, 'p.pay_date');
        [$fr, $br] = $this->yearFilter($year, 'r.rci_date');

        $s = $this->db->query($this->cte() . "
            SELECT
              (SELECT COUNT(*) FROM obl WHERE 1 {$fo}) obl_n,
              (SELECT COALESCE(SUM(amount),0) FROM obl WHERE 1 {$fo}) obl_amt,
              (SELECT COUNT(DISTINCT payee_name) FROM obl WHERE 1 {$fo}) obl_payees,
              (SELECT COUNT(*) FROM obl WHERE src = 'ORS' {$fo}) ors_n,
              (SELECT COUNT(*) FROM obl WHERE src = 'BURS' {$fo}) burs_n,

              (SELECT COUNT(*) FROM tbl_disbursement_hd d WHERE 1 {$fd}) dv_n,
              (SELECT COALESCE(SUM(d.net_amount),0) FROM tbl_disbursement_hd d WHERE 1 {$fd}) dv_amt,

              (SELECT COUNT(*) FROM pay p WHERE 1 {$fp}) pay_n,
              (SELECT COALESCE(SUM(p.amount),0) FROM pay p WHERE 1 {$fp}) pay_amt,
              (SELECT COUNT(*) FROM pay p WHERE p.channel = 'LDDAP-ADA' {$fp}) lddap_n,
              (SELECT COUNT(*) FROM pay p WHERE p.channel = 'ADA' {$fp}) ada_n,

              (SELECT COUNT(*) FROM rci r WHERE 1 {$fr}) rci_n,
              (SELECT COALESCE(SUM(r.amount),0) FROM rci r WHERE 1 {$fr}) rci_amt,
              (SELECT COUNT(DISTINCT r.reportno) FROM rci r WHERE 1 {$fr}) rci_reports,

              (SELECT COUNT(*) FROM flow WHERE stage >= 2 {$fo}) reached_dv,
              (SELECT COUNT(*) FROM flow WHERE stage >= 3 {$fo}) reached_pay,
              (SELECT COUNT(*) FROM flow WHERE stage >= 4 {$fo}) reached_rci
        ", array_merge($bo, $bo, $bo, $bo, $bo, $bd, $bd, $bp, $bp, $bp, $bp, $br, $br, $br, $bo, $bo, $bo))->getRowArray();

        // Waiting queues: what each next office still has to act on
        $s['wait_dv'] = $this->db->query($this->cte() . "
            SELECT COUNT(*) n, COALESCE(SUM(amount),0) amt, MIN(CASE WHEN YEAR(obl_date) >= 2000 THEN obl_date END) oldest
            FROM flow WHERE stage = 1 {$fo}
        ", $bo)->getRowArray();

        $s['wait_pay'] = $this->db->query($this->cte() . "
            SELECT COUNT(*) n, COALESCE(SUM(d.net_amount),0) amt, MIN(CASE WHEN YEAR(d.disbursement_date) >= 2000 THEN d.disbursement_date END) oldest
            FROM tbl_disbursement_hd d
            WHERE NOT EXISTS (SELECT 1 FROM pay p WHERE p.dvno = d.dvno) {$fd}
        ", $bd)->getRowArray();

        $s['wait_rci'] = $this->db->query($this->cte() . "
            SELECT COUNT(*) n, COALESCE(SUM(p.amount),0) amt, MIN(CASE WHEN YEAR(p.pay_date) >= 2000 THEN p.pay_date END) oldest
            FROM pay p
            WHERE p.channel = 'LDDAP-ADA' AND NOT EXISTS (SELECT 1 FROM rci r WHERE r.dvno = p.dvno) {$fp}
        ", $bp)->getRowArray();

        return $s;
    }

    /** Amount per month for each stage (12 months of the year, or the last 12). */
    public function monthly(?int $year): array
    {
        if ($year) {
            $start = sprintf('%04d-01-01', $year);
        } else {
            $start = date('Y-m-01', strtotime('-11 months'));
        }
        $end = date('Y-m-d', strtotime($start . ' +12 months'));

        $rows = $this->db->query($this->cte() . "
            SELECT ym, SUM(obl) obl, SUM(dv) dv, SUM(paid) paid FROM (
              SELECT DATE_FORMAT(obl_date, '%Y-%m') ym, amount obl, 0 dv, 0 paid FROM obl WHERE obl_date >= ? AND obl_date < ?
              UNION ALL
              SELECT DATE_FORMAT(disbursement_date, '%Y-%m'), 0, net_amount, 0 FROM tbl_disbursement_hd WHERE disbursement_date >= ? AND disbursement_date < ?
              UNION ALL
              SELECT DATE_FORMAT(pay_date, '%Y-%m'), 0, 0, amount FROM pay WHERE pay_date >= ? AND pay_date < ?
            ) m GROUP BY ym
        ", [$start, $end, $start, $end, $start, $end])->getResultArray();

        $byMonth = array_column($rows, null, 'ym');
        $out = [];
        for ($i = 0; $i < 12; $i++) {
            $ym = date('Y-m', strtotime($start . " +{$i} months"));
            $out[] = [
                'label' => date('M Y', strtotime($ym . '-01')),
                'obl'   => (float) ($byMonth[$ym]['obl'] ?? 0),
                'dv'    => (float) ($byMonth[$ym]['dv'] ?? 0),
                'paid'  => (float) ($byMonth[$ym]['paid'] ?? 0),
            ];
        }
        return $out;
    }

    /** One row per payee with how far their obligations have moved. */
    public function payees(?int $year): array
    {
        [$fo, $bo] = $this->yearFilter($year, 'obl_date');
        return $this->db->query($this->cte() . "
            SELECT payee_name,
                   COUNT(*) obl_n,
                   SUM(amount) obl_amt,
                   SUM(dv_amt) dv_amt,
                   SUM(pay_amt) pay_amt,
                   SUM(stage >= 2) reached_dv,
                   SUM(stage >= 3) reached_pay,
                   SUM(stage >= 4) reached_rci,
                   MAX(GREATEST(obl_date, COALESCE(dv_date, obl_date), COALESCE(pay_date, obl_date), COALESCE(rci_date, obl_date))) last_activity
            FROM flow
            WHERE payee_name <> '' {$fo}
            GROUP BY payee_name
            ORDER BY last_activity DESC, obl_amt DESC
        ", $bo)->getResultArray();
    }

    /** Latest events across all four stages. */
    public function recent(int $limit = 8): array
    {
        return $this->db->query($this->cte() . "
            SELECT * FROM (
              SELECT 'obligation' stage, CONCAT(o.src, ' ', o.serialno) ref, o.payee_name, o.amount,
                     COALESCE(h.added_at, o.obl_date) at
              FROM obl o
              LEFT JOIN tbl_ors_hd h ON o.src = 'ORS' AND h.recid = o.recid
              UNION ALL
              SELECT 'disbursement', CONCAT('DV ', d.dvno), d.payee_name, d.net_amount, d.added_at FROM tbl_disbursement_hd d
              UNION ALL
              SELECT 'payment', CONCAT('LDDAP-ADA ', h.lddapadano), l.payee_name, l.net_amount, l.added_at
              FROM tbl_lddapada_dt l JOIN tbl_lddapada_hd h ON h.recid = l.lddapada_id
              UNION ALL
              SELECT 'payment', CONCAT('ADA ', a.adano COLLATE utf8mb4_general_ci), a.payee_name COLLATE utf8mb4_general_ci, a.net_amount, a.ada_date FROM tbl_ada a
              UNION ALL
              SELECT 'rci', CONCAT('RCI ', h.reportno), r.payee_name, r.net_amount, r.created_at
              FROM tbl_rci_dt r JOIN tbl_rci_hd h ON h.recid = r.hd_rid
            ) e
            WHERE at IS NOT NULL
            ORDER BY at DESC
            LIMIT {$limit}
        ")->getResultArray();
    }

    /** Every obligation of one payee with its DVs, payments and RCI entries. */
    public function payeeFlow(string $payee): array
    {
        $obligations = $this->db->query($this->cte() . "
            SELECT src, recid, serialno, obl_date, amount, stage FROM flow WHERE payee_name = ? ORDER BY obl_date DESC, recid DESC
        ", [$payee])->getResultArray();

        if (empty($obligations)) {
            return [];
        }

        $serials = array_values(array_unique(array_column($obligations, 'serialno')));
        $in = implode(',', array_fill(0, count($serials), '?'));

        $dvs = $this->db->query("
            SELECT serialno, dvno, disbursement_date, net_amount FROM tbl_disbursement_hd WHERE serialno IN ({$in}) ORDER BY disbursement_date
        ", $serials)->getResultArray();

        $payments = $rcis = [];
        if ($dvs) {
            $dvnos = array_values(array_unique(array_column($dvs, 'dvno')));
            $in2 = implode(',', array_fill(0, count($dvnos), '?'));
            $payments = $this->db->query($this->cte() . " SELECT dvno, channel, ref_no, pay_date, amount FROM pay WHERE dvno IN ({$in2}) ORDER BY pay_date", $dvnos)->getResultArray();
            $rcis = $this->db->query($this->cte() . " SELECT dvno, reportno, rci_date, amount FROM rci WHERE dvno IN ({$in2}) ORDER BY rci_date", $dvnos)->getResultArray();
        }

        $group = function ($rows, $key) {
            $out = [];
            foreach ($rows as $r) { $out[$r[$key]][] = $r; }
            return $out;
        };
        $dvBySerial = $group($dvs, 'serialno');
        $payByDv = $group($payments, 'dvno');
        $rciByDv = $group($rcis, 'dvno');

        foreach ($obligations as &$o) {
            $o['dvs'] = [];
            foreach ($dvBySerial[$o['serialno']] ?? [] as $dv) {
                $dv['payments'] = $payByDv[$dv['dvno']] ?? [];
                $dv['rci'] = $rciByDv[$dv['dvno']] ?? [];
                $o['dvs'][] = $dv;
            }
        }
        return $obligations;
    }

    /**
     * Find a record: ORS, BURS, DV, LDDAP-ADA and RCI by number, payee,
     * particulars or a line's DV / ORS no., only in the modules this user can
     * open. Matches on the record's own number come first, then newest.
     * Returns [type => ['rows' => [...], 'more' => bool]].
     */
    public const SEARCH_TYPES = [
        'ors'      => ['ORS', \Config\AccessCodes::ORS_VIEW],
        'burs'     => ['BURS', \Config\AccessCodes::BURS_VIEW],
        'dv'       => ['DV', \Config\AccessCodes::DISBURSEMENT_VIEW],
        'lddapada' => ['LDDAP-ADA', \Config\AccessCodes::LDDAPADA_VIEW],
        'rci'      => ['RCI', \Config\AccessCodes::RCI_VIEW],
    ];

    public static function searchTypes(): array
    {
        return array_keys(array_filter(self::SEARCH_TYPES, fn ($t) => fods_has_access($t[1])));
    }

    public function search(string $q, array $types, int $limit): array
    {
        $like = '%' . addcslashes($q, '%_\\') . '%';
        $prefix = addcslashes($q, '%_\\') . '%';
        $take = $limit + 1;   // one extra tells whether there are more
        $out = [];

        foreach ($types as $type) {
            switch ($type) {
                case 'ors':
                case 'burs':
                    $date = $type . '_date';
                    $rows = $this->db->query("SELECT h.`recid`, h.`serialno` ref, h.`{$date}` d, h.`payee_name`, h.`particulars`, COALESCE(a.`amt`, 0) amount, h.`dvno`,
                            h.`is_approved_certa`, h.`is_disapproved_certa`, h.`is_approved_certb`, h.`is_disapproved_certb`
                        FROM tbl_{$type}_hd h LEFT JOIN " . self::obligationAmounts($type) . " a ON a.`project_id` = h.`recid`
                        WHERE h.`serialno` LIKE ? OR h.`payee_name` LIKE ? OR h.`particulars` LIKE ? OR h.`dvno` LIKE ?
                        ORDER BY h.`serialno` LIKE ? DESC, h.`{$date}` DESC, h.`recid` DESC LIMIT {$take}",
                        [$like, $like, $like, $like, $prefix])->getResultArray();
                    $statusOf = $type === 'ors' ? [\App\Controllers\MyOrs::class, 'statusOf'] : [\App\Controllers\MyBurs::class, 'statusOf'];
                    $labels = ['pending_a' => ['tone-amber', 'Box A'], 'pending_b' => ['tone-indigo', 'Box B'], 'approved' => ['tone-green', 'Certified'], 'disapproved' => ['tone-red', 'Returned']];
                    $route = $type === 'ors' ? 'myors' : 'myburs';
                    $rows = array_map(fn ($r) => [
                        'ref' => $r['ref'], 'date' => $r['d'], 'payee' => $r['payee_name'],
                        'sub' => trim(mb_substr((string) $r['particulars'], 0, 90) . ($r['dvno'] ? ' · DV ' . $r['dvno'] : '')),
                        'amount' => (float) $r['amount'], 'status' => $labels[$statusOf($r)] ?? null,
                        'link' => site_url($route . '?meaction=MAIN&recid=' . (int) $r['recid']),
                    ], $rows);
                    break;

                case 'dv':
                    $rows = $this->db->query("SELECT h.`recid`, h.`dvno` ref, h.`disbursement_date` d, h.`payee_name`, h.`particulars`, h.`net_amount` amount, h.`serialno`,
                            h.`is_approved_certa`, h.`is_disapproved_certa`, h.`is_approved_certc`, h.`is_disapproved_certc`, h.`is_approved_certd`, h.`is_disapproved_certd`
                        FROM tbl_disbursement_hd h
                        WHERE h.`dvno` LIKE ? OR h.`serialno` LIKE ? OR h.`payee_name` LIKE ? OR h.`particulars` LIKE ?
                        ORDER BY h.`dvno` LIKE ? DESC, h.`disbursement_date` DESC, h.`recid` DESC LIMIT {$take}",
                        [$like, $like, $like, $like, $prefix])->getResultArray();
                    $labels = ['pending_a' => ['tone-amber', 'Box A'], 'pending_c' => ['tone-teal', 'Box C'], 'pending_d' => ['tone-indigo', 'Box D'], 'approved' => ['tone-green', 'Approved'], 'disapproved' => ['tone-red', 'Returned']];
                    $rows = array_map(fn ($r) => [
                        'ref' => $r['ref'] ?: '(no DV no. yet)', 'date' => $r['d'], 'payee' => $r['payee_name'],
                        'sub' => trim(mb_substr((string) $r['particulars'], 0, 90) . ($r['serialno'] ? ' · ' . $r['serialno'] : '')),
                        'amount' => (float) $r['amount'], 'status' => $labels[\App\Controllers\MyDisbursement::statusOf($r)] ?? null,
                        'link' => site_url('mydisbursement?meaction=MAIN&recid=' . (int) $r['recid']),
                    ], $rows);
                    break;

                case 'lddapada':
                    $lineHit = "l.`dvno` LIKE ? OR l.`payee_name` LIKE ? OR l.`serialno` LIKE ?";
                    $rows = $this->db->query("SELECT h.`recid`, h.`lddapadano` ref, h.`lddapada_date` d, h.`ckno`, h.`is_approved`, h.`is_disapproved`,
                            (SELECT COUNT(*) FROM tbl_lddapada_dt l WHERE l.`lddapada_id` = h.`recid`) n,
                            (SELECT SUM(l.`net_amount`) FROM tbl_lddapada_dt l WHERE l.`lddapada_id` = h.`recid`) amount,
                            (SELECT l.`payee_name` FROM tbl_lddapada_dt l WHERE l.`lddapada_id` = h.`recid` ORDER BY l.`recid` LIMIT 1) payee,
                            (SELECT CONCAT('DV ', l.`dvno`, ' · ', l.`payee_name`) FROM tbl_lddapada_dt l WHERE l.`lddapada_id` = h.`recid` AND ({$lineHit}) LIMIT 1) hit
                        FROM tbl_lddapada_hd h
                        WHERE h.`lddapadano` LIKE ? OR h.`ckno` LIKE ?
                            OR EXISTS (SELECT 1 FROM tbl_lddapada_dt l WHERE l.`lddapada_id` = h.`recid` AND ({$lineHit}))
                        ORDER BY h.`lddapadano` LIKE ? DESC, h.`lddapada_date` DESC, h.`recid` DESC LIMIT {$take}",
                        [$like, $like, $like, $like, $like, $like, $like, $like, $prefix])->getResultArray();
                    $labels = ['pending' => ['tone-amber', 'Pending'], 'approved' => ['tone-green', 'Approved'], 'disapproved' => ['tone-red', 'Returned']];
                    $rows = array_map(fn ($r) => [
                        'ref' => $r['ref'], 'date' => $r['d'],
                        'payee' => $r['payee'] ? $r['payee'] . ((int) $r['n'] > 1 ? ' and ' . ((int) $r['n'] - 1) . ' more' : '') : '',
                        'sub' => trim(($r['hit'] ? 'Has ' . $r['hit'] : (int) $r['n'] . ' DV' . ((int) $r['n'] === 1 ? '' : 's')) . ($r['ckno'] ? ' · Check ' . $r['ckno'] : '')),
                        'amount' => (float) $r['amount'], 'status' => $labels[\App\Controllers\MyLDDAPADA::statusOf($r)] ?? null,
                        'link' => site_url('mylddapada?meaction=MAIN&recid=' . (int) $r['recid']),
                    ], $rows);
                    break;

                case 'rci':
                    $lineHit = "l.`lddapadano` LIKE ? OR l.`dvno` LIKE ? OR l.`payee_name` LIKE ? OR l.`serialno` LIKE ? OR l.`ckno` LIKE ?";
                    $rows = $this->db->query("SELECT h.`recid`, h.`reportno` ref, h.`created_at` d,
                            (SELECT COUNT(*) FROM tbl_rci_dt l WHERE l.`hd_rid` = h.`recid`) n,
                            (SELECT SUM(l.`net_amount`) FROM tbl_rci_dt l WHERE l.`hd_rid` = h.`recid`) amount,
                            (SELECT MIN(l.`lddapada_date`) FROM tbl_rci_dt l WHERE l.`hd_rid` = h.`recid`) first_date,
                            (SELECT CONCAT('DV ', l.`dvno`, ' · ', l.`payee_name`) FROM tbl_rci_dt l WHERE l.`hd_rid` = h.`recid` AND ({$lineHit}) LIMIT 1) hit
                        FROM tbl_rci_hd h
                        WHERE h.`reportno` LIKE ?
                            OR EXISTS (SELECT 1 FROM tbl_rci_dt l WHERE l.`hd_rid` = h.`recid` AND ({$lineHit}))
                        ORDER BY h.`reportno` LIKE ? DESC, h.`created_at` DESC, h.`recid` DESC LIMIT {$take}",
                        array_merge(array_fill(0, 5, $like), [$like], array_fill(0, 5, $like), [$prefix]))->getResultArray();
                    $rows = array_map(fn ($r) => [
                        'ref' => $r['ref'] ?: '(no report no.)', 'date' => $r['d'],
                        'payee' => (int) $r['n'] . ' DV' . ((int) $r['n'] === 1 ? '' : 's') . ' paid',
                        'sub' => $r['hit'] ? 'Has ' . $r['hit'] : '',
                        'amount' => (float) $r['amount'], 'status' => null,
                        'link' => site_url('myrci?meaction=MAIN&recid=' . (int) $r['recid']),
                    ], $rows);
                    break;

                default:
                    continue 2;
            }
            $out[$type] = ['rows' => array_slice($rows, 0, $limit), 'more' => count($rows) > $limit];
        }
        return $out;
    }
}
