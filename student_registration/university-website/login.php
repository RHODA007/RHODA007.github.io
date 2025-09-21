<?php
session_start();
require_once 'includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
            $_SESSION['student'] = [
                'id'      => $user['id'],
                'name'    => $user['name'],
                'email'   => $user['email'],
                'program' => $user['program'] ?? ''
            ];
            header("Location: student_dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Please enter email and password";
    }
}
?>

<?php include 'includes/header.php'; ?>

<section class="container py-5">
    <h2 class="text-center mb-4 text-primary fw-bold">Student Login</h2>
    <p class="text-center mb-5 text-muted">Access your dashboard and continue learning at RhodaX Tech School.</p>

    <?php if($error): ?>
        <p class="text-center text-danger mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="feature-card p-4 shadow-sm rounded-4 bg-white">
                <form method="POST" action="">
                    <div class="mb-3">
                        <input type="email" name="email" placeholder="Email" required class="form-control p-3 rounded-3">
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" placeholder="Password" required class="form-control p-3 rounded-3">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3">Login</button>
                </form>
                <p class="mt-3 text-center text-muted" style="font-size:0.9rem;">
                    <a href="reset.php" class="text-decoration-underline">Forgot Password?</a> | 
                    <a href="register.php" class="text-decoration-underline">Register</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
