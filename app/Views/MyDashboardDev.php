<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');

echo view('templates/myheader.php');
?>

<style>
/* ============================================================
   ROOT VARIABLES
   ============================================================ */
:root {
    --primary: #0f6f6f;
    --primary-light: #e6f3f3;
    --primary-dark: #0a4f4f;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #3b82f6;
    --gray: #64748b;
    --gray-light: #f1f5f9;
    --gray-border: #e2e8f0;
    --card-shadow: 0 4px 20px rgba(0,0,0,0.08);
    --border-radius: 16px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

* {
    box-sizing: border-box;
}

body {
    background: #f0f4f8;
    font-family: "Segoe UI", -apple-system, BlinkMacSystemFont, sans-serif;
}

.dashboard-wrapper {
    padding: 24px 30px;
    max-width: 1600px;
    margin: 0 auto;
}

/* ============================================================
   HEADER
   ============================================================ */
.dashboard-header {
    background: linear-gradient(135deg, #0a1a3a 0%, #0f6f6f 100%);
    border-radius: var(--border-radius);
    padding: 24px 32px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    box-shadow: 0 8px 32px rgba(15, 111, 111, 0.25);
}

.header-left {
    display: flex;
    align-items: center;
    gap: 18px;
}

.header-icon {
    width: 52px;
    height: 52px;
    background: rgba(255,255,255,0.15);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
}

.header-left h1 {
    color: #fff;
    font-size: 22px;
    font-weight: 700;
    margin: 0;
    letter-spacing: -0.3px;
}

.header-left h1 small {
    font-size: 13px;
    font-weight: 400;
    opacity: 0.8;
    display: block;
    margin-top: 2px;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.live-badge {
    background: rgba(16, 185, 129, 0.2);
    color: #6ee7b7;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.live-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    animation: pulse-dot 1.5s ease-in-out infinite;
}

@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.8); }
}

/* ============================================================
   STATS ROW
   ============================================================ */
.stats-row {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 16px;
    margin-bottom: 30px;
}

.stat-card {
    background: #fff;
    padding: 18px 20px;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    border: 1px solid var(--gray-border);
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.1);
}

.stat-card .stat-label {
    font-size: 11px;
    text-transform: uppercase;
    color: var(--gray);
    letter-spacing: 0.5px;
    font-weight: 600;
}

.stat-card .stat-number {
    font-size: 28px;
    font-weight: 800;
    color: #0a1a3a;
    margin: 4px 0 2px;
    line-height: 1.2;
}

.stat-card .stat-sub {
    font-size: 11px;
    color: var(--gray);
}

.stat-card .stat-icon {
    float: right;
    font-size: 28px;
    opacity: 0.3;
}

/* ============================================================
   FILTER / SEARCH
   ============================================================ */
.filter-section {
    background: #fff;
    border-radius: var(--border-radius);
    padding: 16px 24px;
    margin-bottom: 28px;
    border: 1px solid var(--gray-border);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 16px;
    box-shadow: var(--card-shadow);
}

.filter-section label {
    font-size: 13px;
    font-weight: 600;
    color: #0a1a3a;
}

.filter-section input {
    padding: 8px 14px;
    border: 1px solid var(--gray-border);
    border-radius: 8px;
    font-size: 13px;
    background: #fff;
    min-width: 220px;
    flex: 1;
    transition: var(--transition);
}

.filter-section input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(15, 111, 111, 0.1);
}

.filter-section .filter-btn {
    background: var(--primary);
    color: #fff;
    border: none;
    padding: 8px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
}

.filter-section .filter-btn:hover {
    background: var(--primary-dark);
    transform: scale(1.02);
}

.filter-section .clear-btn {
    background: var(--gray-light);
    color: var(--gray);
    border: none;
    padding: 8px 18px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: var(--transition);
}

.filter-section .clear-btn:hover {
    background: #e2e8f0;
}

.filter-section .result-count {
    font-size: 13px;
    color: var(--gray);
    margin-left: auto;
}

/* ============================================================
   PAYEE CARD
   ============================================================ */
.payee-card {
    background: #ffffff;
    border-radius: var(--border-radius);
    border: 1px solid var(--gray-border);
    box-shadow: var(--card-shadow);
    margin-bottom: 28px;
    overflow: hidden;
    transition: var(--transition);
}

.payee-card:hover {
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
}

/* Card Header */
.card-header-custom {
    padding: 18px 24px 14px;
    border-bottom: 1px solid var(--gray-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #fafcfc 0%, #f1f5f9 100%);
    cursor: pointer;
    transition: var(--transition);
}

.card-header-custom:hover {
    background: linear-gradient(135deg, #f5f7fa 0%, #e8edf3 100%);
}

.payee-info h3 {
    font-size: 16px;
    font-weight: 700;
    color: #0a1a3a;
    margin: 0 0 4px;
}

.payee-info .payee-detail {
    font-size: 12px;
    color: var(--gray);
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.payee-info .payee-detail span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.toggle-icon {
    font-size: 20px;
    color: var(--gray);
    transition: var(--transition);
    margin-left: 10px;
}

.toggle-icon.expanded {
    transform: rotate(180deg);
}

/* ============================================================
   TRANSACTION FLOW ROW - ONE ROW PER TRANSACTION
   ============================================================ */
.transactions-wrapper {
    padding: 0 24px 24px;
    display: none;
}

.transactions-wrapper.show {
    display: block;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.flow-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    margin-top: 12px;
}

.flow-table th {
    text-align: left;
    padding: 10px 12px;
    background: #f8fafc;
    color: var(--gray);
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border-bottom: 2px solid var(--gray-border);
}

.flow-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.flow-table tr:hover td {
    background: #f8fafc;
}

.flow-table .ref-no {
    font-family: monospace;
    font-weight: 600;
    color: var(--primary);
}

/* Stage column styling */
.stage-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.stage-cell .stage-label {
    font-size: 11px;
    font-weight: 600;
}

.stage-cell .stage-date {
    font-size: 11px;
    color: var(--gray);
}

.stage-cell .stage-status {
    font-size: 10px;
    color: var(--gray);
    font-style: italic;
}

.stage-cell .stage-ref {
    font-size: 10px;
    font-family: monospace;
    color: var(--gray);
}

.stage-badge-flow {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 600;
}

.stage-badge-flow.ors { background: #fef3c7; color: #92400e; }
.stage-badge-flow.disbursement { background: #dbeafe; color: #1e40af; }
.stage-badge-flow.lddap { background: #d1fae5; color: #065f46; }
.stage-badge-flow.not-processed { background: #f1f5f9; color: #94a3b8; }

/* Arrow between stages */
.flow-arrow {
    color: var(--gray-border);
    font-size: 18px;
    padding: 0 4px;
}

/* ============================================================
   EMPTY STATE
   ============================================================ */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: var(--border-radius);
    border: 2px dashed var(--gray-border);
}

.empty-state .empty-icon {
    font-size: 52px;
    margin-bottom: 16px;
    opacity: 0.4;
}

.empty-state h3 {
    color: #0a1a3a;
    margin-bottom: 8px;
}

.empty-state p {
    color: var(--gray);
    font-size: 14px;
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 1200px) {
    .stats-row { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 768px) {
    .dashboard-wrapper { padding: 16px; }
    .dashboard-header { flex-direction: column; align-items: stretch; text-align: center; padding: 20px; }
    .header-left { flex-direction: column; }
    .header-right { justify-content: center; }
    .stats-row { grid-template-columns: repeat(2, 1fr); }
    .filter-section { flex-direction: column; align-items: stretch; }
    .filter-section input { min-width: auto; }
    .card-header-custom { flex-direction: column; align-items: stretch; gap: 10px; }
    .flow-table { font-size: 12px; }
    .flow-table th, .flow-table td { padding: 6px 8px; }
    .transactions-wrapper { padding: 0 12px 16px; }
    .flow-arrow { display: none; }
}

@media (max-width: 480px) {
    .stats-row { grid-template-columns: 1fr; }
    .flow-table { font-size: 11px; }
    .stage-cell .stage-label { font-size: 10px; }
}
</style>

<?php
// ============================================================
// DATA FETCHING - GROUP BY SERIALNO TO FORM COMPLETE FLOW
// ============================================================

// --- Get all ORS records ---
$orsRecords = $this->db->table('tbl_ors_hd')
    ->select('recid, serialno, payee_name, particulars, funding_source, added_at, ors_date')
    ->orderBy('added_at', 'DESC')
    ->get()
    ->getResultArray();

// --- Get all Disbursement records ---
$disbRecords = $this->db->table('tbl_disbursement_hd')
    ->select('recid, serialno, payee_name, particulars, funding_source, added_at, disbursement_date, dvno')
    ->orderBy('added_at', 'DESC')
    ->get()
    ->getResultArray();

// --- Get all LDDAP records ---
$lddapRecords = $this->db->table('tbl_lddapada_dt')
    ->select('recid, dvno, payee_name, serialno, gross_amount, net_amount, added_at')
    ->orderBy('added_at', 'DESC')
    ->get()
    ->getResultArray();

// --- Build Payee Map with grouped transactions ---
$payeeMap = [];

// Process ORS - create flow entries
foreach ($orsRecords as $row) {
    $key = $row['payee_name'];
    if (!isset($payeeMap[$key])) {
        $payeeMap[$key] = [
            'payee_name' => $row['payee_name'],
            'flows' => [],
            'ors_count' => 0,
            'disb_count' => 0,
            'lddap_count' => 0,
        ];
    }
    
    // Find if this ORS has a disbursement (by serialno match or dvno)
    $disbMatch = null;
    $lddapMatch = null;
    
    // Try to match by serialno
    foreach ($disbRecords as $disb) {
        if ($disb['serialno'] == $row['serialno'] && $disb['payee_name'] == $row['payee_name']) {
            $disbMatch = $disb;
            break;
        }
    }
    
    // If found disbursement, try to match LDDAP by dvno
    if ($disbMatch) {
        foreach ($lddapRecords as $lddap) {
            if ($lddap['dvno'] == $disbMatch['dvno'] && $lddap['payee_name'] == $row['payee_name']) {
                $lddapMatch = $lddap;
                break;
            }
        }
    }
    
    // If no LDDAP by dvno, try by serialno
    if (!$lddapMatch) {
        foreach ($lddapRecords as $lddap) {
            if ($lddap['serialno'] == $row['serialno'] && $lddap['payee_name'] == $row['payee_name']) {
                $lddapMatch = $lddap;
                break;
            }
        }
    }
    
    $payeeMap[$key]['flows'][] = [
        'ors' => $row,
        'disbursement' => $disbMatch,
        'lddap' => $lddapMatch,
    ];
    
    $payeeMap[$key]['ors_count']++;
    if ($disbMatch) $payeeMap[$key]['disb_count']++;
    if ($lddapMatch) $payeeMap[$key]['lddap_count']++;
}

// Sort payees by total transactions
usort($payeeMap, function($a, $b) {
    $aTotal = $a['ors_count'] + $a['disb_count'] + $a['lddap_count'];
    $bTotal = $b['ors_count'] + $b['disb_count'] + $b['lddap_count'];
    return $bTotal - $aTotal;
});

// --- Stats ---
$totalPayees = count($payeeMap);
$totalORS = array_sum(array_column($payeeMap, 'ors_count'));
$totalDisb = array_sum(array_column($payeeMap, 'disb_count'));
$totalLDDAP = array_sum(array_column($payeeMap, 'lddap_count'));
$completedFlows = 0;
$inProgressFlows = 0;

foreach ($payeeMap as $p) {
    foreach ($p['flows'] as $flow) {
        if ($flow['ors'] && $flow['disbursement'] && $flow['lddap']) {
            $completedFlows++;
        } elseif ($flow['ors'] && $flow['disbursement']) {
            $inProgressFlows++;
        } elseif ($flow['ors']) {
            $inProgressFlows++;
        }
    }
}

$completedPayees = count(array_filter($payeeMap, function($p) {
    foreach ($p['flows'] as $flow) {
        if ($flow['ors'] && $flow['disbursement'] && $flow['lddap']) return true;
    }
    return false;
}));

$inProgressPayees = count(array_filter($payeeMap, function($p) {
    foreach ($p['flows'] as $flow) {
        if ($flow['ors'] && $flow['disbursement'] && !$flow['lddap']) return true;
        if ($flow['ors'] && !$flow['disbursement']) return true;
    }
    return false;
}));
?>

<div class="dashboard-wrapper">

    <!-- ============================================================
    HEADER
    ============================================================ -->
    <div class="dashboard-header">
        <div class="header-left">
            <div class="header-icon">🏦</div>
            <div>
                <h1>
                    Payee Transaction Flow
                    <small>ORS → Disbursement → LDDAP per transaction</small>
                </h1>
            </div>
        </div>
        <div class="header-right">
            <div class="live-badge">
                <span class="live-dot"></span>
                Live
            </div>
            <span style="color:rgba(255,255,255,0.6); font-size:13px;">
                <?= date('F d, Y h:i A') ?>
            </span>
        </div>
    </div>

    <!-- ============================================================
    STATS
    ============================================================ -->
    <div class="stats-row">
        <div class="stat-card">
            <span class="stat-icon">👤</span>
            <div class="stat-label">Total Payees</div>
            <div class="stat-number"><?= number_format($totalPayees) ?></div>
            <div class="stat-sub">Tracked across all stages</div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">📋</span>
            <div class="stat-label">Total ORS</div>
            <div class="stat-number"><?= number_format($totalORS) ?></div>
            <div class="stat-sub">Obligation records</div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">📤</span>
            <div class="stat-label">Total Disbursements</div>
            <div class="stat-number"><?= number_format($totalDisb) ?></div>
            <div class="stat-sub">Disbursement records</div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">✅</span>
            <div class="stat-label">Total LDDAP</div>
            <div class="stat-number"><?= number_format($totalLDDAP) ?></div>
            <div class="stat-sub">Completed transactions</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid var(--success);">
            <span class="stat-icon">🎯</span>
            <div class="stat-label">Completed Flows</div>
            <div class="stat-number" style="color: var(--success);"><?= number_format($completedFlows) ?></div>
            <div class="stat-sub">Full ORS → Disb → LDDAP</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid var(--warning);">
            <span class="stat-icon">⏳</span>
            <div class="stat-label">In Progress</div>
            <div class="stat-number" style="color: var(--warning);"><?= number_format($inProgressFlows) ?></div>
            <div class="stat-sub">ORS → Disbursement</div>
        </div>
    </div>

    <!-- ============================================================
    FILTER
    ============================================================ -->
    <div class="filter-section">
        <label for="searchPayee">🔍 Search Payee:</label>
        <input type="text" id="searchPayee" placeholder="Type payee name to see all transactions..." onkeyup="filterCards()">
        
        <button class="filter-btn" onclick="filterCards()">Apply Filter</button>
        <button class="clear-btn" onclick="clearFilters()">Clear</button>
        
        <span class="result-count">
            <span id="visibleCount"><?= count($payeeMap) ?></span> payees shown
        </span>
    </div>

    <!-- ============================================================
    PAYEE CARDS WITH FLOW ROWS
    ============================================================ -->
    <div id="cardsContainer">

        <?php if (empty($payeeMap)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>No Payee Data Found</h3>
                <p>Start by creating ORS records to track payees through the process.</p>
            </div>
        <?php else: ?>

            <?php foreach ($payeeMap as $payee): 
                $totalTransactions = $payee['ors_count'] + $payee['disb_count'] + $payee['lddap_count'];
            ?>
                <div class="payee-card" data-payee="<?= strtolower($payee['payee_name']) ?>">
                    
                    <!-- Card Header -->
                    <div class="card-header-custom" onclick="toggleTransactions(this)">
                        <div class="payee-info">
                            <h3>
                                <?= htmlspecialchars($payee['payee_name']) ?>
                                <span style="font-size:13px; font-weight:400; color:var(--gray); margin-left:10px;">
                                    (<?= count($payee['flows']) ?> transaction<?= count($payee['flows']) > 1 ? 's' : '' ?>)
                                </span>
                            </h3>
                            <div class="payee-detail">
                                <span>📄 <?= $payee['ors_count'] ?> ORS</span>
                                <span>📤 <?= $payee['disb_count'] ?> Disb</span>
                                <span>✅ <?= $payee['lddap_count'] ?> LDDAP</span>
                            </div>
                        </div>
                        <div>
                            <span class="toggle-icon">▼</span>
                        </div>
                    </div>
                    
                    <!-- Transactions Flow -->
                    <div class="transactions-wrapper">
                        <table class="flow-table">
                            <thead>
                                <tr>
                                    <th style="width:30%;">ORS</th>
                                    <th style="width:5%;"></th>
                                    <th style="width:30%;">Disbursement</th>
                                    <th style="width:5%;"></th>
                                    <th style="width:30%;">LDDAP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Sort flows by ORS date (newest first)
                                $sortedFlows = $payee['flows'];
                                usort($sortedFlows, function($a, $b) {
                                    return strtotime($b['ors']['added_at']) - strtotime($a['ors']['added_at']);
                                });
                                
                                foreach ($sortedFlows as $flow):
                                    $ors = $flow['ors'];
                                    $disb = $flow['disbursement'];
                                    $lddap = $flow['lddap'];
                                ?>
                                    <tr>
                                        <!-- ORS Column -->
                                        <td>
                                            <div class="stage-cell">
                                                <div class="stage-label">
                                                    <span class="stage-badge-flow ors">ORS</span>
                                                    <span class="ref-no"><?= htmlspecialchars($ors['serialno']) ?></span>
                                                </div>
                                                <div class="stage-date"><?= date('M d, Y', strtotime($ors['added_at'])) ?></div>
                                                <div class="stage-ref"><?= htmlspecialchars(substr($ors['particulars'] ?? '', 0, 40)) ?>...</div>
                                            </div>
                                        </td>
                                        <td class="flow-arrow">→</td>
                                        
                                        <!-- Disbursement Column -->
                                        <td>
                                            <div class="stage-cell">
                                                <?php if ($disb): ?>
                                                    <div class="stage-label">
                                                        <span class="stage-badge-flow disbursement">Disbursement</span>
                                                        <span class="ref-no"><?= htmlspecialchars($disb['dvno'] ?? $disb['serialno'] ?? '—') ?></span>
                                                    </div>
                                                    <div class="stage-date"><?= date('M d, Y', strtotime($disb['added_at'])) ?></div>
                                                    <div class="stage-ref"><?= htmlspecialchars(substr($disb['particulars'] ?? '', 0, 30)) ?>...</div>
                                                <?php else: ?>
                                                    <div class="stage-label">
                                                        <span class="stage-badge-flow not-processed">Not yet processed</span>
                                                    </div>
                                                    <div class="stage-status">Waiting for disbursement</div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="flow-arrow">→</td>
                                        
                                        <!-- LDDAP Column -->
                                        <td>
                                            <div class="stage-cell">
                                                <?php if ($lddap): ?>
                                                    <div class="stage-label">
                                                        <span class="stage-badge-flow lddap">LDDAP</span>
                                                        <span class="ref-no"><?= htmlspecialchars($lddap['dvno'] ?? $lddap['serialno'] ?? '—') ?></span>
                                                    </div>
                                                    <div class="stage-date"><?= date('M d, Y', strtotime($lddap['added_at'])) ?></div>
                                                    <?php if ($lddap['net_amount']): ?>
                                                        <div class="stage-ref">₱<?= number_format($lddap['net_amount'], 2) ?></div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div class="stage-label">
                                                        <span class="stage-badge-flow not-processed">Not yet processed</span>
                                                    </div>
                                                    <div class="stage-status">Waiting for LDDAP</div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if (empty($sortedFlows)): ?>
                            <p style="color:var(--gray); padding:20px; text-align:center;">No transactions found for this payee.</p>
                        <?php endif; ?>
                    </div>
                    
                </div>
            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</div>

<script>
// ============================================================
// TOGGLE TRANSACTIONS
// ============================================================

function toggleTransactions(headerElement) {
    const card = headerElement.closest('.payee-card');
    const wrapper = card.querySelector('.transactions-wrapper');
    const icon = headerElement.querySelector('.toggle-icon');
    
    wrapper.classList.toggle('show');
    icon.classList.toggle('expanded');
}

// ============================================================
// FILTER FUNCTIONALITY
// ============================================================

function filterCards() {
    const searchValue = document.getElementById('searchPayee').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.payee-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const payeeName = card.getAttribute('data-payee') || '';
        let show = true;
        
        if (searchValue && !payeeName.includes(searchValue)) {
            show = false;
        }
        
        card.style.display = show ? 'block' : 'none';
        if (show) visibleCount++;
    });
    
    document.getElementById('visibleCount').textContent = visibleCount;
}

function clearFilters() {
    document.getElementById('searchPayee').value = '';
    filterCards();
}

// ============================================================
// AUTO-EXPAND FIRST PAYEE CARD
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    // Auto-expand first card
    const firstCard = document.querySelector('.payee-card');
    if (firstCard) {
        const header = firstCard.querySelector('.card-header-custom');
        if (header) {
            toggleTransactions(header);
        }
    }
});

// Live search with debounce
let searchTimeout;
document.getElementById('searchPayee').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(filterCards, 300);
});
</script>

<?php echo view('templates/myfooter.php'); ?>