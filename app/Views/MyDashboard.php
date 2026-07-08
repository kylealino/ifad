<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');

$month = date('F');
$year = '2025';
if ($month == 'January') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-02-01';
}elseif ($month == 'February') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-03-01';
}elseif ($month == 'March') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-04-01';
}elseif ($month == 'April') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-05-01';
}elseif ($month == 'May') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-06-01';
}elseif ($month == 'June') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-07-01';
}elseif ($month == 'July') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-08-01';
}elseif ($month == 'August') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-09-01';
}elseif ($month == 'September') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-10-01';
}elseif ($month == 'October') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-11-01';
}elseif ($month == 'November') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-12-01';
}elseif ($month == 'December') {
    $og_date_from = $year . '-01-01';
    $og_date_to = $year . '-12-31';
}


$query = $this->db->query("
SELECT 
    `full_name`
    
FROM 
    `myua_user` 
WHERE 
    `username` = '$this->cuser'"
);

$data = $query->getRowArray();
$full_name = $data['full_name'];

//total approved budget
$query = $this->db->query("
    SELECT
        SUM(
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_ps_dt WHERE project_id = a.`recid` ), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_mooe_dt WHERE project_id = a.`recid`), 0) +
            COALESCE((SELECT SUM(approved_budget) FROM tbl_saob_co_dt WHERE project_id = a.`recid`), 0)
        ) AS total_approved_budget
    FROM
        `tbl_saob_hd` a
    ORDER BY a.`recid`
");
$data = $query->getRowArray();
$total_approved_budget = $data['total_approved_budget'];

$totalquery = $this->db->query("
SELECT
    COALESCE(COUNT(recid), 0) AS total_transactions
FROM
    `tbl_ors_hd`
");

$totaldata = $totalquery->getRowArray();
$total_transactions = $totaldata['total_transactions'];

$pendingquery = $this->db->query("
SELECT
    COALESCE(COUNT(recid), 0) AS total_pending
FROM
    `tbl_ors_hd`
WHERE 
    `is_pending` = '1'
");

$pendingdata = $pendingquery->getRowArray();
$total_pending = $pendingdata['total_pending'];

$approvedquery = $this->db->query("
SELECT
    COALESCE(COUNT(recid), 0) AS total_approved_certa
FROM
    `tbl_ors_hd`
WHERE 
    `is_approved_certa` = '1'
");

$approveddata = $approvedquery->getRowArray();
$total_approved_certa = $approveddata['total_approved_certa'];

$disapprovedquery = $this->db->query("
SELECT
    COALESCE(COUNT(recid), 0) AS total_disapproved_certa
FROM
    `tbl_ors_hd`
WHERE 
    `is_disapproved_certa` = '1'
");

$disapproveddata = $disapprovedquery->getRowArray();
$total_disapproved_certa = $disapproveddata['total_disapproved_certa'];

//TRANSACTIONS PER MONTH

$query = $this->db->query("
SELECT 
    m.month_name,
    COALESCE(t.total_count, 0) AS total_count
FROM (
    SELECT 1 AS month_num, 'January' AS month_name UNION ALL
    SELECT 2, 'February' UNION ALL
    SELECT 3, 'March' UNION ALL
    SELECT 4, 'April' UNION ALL
    SELECT 5, 'May' UNION ALL
    SELECT 6, 'June' UNION ALL
    SELECT 7, 'July' UNION ALL
    SELECT 8, 'August' UNION ALL
    SELECT 9, 'September' UNION ALL
    SELECT 10, 'October' UNION ALL
    SELECT 11, 'November' UNION ALL
    SELECT 12, 'December'
) AS m
LEFT JOIN (
    SELECT 
        MONTH(ors_date) AS month_num,
        COUNT(recid) AS total_count
    FROM tbl_ors_hd
    WHERE funding_source LIKE '10%'
      AND YEAR(ors_date) = 2025
    GROUP BY MONTH(ors_date)
) AS t 
    ON m.month_num = t.month_num
ORDER BY m.month_num
");

$rows = $query->getResultArray();
$counts = array_column($rows, 'total_count');

$query = $this->db->query("
SELECT 
    MONTH(ors_date) AS month_num,
    MONTHNAME(ors_date) AS month_name,
    COUNT(recid) AS total_count
FROM tbl_ors_hd
WHERE funding_source LIKE '10%'
  AND YEAR(ors_date) = 2025
GROUP BY MONTH(ors_date)
ORDER BY month_num DESC
LIMIT 1;

");

$rows = $query->getRowArray();
$month_name = $rows['month_name'];

echo view('templates/myheader.php');
?>
<div class="container-fluid">
    <div class="page-titles mb-2">
        <div class="row mb-2 mt-0">
            <h4 class="fw-semibold mb-8">Dashboard</h4>

            <div class="col-sm-10">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="<?=site_url();?>"><i class="ti ti-home fs-5"></i></a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">Home</li>
                    <li class="breadcrumb-item" aria-current="page"><span class="form-label fw-bold">Dashboard</span></li>
                    </ol>
                </nav>
            </div>
            <div class="col-sm-2 text-end">
                <select name="" id="" class="form-select form-select-sm text-center">
                    <option value="General Fund">General Fund</option>
                    <option value="Project Fund">Project Fund</option>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8">
                <div class="card bg-primary-gt text-white overflow-hidden shadow-none">
                        <div class="card-body py-2">
                            <div class="row justify-content-between align-items-center">
                            <div class="col-sm-6">
                                <h5 class="fw-semibold mb-7 fs-5 text-white">Welcome back <?=$full_name;?>!</h5>
                                <p class="mb-9 opacity-75">
                                    Great to see you again — let’s make this month even more productive!
                                </p>
                                <button type="button" class="btn btn-danger">View Profile</button>
                            </div>
                            <div class="col-sm-5">
                                <div class="position-relative mb-n7 text-end">
                                <img src="<?=base_url('assets/images/backgrounds/welcome-bg2.png')?>" alt="flexy-img" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card bg-primary">
                <div class="card-body mb-2 py-2">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title text-white">Approved Budget</h4>
                        <div class="ms-auto">
                            <span class="btn round-48 fs-7 rounded-circle btn-info d-flex align-items-center justify-content-center">
                            ₱
                            </span>
                        </div>
                        </div>
                    <div class="mt-5">
                        <h2 class="fs-8 text-white mb-1" id="totalProjectBudget">Loading...</h2>
                        <span class="text-white text-opacity-50 ">Based on SAOB entry year 2025</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12 mb-3">
            <div class="card-group">
                <div class="card">
                    <div class="card-body py-2">
                        <span class="btn round-50 fs-6 text-info rounded-circle bg-info-subtle d-flex align-items-center justify-content-center">
                            <i class="ti ti-users"></i>
                        </span>
                        <h3 class="mt-1 pt-1 mb-0 fs-6">
                            <?=$total_transactions;?>
                            <span class="fs-2 ms-1 text-danger fw-medium">-9%</span>
                        </h3>
                        <h6 class="text-muted mb-0 fw-normal">Total Transactions</h6>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body py-2">
                    <span class="btn round-50 fs-6 text-warning rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center">
                        <i class="ti ti-package"></i>
                    </span>
                    <h3 class="mt-1 pt-1 mb-0 fs-6">
                        <?=$total_pending;?>
                        <span class="fs-2 ms-1 text-success fw-medium">+23%</span>
                    </h3>
                    <h6 class="text-muted mb-0 fw-normal">Pending</h6>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body py-2">
                    <span class="btn round-50 fs-6 text-danger rounded-circle bg-danger-subtle d-flex align-items-center justify-content-center">
                        <i class="ti ti-chart-bar"></i>
                    </span>
                    <h3 class="mt-1 pt-1 mb-0 fs-6 d-flex align-items-center">
                        <?=$total_approved_certa;?>
                        <span class="fs-2 ms-1 text-success fw-medium">+38%</span>
                    </h3>
                    <h6 class="text-muted mb-0 fw-normal">Approved</h6>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body py-2">
                    <span class="btn round-50 fs-6 text-success rounded-circle bg-success-subtle d-flex align-items-center justify-content-center">
                        <i class="ti ti-refresh"></i>
                    </span>
                    <h3 class="mt-1 pt-1 mb-0 fs-6">
                        <?=$total_disapproved_certa;?>
                        <span class="fs-2 ms-1 text-danger fw-medium">-12%</span>
                    </h3>
                    <h6 class="text-muted mb-0 fw-normal">Disapproved</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="row">
                <div class="col-lg-4 d-flex align-items-stretch">
                    <div class="card w-100">
                        <div class="card-body  py-2">
                            <span class="fs-5 fw-bolder">Percent of Utilization</span>
                            <div id="radialChart">
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body py-2">
                        <span class="fs-5 fw-bolder">Transactions per month</span>
                        <div id="chart-line-basic" class="mx-n3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card bg-warning">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title text-white">Obligation To Date</h4>
                        <div class="ms-auto">
                            <span class="btn round-48 fs-7 rounded-circle btn-info d-flex align-items-center justify-content-center">
                            ₱
                            </span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h2 class="fs-8 text-white mb-0" id="todateGrandTotal">Loading...</h2>
                        <span class="text-white text-opacity-50">Based on ORS entry year 2025<sspan>
                    </div>
                </div>
            </div>
            <div class="card bg-info">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title text-white">Unobligated Balance</h4>
                        <div class="ms-auto">
                            <span class="btn round-48 fs-7 rounded-circle btn-primary d-flex align-items-center justify-content-center">
                            ₱
                            </span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h2 class="fs-8 text-white mb-0" id="totalUnobligated">Loading...</h2>
                        <span class="text-white text-opacity-50">Year 2025<sspan>
                    </div>
                </div>
            </div>
        </div>


 

    </div>
    <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfModalLabel">PDF Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe id="pdfFrame" src="" style="width: 100%; height: 80vh;" frameborder="0"></iframe>
            </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div id="pdfContainer" style="display:none; width: 100%; height: 600px; border: 1px solid #ccc; position: relative;">
        <iframe id="pdfFrame" style="width: 100%; height: 100%; border: none; display: none;"></iframe>

        <div class="text-white fw-bolder" id="pdfPlaceholder" style="
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.2rem;
            background:rgb(156, 147, 147);
        ">
            No PDF loaded yet. Please select month and year.
        </div>
    </div>

    <input type="hidden" name="month" id="month" value="<?=$month_name;?>">
    <input type="hidden" name="year" id="year">
    <h2 class="fs-8 mb-0" id="totalPercent" style="display:none;">Loading...</h2>
    

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?=base_url('assets/libs/apexcharts/dist/apexcharts.min.js')?>"></script>
<script src="<?=base_url('assets/js/report/mysaobreport.js?v=3');?>"></script>
<script src="<?=base_url('assets/js/dashboards/dashboard2.js')?>"></script>
<script src="<?=base_url('assets/js/apex-chart/apex.radial.init.js')?>"></script>

<script>
"use strict";

const pdfUrl = "<?= base_url('mysaobrpt?meaction=SAOB-PDF') ?>";
let radialChart = null; // Global variable to hold the chart instance for updating

// --- SAOB Initialization Functions ---

function isSaobReady() {
    return document.getElementById("month") &&
           document.getElementById("year") &&
           document.getElementById("pdfFrame") &&
           window.__mysys_saob_rpt_ent?.__saob_print;
}

function runSaob() {
    const monthEl = document.getElementById("month");
    const yearEl = document.getElementById("year");

    // Setting values that will be used by the SAOB report backend
    monthEl.value = "<?php echo $month_name; ?>";
    yearEl.value = "2025";

    // Call the external SAOB print function, which sets data in the session
    __mysys_saob_rpt_ent.__saob_print(pdfUrl);
}

function initSaobPDF() {
    function tryInit() {
        if (!isSaobReady()) {
            setTimeout(tryInit, 100); // keep checking until DOM elements and external JS are ready
            return;
        }

        // 1. First call: sets session data in the backend
        runSaob();

        // 2. Second call after 500ms: Renders PDF using session data (and ensures data is ready for AJAX poll)
        setTimeout(() => {
            runSaob();
            console.log("✅ SAOB PDF rendered after session is ready.");
            
            // 3. Start polling for the session data required for the dashboard
            pollForSessionData();
        }, 500);
    }
    
    tryInit();
}

// --- ApexCharts Configuration Function (Now defined as a reusable function) ---

function createRadialChart(percentValue) {
    var options_gradient = {
        series: [percentValue], // Use the dynamically retrieved percentage value
        chart: {
            fontFamily: "inherit",
            height: 250,
            type: "radialBar",
            toolbar: {
                show: false,
            },
        },
        plotOptions: {
            radialBar: {
                startAngle: -135,
                endAngle: 225,
                hollow: {
                    margin: 0,
                    size: "70%",
                    background: "#fff",
                    position: "front",
                    dropShadow: {
                        enabled: true,
                        top: 3,
                        left: 0,
                        blur: 4,
                        opacity: 0.24,
                    },
                },
                track: {
                    background: "#fff",
                    strokeWidth: "67%",
                    margin: 0,
                    dropShadow: {
                        enabled: true,
                        top: -3,
                        left: 0,
                        blur: 4,
                        opacity: 0.35,
                    },
                },
                dataLabels: {
                    show: true,
                    name: {
                        offsetY: -10,
                        show: true,
                        color: "#615dff",
                        fontSize: "17px",
                    },
                    value: {
                        formatter: function (val) {
                            // Display the value as an integer (percentage)
                            return parseFloat(val).toFixed(2);
                        },
                        color: "#6610f2",
                        fontSize: "34px",
                        show: true,
                    },
                },
            },
        },
        fill: {
            type: "gradient",
            gradient: {
                shade: "dark",
                type: "horizontal",
                shadeIntensity: 0.5,
                gradientToColors: ["#615dff"],
                inverseColors: true,
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 100],
            },
        },
        stroke: {
            lineCap: "round",
        },
        labels: ["Percent"],
    };
    
    // Check if the chart instance already exists
    if (radialChart) {
        // If it exists, just update the series data
        radialChart.updateSeries([percentValue]);
    } else {
        // If it doesn't exist, create and render it
        const chartElement = document.querySelector("#radialChart");
        if(chartElement) {
            radialChart = new ApexCharts(chartElement, options_gradient);
            radialChart.render();
        } else {
             console.error("Error: Chart container #radialChart not found.");
        }
    }
}


// --- AJAX Poll for Session Data ---

function pollForSessionData(attempt = 0, maxAttempts = 10) {
    $.ajax({
        url: '<?= base_url('dashboard/saob-session-data') ?>',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            // Check if data is valid and budget is non-zero
            if (data && data.grandtotal_total_project_budget > 0) {
                
                // Get the raw percentage value
                const rawPercentage = parseFloat(data.grandtotal_grand_percentage_minus) || 0; 
                
                // Update Text Display Fields
                $('#totalProjectBudget').text('₱' + Number(data.grandtotal_total_project_budget).toLocaleString(undefined, {minimumFractionDigits: 2}));
                $('#todateGrandTotal').text('₱' + Number(data.grandtotal_todate_grand_total).toLocaleString(undefined, {minimumFractionDigits: 2}));
                $('#totalUnobligated').text('₱' + Number(data.grandtotal_grand_unobligated).toLocaleString(undefined, {minimumFractionDigits: 2}));
                
                // Update the Percentage Display with currency format
                $('#totalPercent').text('P' + rawPercentage.toLocaleString(undefined, {minimumFractionDigits: 2}));

                // --- CRITICAL STEP: Initialize/Update the Chart ---
                // We use the raw number (not P-formatted) for the chart series
                createRadialChart(rawPercentage);

                console.log('✅ SAOB session data loaded and chart updated.');
            } else if (attempt < maxAttempts) {
                // Retry after 1 second if data not ready
                setTimeout(() => pollForSessionData(attempt + 1, maxAttempts), 300);
            } else {
                console.warn('SAOB session data not available after max attempts.');
                // Optionally render the chart with 0% if data never loads
                createRadialChart(0);
            }
        },
        error: function() {
            console.error('Failed to load SAOB session data');
            // Optionally render the chart with 0% on error
            createRadialChart(0);
        }
    });
}

// Start the entire process once the DOM is fully loaded
$(document).ready(function() {
    initSaobPDF();
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Get PHP data (array of 12 numbers)
    var monthlyCounts = <?php echo json_encode($counts); ?>;

    // Chart configuration
    var options_line = {
        series: [{
            name: "Transactions",
            data: monthlyCounts
        }],
        chart: {
            height: 300,
            type: 'line',
            toolbar: { show: false }
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        colors: ['#615dff'],
        dataLabels: {
            enabled: true
        },
        xaxis: {
            categories: [
                "January","February","March","April","May","June",
                "July","August","September","October","November","December"
            ],
        },
        yaxis: {
            min: 0
        },
        grid: {
            borderColor: "#f1f1f1"
        },
        title: {
            align: "left",
            style: { fontSize: "16px" }
        }
    };

    // Render chart in the correct container
    var chart = new ApexCharts(document.querySelector("#chart-line-basic"), options_line);
    chart.render();
});
</script>


<?php
    echo view('templates/myfooter.php');
?>
