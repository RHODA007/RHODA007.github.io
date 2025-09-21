<?php
session_start();
if(!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require 'db_connect.php';

// ✅ Updated tech courses
$courseList = ['Web Development','Data Science','AI & ML','Cybersecurity','UI/UX Design'];

$id = $_GET['id'] ?? null;
if (!$id) { die("Invalid student ID."); }

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();
if (!$student) { die("Student not found."); }

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = $_POST['name'];
    $email  = $_POST['email'];
    $phone  = $_POST['phone'];
    $dob    = $_POST['dob'];
    $gender = $_POST['gender'];
    $course = $_POST['course'];

    $update = $pdo->prepare("UPDATE students SET name=?, email=?, phone=?, dob=?, gender=?, course=? WHERE id=?");
    $update->execute([$name, $email, $phone, $dob, $gender, $course, $id]);

    header("Location: view.php?msg=updated&type=success");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Student</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #89f7fe, #66a6ff);
    margin: 0;
    min-height: 100vh;
    transition: background 0.5s, color 0.5s;
    overflow-x: hidden;
}
.dark-mode { background: linear-gradient(135deg, #2c3e50, #4ca1af); color: #f8f9fa; }
.navbar { background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.3); }
.glassmorphic {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(15px);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 8px 32px rgba(31, 38, 135, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
}
.btn-custom { border-radius: 10px; padding: 10px 20px; font-weight: 500; }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg p-3">
  <div class="container-fluid d-flex justify-content-between">
    <h4 class="text-white m-0">🎓 Student Dashboard</h4>
    <button id="darkToggle" class="btn btn-outline-light">🌙 Dark Mode</button>
  </div>
</nav>

<div class="container my-5">
  <div class="glassmorphic mx-auto" style="max-width:600px;">
     <h2 class="text-center mb-4">✏️ Edit Student</h2>
     <form method="POST">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($student['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($student['phone']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Date of Birth</label>
            <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($student['dob']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Gender</label>
            <select name="gender" class="form-control" required>
                <option value="Male"   <?= $student['gender']=="Male" ? "selected" : "" ?>>Male</option>
                <option value="Female" <?= $student['gender']=="Female" ? "selected" : "" ?>>Female</option>
                <option value="Other"  <?= $student['gender']=="Other" ? "selected" : "" ?>>Other</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Course</label>
            <select name="course" class="form-control" required>
                <?php foreach($courseList as $c): ?>
                    <option value="<?= $c ?>" <?= $student['course']==$c ? "selected" : "" ?>><?= $c ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="d-flex justify-content-between">
            <a href="view.php" class="btn btn-secondary btn-custom">⬅ Back</a>
            <button type="submit" class="btn btn-success btn-custom">💾 Save Changes</button>
        </div>
     </form>
  </div>
</div>

<script>
document.getElementById("darkToggle").addEventListener("click", function() {
    document.body.classList.toggle("dark-mode");
    this.textContent = document.body.classList.contains("dark-mode") ? "☀️ Light Mode" : "🌙 Dark Mode";
});
</script>

</body>
</html>
