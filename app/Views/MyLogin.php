<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FODS - Financial Obligation and Disbursement System</title>
  <link rel="shortcut icon" type="image/png" href="<?=base_url('assets/images/logos/fnrilogo.png')?>" />
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Toastr CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
  <!-- Toastr JS (jQuery is required) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  <style>
    :root {
      --primary-navy: #0a1a3a;
      --primary-navy-light: #1e3260;
      --primary-navy-soft: #2c3e6e;
      --accent-teal: #0f6f6f;
      --accent-teal-light: #148a8a;
      --accent-gold: #c6a13b;
      --neutral-white: #ffffff;
      --neutral-gray-50: #f8fafc;
      --neutral-gray-100: #f1f4f9;
      --neutral-gray-200: #e9edf2;
      --neutral-gray-300: #d0d7e2;
      --text-primary: #1e293b;
      --text-secondary: #475569;
      --text-tertiary: #64748b;
      --border-light: rgba(0, 0, 0, 0.05);
      --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05);
      --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.08);
      --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      overflow: hidden;
      background: var(--primary-navy);
    }
    
    #main-wrapper {
      position: relative;
      width: 100vw;
      height: 100vh;
      overflow: hidden;
      display: flex;
      flex-direction: row-reverse;
    }
    
    /* Interactive Canvas Background */
    #interactive-bg {
      position: absolute;
      top: 0;
      left: 0;
      width: 72%;
      height: 100%;
      z-index: 0;
    }
    
    /* Right Side - Login Container */
    .login-container {
      position: relative;
      z-index: 10;
      width: 28%;
      min-width: 360px;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--neutral-white);
      box-shadow: -8px 0 32px rgba(0, 0, 0, 0.1);
    }
    
    .auth-card {
      width: 100%;
      max-width: 300px;
      padding: 2rem 1.5rem;
    }
    
    /* Logo Section */
    .logo-section {
      margin-bottom: 2rem;
      text-align: center;
    }
    
    .institution-logo {
      width: 70px;
      height: 70px;
      margin: 0 auto 0.75rem;
      background: var(--neutral-gray-50);
      border-radius: 12px;
      padding: 8px;
      border: 1px solid var(--neutral-gray-200);
    }
    
    .institution-logo img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
    
    .system-name {
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--text-primary);
      letter-spacing: -0.01em;
      margin-bottom: 0.15rem;
    }
    
    .system-subtitle {
      font-size: 0.65rem;
      color: var(--text-tertiary);
      font-weight: 500;
      letter-spacing: 0.02em;
      text-transform: uppercase;
    }
    
    .divider {
      width: 40px;
      height: 2px;
      background: linear-gradient(90deg, transparent, var(--accent-teal), transparent);
      margin: 0.75rem auto 0;
    }
    
    /* Form Elements */
    .form-group {
      margin-bottom: 1.25rem;
    }
    
    .form-label {
      display: block;
      color: var(--text-secondary);
      font-size: 0.7rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.03em;
      margin-bottom: 0.35rem;
    }
    
    .input-wrapper {
      position: relative;
    }
    
    .input-icon {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--accent-teal);
      font-size: 0.9rem;
      opacity: 0.6;
    }
    
    .form-control {
      width: 100%;
      padding: 0.6rem 0.6rem 0.6rem 2.2rem;
      font-size: 0.8rem;
      border: 1px solid var(--neutral-gray-300);
      border-radius: 8px;
      background: var(--neutral-white);
      color: var(--text-primary);
      transition: all 0.2s ease;
    }
    
    .form-control:focus {
      outline: none;
      border-color: var(--accent-teal);
      box-shadow: 0 0 0 3px rgba(15, 111, 111, 0.1);
    }
    
    .options-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.5rem;
    }
    
    .checkbox-wrapper {
      display: flex;
      align-items: center;
      gap: 0.4rem;
    }
    
    .checkbox-wrapper input[type="checkbox"] {
      width: 14px;
      height: 14px;
      accent-color: var(--accent-teal);
      cursor: pointer;
    }
    
    .checkbox-wrapper label {
      font-size: 0.7rem;
      color: var(--text-secondary);
      cursor: pointer;
    }
    
    .forgot-link {
      font-size: 0.7rem;
      color: var(--accent-teal);
      text-decoration: none;
      font-weight: 500;
    }
    
    .forgot-link:hover {
      text-decoration: underline;
    }
    
    /* Sign In Button */
    .btn-signin {
      width: 100%;
      padding: 0.7rem;
      background: var(--primary-navy);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 0.8rem;
      font-weight: 600;
      letter-spacing: 0.02em;
      cursor: pointer;
      transition: all 0.2s ease;
      margin-bottom: 1rem;
    }
    
    .btn-signin:hover {
      background: var(--primary-navy-light);
      transform: translateY(-1px);
      box-shadow: var(--shadow-md);
    }
    
    /* Agency Footer */
    .agency-footer {
      margin-top: 2rem;
      padding-top: 1rem;
      border-top: 1px solid var(--neutral-gray-200);
      text-align: center;
    }
    
    .agency-footer small {
      font-size: 0.6rem;
      color: var(--text-tertiary);
      display: block;
      line-height: 1.4;
    }
    
    /* Top Left Agency Logo */
    .agency-badge {
      position: absolute;
      top: 20px;
      left: 20px;
      z-index: 10;
      display: flex;
      align-items: center;
      gap: 10px;
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(10px);
      padding: 6px 14px;
      border-radius: 40px;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .agency-badge img {
      width: 24px;
      height: 24px;
      border-radius: 6px;
    }
    
    .agency-badge span {
      color: white;
      font-weight: 500;
      font-size: 0.7rem;
      letter-spacing: 0.02em;
      opacity: 0.9;
    }
    
    /* INTERACTIVE DATA VISUALIZATIONS */
    .viz-container {
      position: absolute;
      top: 0;
      left: 0;
      width: 72%;
      height: 100%;
      z-index: 5;
      pointer-events: none;
      perspective: 1000px;
    }
    
    /* Interactive Card Design */
    .data-card {
      position: absolute;
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: 16px;
      padding: 16px;
      color: white;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
      min-width: 200px;
      pointer-events: auto;
      cursor: pointer;
      transition: all 0.4s cubic-bezier(0.2, 0.9, 0.3, 1.1);
      animation: cardFloat 6s ease-in-out infinite;
      transform-style: preserve-3d;
    }
    
    .data-card:hover {
      transform: translateY(-10px) scale(1.05) rotateX(2deg);
      border-color: var(--accent-teal);
      box-shadow: 0 30px 50px rgba(15, 111, 111, 0.3);
      background: rgba(255, 255, 255, 0.08);
    }
    
    .data-card:active {
      transform: translateY(-5px) scale(1.02);
    }
    
    @keyframes cardFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-5px); }
    }
    
    /* Card hover reveal effects */
    .data-card:hover .card-header i {
      transform: scale(1.2) rotate(5deg);
      color: var(--accent-gold);
    }
    
    .data-card:hover .bar {
      opacity: 1;
      filter: brightness(1.2);
    }
    
    .data-card:hover .trend-line {
      stroke: var(--accent-gold);
      stroke-width: 3;
    }
    
    .data-card:hover .metric-value {
      color: var(--accent-gold);
      transform: translateX(2px);
    }
    
    .card-header {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 12px;
      padding-bottom: 8px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      transition: all 0.3s ease;
    }
    
    .card-header i {
      color: var(--accent-teal);
      font-size: 1rem;
      opacity: 0.8;
      transition: all 0.3s ease;
    }
    
    .card-header span {
      font-size: 0.7rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: rgba(255, 255, 255, 0.7);
      transition: color 0.3s ease;
    }
    
    .data-card:hover .card-header span {
      color: white;
    }
    
    /* Animated Bar Chart */
    .chart-bars {
      display: flex;
      align-items: flex-end;
      gap: 12px;
      height: 100px;
      margin: 12px 0;
    }
    
    .bar-container {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
    }
    
    .bar {
      width: 100%;
      background: linear-gradient(180deg, var(--accent-teal-light), var(--accent-teal));
      border-radius: 4px 4px 0 0;
      opacity: 0.7;
      transition: all 0.5s cubic-bezier(0.2, 0.9, 0.3, 1.1);
      animation: barPulse 2s ease-in-out infinite;
    }
    
    @keyframes barPulse {
      0%, 100% { opacity: 0.7; }
      50% { opacity: 0.9; }
    }
    
    .data-card:hover .bar {
      animation: barWave 0.5s ease-out;
    }
    
    @keyframes barWave {
      0% { transform: scaleY(1); }
      50% { transform: scaleY(1.1); }
      100% { transform: scaleY(1); }
    }
    
    .bar-label {
      font-size: 0.55rem;
      color: rgba(255, 255, 255, 0.5);
      text-transform: uppercase;
      transition: color 0.3s ease;
    }
    
    .data-card:hover .bar-label {
      color: rgba(255, 255, 255, 0.9);
    }
    
    /* Animated Line Graph */
    .graph-container {
      width: 100%;
      height: 70px;
      margin: 10px 0;
      position: relative;
      overflow: hidden;
    }
    
    .trend-line {
      stroke: var(--accent-teal);
      stroke-width: 2;
      fill: none;
      stroke-linecap: round;
      filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
      transition: all 0.3s ease;
      stroke-dasharray: 1000;
      stroke-dashoffset: 0;
    }
    
    .data-card:hover .trend-line {
      animation: drawLine 1.5s ease-out;
    }
    
    @keyframes drawLine {
      0% { stroke-dashoffset: 1000; }
      100% { stroke-dashoffset: 0; }
    }
    
    .trend-area {
      fill: rgba(15, 111, 111, 0.15);
      transition: fill 0.3s ease;
    }
    
    .data-card:hover .trend-area {
      fill: rgba(15, 111, 111, 0.3);
    }
    
    /* Animated Pie Chart */
    .pie-container {
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 10px 0;
    }
    
    .pie-segment {
      width: 70px;
      height: 70px;
      border-radius: 50%;
      background: conic-gradient(
        var(--accent-teal) 0deg 130deg,
        var(--primary-navy-light) 130deg 240deg,
        var(--accent-teal-light) 240deg 360deg
      );
      border: 2px solid rgba(255, 255, 255, 0.1);
      transition: all 0.5s ease;
      animation: pieRotate 10s linear infinite;
    }
    
    @keyframes pieRotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    
    .data-card:hover .pie-segment {
      animation: piePulse 1s ease-out;
      border-color: var(--accent-gold);
    }
    
    @keyframes piePulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.1); }
      100% { transform: scale(1); }
    }
    
    /* Metric animations */
    .metric-row {
      display: flex;
      justify-content: space-between;
      padding: 4px 0;
      font-size: 0.7rem;
      color: rgba(255, 255, 255, 0.8);
      transition: all 0.3s ease;
      border-bottom: 1px solid transparent;
    }
    
    .data-card:hover .metric-row {
      border-bottom-color: rgba(198, 161, 59, 0.2);
      padding-left: 5px;
    }
    
    .metric-label {
      color: rgba(255, 255, 255, 0.5);
      transition: color 0.3s ease;
    }
    
    .metric-value {
      font-weight: 600;
      color: var(--accent-teal);
      transition: all 0.3s ease;
    }
    
    /* Table animations */
    .data-table {
      width: 100%;
      font-size: 0.65rem;
      border-collapse: collapse;
    }
    
    .data-table td {
      padding: 4px 0;
      color: rgba(255, 255, 255, 0.7);
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      transition: all 0.3s ease;
    }
    
    .data-table td:last-child {
      text-align: right;
      color: var(--accent-teal);
      font-weight: 500;
    }
    
    .data-card:hover .data-table tr {
      animation: tableRowFade 0.5s ease-out;
    }
    
    @keyframes tableRowFade {
      0% { opacity: 0.5; transform: translateX(-5px); }
      100% { opacity: 1; transform: translateX(0); }
    }
    
    .data-card:hover .data-table td:last-child {
      color: var(--accent-gold);
    }
    
    /* Mini bar chart animations */
    .mini-bar {
      transition: all 0.3s ease;
      animation: miniBarPulse 2s ease-in-out infinite;
    }
    
    @keyframes miniBarPulse {
      0%, 100% { opacity: 0.5; }
      50% { opacity: 0.8; }
    }
    
    .data-card:hover .mini-bar {
      animation: miniBarWave 0.5s ease-out;
      opacity: 1;
    }
    
    @keyframes miniBarWave {
      0% { transform: scaleY(1); }
      50% { transform: scaleY(1.2); }
      100% { transform: scaleY(1); }
    }
    
    /* Glow effect on hover */
    .data-card::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      border-radius: 16px;
      background: radial-gradient(circle at var(--mouse-x, 50%) var(--mouse-y, 50%), 
                                 rgba(15, 111, 111, 0.2), 
                                 transparent 70%);
      opacity: 0;
      transition: opacity 0.3s ease;
      pointer-events: none;
    }
    
    .data-card:hover::after {
      opacity: 1;
    }
    
    /* POSITIONS with different float delays */
    .pos-1 { top: 12%; left: 5%; animation-delay: 0s; }
    .pos-2 { top: 25%; left: 38%; animation-delay: 1s; }
    .pos-3 { top: 45%; left: 15%; animation-delay: 2s; }
    .pos-4 { top: 65%; left: 42%; animation-delay: 0.5s; }
    .pos-5 { top: 15%; left: 68%; animation-delay: 1.5s; }
    .pos-6 { top: 35%; left: 79%; animation-delay: 2.5s; }
    .pos-7 { top: 55%; left: 55%; animation-delay: 0.8s; }
    .pos-8 { top: 75%; left: 18%; animation-delay: 1.8s; }
    .pos-9 { top: 82%; left: 68%; animation-delay: 2.2s; }
    
    /* Mouse move effect */
    .data-card {
      --mouse-x: 50%;
      --mouse-y: 50%;
    }
    
    /* Responsive */
    @media (max-width: 992px) {
      .login-container { width: 32%; min-width: 320px; }
      .viz-container { display: none; }
    }
    /* Executive-level interactive title */
    .fods-title-container {
        position: absolute;
        top: 20px;
        left: 36%;
        transform: translateX(-50%);
        z-index: 9999;
        pointer-events: auto;
    }

    .fods-title-container h1 {
        font-size: 2.5rem !important;
        font-weight: 400 !important;
        background: linear-gradient(135deg, #fff 0%, rgba(255,255,255,0.9) 100%) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
        letter-spacing: 10px !important;
        text-shadow: 0 5px 15px rgba(0,0,0,0.3) !important;
        transition: all 0.5s cubic-bezier(0.2, 0.9, 0.3, 1.1) !important;
        position: relative;
    }

    /* Sophisticated data-inspired animation */
    .fods-title-container:hover h1 {
        letter-spacing: 12px !important;
        text-shadow: 
            0 5px 20px rgba(15,111,111,0.4),
            0 0 40px rgba(198,161,59,0.2) !important;
    }

    /* Animated data bars that respond to hover */
    .fods-title-container .title-bars {
        position: absolute;
        bottom: -15px;
        left: 0;
        width: 100%;
        height: 3px;
        display: flex;
        gap: 3px;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .fods-title-container:hover .title-bars {
        opacity: 1;
    }

    .fods-title-container .title-bars span {
        width: 8px;
        height: 3px;
        background: var(--accent-teal);
        border-radius: 2px;
        animation: barWave 1.5s ease-in-out infinite;
        animation-delay: calc(var(--i) * 0.1s);
    }

    @keyframes barWave {
        0%, 100% { height: 3px; background: var(--accent-teal); }
        50% { height: 8px; background: var(--accent-gold); }
    }

    /* Subtle connection to background cards */
    .fods-title-container:hover ~ .viz-container .data-card {
        border-color: rgba(15, 111, 111, 0.2);
        transition: border-color 0.5s ease;
    }

    .fods-title-container:hover ~ .viz-container .data-card:hover {
        border-color: var(--accent-gold);
    }

/* New institution badge for login card */
.institution-badge {
    text-align: center;
    margin-bottom: 0.5rem;
}

.institution-name {
    display: block;
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--primary-navy);
    letter-spacing: 0.5px;
}

.institution-tagline {
    display: block;
    font-size: 0.6rem;
    color: var(--text-tertiary);
    letter-spacing: 0.3px;
    text-transform: uppercase;
    margin-top: 2px;
}

/* Adjust logo section spacing */
.logo-section {
    margin-bottom: 1.5rem; /* Reduced from 2rem */
}

.institution-logo {
    width: 70px;
    height: 70px;
    margin: 0 auto 0.5rem; /* Reduced bottom margin */
}
  </style>
</head>
<body>
  <div id="main-wrapper">
    <!-- Agency Badge - Top Left -->
    <div class="agency-badge">
      <img src="<?=base_url('assets/images/logos/fnrilogo.png')?>" alt="DOST FNRI">
      <span>DOST - FNRI</span>
    </div>

<!-- Interactive FODS Title with Data Bars -->
<div class="fods-title-container">
    <div class="title-glow"></div>
    <h1>FODS</h1>
    <div class="title-bars">
        <span style="--i:1"></span>
        <span style="--i:2"></span>
        <span style="--i:3"></span>
        <span style="--i:4"></span>
        <span style="--i:5"></span>
        <span style="--i:6"></span>
    </div>
</div>
    
    <!-- Right Side - Professional Login -->
<div class="login-container">
    <div class="auth-card">
        <!-- Simplified logo section - removed FODS text since it's already in the background -->
        <div class="logo-section">
            <div class="institution-logo">
                <img src="<?=base_url('assets/images/logos/fnrilogo.png')?>" alt="DOST FNRI">
            </div>
            <!-- Removed system-name and system-subtitle since FODS is now in the background -->
            <!-- Just keeping a subtle institutional identifier -->
            <div class="institution-badge">
                <span class="institution-name">DOST - FNRI</span>
                <span class="institution-tagline">Financial Obligation and Disbursement System</span>
            </div>
            <div class="divider"></div>
        </div>
        
        <form action="<?=site_url();?>mylogin-auth" method="post" novalidate>
            <div class="form-group">
                <label class="form-label">Username</label>
                <div class="input-wrapper">
                    <i class="bi bi-person input-icon"></i>
                    <input type="text" class="form-control" name="MyUsername" id="MyUsername" placeholder="Enter your username" value="<?= old('MyUsername') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrapper">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" class="form-control" placeholder="Enter your password" name="MyPassword" id="MyPassword">
                </div>
            </div>
            
            <div class="options-row">
                <div class="checkbox-wrapper">
                    <input type="checkbox" id="remember">
                    <label for="remember">Remember device</label>
                </div>
                <a href="#" class="forgot-link">Forgot password?</a>
            </div>
            
            <button type="submit" class="btn-signin">Sign In</button>
            
            <div class="agency-footer">
                <small>Department of Science and Technology<br>Food and Nutrition Research Institute</small>
            </div>
        </form>

        <!-- Add this right before closing body tag -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">


        <script>
        $(document).ready(function() {
            // Toastr configuration
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000",
                "showDuration": "300",
                "hideDuration": "1000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
            
            // Show error toast if flashdata exists
            <?php if(session()->getFlashdata('mesyszicas_memsg_login')): ?>
                toastr.error(
                    '<?= session()->getFlashdata('mesyszicas_memsg_login') ?>', 
                    'Login Error', 
                    {
                        timeOut: 5000,
                        progressBar: true,
                        closeButton: true
                    }
                );
            <?php endif; ?>
        });
        </script>
            </div>
        </div>
    
    <!-- Left Side - Interactive Data Visualizations -->
    <canvas id="interactive-bg"></canvas>
    
    <div class="viz-container" id="vizContainer">
      
      <!-- Bar Chart Card -->
      <div class="data-card pos-1" data-card="bar">
        <div class="card-header">
          <i class="bi bi-bar-chart"></i>
          <span>Quarterly Obligations</span>
        </div>
        <div class="chart-bars">
          <div class="bar-container">
            <div class="bar" style="height: 45px;"></div>
            <div class="bar-label">Q1</div>
          </div>
          <div class="bar-container">
            <div class="bar" style="height: 65px;"></div>
            <div class="bar-label">Q2</div>
          </div>
          <div class="bar-container">
            <div class="bar" style="height: 55px;"></div>
            <div class="bar-label">Q3</div>
          </div>
          <div class="bar-container">
            <div class="bar" style="height: 80px;"></div>
            <div class="bar-label">Q4</div>
          </div>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 8px;">
          <span style="color: var(--accent-gold); font-size: 0.6rem;">↑ 12.3%</span>
          <span style="color: rgba(255,255,255,0.4); font-size: 0.6rem;">vs prior year</span>
        </div>
      </div>
      
      <!-- Line Graph Card -->
      <div class="data-card pos-2" data-card="line">
        <div class="card-header">
          <i class="bi bi-graph-up"></i>
          <span>Disbursement Trend</span>
        </div>
        <div class="graph-container">
          <svg width="100%" height="100%" viewBox="0 0 180 60" preserveAspectRatio="none">
            <path class="trend-area" d="M5,50 L5,50 Q30,35 45,40 T75,25 T105,20 T135,15 T165,10 L165,50 Z" />
            <path class="trend-line" d="M5,50 Q30,35 45,40 T75,25 T105,20 T135,15 T165,10" />
          </svg>
        </div>
        <div style="text-align: center; margin-top: 5px;">
          <span style="color: var(--accent-teal); font-size: 0.6rem;">+8.5% growth</span>
        </div>
      </div>
      
      <!-- Pie Chart Card -->
      <div class="data-card pos-3" data-card="pie">
        <div class="card-header">
          <i class="bi bi-pie-chart"></i>
          <span>Budget Allocation</span>
        </div>
        <div class="pie-container">
          <div class="pie-segment"></div>
        </div>
        <div style="display: flex; justify-content: center; gap: 10px; margin-top: 8px;">
          <span style="color: var(--accent-teal); font-size: 0.55rem;">● PS 45%</span>
          <span style="color: var(--primary-navy-light); font-size: 0.55rem;">● MOOE 32%</span>
          <span style="color: var(--accent-teal-light); font-size: 0.55rem;">● CO 23%</span>
        </div>
      </div>
      
      <!-- Metrics Card -->
      <div class="data-card pos-4" data-card="metrics">
        <div class="card-header">
          <i class="bi bi-calculator"></i>
          <span>Key Metrics</span>
        </div>
        <div class="metric-row">
          <span class="metric-label">Total Appropriation</span>
          <span class="metric-value">₱ 124.5M</span>
        </div>
        <div class="metric-row">
          <span class="metric-label">Obligations</span>
          <span class="metric-value">₱ 89.2M</span>
        </div>
        <div class="metric-row">
          <span class="metric-label">Disbursements</span>
          <span class="metric-value">₱ 76.8M</span>
        </div>
        <div class="metric-row">
          <span class="metric-label">Utilization Rate</span>
          <span class="metric-value">71.6%</span>
        </div>
      </div>
      
      <!-- ORS Transactions Card -->
      <div class="data-card pos-5" data-card="ors">
        <div class="card-header">
          <i class="bi bi-journal-text"></i>
          <span>ORS Transactions</span>
        </div>
        <table class="data-table">
          <tr><td>ORS-2024-001</td><td>₱ 125K</td></tr>
          <tr><td>ORS-2024-002</td><td>₱ 87.5K</td></tr>
          <tr><td>ORS-2024-003</td><td>₱ 210K</td></tr>
          <tr><td>ORS-2024-004</td><td>₱ 42.3K</td></tr>
        </table>
        <div style="margin-top: 8px; text-align: right;">
          <span style="color: var(--accent-gold); font-size: 0.55rem;">View all →</span>
        </div>
      </div>
      
      <!-- BURS Entries Card -->
      <div class="data-card pos-6" data-card="burs">
        <div class="card-header">
          <i class="bi bi-cash-stack"></i>
          <span>BURS Entries</span>
        </div>
        <table class="data-table">
          <tr><td>BURS-2024-112</td><td>₱ 34.5K</td></tr>
          <tr><td>BURS-2024-113</td><td>₱ 78.2K</td></tr>
          <tr><td>BURS-2024-114</td><td>₱ 12.7K</td></tr>
          <tr><td>BURS-2024-115</td><td>₱ 95.4K</td></tr>
        </table>
      </div>
      
      <!-- Procurement Status Card -->
      <div class="data-card pos-7" data-card="procurement">
        <div class="card-header">
          <i class="bi bi-clipboard-data"></i>
          <span>Procurement Status</span>
        </div>
        <table class="data-table">
          <tr><td>PPMP-001</td><td>Q1</td><td>₱ 450K</td></tr>
          <tr><td>PPMP-002</td><td>Q1</td><td>₱ 230K</td></tr>
          <tr><td>PR-0245</td><td>For Approval</td><td>₱ 125K</td></tr>
          <tr><td>PR-0246</td><td>Bidding</td><td>₱ 890K</td></tr>
          <tr><td>ABSTRACT-01</td><td>Awarded</td><td>₱ 340K</td></tr>
        </table>
      </div>
      
      <!-- Computation Card -->
      <div class="data-card pos-8" data-card="computation">
        <div class="card-header">
          <i class="bi bi-calculator"></i>
          <span>Budget Computation</span>
        </div>
        <div class="metric-row">
          <span class="metric-label">Appropriation</span>
          <span class="metric-value">₱ 12.5M</span>
        </div>
        <div class="metric-row">
          <span class="metric-label">Obligations</span>
          <span class="metric-value">₱ 8.2M</span>
        </div>
        <div class="metric-row">
          <span class="metric-label">Disbursements</span>
          <span class="metric-value">₱ 6.8M</span>
        </div>
        <div class="metric-row" style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 4px; padding-top: 4px;">
          <span class="metric-label">Balance</span>
          <span class="metric-value">₱ 5.7M</span>
        </div>
      </div>
      
      <!-- Mini Bar Chart Card -->
      <div class="data-card pos-9" data-card="monthly">
        <div class="card-header">
          <i class="bi bi-bar-chart-steps"></i>
          <span>Monthly Trend</span>
        </div>
        <div style="display: flex; gap: 6px; align-items: flex-end; height: 50px;">
          <div class="mini-bar" style="flex: 1; height: 25px; background: var(--accent-teal); opacity: 0.5;"></div>
          <div class="mini-bar" style="flex: 1; height: 40px; background: var(--accent-teal); opacity: 0.7;"></div>
          <div class="mini-bar" style="flex: 1; height: 55px; background: var(--accent-teal);"></div>
          <div class="mini-bar" style="flex: 1; height: 35px; background: var(--accent-teal); opacity: 0.6;"></div>
          <div class="mini-bar" style="flex: 1; height: 20px; background: var(--accent-teal); opacity: 0.4;"></div>
          <div class="mini-bar" style="flex: 1; height: 45px; background: var(--accent-teal); opacity: 0.8;"></div>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 5px;">
          <span style="color: rgba(255,255,255,0.3); font-size: 0.5rem;">J</span>
          <span style="color: rgba(255,255,255,0.3); font-size: 0.5rem;">F</span>
          <span style="color: rgba(255,255,255,0.3); font-size: 0.5rem;">M</span>
          <span style="color: rgba(255,255,255,0.3); font-size: 0.5rem;">A</span>
          <span style="color: rgba(255,255,255,0.3); font-size: 0.5rem;">M</span>
          <span style="color: rgba(255,255,255,0.3); font-size: 0.5rem;">J</span>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Interactive Background Animation Script -->
  <script>
    (function() {
      const canvas = document.getElementById('interactive-bg');
      const ctx = canvas.getContext('2d');
      const container = document.getElementById('vizContainer');
      
      let mouseX = 0, mouseY = 0;
      let time = 0;
      
      // Mouse move effect for cards
      document.querySelectorAll('.data-card').forEach(card => {
        card.addEventListener('mousemove', (e) => {
          const rect = card.getBoundingClientRect();
          const x = ((e.clientX - rect.left) / rect.width) * 100;
          const y = ((e.clientY - rect.top) / rect.height) * 100;
          card.style.setProperty('--mouse-x', `${x}%`);
          card.style.setProperty('--mouse-y', `${y}%`);
        });
      });
      
      function resizeCanvas() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
      }
      
      canvas.addEventListener('mousemove', (e) => {
        const rect = canvas.getBoundingClientRect();
        mouseX = (e.clientX - rect.left) / canvas.width;
        mouseY = (e.clientY - rect.top) / canvas.height;
      });
      
      // Enhanced interactive background
      function drawGrid() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Animated navy gradient that responds to mouse
        const gradient = ctx.createLinearGradient(
          0, 0, 
          canvas.width * (0.5 + mouseX * 0.3), 
          canvas.height * (0.5 + mouseY * 0.3)
        );
        gradient.addColorStop(0, '#0a1a3a');
        gradient.addColorStop(0.6, '#1e3260');
        gradient.addColorStop(1, '#2c3e6e');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        // Interactive data grid that responds to mouse
        ctx.strokeStyle = 'rgba(15, 111, 111, 0.15)';
        ctx.lineWidth = 0.5;
        
        // Horizontal lines with mouse influence
        for (let i = 0; i < canvas.height; i += 40) {
          ctx.beginPath();
          const offset = Math.sin(i * 0.01 + time) * 5 * mouseX;
          ctx.moveTo(0, i + offset);
          ctx.lineTo(canvas.width, i + Math.cos(i * 0.01 + time) * 5 * mouseY);
          ctx.strokeStyle = `rgba(15, 111, 111, ${0.05 + Math.sin(i * 0.01 + time) * 0.02 + mouseX * 0.02})`;
          ctx.stroke();
        }
        
        // Vertical lines with mouse influence
        for (let i = 0; i < canvas.width; i += 40) {
          ctx.beginPath();
          const offset = Math.cos(i * 0.01 + time) * 5 * mouseY;
          ctx.moveTo(i + offset, 0);
          ctx.lineTo(i + Math.sin(i * 0.01 + time) * 5 * mouseX, canvas.height);
          ctx.strokeStyle = `rgba(15, 111, 111, ${0.05 + Math.cos(i * 0.01 + time) * 0.02 + mouseY * 0.02})`;
          ctx.stroke();
        }
        
        // Interactive floating particles that follow mouse
        for (let i = 0; i < 40; i++) {
          const x = (Math.sin(i * 0.5 + time) * 0.3 + 0.5 + mouseX * 0.1) * canvas.width;
          const y = (Math.cos(i * 0.3 + time * 0.5) * 0.3 + 0.5 + mouseY * 0.1) * canvas.height;
          
          ctx.beginPath();
          ctx.arc(x, y, 2 + Math.sin(time + i) * 1, 0, Math.PI * 2);
          ctx.fillStyle = `rgba(15, 111, 111, ${0.1 + mouseX * 0.1})`;
          ctx.fill();
        }
        
        time += 0.002;
        requestAnimationFrame(drawGrid);
      }
      
      window.addEventListener('resize', resizeCanvas);
      resizeCanvas();
      drawGrid();
    })();
  </script>
  
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Add this right before closing </body> tag -->

      <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- jQuery (make sure this is loaded first) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


</body>
</html>