<?php
session_start();
if(!isset($_SESSION['instructor'])){
    header('Location: login.php');
    exit;
}
require_once 'includes/config.php';
// Stats
$totalCourses = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE instructor_id=?");
$totalCourses->execute([$_SESSION['instructor']['id']]);
$totalCourses = $totalCourses->fetchColumn();

$totalStudents = $pdo->prepare("
    SELECT COUNT(DISTINCT e.student_id) 
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ?
");
$totalStudents->execute([$_SESSION['instructor']['id']]);
$totalStudents = $totalStudents->fetchColumn();

// Fetch all instructor’s courses
$myCourses = $pdo->prepare("SELECT * FROM courses WHERE instructor_id=? ORDER BY created_at DESC");
$myCourses->execute([$_SESSION['instructor']['id']]);
$myCourses = $myCourses->fetchAll(PDO::FETCH_ASSOC);

// Fetch students enrolled in instructor's courses
$myStudents = $pdo->prepare("
    SELECT DISTINCT s.id, s.name, s.email 
    FROM enrollments e
    JOIN students s ON e.student_id = s.id
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ?
");
$myStudents->execute([$_SESSION['instructor']['id']]);
$myStudents = $myStudents->fetchAll(PDO::FETCH_ASSOC);

// Fetch student progress
$progressData = $pdo->prepare("
    SELECT s.name AS student_name, c.title AS course_title, e.progress 
    FROM enrollments e
    JOIN students s ON e.student_id = s.id
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ?
    ORDER BY s.name
");
$progressData->execute([$_SESSION['instructor']['id']]);
$progressData = $progressData->fetchAll(PDO::FETCH_ASSOC);


// Fetch timetable/schedules
$schedules = $pdo->prepare("
    SELECT s.id, c.title AS course_title, s.day, s.start_time, s.end_time, s.room 
    FROM schedules s
    JOIN courses c ON s.course_id = c.id
    WHERE c.instructor_id = ?
    ORDER BY FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), start_time
");
$schedules->execute([$_SESSION['instructor']['id']]);
$schedules = $schedules->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Instructor Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body { background:#f8f9fa; }
    .sidebar {
      position: fixed; left: 0; top: 0; height: 100vh; width: 240px;
      background: #1f1f2c; color: #fff; display: flex; flex-direction: column;
      box-shadow: 2px 0 10px rgba(0,0,0,0.2); z-index: 1000;
    }
    .sidebar-header {
      padding: 20px; text-align: center; background: #29293d; border-bottom: 1px solid #333;
    }
    .sidebar-header h3 { margin: 0; font-size: 18px; font-weight: bold; }
    .sidebar-menu { list-style: none; margin: 0; padding: 0; flex: 1; }
    .sidebar-menu li { width: 100%; }
    .sidebar-menu a {
      display: flex; align-items: center; gap: 10px;
      padding: 14px 20px; color: #cfd2dc; text-decoration: none; font-size: 15px;
      transition: all 0.3s ease;
    }
    .sidebar-menu a:hover, .sidebar-menu a.active {
      background: #b6c8da; color: #fff; padding-left: 25px;
    }
    .sidebar-menu .logout { background: #d9534f; color: #fff; margin-top: auto; }
    .sidebar-menu .logout:hover { background: #c9302c; }
    .main { margin-left:240px; padding:20px; }
    .card { border-radius:8px; }
    .progress { height: 18px; }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-header">
    <h3>Instructor Panel</h3>
  </div>
  <ul class="sidebar-menu">
    <li><a href="instructor_dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="create_course.php"><i class="fas fa-plus-circle"></i> Create Course</a></li>
    <li><a href="manage_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
    <li><a href="students.php" class="active"><i class="fas fa-users"></i> My Students</a></li>
    <li><a href="assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
    <li><a href="submissions.php"><i class="fas fa-file-upload"></i> Submissions</a></li>
      <li><a href="timetable.php" class="active"><i class="fas fa-calendar-alt"></i> Timetable</a></li>
    <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Manage Schedule</a></li>
    <li><a href="progress.php"><i class="fas fa-chart-line"></i> Students Progress</a></li>
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="settings.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main">
  <h2 class="mb-4">Welcome, <?= htmlspecialchars($_SESSION['instructor']['name']) ?></h2>

  <!-- Stats -->
  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card text-center p-3 shadow-sm">
        <h6>Total Courses</h6>
        <h3><?= $totalCourses ?></h3>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card text-center p-3 shadow-sm">
        <h6>Total Students</h6>
        <h3><?= $totalStudents ?></h3>
      </div>
    </div>
  </div>

  <!-- My Courses -->
  <div class="card p-3 shadow-sm mb-4">
    <h5 class="mb-3">My Courses</h5>
    <a href="create_course.php" class="btn btn-sm btn-primary mb-3"><i class="fas fa-plus"></i> Add New Course</a>
    <?php if($myCourses): ?>
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>Title</th>
            <th>Image</th>
            <th>Description</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($myCourses as $course): ?>
            <tr>
              <td><?= htmlspecialchars($course['title']) ?></td>
              <td><img src="../<?= htmlspecialchars($course['image']) ?>" alt="img" width="80"></td>
              <td><?= htmlspecialchars(substr($course['description'],0,40)) ?>...</td>
              <td><?= htmlspecialchars($course['created_at']) ?></td>
              <td>
                <a href="edit_course.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                <a href="delete_course.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('Delete this course?')">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="text-muted">You haven’t created any courses yet.</p>
    <?php endif; ?>
  </div>

  <!-- Student Progress -->
  <div class="card p-3 shadow-sm mb-4">
    <h5 class="mb-3">Student Progress</h5>
    <?php if($progressData): ?>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Student</th>
            <th>Course</th>
            <th>Progress</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($progressData as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['student_name']) ?></td>
              <td><?= htmlspecialchars($row['course_title']) ?></td>
              <td>
                <div class="progress">
                  <div class="progress-bar bg-success" role="progressbar" style="width: <?= $row['progress'] ?>%">
                    <?= $row['progress'] ?>%
                  </div>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="text-muted">No progress data available.</p>
    <?php endif; ?>
  </div>

  <!-- Class Schedule -->
  <div class="card p-3 shadow-sm">
    <h5 class="mb-3">Class Schedule & Timetable</h5>
    <a href="schedule.php" class="btn btn-sm btn-primary mb-3"><i class="fas fa-calendar-plus"></i> Add Schedule</a>
    <?php if($schedules): ?>
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>Course</th>
            <th>Day</th>
            <th>Time</th>
            <th>Room</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($schedules as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['course_title']) ?></td>
              <td><?= htmlspecialchars($s['day']) ?></td>
              <td><?= htmlspecialchars($s['start_time'].' - '.$s['end_time']) ?></td>
              <td><?= htmlspecialchars($s['room']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="text-muted">No schedules created yet.</p>
    <?php endif; ?>
  </div>

</div>
</body>
</html>
