<?php
session_start();
if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}
require 'db_connect.php';

// Stats
$totalInstructors = $pdo->query("SELECT COUNT(*) FROM instructors")->fetchColumn();

// Latest instructors
$recentInstructors = $pdo->query("
    SELECT i.*, 
           (SELECT COUNT(*) FROM courses c WHERE c.instructor_id = i.id) AS course_count
    FROM instructors i
    ORDER BY i.created_at DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Instructors</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body { background:#f8f9fa; }

    /* Sidebar Styling */
    .sidebar {
      position: fixed;
      left: 0;
      top: 0;
      height: 100vh;
      width: 240px;
      background: #1f1f2cff;
      color: #fff;
      display: flex;
      flex-direction: column;
      box-shadow: 2px 0 10px rgba(0,0,0,0.2);
    }
    .sidebar-header {
      padding: 20px;
      text-align: center;
      background: #29293d;
      border-bottom: 1px solid #333;
    }
    .sidebar-header h3 {
      margin: 0; font-size: 18px; font-weight: bold;
    }
    .sidebar-menu {
      list-style: none; margin: 0; padding: 0; flex: 1;
    }
    .sidebar-menu li { width: 100%; }
    .sidebar-menu a {
      display: flex; align-items: center; gap: 10px;
      padding: 14px 20px; color: #cfd2dc; text-decoration: none; font-size: 15px;
      transition: all 0.3s ease;
    }
    .sidebar-menu a i { width: 20px; text-align: center; font-size: 16px; }
    .sidebar-menu a:hover, .sidebar-menu a.active {
      background: #b6c8daff; color: #fff; padding-left: 25px;
    }
    .sidebar-menu .logout { background: #d9534f; color: #fff; margin-top: auto; }
    .sidebar-menu .logout:hover { background: #c9302c; }

    .main { margin-left:240px; padding:20px; }
    .card { border-radius:8px; }
    .instructor-photo { width:50px; height:50px; border-radius:50%; object-fit:cover; }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-header"><h3>Admin Panel</h3></div>
  <ul class="sidebar-menu">
    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="students.php"><i class="fas fa-users"></i> Students</a></li>
    <li><a href="instructors.php" class="active"><i class="fas fa-users"></i> Instructors</a></li>
    <li><a href="courses.php"><i class="fas fa-book"></i> Courses</a></li>
    <li><a href="assign_course.php"><i class="fas fa-tasks"></i> Assign Courses</a></li>
    <li><a href="assignments.php"><i class="fas fa-file-alt"></i> Assignments</a></li>
    <li><a href="submissions.php"><i class="fas fa-file-upload"></i> Submissions</a></li>
    <li><a href="messages.php"><i class="fas fa-bell"></i> Notifications</a></li>
    <li><a href="send_message.php"><i class="fas fa-envelope"></i> Send Message</a></li>
    <li><a href="manage_courses.php"><i class="fas fa-cogs"></i> Manage Courses</a></li>
    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="settings.php"><i class="fas fa-user-cog"></i> Admin Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main">
  <h2 class="mb-4">Instructors Management</h2>

  <!-- Stats -->
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card text-center p-3 shadow-sm">
        <h6>Total Instructors</h6>
        <h3><?= $totalInstructors ?></h3>
      </div>
    </div>
  </div>

  <!-- Latest Instructors -->
  <div class="card p-3 mb-4 shadow-sm">
    <h5>Latest Instructors</h5>
    <table class="table table-striped table-hover mt-3">
      <thead>
        <tr>
          <th>Photo</th>
          <th>Name</th>
          <th>Email</th>
          <th>Bio</th>
          <th>Education</th>
          <th>Experience</th>
          <th>Courses</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($recentInstructors as $inst): ?>
          <tr>
            <td>
              <?php if(!empty($inst['photo'])): ?>
                <img src="<?= htmlspecialchars($inst['photo']) ?>" class="instructor-photo">
              <?php else: ?>
                <img src="assets/images/default.png" class="instructor-photo">
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($inst['name']) ?></td>
            <td><?= htmlspecialchars($inst['email']) ?></td>
            <td><?= htmlspecialchars($inst['bio'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($inst['education'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($inst['experience'] ?? 'N/A') ?></td>
            <td><?= $inst['course_count'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
