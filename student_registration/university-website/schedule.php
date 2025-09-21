<?php
session_start();
if(!isset($_SESSION['student'])){
    header("Location: login.php");
    exit;
}

require 'includes/config.php';

$student_id = $_SESSION['student']['id'];
$message = "";

// Fetch enrolled courses with progress
$stmt = $pdo->prepare("
    SELECT c.id, c.title, e.progress
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.student_id = ?
");
$stmt->execute([$student_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch schedules for enrolled courses
$schedulesStmt = $pdo->prepare("
    SELECT s.id, s.day, s.start_time, s.end_time, s.platform_link, 
           c.title AS course_title, e.progress, i.name AS instructor_name
    FROM schedules s
    JOIN courses c ON s.course_id = c.id
    JOIN enrollments e ON e.course_id = c.id
    JOIN instructors i ON s.instructor_id = i.id
    WHERE e.student_id = ?
    ORDER BY FIELD(s.day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), s.start_time ASC
");
$schedulesStmt->execute([$student_id]);
$schedules = $schedulesStmt->fetchAll(PDO::FETCH_ASSOC);


// Simple attendance: if current time > end_time, assume "Attended Pending"
date_default_timezone_set('Africa/Lagos');

function esc($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Schedule</title>
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
.card { border-radius:8px; padding:20px; margin-bottom:20px; }
table th, table td { vertical-align: middle !important; }
.progress { height:18px; }
</style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-header"><h3>Student Panel</h3></div>
  <ul class="sidebar-menu">
    <li><a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="my_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
    <li><a href="progress.php"><i class="fas fa-chart-line"></i> Progress</a></li>
    <li><a href="schedule.php" class="active"><i class="fas fa-calendar-alt"></i> My Schedule</a></li>
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<div class="main">
    <h2 class="mb-4">My Schedule</h2>

    <?php if($schedules): ?>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Course</th>
                <th>Day</th>
                <th>Start</th>
                <th>End</th>
                <th>Progress</th>
                <th>Platform / Zoom Link</th>
                <th>Join</th>
                <th>Attendance</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($schedules as $s): ?>
            <?php
                $endDateTime = strtotime(date('Y-m-d') . ' ' . $s['end_time']);
                $attendance = time() > $endDateTime ? "Attended / Pending" : "Upcoming";
            ?>
            <tr>
                <td><?= esc($s['course_title']) ?></td>
                <td><?= esc($s['day']) ?></td>
                <td><?= esc($s['start_time']) ?></td>
                <td><?= esc($s['end_time']) ?></td>
                <td><?= esc($s['instructor_name']) ?></td>
                <td>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: <?= esc($s['progress']) ?>%;" aria-valuenow="<?= esc($s['progress']) ?>" aria-valuemin="0" aria-valuemax="100"><?= esc($s['progress']) ?>%</div>
                    </div>
                </td>
                <td><a href="<?= esc($s['platform_link']) ?>" target="_blank"><?= esc($s['platform_link']) ?></a></td>
                <td>
                    <?php if($s['platform_link']): ?>
                        <a href="<?= esc($s['platform_link']) ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fas fa-video"></i> Join</a>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
                <td><?= $attendance ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No schedules available yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
