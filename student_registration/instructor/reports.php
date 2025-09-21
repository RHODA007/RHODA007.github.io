<?php
session_start();
require 'includes/config.php';

if(!isset($_SESSION['instructor'])){
    header("Location: login.php");
    exit;
}

$instructor_id = $_SESSION['instructor']['id'];
$message = "";

// Handle add report
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])){
    $action = $_POST['action'];
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $grade = trim($_POST['grade']);

    if($action==='add'){
        $stmt = $pdo->prepare("INSERT INTO reports (student_id, instructor_id, course_id, title, description, grade, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        if($stmt->execute([$student_id, $instructor_id, $course_id, $title, $description, $grade])){
            $message = "<div class='alert alert-success'>Report added successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to add report.</div>";
        }
    }
}

// Fetch instructor courses
$coursesStmt = $pdo->prepare("SELECT id, title FROM courses WHERE instructor_id=?");
$coursesStmt->execute([$instructor_id]);
$courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch students enrolled in any course of this instructor
$studentsStmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.name
    FROM users u
    JOIN enrollments e ON u.id = e.user_id
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ?
    ORDER BY u.name
");
$studentsStmt->execute([$instructor_id]);
$students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);


// Fetch all reports
$reportsStmt = $pdo->prepare("
    SELECT r.id, r.title, r.description, r.grade, r.created_at,
           u.name AS student_name, c.title AS course_title
    FROM reports r
    JOIN users u ON r.student_id=u.id
    JOIN courses c ON r.course_id=c.id
    WHERE r.instructor_id=?
    ORDER BY r.created_at DESC
");
$reportsStmt->execute([$instructor_id]);
$reports = $reportsStmt->fetchAll(PDO::FETCH_ASSOC);

function esc($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instructor Reports</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body{background:#f8f9fa;font-family:Arial,sans-serif;margin:0;}
.sidebar{position:fixed;left:0;top:0;height:100vh;width:240px;background:#1f1f2c;color:#fff;display:flex;flex-direction:column;box-shadow:2px 0 10px rgba(0,0,0,0.2);}
.sidebar-header{padding:20px;text-align:center;background:#29293d;border-bottom:1px solid #333;}
.sidebar-header h3{margin:0;font-size:18px;font-weight:bold;}
.sidebar-menu{list-style:none;margin:0;padding:0;flex:1;}
.sidebar-menu li{width:100%;}
.sidebar-menu a{display:flex;align-items:center;gap:10px;padding:14px 20px;color:#cfd2dc;text-decoration:none;font-size:15px;transition:all 0.3s ease;}
.sidebar-menu a:hover,.sidebar-menu a.active{background:#0d6efd;color:#fff;padding-left:25px;}
.sidebar-menu .logout{background:#d9534f;color:#fff;margin-top:auto;}
.sidebar-menu .logout:hover{background:#c9302c;}
.main{margin-left:240px;padding:20px;}
.card{border-radius:8px;padding:20px;margin-bottom:20px;box-shadow:0 4px 12px rgba(0,0,0,0.08);}
table th,table td{vertical-align:middle !important;}
</style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-header"><h3>Instructor Panel</h3></div>
  <ul class="sidebar-menu">
    <li><a href="instructor_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="create_course.php"><i class="fas fa-plus-circle"></i> Create Course</a></li>
    <li><a href="manage_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
    <li><a href="students.php"><i class="fas fa-users"></i> My Students</a></li>
    <li><a href="assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
    <li><a href="submissions.php"><i class="fas fa-file-upload"></i> Submissions</a></li>
    <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Manage Schedule</a></li>
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="settings.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<div class="main">
  <h4 class="mb-3">📊 Manage Reports</h4>
  <?= $message ?>

  <div class="card">
    <h5>Add New Report</h5>
    <form method="post" class="row g-3">
      <input type="hidden" name="action" value="add">
      <div class="col-md-3">
        <label class="form-label">Student</label>
        <select name="student_id" class="form-select" required>
    <option value="">-- Select Student --</option>
    <?php foreach($students as $s): ?>
        <option value="<?= htmlspecialchars($s['id']) ?>">
            <?= htmlspecialchars($s['name']) ?>
        </option>
    <?php endforeach; ?>
</select>

      </div>
      <div class="col-md-3">
        <label class="form-label">Course</label>
        <select name="course_id" class="form-select" required>
          <option value="">-- Select Course --</option>
          <?php foreach($courses as $c): ?>
            <option value="<?= esc($c['id']) ?>"><?= esc($c['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Grade</label>
        <input type="text" name="grade" class="form-control">
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="3" class="form-control" required></textarea>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Add Report</button>
      </div>
    </form>
  </div>

  <div class="card">
    <h5>All Reports</h5>
    <?php if($reports): ?>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Student</th>
          <th>Course</th>
          <th>Title</th>
          <th>Description</th>
          <th>Grade</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($reports as $r): ?>
        <tr>
          <td><?= esc($r['student_name']) ?></td>
          <td><?= esc($r['course_title']) ?></td>
          <td><?= esc($r['title']) ?></td>
          <td><?= esc($r['description']) ?></td>
          <td><?= esc($r['grade']) ?></td>
          <td><?= esc(date('d M Y', strtotime($r['created_at']))) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <p>No reports yet.</p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
