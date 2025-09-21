<?php 
session_start();
require 'db_connect.php'; // login only needs DB

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            // ✅ Store all useful session data (compatible with settings.php)
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin']      = $admin['username'];
            $_SESSION['email']      = $admin['email'] ?? null;
            $_SESSION['profile_pic']= $admin['profile_pic'] ?? null;

            header('Location: index.php');
            exit;
        } else {
            $msg = "Invalid username or password.";
        }
    } else {
        $msg = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>
<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    min-height: 100vh;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #89f7fe, #66a6ff);
    transition: background 0.4s, color 0.4s;
    overflow: hidden;
}
body.dark-mode {
    background: linear-gradient(135deg, #1e1e1e, #121212);
    color: #f1f1f1;
}

.animated-bg {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    z-index: -1;
    overflow: hidden;
}
.circle {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    animation: float 10s infinite ease-in-out;
}
.circle:nth-child(1) { width: 100px; height: 100px; top: 10%; left: 20%; animation-duration: 12s; }
.circle:nth-child(2) { width: 150px; height: 150px; top: 60%; left: 70%; animation-duration: 15s; }
.circle:nth-child(3) { width: 80px; height: 80px; top: 40%; left: 40%; animation-duration: 18s; }

@keyframes float {
    0% { transform: translateY(0); }
    50% { transform: translateY(-50px); }
    100% { transform: translateY(0); }
}

/* Glass card */
.glass-card {
    background: rgba(255,255,255,0.25);
    border-radius: 20px;
    backdrop-filter: blur(12px);
    box-shadow: 0 8px 32px rgba(31,38,135,0.2);
    border: 1px solid rgba(255,255,255,0.18);
    padding: 40px;
    width: 350px;
    text-align: center;
    animation: fadeInUp 1s ease;
}
body.dark-mode .glass-card {
    background: rgba(0,0,0,0.4);
    color: #f1f1f1;
}
h2 {
    margin-bottom: 20px;
}
input.form-control {
    border-radius: 12px;
}
button.btn-custom {
    border-radius: 30px;
    padding: 10px 20px;
    font-weight: 600;
    transition: 0.3s;
}
button.btn-custom:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

@keyframes fadeInUp {
    0% { opacity: 0; transform: translateY(40px); }
    100% { opacity: 1; transform: translateY(0); }
}

a.forgot-link {
    font-size: 0.9rem;
    display: block;
    margin-top: 10px;
    color: #333;
    transition: 0.3s;
    text-decoration: none;
}
a.forgot-link:hover {
    color: #4CAF50;
}
body.dark-mode a.forgot-link {
    color: #f1f1f1;
}
</style>
</head>
<body>

<div class="animated-bg">
    <div class="circle"></div>
    <div class="circle"></div>
    <div class="circle"></div>
</div>

<div class="glass-card">
    <h2>🔐 Admin Login</h2>
    <?php if($msg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <input type="text" name="username" class="form-control mb-3" placeholder="Username" required>
        <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
        <button type="submit" class="btn btn-primary btn-custom w-100">Login</button>
    </form>
    <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
    <button id="toggleDark" class="btn btn-dark btn-sm mt-3">🌙 Toggle Dark Mode</button>
</div>

<script>
const toggleBtn = document.getElementById('toggleDark');
const body = document.body;

if(localStorage.getItem("dark-mode") === "enabled") {
    body.classList.add("dark-mode");
}

toggleBtn.addEventListener("click", () => {
    body.classList.toggle("dark-mode");
    localStorage.setItem("dark-mode", body.classList.contains("dark-mode") ? "enabled" : "disabled");
});
</script>

</body>
</html>
