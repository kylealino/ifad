
<?php 
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');

  $query = $this->db->query("
  SELECT 
      `full_name`, 
      `division`,
      `section`, 
      `position`,
      `username`, 
      `hash_password`,
      `hash_value`
  FROM 
      `myua_user` 
  WHERE 
      `username` = '$this->cuser'"
  );

  $data = $query->getRowArray();
  $full_name = $data['full_name'];
  $position = $data['position'];
  $section = $data['section'];
  $division = $data['division'];

?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">

<head>
  <!-- Required meta tags -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- Favicon icon-->
  <link rel="shortcut icon" type="image/png" href="<?=base_url('assets/images/logos/fnrilogo.png')?>" />

  <!-- Core Css -->
  <link rel="stylesheet" href="<?=base_url('assets/css/styles.css')?>" />

  <title>FODS</title>
  <style>
/* ===================== */
/* 🎨 SIDEBAR REDESIGN */
/* ===================== */
.left-sidebar {
  background: linear-gradient(180deg, #0a1a3a 0%, #1e3260 100%);
  box-shadow: 4px 0 20px rgba(0,0,0,0.2);
  border-right: 1px solid rgba(255,255,255,0.05);
}

.brand-logo {
  padding: 16px 20px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}

.brand-logo a {
  color: white;
  font-weight: 700;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  gap: 10px;
}

.sidebar-nav {
  padding: 10px 0;
}

/* Section Titles */
.nav-small-cap span {
  color: rgba(255,255,255,0.4);
  font-size: 0.65rem;
  letter-spacing: 1px;
}

/* Menu Items */
.sidebar-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 18px;
  color: rgba(255,255,255,0.75);
  border-radius: 10px;
  margin: 4px 10px;
  transition: all 0.25s ease;
}

/* Hover Effect */
.sidebar-link:hover {
  background: rgba(255,255,255,0.08);
  color: #fff;
  transform: translateX(4px);
}

/* Active Item */
.sidebar-item.active .sidebar-link {
  background: linear-gradient(90deg, #0f6f6f, #148a8a);
  color: #fff;
  box-shadow: 0 4px 12px rgba(15,111,111,0.4);
}

/* Icons */
.sidebar-link i {
  font-size: 1.1rem;
  opacity: 0.8;
}

/* ===================== */
/* 🔝 TOPBAR REDESIGN */
/* ===================== */
.topbar {
  background: rgba(255,255,255,0.9);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(0,0,0,0.05);
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.navbar {
  padding: 10px 20px;
}

/* Sidebar Toggle */
#headerCollapse i {
  font-size: 1.4rem;
  color: #1e293b;
  transition: 0.2s;
}

#headerCollapse:hover i {
  color: #0f6f6f;
}

/* ===================== */
/* 👤 USER PROFILE */
/* ===================== */
.user-profile-img img {
  border: 2px solid #0f6f6f;
  transition: 0.3s;
}

.user-profile-img img:hover {
  transform: scale(1.05);
}

/* Dropdown */
.dropdown-menu {
  border-radius: 12px;
  border: none;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.profile-dropdown h5 {
  color: #1e293b;
}

.profile-dropdown span {
  color: #64748b;
}

/* Logout Button */
.btn-outline-primary {
  border-radius: 8px;
  border-color: #0f6f6f;
  color: #0f6f6f;
}

.btn-outline-primary:hover {
  background: #0f6f6f;
  color: white;
}

/* ===================== */
/* ✨ SCROLLBAR CLEAN */
/* ===================== */
/* Sidebar scroll */
.left-sidebar.with-vertical {
    height: 100vh;          /* full viewport height */
    overflow-y: auto;       /* enable vertical scrolling */
    overflow-x: hidden;     /* hide horizontal scroll if any */
}

/* White scrollbar for Webkit browsers (Chrome, Edge, Safari) */
.left-sidebar.with-vertical::-webkit-scrollbar {
    width: 8px;
}

.left-sidebar.with-vertical::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.05);
    border-radius: 4px;
}

.left-sidebar.with-vertical::-webkit-scrollbar-thumb {
    background-color: white;
    border-radius: 4px;
    border: 2px solid rgba(255,255,255,0.1);
}

/* Firefox scrollbar */
.left-sidebar.with-vertical {
    scrollbar-width: thin;
    scrollbar-color: white rgba(255,255,255,0.05);
}


  </style>
</head>
<body>
  <!-- Preloader -->
  <div class="preloader">
    <img src="<?=base_url('assets/images/logos/preloader.svg')?>" alt="loader" class="lds-ripple img-fluid" />
  </div>
  <div id="main-wrapper">
    <!-- Sidebar Start -->
    <aside class="left-sidebar with-vertical">
      <div><!-- ---------------------------------- -->
        <!-- Start Vertical Layout Sidebar -->
        <!-- ---------------------------------- -->
        <div class="brand-logo d-flex align-items-center justify-content-between">
          <a href="<?=site_url();?>mydashboard" class="text-nowrap logo-img">
            <img src="<?=base_url('assets/images/logos/fnrilogo.png')?>" style="width: 35px; height: auto;"/>
            FODS
          </a> 
          <a href="javascript:void(0)" class="sidebartoggler ms-auto text-decoration-none fs-5 d-block d-xl-none">
            <i class="ti ti-x"></i>
          </a>
        </div>


        <nav class="sidebar-nav scroll-sidebar" data-simplebar style="height: 100vh !important;">
          <ul id="sidebarnav">
            <!-- ---------------------------------- -->
            <!-- Home -->
            <!-- ---------------------------------- -->
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Home</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="<?=site_url();?>mydashboard" aria-expanded="false">
                <span>
                  <i class="ti ti-aperture"></i>
                </span>
                <span class="hide-menu">Dashboard</span>
              </a>
            </li>
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Maintenance</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="<?=site_url();?>mypayee?meaction=MAIN" aria-expanded="false">
                <span>
                  <i class="ti ti-user-check"></i>
                </span>
                <span class="hide-menu">Payee</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="<?=site_url();?>myproducts?meaction=MAIN" aria-expanded="false">
                <span>
                  <i class="ti ti-list-details"></i>
                </span>
                <span class="hide-menu">Supplies</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="<?=site_url();?>myproject?meaction=MAIN" aria-expanded="false">
                <span>
                  <i class="ti ti-align-box-left-top"></i>
                </span>
                <span class="hide-menu">Projects</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="<?=site_url();?>myuacs?meaction=MAIN" aria-expanded="false">
                <span>
                  <i class="ti ti-align-left"></i>
                </span>
                <span class="hide-menu">UACS</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="<?=site_url();?>myua?meaction=MAIN" aria-expanded="false">
                <span>
                  <i class="ti ti-user"></i>
                </span>
                <span class="hide-menu">User Management</span>
              </a>
            </li>
            <!-- <li class="sidebar-item">
              <a class="sidebar-link" href="<?=site_url();?>myaccount?meaction=MAIN" aria-expanded="false">
                <span>
                  <i class="ti ti-user"></i>
                </span>
                <span class="hide-menu">Account Settings</span>
              </a>
            </li> -->
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">BUDGET</span>
            </li>

            </li>      
            <li class="sidebar-item">
              <a class="sidebar-link sidebar-link" href="<?=site_url();?>mybudgetallotment?meaction=MAIN" aria-expanded="false">
                <span class="rounded-3">
                  <i class="ti ti-cash"></i>
                </span>
                <span class="hide-menu">Budget Allotment</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link sidebar-link" href="<?=site_url();?>mybudgetapproval?meaction=MAIN" aria-expanded="false">
                <span class="rounded-3">
                  <i class="ti ti-checklist"></i>
                </span>
                <span class="hide-menu">Budget Approval</span>
              </a>
            </li>
            <!-- ---------------------------------- -->
            <!-- OTHER -->
            <!-- ---------------------------------- -->
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">ORS</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link sidebar-link" href="<?=site_url();?>myors?meaction=MAIN" aria-expanded="false">
                <span class="rounded-3">
                  <i class="ti ti-clipboard-text"></i>
                </span>
                <span class="hide-menu">ORS Entry</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link sidebar-link" href="<?=site_url();?>myorsapproval?meaction=MAIN" aria-expanded="false">
                <span class="rounded-3">
                  <i class="ti ti-clipboard-check"></i>
                </span>
                <span class="hide-menu">ORS Approval</span>
              </a>
            </li>
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">BURS</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link sidebar-link" href="<?=site_url();?>myburs?meaction=MAIN" aria-expanded="false">
                <span class="rounded-3">
                  <i class="ti ti-clipboard-text"></i>
                </span>
                <span class="hide-menu">BURS Entry</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link sidebar-link" onclick="event.preventDefault();"  aria-expanded="false">
                <span class="rounded-3">
                  <i class="ti ti-clipboard-check"></i>
                </span>
                <span class="hide-menu">BURS Approval</span>
              </a>
            </li>
            <!-- <li class="sidebar-item">
              <a class="sidebar-link sidebar-link" href="<?=site_url();?>myburs?meaction=MAIN" aria-expanded="false">
                <span class="rounded-3">
                  <i class="ti ti-pencil"></i>
                </span>
                <span class="hide-menu">BURS</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link sidebar-link" href="<?=site_url();?>mybursapproval?meaction=MAIN" aria-expanded="false">
                <span class="rounded-3">
                  <i class="ti ti-pencil"></i>
                </span>
                <span class="hide-menu">BURS Approval</span>
              </a>
            </li> -->
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Reports</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link sidebar-link" href="<?=site_url();?>mysaobrpt?meaction=MAIN" aria-expanded="false">
                <span class="rounded-3">
                  <i class="ti ti-file"></i>
                </span>
                <span class="hide-menu">SAOB</span>
              </a>
            </li> 
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Procurement</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link sidebar-link" href="<?=site_url();?>myppmp?meaction=MAIN" aria-expanded="false">
                <span class="rounded-3">
                  <i class="ti ti-list-numbers"></i>
                </span>
                <span class="hide-menu">PPMP</span>
              </a>
            </li> 
            <li class="sidebar-item">
              <a class="sidebar-link sidebar-link" href="<?=site_url();?>mypr?meaction=PR-MAIN" aria-expanded="false">
                <span class="rounded-3">
                  <i class="ti ti-playlist-add"></i>
                </span>
                <span class="hide-menu">PR</span>
              </a>
            </li> 
            <li class="sidebar-item">
              <a class="sidebar-link sidebar-link" href="<?=site_url();?>myabstract?meaction=MAIN" aria-expanded="false">
                <span class="rounded-3">
                  <i class="ti ti-mist"></i>
                </span>
                <span class="hide-menu">ABSTRACT</span>
              </a>
            </li> 
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Accounting</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link sidebar-link" href="<?=site_url();?>mydisbursement?meaction=MAIN" aria-expanded="false">
                <span class="rounded-3">
                  <i class="ti ti-file"></i>
                </span>
                <span class="hide-menu">Disbursement</span>
              </a>
            </li> 
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Cashier</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link sidebar-link" href="<?=site_url();?>mylddapada?meaction=MAIN" aria-expanded="false">
                <span class="rounded-3">
                  <i class="ti ti-cash"></i>
                </span>
                <span class="hide-menu">LDDAP-ADA</span>
              </a>
            </li> 
            <li class="sidebar-item">
              <a class="sidebar-link sidebar-link" href="<?=site_url();?>myrci?meaction=MAIN" aria-expanded="false">
                <span class="rounded-3">
                  <i class="ti ti-file"></i>
                </span>
                <span class="hide-menu">RCI</span>
              </a>
            </li> 
          </ul>
        </nav>

        <!-- ---------------------------------- -->
        <!-- Start Vertical Layout Sidebar -->
        <!-- ---------------------------------- -->
      </div>
    </aside>
    <!--  Sidebar End -->
    <div class="page-wrapper">
      <!--  Header Start -->
      <header class="topbar">
        <div class="with-vertical"><!-- ---------------------------------- -->
          <!-- Start Vertical Layout Header -->
          <!-- ---------------------------------- -->
          <nav class="navbar navbar-expand-lg p-0">
            <ul class="navbar-nav">
              <li class="nav-item nav-icon-hover-bg rounded-circle ms-n2">
                <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                  <i class="ti ti-menu-2"></i>
                </a>
              </li>
            </ul>

            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-center">
              <!-- ------------------------------- -->
              <!-- start language Dropdown -->
              <!-- ------------------------------- -->

              <!-- ------------------------------- -->
              <!-- start profile Dropdown -->
              <!-- ------------------------------- -->
              <li class="nav-item dropdown">
                <a class="nav-link pe-0" href="javascript:void(0)" id="drop1" aria-expanded="false">
                  <div class="d-flex align-items-center">
                    <div class="user-profile-img">
                      <img src="<?=base_url('assets/images/profile/user-1.jpg')?>" class="rounded-circle" width="35" height="35" alt="flexy-img" />
                    </div>
                  </div>
                </a>
                <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop1">
                  <div class="profile-dropdown position-relative" data-simplebar>
                    <div class="py-3 px-7 pb-0">
                      <h5 class="mb-0 fs-5 fw-semibold">User Profile</h5>
                    </div>
                    <div class="d-flex align-items-center py-9 mx-7 border-bottom">
                      <img src="<?=base_url('assets/images/profile/user-1.jpg')?>" class="rounded-circle" width="80" height="80" alt="flexy-img" />
                      <div class="ms-3">
                        <h5 class="mb-1 fs-4"><?=$this->cuser;?></h5>
                        <span class="mb-1 d-block"><?=$full_name;?></span>
                        <span class="mb-1 d-block fs-2"><?=$position;?></span>
                        <span class="mb-1 d-block fs-2"><?=$division . ' - ' . $section;?></span>
                      </div>
                    </div>
                    <div class="message-body">
                      <a href="" class="py-8 px-7 mt-8 d-flex align-items-center">
                        <span class="d-flex align-items-center justify-content-center text-bg-light rounded-1 p-6">
                          <img src="<?=base_url('assets/images/svgs/icon-account.svg')?>" alt="flexy-img" width="24" height="24" />
                        </span>
                        <div class="w-100 ps-3">
                          <h6 class="mb-0 fs-4 lh-base">My Profile</h6>
                          <span class="fs-3 d-block text-body-secondary">Account Settings</span>
                        </div>
                      </a>
                      <a href="" class="py-8 px-7 d-flex align-items-center">
                        <span class="d-flex align-items-center justify-content-center text-bg-light rounded-1 p-6">
                          <img src="<?=base_url('assets/images/svgs/icon-inbox.svg')?>" alt="flexy-img" width="24" height="24" />
                        </span>
                        <div class="w-100 ps-3">
                          <h6 class="mb-0 fs-4 lh-base">My Inbox</h6>
                          <span class="fs-3 d-block text-body-secondary">Messages & Emails</span>
                        </div>
                      </a>
                      <a href="" class="py-8 px-7 d-flex align-items-center">
                        <span class="d-flex align-items-center justify-content-center text-bg-light rounded-1 p-6">
                          <img src="<?=base_url('assets/images/svgs/icon-tasks.svg')?>" alt="flexy-img" width="24" height="24" />
                        </span>
                        <div class="w-100 ps-3">
                          <h6 class="mb-0 fs-4 lh-base">My Task</h6>
                          <span class="fs-3 d-block text-body-secondary">To-do and Daily Tasks</span>
                        </div>
                      </a>
                    </div>
                    <div class="d-grid py-4 px-7 pt-8">
                      <form action="<?= site_url('mylogout'); ?>" method="post" novalidate>
                          <!-- Add a CSRF token for security -->
                          <?= csrf_field(); ?>
                          
                          <button type="submit" class="btn btn-outline-primary">Log Out</button>
                      </form>
                    </div>
                  </div>
                </div>
              </li>
              <!-- ------------------------------- -->
              <!-- end profile Dropdown -->
              <!-- ------------------------------- -->
            </ul>
          </nav>

        </div>
      </header>
      <!--  Header End -->
<script>
  const currentUrl = window.location.href;
  document.querySelectorAll('.sidebar-item a').forEach(link => {
    if (link.href === currentUrl) {
      link.parentElement.classList.add('active');
    }
  });
</script>
      <div class="body-wrapper">