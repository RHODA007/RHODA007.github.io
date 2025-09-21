<?php
session_start();
if(!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require 'db_connect.php';

$message = "";
$type = "error";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dob   = trim($_POST['dob'] ?? '');

    if (!empty($name) && !empty($email) && !empty($phone) && !empty($dob)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO students (name, email, phone, dob) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $dob]);

            $message = "🎉 Student Registered Successfully!";
            $type = "success";
        } catch (Exception $e) {
            $message = "⚠️ Error inserting student: " . $e->getMessage();
            $type = "error";
        }
    } else {
        $message = "⚠️ Please fill in all fields.";
        $type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Registration Status</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background: linear-gradient(135deg, #e0f7fa, #fce4ec);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .glass-card {
      background: rgba(255, 255, 255, 0.25);
      border-radius: 20px;
      backdrop-filter: blur(12px);
      box-shadow: 0 8px 32px rgba(31, 38, 135, 0.2);
      padding: 30px;
      text-align: center;
      width: 400px;
      animation: fadeIn 1s ease-in-out;
    }
    .glass-card h2 {
      font-weight: bold;
    }
    .success { color: #2e7d32; }
    .error { color: #c62828; }
    .btn-custom {
      margin-top: 20px;
      width: 100%;
      border-radius: 12px;
    }
    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(-20px);}
      to {opacity: 1; transform: translateY(0);}
    }
  </style>
</head>
<body>
  <div class="glass-card">
    <h2 class="<?= $type ?>"><?= $message ?></h2>
    <a href="index.php" class="btn btn-primary btn-custom">⬅ Back to Registration</a>
    <a href="view.php" class="btn btn-outline-secondary btn-custom">📋 View Students</a>
  </div>
  <script>
  // 🌙 Dark Mode Toggle
  const toggleBtn = document.getElementById('toggleDark');
  const body = document.body;

  if(localStorage.getItem("dark-mode") === "enabled"){
    body.classList.add("dark-mode");
  }

  toggleBtn.addEventListener("click", () => {
    body.classList.toggle("dark-mode");
    if(body.classList.contains("dark-mode")){
      localStorage.setItem("dark-mode", "enabled");
    } else {
      localStorage.setItem("dark-mode", "disabled");
    }
  });
</script>
<script src="script.js"></script>
</body>
</html>
