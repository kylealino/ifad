<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');

echo view('templates/myheader.php');
?>

<style>
html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f1f5f9;
}

/* Wrapper */
.dashboard-wrapper {
    min-height: calc(100vh - 60px);
    padding: 25px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

.dashboard-container {
    width: 100%;
    max-width: 1200px;
    background: #ffffff;
    border-radius: 14px;
    padding: 25px;
    box-shadow: 0 10px 30px rgba(2, 6, 23, 0.08);
    border: 1px solid #e2e8f0;
}

/* ===================== */
/* 🔥 HEADER */
/* ===================== */
.dev-header {
    text-align: center;
    margin-bottom: 30px;
}

.dev-header .header-icon {
    font-size: 32px;
    margin-bottom: 10px;
    animation: spin 3s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.dev-header h2 {
    font-size: 22px;
    font-weight: 700;
    color: #0a1a3a;
    margin-bottom: 8px;
}

.dev-header p {
    font-size: 14px;
    color: #64748b;
    max-width: 520px;
    margin: auto;
}

/* Status badge */
.dev-badge {
    display: inline-block;
    margin-top: 10px;
    background: rgba(15,111,111,0.1);
    color: #0f6f6f;
    font-size: 12px;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
}

/* ===================== */
/* ✨ SKELETON */
/* ===================== */
.skeleton {
    background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 37%, #e2e8f0 63%);
    background-size: 400% 100%;
    animation: shimmer 1.4s infinite;
    border-radius: 12px;
}

@keyframes shimmer {
    0% { background-position: -400px 0; }
    100% { background-position: 400px 0; }
}

/* Banner */
.banner-row {
    display: flex;
    gap: 18px;
    margin-bottom: 25px;
}

.banner {
    flex: 1;
    height: 100px;
}

/* KPI */
.kpi-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 25px;
}

.kpi-card {
    height: 70px;
    position: relative;
}

/* Lower */
.lower-row {
    display: grid;
    grid-template-columns: 1.2fr 2fr 1fr 1fr;
    gap: 18px;
}

.box {
    height: 200px;
}

/* Fake labels */
.fake-label {
    position: absolute;
    top: 10px;
    left: 12px;
    font-size: 11px;
    color: #64748b;
}

/* Footer note */
.dev-note {
    text-align: center;
    margin-top: 25px;
    font-size: 12px;
    color: #94a3b8;
}
</style>

<div class="dashboard-wrapper">
    <div class="dashboard-container">

        <!-- 🔥 HEADER -->
        <div class="dev-header">
            <div class="header-icon">⚙️</div>
            <h2>FODS Dashboard (Under Development Testing)</h2>
            <p>
                The Financial Obligation and Disbursement dashboard is currently being enhanced 
                to provide real-time insights, analytics, and reporting tailored to your division.
            </p>
            <div class="dev-badge">System Module in Progress</div>
        </div>

        <!-- Banner -->
        <div class="banner-row">
            <div class="skeleton banner"></div>
            <div class="skeleton banner"></div>
        </div>

        <!-- KPI -->
        <div class="kpi-row">
            <div class="skeleton kpi-card"><span class="fake-label">Total Budget</span></div>
            <div class="skeleton kpi-card"><span class="fake-label">Obligations</span></div>
            <div class="skeleton kpi-card"><span class="fake-label">Disbursements</span></div>
            <div class="skeleton kpi-card"><span class="fake-label">Balance</span></div>
        </div>

        <!-- Lower Section -->
        <div class="lower-row">
            <div class="skeleton box"></div>
            <div class="skeleton box"></div>
            <div class="skeleton box"></div>
            <div class="skeleton box"></div>
        </div>

        <!-- Footer Note -->
        <div class="dev-note">
            Expected features: Budget utilization charts, ORS/BURS tracking, and real-time financial summaries.
        </div>

    </div>
</div>

<?php echo view('templates/myfooter.php'); ?>