<?php
session_start();
require 'db_connect.php';

// Fetch students
$students = $pdo->query("SELECT id, name, email FROM students")->fetchAll(PDO::FETCH_ASSOC);

// Fetch courses
$courses = $pdo->query("SELECT id, title FROM courses")->fetchAll(PDO::FETCH_ASSOC);

// Handle assignment
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $course_id = $_POST['course_id'] ?? '';

    if ($student_id && $course_id) {
        // Check if already assigned
        $check = $pdo->prepare("SELECT * FROM student_courses WHERE student_id = ? AND course_id = ?");
        $check->execute([$student_id, $course_id]);

        if ($check->rowCount() > 0) {
            $error = "⚠ This course is already assigned to the student.";
        } else {
            $pdf_file = '';
            $video_file = '';

            // Handle PDF upload
            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['pdf_file']['tmp_name'];
                $fileName = $_FILES['pdf_file']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $uploadDir = './uploads/pdfs/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $dest_path = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $pdf_file = $newFileName;
                } else {
                    $error = "⚠ Failed to upload PDF.";
                }
            } else {
                $error = "⚠ Please upload a PDF file.";
            }

            // Handle video upload
            if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['video_file']['tmp_name'];
                $fileName = $_FILES['video_file']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $uploadDir = './uploads/videos/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $dest_path = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $video_file = $newFileName;
                } else {
                    $error = "⚠ Failed to upload video.";
                }
            } else {
                $error = "⚠ Please upload a video file.";
            }

            // Insert assignment if no error
            if (!$error) {
                $stmt = $pdo->prepare("INSERT INTO student_courses (student_id, course_id, pdf_file, video_file, progress, assigned_at) VALUES (?, ?, ?, ?, 0, NOW())");
                $stmt->execute([$student_id, $course_id, $pdf_file, $video_file]);
                $success = "✅ Course assigned with PDF and Video successfully!";
            }
        }
    } else {
        $error = "⚠ Please select both student and course.";
    }
}

// Fetch assigned courses with student + course info
$assignments = $pdo->query("
    SELECT sc.id, s.name AS student_name, s.email, c.title AS course_title, sc.pdf_file, sc.video_file, sc.assigned_at, sc.progress
    FROM student_courses sc
    JOIN students s ON sc.student_id = s.id
    JOIN courses c ON sc.course_id = c.id
    ORDER BY sc.assigned_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assign Courses</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body {margin:0; font-family:"Segoe UI", Tahoma, Geneva, Verdana,sans-serif; background:#f8f9fa;}
.sidebar {width:240px; background:#212529; color:#fff; height:100vh; position:fixed; left:0; top:0; padding-top:30px;}
.sidebar h3 {text-align:center; margin-bottom:40px; font-weight:bold;}
.sidebar a {display:block; padding:12px 20px; color:#ddd; text-decoration:none; transition:0.3s; border-radius:8px; margin:4px 12px;}
.sidebar a:hover, .sidebar a.active {background:#0d6efd; color:#fff;}
.main-content {margin-left:240px; padding:40px;}
.assign-card {background:#fff; border-radius:20px; box-shadow:0px 8px 25px rgba(0,0,0,0.08); padding:30px; max-width:700px; margin:auto;}
.assign-card h2 {font-weight:700; color:#333;}
.form-select, .form-control, .btn {border-radius:12px; font-size:16px; padding:12px;}
.btn-primary {background:#007bff; border:none; font-weight:600; transition:all 0.3s ease;}
.btn-primary:hover {background:#0056b3; transform:translateY(-2px);}
.alert {border-radius:12px;}
.table-container {margin-top:50px; background:#fff; padding:25px; border-radius:20px; box-shadow:0px 8px 25px rgba(0,0,0,0.08);}
table {border-radius:12px; overflow:hidden;}
thead {background:#0d6efd; color:#fff;}
tbody tr:hover {background:#f1f5ff;}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header"><h3>Admin Panel</h3></div>
  <ul class="sidebar-menu">
    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="students.php"><i class="fas fa-users"></i> Students</a></li>
    <li><a href="courses.php"><i class="fas fa-book"></i> Courses</a></li>
    <li><a href="assign_course.php" class="active"><i class="fas fa-tasks"></i> Assign Courses</a></li>
    <li><a href="assignments.php"><i class="fas fa-file-alt"></i> Assignments</a></li>
    <li><a href="send_message.php"><i class="fas fa-envelope"></i> Send Message</a></li>
    <li><a href="manage_courses.php"><i class="fas fa-cogs"></i> Manage Courses</a></li>
    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="settings.php"><i class="fas fa-user-cog"></i> Admin Settings</a></li>
    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="assign-card">
    <h2 class="text-center mb-4">🎓 Assign Course to Student</h2>

    <?php if(!empty($success)): ?>
      <div class="alert alert-success text-center"><?= htmlspecialchars($success) ?></div>
    <?php elseif(!empty($error)): ?>
      <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label fw-semibold">👤 Select Student</label>
        <select name="student_id" class="form-select" required>
          <option value="">-- Choose Student --</option>
          <?php foreach($students as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['email']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">📘 Select Course</label>
        <select name="course_id" class="form-select" required>
          <option value="">-- Choose Course --</option>
          <?php foreach($courses as $c): ?>
            <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">📄 Upload PDF Book</label>
        <input type="file" name="pdf_file" class="form-control" accept="application/pdf" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">🎥 Upload Video</label>
        <input type="file" name="video_file" class="form-control" accept="video/*" required>
      </div>

      <button type="submit" class="btn btn-primary w-100">✅ Assign Course</button>
    </form>
  </div>

  <div class="table-container mt-5">
    <h4 class="mb-3">📋 Assigned Courses</h4>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Student</th>
            <th>Email</th>
            <th>Course</th>
            <th>PDF</th>
            <th>Video</th>
            <th>Assigned At</th>
            <th>Progress</th>
          </tr>
        </thead>
        <tbody>
          <?php if(count($assignments) > 0): ?>
            <?php foreach($assignments as $i => $a): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($a['student_name']) ?></td>
                <td><?= htmlspecialchars($a['email']) ?></td>
                <td><?= htmlspecialchars($a['course_title']) ?></td>
                <td>
                  <?php if(!empty($a['pdf_file'])): ?>
                    <a href="uploads/pdfs/<?= htmlspecialchars($a['pdf_file']) ?>" target="_blank" class="btn btn-sm btn-info">📄 View PDF</a>
                  <?php else: ?>N/A<?php endif; ?>
                </td>
                <td>
                  <?php if(!empty($a['video_file'])): ?>
                    <a href="uploads/videos/<?= htmlspecialchars($a['video_file']) ?>" target="_blank" class="btn btn-sm btn-warning">🎬 Watch Video</a>
                  <?php else: ?>N/A<?php endif; ?>
                </td>
                <td><?= htmlspecialchars($a['assigned_at']) ?></td>
                <td><?= htmlspecialchars($a['progress']) ?>%</td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-center text-muted">No assignments yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>
