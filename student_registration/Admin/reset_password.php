<?php
session_start();
require 'db_connect.php';
$msg = '';
$showForm = false;

// Check token
$token = $_GET['token'] ?? '';
if ($token) {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE reset_token = ?");
    $stmt->execute([$token]);
    $admin = $stmt->fetch();

    if ($admin) {
        $showForm = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if ($password !== $confirm) {
                $msg = "Passwords do not match!";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admins SET password = ?, reset_token = NULL WHERE reset_token = ?");
                $stmt->execute([$hashed, $token]);
                $msg = "Password successfully reset! <a href='login.php'>Login now</a>";
                $showForm = false;
            }
        }
    } else {
        $msg = "Invalid or expired token!";
    }
} else {
    $msg = "No token provided!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    min-height: 100vh;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #f6d365, #fda085);
    overflow: hidden;
    transition: background 0.5s, color 0.5s;
}

/* Dark mode */
body.dark-mode {
    background: linear-gradient(135deg, #1e1e1e, #121212);
    color: #f1f1f1;
}

/* Animated background circles */
.animated-bg { position: absolute; width: 100%; height: 100%; overflow: hidden; top: 0; left: 0; z-index: -1; }
.circle { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.2); animation: float 12s infinite ease-in-out; }
.circle:nth-child(1){ width:100px;height:100px;top:10%;left:20%; }
.circle:nth-child(2){ width:150px;height:150px;top:60%;left:70%; animation-duration:15s; }
.circle:nth-child(3){ width:80px;height:80px;top:40%;left:40%; animation-duration:18s; }
@keyframes float {0%{transform:translateY(0);}50%{transform:translateY(-50px);}100%{transform:translateY(0);}}

/* Glassmorphic card */
.glass-card {
    background: rgba(255, 255, 255, 0.25);
    border-radius: 20px;
    backdrop-filter: blur(12px);
    box-shadow: 0 8px 32px rgba(31,38,135,0.2);
    border: 1px solid rgba(255,255,255,0.18);
    padding: 40px;
    width: 350px;
    text-align: center;
    animation: fadeInUp 1s ease;
}
h1 { margin-bottom: 20px; }
.btn-custom { border-radius: 30px; transition: 0.3s; }
.btn-custom:hover { transform: scale(1.05); box-shadow:0 5px 15px rgba(0,0,0,0.2);}
@keyframes fadeInUp {0%{opacity:0;transform:translateY(40px);}100%{opacity:1;transform:translateY(0);}}
</style>
</head>
<body>

<!-- Animated background circles -->
<div class="animated-bg">
    <div class="circle"></div>
    <div class="circle"></div>
    <div class="circle"></div>
</div>

<div class="glass-card">
    <h1>Reset Password</h1>
    <?php if($msg): ?>
        <div class="alert alert-info"><?= $msg ?></div>
    <?php endif; ?>

    <?php if($showForm): ?>
    <form method="POST">
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="New Password" required>
        </div>
        <div class="mb-3">
            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-custom w-100">Reset Password</button>
    </form>
    <?php endif; ?>

    <a href="login.php" class="d-block mt-3">Back to Login</a>
    <button id="toggleDark" class="btn btn-dark btn-sm mt-3">🌙 Toggle Dark Mode</button>
</div>

<script>
// Dark mode toggle
const toggleBtn = document.getElementById('toggleDark');
const body = document.body;
if(localStorage.getItem("dark-mode")==="enabled"){ body.classList.add("dark-mode"); }
toggleBtn.addEventListener("click", ()=>{
    body.classList.toggle("dark-mode");
    if(body.classList.contains("dark-mode")){ localStorage.setItem("dark-mode","enabled"); }
    else { localStorage.setItem("dark-mode","disabled"); }
});
</script>

</body>
</html>
