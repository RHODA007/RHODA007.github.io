<?php
session_start();
require_once 'includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 🔹 reCAPTCHA validation
    $secretKey = "6LdCVMorAAAAAAY0_5kssY87tEHpDGLsubouXcWD";
    $captchaResponse = $_POST['g-recaptcha-response'] ?? '';

    $verify = file_get_contents(
        "https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$captchaResponse}"
    );
    $responseData = json_decode($verify);

    if (!$responseData->success) {
        $error = "⚠ Please confirm you are not a robot!";
    } else {
        // Continue registration if captcha passed
        $name = trim($_POST['name']);
        $email = strtolower(trim($_POST['email']));
        $phone = trim($_POST['phone']);
        $dob = $_POST['dob'];
        $gender = $_POST['gender'];
        $course = $_POST['course'];
        $password = trim($_POST['password']);

        // Check if email exists
        $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "⚠ Email already registered!";
        } else {
            $photo = null;
            if (!empty($_FILES['photo']['name'])) {
                $targetDir = "uploads/";
                $photo = time() . "_" . basename($_FILES["photo"]["name"]);
                move_uploaded_file($_FILES["photo"]["tmp_name"], $targetDir . $photo);
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO students (name,email,phone,dob,gender,course,password,profile_pic) VALUES (?,?,?,?,?,?,?,?)");
            if ($stmt->execute([$name,$email,$phone,$dob,$gender,$course,$hashedPassword,$photo])) {
                $_SESSION['student_id'] = $pdo->lastInsertId();
                $_SESSION['student'] = $name;
                $_SESSION['email'] = $email;

                // ✅ Redirect to Welcome Page instead of dashboard
                header('Location: welcome.php');
                exit;
            } else {
                $error = "❌ Something went wrong. Try again.";
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>
<!-- Load Google reCAPTCHA -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<section class="container py-5">
    <h2 class="text-center mb-4 text-primary fw-bold">➕ Register as a Student</h2>
    <p class="text-center mb-5 text-muted">Join RhodaX Tech School and start learning today!</p>

    <?php if($error): ?>
        <p class="text-center text-danger mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="feature-card p-4 shadow-sm rounded-4 bg-white">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3"><input type="text" name="name" placeholder="Full Name" required class="form-control p-3 rounded-3"></div>
                    <div class="mb-3"><input type="email" name="email" placeholder="Email" required class="form-control p-3 rounded-3"></div>
                    <div class="mb-3"><input type="password" name="password" placeholder="Password" required class="form-control p-3 rounded-3"></div>
                    <div class="mb-3"><input type="text" name="phone" placeholder="Phone" required class="form-control p-3 rounded-3"></div>
                    <div class="mb-3"><input type="date" name="dob" required class="form-control p-3 rounded-3"></div>
                    <div class="mb-3">
                        <select name="gender" required class="form-select p-3 rounded-3">
                            <option value="">Select Gender</option>
                            <option>Male</option><option>Female</option>
                        </select>
                    </div>
                    <div class="mb-3"><input type="file" name="photo" accept="image/*" class="form-control p-2 rounded-3"></div>
                    <div class="mb-3">
                        <select name="course" required class="form-select p-3 rounded-3">
                            <option value="">Select Course</option>
                            <option>Web Development</option><option>AI</option><option>Cybersecurity</option>
                            <option>Data Science</option><option>Mobile App</option><option>Cloud Computing</option>
                            <option>Blockchain</option><option>UI/UX</option><option>Game Development</option>
                            <option>Robotics</option><option>Networking</option><option>AR/VR</option>
                        </select>
                    </div>

                    <!-- 🔹 Google reCAPTCHA widget -->
                    <div class="mb-3">
                        <div class="g-recaptcha" data-sitekey="6LdCVMorAAAAAKrdfzXvYsSLYncRy0mmH73U1Ehw"></div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3">Register</button>
                    <p class="mt-3 text-center text-muted">Already have an account? <a href="login.php" class="text-decoration-underline">Login</a></p>
                </form>
            </div>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
