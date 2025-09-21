<?php
session_start();
if(!isset($_SESSION['instructor'])){
    header("Location: instructor_login.php");
    exit;
}
require_once 'includes/config.php';
// adjust path

$instructor_id = $_SESSION['instructor']['id'];

// Validate course ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: manage_courses.php");
    exit;
}

$course_id = (int)$_GET['id'];

// Fetch course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$course_id, $instructor_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$course){
    die("Course not found or you don't have permission to edit it.");
}

$message = "";

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $level = trim($_POST['level']);
    $duration = trim($_POST['duration']);

    if($title && $description && $category && $level && $duration){
        $update = $pdo->prepare("UPDATE courses 
            SET title = ?, description = ?, category = ?, level = ?, duration = ? 
            WHERE id = ? AND instructor_id = ?");
        if($update->execute([$title, $description, $category, $level, $duration, $course_id, $instructor_id])){
            $message = "<div class='alert alert-success'>Course updated successfully.</div>";
            // Refresh course details
            $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND instructor_id = ?");
            $stmt->execute([$course_id, $instructor_id]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $message = "<div class='alert alert-danger'>Error updating course.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>All fields are required.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Course - Instructor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body { display:flex; min-height:100vh; margin:0; font-family:Arial,sans-serif; }
    .sidebar { width:250px; background:#343a40; color:#fff; flex-shrink:0; }
    .sidebar-header { padding:20px; background:#23272b; text-align:center; }
    .sidebar-header h3 { margin:0; font-size:20px; }
    .sidebar-menu { list-style:none; margin:0; padding:0; }
    .sidebar-menu li { border-bottom:1px solid rgba(255,255,255,0.1); }
    .sidebar-menu li a { display:block; padding:12px 20px; color:#fff; text-decoration:none; transition:background 0.3s; }
    .sidebar-menu li a i { margin-right:10px; }
    .sidebar-menu li a:hover, .sidebar-menu li a.active { background:#495057; }
    .sidebar-menu li a.logout { background:#dc3545; }
    .sidebar-menu li a.logout:hover { background:#c82333; }
    .main-content { flex-grow:1; padding:20px; background:#f8f9fa; }
    .card { border-radius:12px; }
  </style>
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
      <li><a href="manage_courses.php" class="active"><i class="fas fa-book"></i> My Courses</a></li>
      <li><a href="students.php"><i class="fas fa-users"></i> My Students</a></li>
      <li><a href="assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
      <li><a href="submissions.php"><i class="fas fa-file-upload"></i> Submissions</a></li>
      <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Manage Schedule</a></li>
      <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
      <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
      <li><a href="settings.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
      <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="container">
      <h2><i class="fas fa-edit"></i> Edit Course</h2>
      <?= $message ?>
      <div class="card shadow p-4 mt-3">
        <form method="post">
          <div class="mb-3">
            <label for="title" class="form-label">Course Title</label>
            <input type="text" id="title" name="title" class="form-control" 
                   value="<?= htmlspecialchars($course['title']) ?>" required>
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Course Description</label>
            <textarea id="description" name="description" class="form-control" rows="5" required><?= htmlspecialchars($course['description']) ?></textarea>
          </div>
          <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <input type="text" id="category" name="category" class="form-control" 
                   value="<?= htmlspecialchars($course['category']) ?>" required>
          </div>
          <div class="mb-3">
            <label for="level" class="form-label">Level</label>
            <input type="text" id="level" name="level" class="form-control" 
                   value="<?= htmlspecialchars($course['level']) ?>" required>
          </div>
          <div class="mb-3">
            <label for="duration" class="form-label">Duration</label>
            <input type="text" id="duration" name="duration" class="form-control" 
                   value="<?= htmlspecialchars($course['duration']) ?>" required>
          </div>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Save Changes
          </button>
          <a href="manage_courses.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
          </a>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
