
<?php
    echo view('templates/myheader.php');
?>

<style>
    .access-restricted-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 75vh;
        background: linear-gradient(to right, #d1ecf1, #bee5eb);
    }
    .message-box {
        background: #ffffff;
        padding: 50px 40px;
        border-radius: 12px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        text-align: center;
        max-width: 750px;
        width: 90%;
        animation: fadeIn 1s ease-in-out;
    }
    .message-box h1 {
        font-size: 2.5rem;
        color: #0c5460;
        margin-bottom: 20px;
    }
    .message-box p {
        font-size: 1.2rem;
        color: #495057;
        margin-bottom: 30px;
    }
    .message-box a {
        text-decoration: none;
        padding: 10px 25px;
        background-color: #0c5460;
        color: #fff;
        border-radius: 5px;
        font-weight: 500;
        transition: background-color 0.3s;
    }
    .message-box a:hover {
        background-color: #07363d;
    }
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="access-restricted-wrapper mb-4">
    <div class="message-box">
        <h1 class="text-danger fw-bold">Access Restricted</h1>
        <p>You do not have permission to view this page.<br>Please contact your system administrator to request access.</p>
        <a href="<?=site_url();?>mydashboard">Return to Homepage</a>
    </div>
</div>


<?php
    echo view('templates/myfooter.php');
?>
