<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Topbar notifications (the bell in templates/myheader.php): what is waiting
 * for the signed-in user to act on, across the systems they can open.
 *   FODS - approval steps they can decide (ORS, BURS, DV, LDDAP-ADA, ADA,
 *          PO, PR), from MyAssistantModel::pending_approvals(), which reuses
 *          each module's own statusOf / can_decide rules
 *   SORF - requests waiting for their "Noted by" signature (drf_approval_count)
 * Nothing is stored: it is worked out on request and kept in the session for
 * CACHE_SECONDS, so the bell's polling stays cheap.
 */
class MyNotificationsModel extends Model
{
    public const CACHE_SECONDS = 45;

    // FODS module (as MyAssistantModel names it) => [short label, icon]
    private const FODS_MODULES = [
        'ORS' => ['ORS', 'ti-file-invoice'],
        'BURS' => ['BURS', 'ti-file-invoice'],
        'Disbursement vouchers' => ['Disbursement vouchers', 'ti-receipt'],
        'LDDAP-ADA' => ['LDDAP-ADA', 'ti-building-bank'],
        'ADA' => ['ADA', 'ti-building-bank'],
        'Purchase orders' => ['Purchase orders', 'ti-shopping-cart'],
        'Purchase requests' => ['Purchase requests', 'ti-clipboard-list'],
    ];

    /**
     * ['unread' => total waiting, 'groups' => [[system, label, icon, count,
     * url, oldest]]], one group per module with something waiting.
     */
    public function inbox(bool $fresh = false): array
    {
        $user = (string) session('__xsys_myuserzicas__');
        $cached = session('__ifad_notifications');
        if (!$fresh && is_array($cached) && ($cached['user'] ?? '') === $user && ($cached['at'] ?? 0) > time() - self::CACHE_SECONDS) {
            return $cached['data'];
        }

        $groups = [];
        if (ifad_can_open('fods')) {
            try {
                $pending = model('App\Models\MyAssistantModel')->pending_approvals();
                foreach ((array) ($pending['waiting'] ?? []) as $module => $g) {
                    if (empty($g['count'])) {
                        continue;
                    }
                    [$label, $icon] = self::FODS_MODULES[$module] ?? [$module, 'ti-checklist'];
                    $groups[] = [
                        'system' => 'FODS',
                        'label' => $label,
                        'icon' => $icon,
                        'count' => (int) $g['count'],
                        'url' => site_url($g['page_link'] ?? 'mydashboard'),
                        'oldest' => self::describe($g['oldest_first'][0] ?? []),
                    ];
                }
            } catch (\Throwable $e) {
                log_message('error', 'Notifications (FODS): ' . $e->getMessage());
            }
        }
        if (ifad_can_open('sorf')) {
            try {
                $n = drf_approval_count();
                if ($n > 0) {
                    $groups[] = [
                        'system' => 'SORF',
                        'label' => 'Document requests',
                        'icon' => 'ti-file-description',
                        'count' => $n,
                        'url' => sorf_url('myapproval'),
                        'oldest' => 'Waiting for your "Noted by" signature',
                    ];
                }
            } catch (\Throwable $e) {
                log_message('error', 'Notifications (SORF): ' . $e->getMessage());
            }
        }

        $data = ['unread' => array_sum(array_column($groups, 'count')), 'groups' => $groups];
        session()->set('__ifad_notifications', ['user' => $user, 'at' => time(), 'data' => $data]);
        return $data;
    }

    // The oldest waiting record in words: its number, then who / what
    private static function describe(array $r): string
    {
        $no = '';
        foreach (['serial_no', 'dv_no', 'lddap_ada_no', 'ada_no', 'po_no', 'pr_no'] as $k) {
            if (!empty($r[$k])) {
                $no = (string) $r[$k];
                break;
            }
        }
        $who = '';
        foreach (['payee', 'supplier', 'purpose'] as $k) {
            if (!empty($r[$k])) {
                $who = (string) $r[$k];
                break;
            }
        }
        $step = !empty($r['step']) ? ' (' . $r['step'] . ')' : '';
        $text = trim($no . ($no !== '' && $who !== '' ? ' · ' : '') . $who);
        return $text !== '' ? 'Oldest: ' . $text . $step : '';
    }
}
