<?php
session_start();
require_once 'includes/config.php'; // adjust path

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 🔹 reCAPTCHA validation
    $secretKey = "6LdCVMorAAAAAAY0_5kssY87tEHpDGLsubouXcWD"; // same secret key
    $captchaResponse = $_POST['g-recaptcha-response'] ?? '';

    $verify = file_get_contents(
        "https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$captchaResponse}"
    );
    $responseData = json_decode($verify);

    if (!$responseData->success) {
        $error = "⚠ Please confirm you are not a robot!";
    } else {
        // Continue registration if captcha passed
        $name     = trim($_POST['name']);
        $email    = trim($_POST['email']);
        $password = trim($_POST['password']);
        $bio      = trim($_POST['bio']);

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM instructors WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered.";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert instructor with approved = 0, unapproved = 1
            $stmt = $pdo->prepare("
                INSERT INTO instructors 
                (name, email, password, bio, approved, unapproved, created_at) 
                VALUES (?, ?, ?, ?, 0, 1, NOW())
            ");
            if ($stmt->execute([$name, $email, $hashedPassword, $bio])) {
                // Redirect to qualification form
                $instructor_id = $pdo->lastInsertId();
                header("Location: qualification.php?id=$instructor_id");
                exit;
            } else {
                $error = "❌ Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Instructor Registration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <style>
    body {
      background: #f0f2f5;
      display: flex; 
      justify-content: center; 
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .register-card {
      width: 100%;
      max-width: 500px;
      padding: 30px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }
    .register-card h3 {
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
  </style>
</head>
<body>
  <div class="register-card">
    <h3>Instructor Registration</h3>

    <?php if($error): ?>
      <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if($success): ?>
      <div class="alert alert-success text-center"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Create a password" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Bio</label>
        <textarea name="bio" class="form-control" rows="3" placeholder="Tell students about yourself..."></textarea>
      </div>

      <!-- 🔹 Google reCAPTCHA widget -->
      <div class="mb-3">
        <div class="g-recaptcha" data-sitekey="6LdCVMorAAAAAKrdfzXvYsSLYncRy0mmH73U1Ehw"></div>
      </div>

      <button type="submit" class="btn btn-custom w-100">Register</button>
    </form>

    <p class="mt-3 text-center">
      Already have an account? <a href="login.php">Login here</a>
    </p>
  </div>
</body>
</html>
