<?php
session_start();
require '../db_connect.php'; 

// Redirect if not logged in as instructor
if (!isset($_SESSION['instructor'])) {
    header("Location: login.php");
    exit;
}

$course_id = $_GET['id'] ?? null;
$message = "";

if ($course_id) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Delete course
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        if ($stmt->execute([$course_id])) {
            $message = "<div class='success'>✅ Course deleted successfully.</div>";
        } else {
            $message = "<div class='error'>❌ Failed to delete course.</div>";
        }
    } else {
        // Fetch course info for confirmation
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$course_id]);
        $course = $stmt->fetch();
    }
} else {
    $message = "<div class='error'>⚠ Invalid request. No course selected.</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Delete Course - Instructor Panel</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      display: flex;
      background: #f4f6f9;
    }
    /* Sidebar */
    .sidebar {
      width: 250px;
      background: #2c3e50;
      color: #fff;
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      padding-top: 20px;
    }
    .sidebar-header {
      text-align: center;
      margin-bottom: 20px;
    }
    .sidebar-header h3 {
      margin: 0;
      font-size: 20px;
    }
    .sidebar-menu {
      list-style: none;
      padding: 0;
    }
    .sidebar-menu li {
      margin: 10px 0;
    }
    .sidebar-menu a {
      color: #fff;
      text-decoration: none;
      display: block;
      padding: 10px 20px;
      transition: background 0.3s;
    }
    .sidebar-menu a:hover,
    .sidebar-menu a.active {
      background: #1abc9c;
      border-radius: 5px;
    }
    .sidebar-menu .logout {
      color: #e74c3c;
    }
    /* Main content */
    .main-content {
      margin-left: 250px;
      padding: 20px;
      flex: 1;
    }
    h2 {
      color: #2c3e50;
      margin-bottom: 20px;
    }
    .success {
      padding: 10px;
      background: #2ecc71;
      color: #fff;
      margin-bottom: 15px;
      border-radius: 5px;
    }
    .error {
      padding: 10px;
      background: #e74c3c;
      color: #fff;
      margin-bottom: 15px;
      border-radius: 5px;
    }
    .confirm-box {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      text-align: center;
    }
    .confirm-box h3 {
      margin-bottom: 15px;
    }
    .confirm-box form {
      margin-top: 15px;
    }
    button {
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin: 5px;
    }
    .btn-danger {
      background: #e74c3c;
      color: #fff;
    }
    .btn-secondary {
      background: #95a5a6;
      color: #fff;
    }
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
    <h2>Delete Course</h2>
    <?php if ($message): ?>
      <?= $message ?>
    <?php elseif ($course): ?>
      <div class="confirm-box">
        <h3>Are you sure you want to delete <strong><?= htmlspecialchars($course['title']) ?></strong>?</h3>
        <form method="post">
          <button type="submit" class="btn-danger"><i class="fas fa-trash"></i> Yes, Delete</button>
          <a href="manage_courses.php" class="btn-secondary"><i class="fas fa-times"></i> Cancel</a>
        </form>
      </div>
    <?php else: ?>
      <div class="error">Course not found.</div>
    <?php endif; ?>
  </div>
</body>
</html>
