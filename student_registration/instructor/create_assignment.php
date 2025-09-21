<?php
session_start();
if (!isset($_SESSION['instructor'])) {
    header('Location: login.php');
    exit;
}

require '../db_connect.php'; // adjust path
$instructor_id = $_SESSION['instructor']['id'];
$message = "";

// Fetch instructor courses
$stmt = $pdo->prepare("SELECT id, title FROM courses WHERE instructor_id=? ORDER BY title ASC");
$stmt->execute([$instructor_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch students for a selected course via GET
$selected_course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$students = [];
if ($selected_course_id) {
    // Get course title for mapping to students.course column
    $stmt = $pdo->prepare("SELECT title FROM courses WHERE id=?");
    $stmt->execute([$selected_course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($course) {
        $course_title = $course['title'];
        // Fetch students that belong to this course (via course column)
        $stmt = $pdo->prepare("SELECT id, name FROM students WHERE course = ? ORDER BY name ASC");
        $stmt->execute([$course_title]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = (int)$_POST['course_id'];
    $student_id = (int)$_POST['student_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = $_POST['due_date'];

    $file_path = NULL;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['application/pdf']; // Only PDFs
        if (in_array($_FILES['file']['type'], $allowedTypes)) {
            $uploadDir = '../uploads/assignments/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $filename = time() . '_' . basename($_FILES['file']['name']);
            $targetFile = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
                $file_path = 'uploads/assignments/' . $filename;
            } else {
                $message = "<div class='alert alert-danger'>Error uploading file.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Only PDF files are allowed.</div>";
        }
    }

    if (empty($message)) { // Only insert if no file error
        $stmt = $pdo->prepare("
            INSERT INTO assignments (student_id, course_id, title, description, due_date, file_path, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        if ($stmt->execute([$student_id, $course_id, $title, $description, $due_date, $file_path])) {
            $message = "<div class='alert alert-success'>Assignment created successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error creating assignment.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Assignment</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { background:#f8f9fa; margin:0; font-family:Arial,sans-serif; }
.sidebar { position: fixed; left: 0; top: 0; height: 100vh; width: 240px; background: #1f1f2c; color: #fff; display: flex; flex-direction: column; box-shadow: 2px 0 10px rgba(0,0,0,0.2); z-index: 1000; }
.sidebar-header { padding: 20px; text-align: center; background: #29293d; border-bottom: 1px solid #333; }
.sidebar-header h3 { margin: 0; font-size: 18px; font-weight: bold; }
.sidebar-menu { list-style: none; margin: 0; padding: 0; flex: 1; }
.sidebar-menu li { width: 100%; }
.sidebar-menu a { display: flex; align-items: center; gap: 10px; padding: 14px 20px; color: #cfd2dc; text-decoration: none; font-size: 15px; transition: all 0.3s ease; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: #0d6efd; color: #fff; }
.sidebar-menu .logout { background: #d9534f; color: #fff; margin-top: auto; }
.sidebar-menu .logout:hover { background: #c9302c; }
.main { margin-left:240px; padding:20px; }
.card { border-radius:8px; }
</style>
<script>
function loadStudents(courseId){
    window.location.href = 'create_assignment.php?course_id=' + courseId;
}
</script>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-header">
    <h3>Instructor Panel</h3>
  </div>
  <ul class="sidebar-menu">
    <li><a href="instructor_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="create_course.php"><i class="fas fa-plus-circle"></i> Create Course</a></li>
    <li><a href="manage_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
    <li><a href="students.php"><i class="fas fa-users"></i> My Students</a></li>
    <li><a href="assignments.php" class="active"><i class="fas fa-tasks"></i> Assignments</a></li>
    <li><a href="submissions.php"><i class="fas fa-file-upload"></i> Submissions</a></li>
    <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Manage Schedule</a></li>
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="settings.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main">
  <h2>Create New Assignment / Upload PDF</h2>
  <?= $message ?>

  <form method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="course" class="form-label">Select Course</label>
        <select name="course_id" id="course" class="form-select" onchange="loadStudents(this.value)" required>
            <option value="">-- Select Course --</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= $course['id'] ?>" <?= ($selected_course_id == $course['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($course['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label for="student" class="form-label">Select Student</label>
        <select name="student_id" id="student" class="form-select" required>
            <option value="">-- Select Student --</option>
            <?php 
            if ($students) {
                foreach ($students as $student): ?>
                    <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['name']) ?></option>
                <?php endforeach;
            } else if ($selected_course_id) {
                echo "<option value=''>No students in this course</option>";
            }
            ?>
        </select>
      </div>

      <div class="mb-3">
        <label for="title" class="form-label">Assignment Title</label>
        <input type="text" name="title" id="title" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" id="description" class="form-control" rows="4" required></textarea>
      </div>

      <div class="mb-3">
        <label for="due_date" class="form-label">Due Date</label>
        <input type="date" name="due_date" id="due_date" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="file" class="form-label">Upload File / PDF (optional)</label>
        <input type="file" name="file" id="file" class="form-control" accept="application/pdf">
      </div>

      <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Create Assignment</button>
      <a href="assignments.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>

</body>
</html>
