<?php
session_start();
require_once 'includes/config.php'; // Adjust path if needed

// Redirect if already logged in
if (isset($_SESSION['instructor'])) {
    header("Location: instructor_dashboard.php");
    exit;
}

$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST['email'] ?? ""));
    $password = trim($_POST['password'] ?? "");

    if (!empty($email) && !empty($password)) {
        // Look up instructor by email
        $stmt = $pdo->prepare("SELECT * FROM instructors WHERE email = ?");
        $stmt->execute([$email]);
        $instructor = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password
        if ($instructor && password_verify($password, $instructor['password'])) {
            // Save session securely
            $_SESSION['instructor'] = [
                'id'    => $instructor['id'],
                'name'  => $instructor['name'],
                'email' => $instructor['email']
            ];

            header("Location: instructor_dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Instructor Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #f0f2f5, #e6e9ef);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .login-card {
      width: 100%;
      max-width: 420px;
      padding: 30px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.15);
      animation: fadeIn 0.6s ease-in-out;
    }
    .login-card h3 {
      text-align: center;
      margin-bottom: 20px;
      color: #1f1f2c;
      font-weight: 600;
    }
    .btn-custom {
      background: #1f1f2c;
      color: #fff;
      transition: 0.3s ease;
    }
    .btn-custom:hover {
      background: #29293d;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="login-card">
    <h3>Instructor Login</h3>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger text-center">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
      </div>
      <button type="submit" class="btn btn-custom w-100">Login</button>
    </form>

    <p class="mt-3 text-center">
      Not registered? <a href="register.php">Sign up</a>
    </p>
  </div>
</body>
</html>
