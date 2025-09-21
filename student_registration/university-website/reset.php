<?php
session_start();
require_once 'includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$email) {
        $error = "Please enter your email.";
    } elseif (!$new_password || !$confirm_password) {
        $error = "Please enter and confirm your new password.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if student exists
        $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE students SET password = ? WHERE email = ?");
            if ($update->execute([$hashed, $email])) {
                $success = "Password updated successfully! You can now <a href='login.php'>login</a>.";
            } else {
                $error = "Failed to update password. Try again.";
            }
        } else {
            $error = "No account found with this email.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>
<body>
<section class="login-page">
    <section class="auth-form glass-card mx-auto text-center mt-5 p-4" style="max-width:400px;">
        <h2>Reset Password</h2>
        <?php if($error): ?>
            <p class="text-danger"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if($success): ?>
            <p class="text-success"><?= $success ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="email" name="email" placeholder="Your email" required class="form-control mb-3">
            <input type="password" name="new_password" placeholder="New password" required class="form-control mb-3">
            <input type="password" name="confirm_password" placeholder="Confirm new password" required class="form-control mb-3">
            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form>

        <p class="mt-3">
            <a href="login.php">Back to Login</a>
        </p>
    </section>
</section>
</body>
<?php include 'includes/footer.php'; ?>
