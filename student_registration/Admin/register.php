<?php
session_start();
if(!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require 'db_connect.php';

$error = "";

// ================================
// Fetch courses dynamically from database
$stmtCourses = $pdo->query("SELECT id, title FROM courses ORDER BY created_at DESC");
$courses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);
// ================================

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 🔹 reCAPTCHA validation
    $secretKey = "6LdCVMorAAAAAAY0_5kssY87tEHpDGLsubouXcWD";
    $captchaResponse = $_POST['g-recaptcha-response'] ?? '';

    $verify = file_get_contents(
        "https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$captchaResponse}"
    );
    $responseData = json_decode($verify);

    if (!$responseData->success) {
        $error = "⚠️ Please confirm you are not a robot!";
    } else {
        $name   = $_POST['name'];
        $email  = $_POST['email'];
        $phone  = $_POST['phone'];
        $dob    = $_POST['dob'];
        $gender = $_POST['gender'] ?? null;
        $course = $_POST['course'] ?? '';
        $photo  = null;

        // Check if email already exists
        $check = $pdo->prepare("SELECT id FROM students WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $error = "⚠️ Email already registered. Please use another one.";
        } else {
            // Handle photo upload
            if (!empty($_FILES['photo']['name'])) {
                $targetDir = "uploads/";
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                $photoName  = time() . "_" . basename($_FILES['photo']['name']);
                $targetFile = $targetDir . $photoName;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) $photo = $photoName;
            }

            // Insert student
            $stmt = $pdo->prepare("INSERT INTO students (name, email, phone, dob, gender, photo, course, created_at) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $phone, $dob, $gender, $photo, $course]);

            header("Location: index.php?msg=Student+added+successfully");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register Student</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f4f6f9;
    margin:0;
    padding:0;
}
.container {
    max-width: 600px;
    margin: 50px auto;
}
.card {
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    padding: 30px;
    background: #fff;
}
h2 {
    text-align: center;
    margin-bottom: 25px;
    font-weight: 600;
    color: #333;
}
input.form-control, select.form-control {
    border-radius: 8px;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
}
button.btn-primary, button.btn-success, a.btn-secondary {
    border-radius: 30px;
    padding: 10px 20px;
    font-weight: 600;
    width: 100%;
    margin-bottom: 10px;
    transition: 0.3s;
}
button:hover, a:hover { transform: scale(1.03); box-shadow:0 5px 15px rgba(0,0,0,0.2); }
.alert { border-radius: 8px; padding: 10px; font-weight: 500; }
</style>
</head>
<body>

<div class="container">
    <div class="card">
        <h2>📚 Register Student</h2>

        <?php if($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" class="form-control" placeholder="Full Name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            <input type="email" name="email" class="form-control" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            <input type="text" name="phone" class="form-control" placeholder="Phone Number" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
            <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>" required>

            <select name="gender" class="form-control" required>
                <option value="">Select Gender</option>
                <option value="Male" <?= (($_POST['gender'] ?? '')==='Male')?'selected':'' ?>>Male</option>
                <option value="Female" <?= (($_POST['gender'] ?? '')==='Female')?'selected':'' ?>>Female</option>
            </select>

            <select name="course" class="form-control" required>
                <option value="">Select Course</option>
                <?php foreach($courses as $c): ?>
                    <option value="<?= htmlspecialchars($c['title']) ?>" <?= (($_POST['course'] ?? '') === $c['title'])?'selected':'' ?>>
                        <?= htmlspecialchars($c['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="file" name="photo" class="form-control" accept="image/*">

            <!-- 🔹 Google reCAPTCHA widget -->
            <div class="g-recaptcha mb-3" data-sitekey="6LdCVMorAAAAAKrdfzXvYsSLYncRy0mmH73U1Ehw"></div>

            <button type="submit" class="btn btn-success">Register Student</button>
            <a href="index.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
        </form>
    </div>
</div>

</body>
</html>
