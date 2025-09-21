<?php
session_start();
require 'includes/config.php';

if (!isset($_SESSION['instructor'])) {
    header("Location: login.php");
    exit;
}

$instructor_id = $_SESSION['instructor']['id'] ?? null;
$message = "";

// Handle Add Timetable Entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_timetable'])) {
    $course_name = trim($_POST['course_name']);
    $day_of_week = trim($_POST['day_of_week']);
    $start_time  = $_POST['start_time'];
    $end_time    = $_POST['end_time'];
    $venue       = trim($_POST['venue']);

    if ($course_name && $day_of_week && $start_time && $end_time) {
        $stmt = $pdo->prepare("INSERT INTO timetable (instructor_id, course_name, day_of_week, start_time, end_time, venue) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$instructor_id, $course_name, $day_of_week, $start_time, $end_time, $venue]);
        $message = "<div class='alert alert-success'>Timetable entry added successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>All fields are required.</div>";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM timetable WHERE id = ? AND instructor_id = ?");
    $stmt->execute([$id, $instructor_id]);
    $message = "<div class='alert alert-warning'>Timetable entry deleted.</div>";
}

// Fetch timetable for this instructor
$stmt = $pdo->prepare("SELECT * FROM timetable WHERE instructor_id = ? ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), start_time");
$stmt->execute([$instructor_id]);
$timetable = $stmt->fetchAll(PDO::FETCH_ASSOC);

function esc($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instructor Timetable</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { background:#f8f9fa; font-family:Arial,sans-serif; margin:0; }
.sidebar { position: fixed; left:0; top:0; height:100vh; width:240px; background:#1f1f2c; color:#fff; display:flex; flex-direction:column; box-shadow:2px 0 10px rgba(0,0,0,0.2); }
.sidebar-header { padding:20px; text-align:center; background:#29293d; border-bottom:1px solid #333; }
.sidebar-header h3 { margin:0; font-size:18px; font-weight:bold; }
.sidebar-menu { list-style:none; margin:0; padding:0; flex:1; }
.sidebar-menu li { width:100%; }
.sidebar-menu a { display:flex; align-items:center; gap:10px; padding:14px 20px; color:#cfd2dc; text-decoration:none; font-size:15px; transition:all 0.3s ease; }
.sidebar-menu a:hover, .sidebar-menu a.active { background:#b6c8da; color:#fff; padding-left:25px; }
.sidebar-menu .logout { background:#d9534f; color:#fff; margin-top:auto; }
.sidebar-menu .logout:hover { background:#c9302c; }
.main { margin-left:240px; padding:20px; }
.card { border-radius:8px; padding:20px; margin-bottom:20px; box-shadow:0 4px 12px rgba(0,0,0,0.08); }
.table thead { background:#0d6efd; color:#fff; }
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
    <li><a href="timetable.php" class="active"><i class="fas fa-calendar-alt"></i> Timetable</a></li>
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="settings.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<div class="main">
  <h4 class="mb-3">📅 My Timetable</h4>
  <?= $message ?>

  <!-- Add Timetable Form -->
  <div class="card">
    <h5>Add Timetable Entry</h5>
    <form method="POST">
      <div class="row g-3">
        <div class="col-md-4">
          <input type="text" name="course_name" class="form-control" placeholder="Course Name" required>
        </div>
        <div class="col-md-2">
          <select name="day_of_week" class="form-select" required>
            <option value="">Day</option>
            <option>Monday</option><option>Tuesday</option><option>Wednesday</option>
            <option>Thursday</option><option>Friday</option><option>Saturday</option><option>Sunday</option>
          </select>
        </div>
        <div class="col-md-2">
          <input type="time" name="start_time" class="form-control" required>
        </div>
        <div class="col-md-2">
          <input type="time" name="end_time" class="form-control" required>
        </div>
        <div class="col-md-2">
          <input type="text" name="venue" class="form-control" placeholder="Venue / Link">
        </div>
        <div class="col-md-12 text-end">
          <button type="submit" name="add_timetable" class="btn btn-primary mt-2"><i class="fas fa-plus"></i> Add</button>
        </div>
      </div>
    </form>
  </div>

  <!-- Timetable Display -->
  <div class="card">
    <h5>My Schedule</h5>
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead>
          <tr>
            <th>Course</th>
            <th>Day</th>
            <th>Start</th>
            <th>End</th>
            <th>Venue / Link</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if($timetable): ?>
            <?php foreach($timetable as $row): ?>
              <tr>
                <td><?= esc($row['course_name']) ?></td>
                <td><?= esc($row['day_of_week']) ?></td>
                <td><?= esc(date("h:i A", strtotime($row['start_time']))) ?></td>
                <td><?= esc(date("h:i A", strtotime($row['end_time']))) ?></td>
                <td><?= esc($row['venue']) ?></td>
                <td>
                  <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this entry?')" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center">No timetable entries yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
