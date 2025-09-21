<?php
session_start();
if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}
require 'db_connect.php';

// Stats
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$maleCount = $pdo->query("SELECT COUNT(*) FROM students WHERE gender='Male'")->fetchColumn();
$femaleCount = $pdo->query("SELECT COUNT(*) FROM students WHERE gender='Female'")->fetchColumn();
$recentStudents = $pdo->query("SELECT * FROM students ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    body { background:#f8f9fa; }
    .sidebar {
      width: 220px; height: 100vh;
      position: fixed; top: 0; left: 0;
      background: #fff; border-right: 1px solid #ddd;
      padding-top: 20px;
    }
    .sidebar a {
      display:block; padding:12px 20px; color:#333; text-decoration:none;
    }
    .sidebar a:hover { background:#f1f1f1; color:#0d6efd; }
    .main { margin-left:220px; padding:20px; }
    .card { border-radius:8px; }

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
  margin: 0;
  font-size: 18px;
  font-weight: bold;
}

.sidebar-menu {
  list-style: none;
  margin: 0;
  padding: 0;
  flex: 1;
}

.sidebar-menu li {
  width: 100%;
}

.sidebar-menu a {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 20px;
  color: #cfd2dc;
  text-decoration: none;
  font-size: 15px;
  transition: all 0.3s ease;
}

.sidebar-menu a i {
  width: 20px;
  text-align: center;
  font-size: 16px;
}

.sidebar-menu a:hover,
.sidebar-menu a.active {
  background: #b6c8daff;
  color: #fff;
  padding-left: 25px;
}

.sidebar-menu .logout {
  background: #d9534f;
  color: #fff;
  margin-top: auto;
}

.sidebar-menu .logout:hover {
  background: #c9302c;
}

  </style>
</head>
<body>

<!-- Sidebar -->
<!-- Sidebar -->
  <div class="sidebar" id="sidebar"> 
  <div class="sidebar-header">
    <h3>Admin Panel</h3>
  </div>
  <ul class="sidebar-menu">
    <li><a href="dashboard.php"><i class="fas fa-home"></i> <span class="text">Dashboard</span></a></li>
    <li><a href="students.php"><i class="fas fa-users"></i> <span class="text">Students</span></a></li>
    <li><a href="instructors.php"><i class="fas fa-users"></i> <span class="text">Insturctors</span></a></li>
    <li><a href="courses.php"><i class="fas fa-book"></i> <span class="text">Courses</span></a></li>
    <li><a href="assign_course.php"><i class="fas fa-tasks"></i> <span class="text">Assign Courses</span></a></li>
    <li><a href="assignments.php"><i class="fas fa-file-alt"></i> <span class="text">Assignments</span></a></li>
    <li><a href="submissions.php"><i class="fas fa-file-upload"></i> <span class="text">Submissions</span></a></li>
    <li><a href="messages.php"><i class="fas fa-bell"></i> <span class="text">Notifications</span></a></li>
    <li><a href="send_message.php"><i class="fas fa-envelope"></i> <span class="text">Send Message</span></a></li>
    <li><a href="manage_courses.php"><i class="fas fa-cogs"></i> <span class="text">Manage Courses</span></a></li>
    <li><a href="reports.php"><i class="fas fa-chart-bar"></i><span class="text">Reports </span></a></li>
    <li><a href="settings.php"><i class="fas fa-user-cog"></i> <span class="text">Admin Settings</span></a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> <span class="text">Logout</span></a></li>
  </ul>
</div>



<!-- Main Content -->
<div class="main">
  <h2 class="mb-4">Welcome, Admin</h2>

  <!-- Stats -->
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card text-center p-3 shadow-sm">
        <h6>Total Students</h6>
        <h3><?= $totalStudents ?></h3>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-center p-3 shadow-sm">
        <h6>Male Students</h6>
        <h3><?= $maleCount ?></h3>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-center p-3 shadow-sm">
        <h6>Female Students</h6>
        <h3><?= $femaleCount ?></h3>
      </div>
    </div>
  </div>

  <!-- Latest Students -->
  <div class="card p-3 mb-4 shadow-sm">
    <h5>Latest Students</h5>
    <table class="table table-striped table-hover mt-3">
      <thead>
        <tr><th>Name</th><th>Email</th><th>Phone</th><th>Gender</th></tr>
      </thead>
      <tbody>
        <?php foreach($recentStudents as $stu): ?>
          <tr>
            <td><?= htmlspecialchars($stu['name']) ?></td>
            <td><?= htmlspecialchars($stu['email']) ?></td>
            <td><?= htmlspecialchars($stu['phone']) ?></td>
            <td><?= htmlspecialchars($stu['gender']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Assign Courses -->
  <div class="card p-3 mb-4 shadow-sm">
    <h5>Assign Course to Student</h5>
    <form method="POST" action="assign_course.php" class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Select Student</label>
        <select name="student_id" class="form-select" required>
          <?php
          $students = $pdo->query("SELECT id, name FROM students")->fetchAll();
          foreach($students as $s){
              echo "<option value='{$s['id']}'>".htmlspecialchars($s['name'])."</option>";
          }
          ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Select Course</label>
        <select name="course_id" class="form-select" required>
          <?php
          $courses = $pdo->query("SELECT id, course_name FROM courses")->fetchAll();
          foreach($courses as $c){
              echo "<option value='{$c['id']}'>".htmlspecialchars($c['course_name'])."</option>";
          }
          ?>
        </select>
      </div>
      <div class="col-12 text-end">
        <button type="submit" class="btn btn-primary">Assign</button>
      </div>
    </form>
  </div>

  <!-- Messages -->
  <div class="card p-3 shadow-sm">
    <h5>Send Message</h5>
    <form method="POST" action="send_message.php" class="row g-3 mb-3">
      <div class="col-md-4">
        <label class="form-label">Select Student</label>
        <select name="receiver_id" class="form-select" required>
          <?php foreach($students as $s){ echo "<option value='{$s['id']}'>".htmlspecialchars($s['name'])."</option>"; } ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Message</label>
        <input type="text" name="message" class="form-control" required>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-success w-100">Send</button>
      </div>
    </form>

    <h6>Recent Messages</h6>
    <div style="max-height:200px; overflow-y:auto;">
      <?php
      $msgs = $pdo->query("
        SELECT m.message, m.created_at, s.name AS student_name 
        FROM messages m 
        JOIN students s ON m.receiver_id = s.id 
        ORDER BY m.created_at DESC LIMIT 5
      ")->fetchAll();
      foreach($msgs as $msg){
          echo "<p><strong>".htmlspecialchars($msg['student_name'])."</strong>: ".htmlspecialchars($msg['message'])." <small class='text-muted'>({$msg['created_at']})</small></p>";
      }
      ?>
    </div>
  </div>
</div>

</body>
</html>
