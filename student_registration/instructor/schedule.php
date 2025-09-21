<?php
session_start();
if(!isset($_SESSION['instructor'])){
    header('Location: login.php');
    exit;
}

require '../db_connect.php'; // Adjust path
$instructor_id = $_SESSION['instructor']['id'];
$message = "";

// Handle add/edit/delete schedule
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){
    $action = $_POST['action'];
    $course_id = $_POST['course_id'];
    $day = $_POST['day'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $platform_link = trim($_POST['platform_link'] ?? '');

    if($action === 'add'){
        $stmt = $pdo->prepare("INSERT INTO schedules (course_id, instructor_id, day, start_time, end_time, platform_link, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        if($stmt->execute([$course_id, $instructor_id, $day, $start_time, $end_time, $platform_link])){
            $message = "<div class='alert alert-success'>Schedule added successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to add schedule.</div>";
        }
    } elseif($action === 'edit' && isset($_POST['schedule_id'])){
        $schedule_id = $_POST['schedule_id'];
        $stmt = $pdo->prepare("UPDATE schedules SET course_id=?, day=?, start_time=?, end_time=?, platform_link=? WHERE id=? AND instructor_id=?");
        if($stmt->execute([$course_id, $day, $start_time, $end_time, $platform_link, $schedule_id, $instructor_id])){
            $message = "<div class='alert alert-success'>Schedule updated successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to update schedule.</div>";
        }
    } elseif($action === 'delete' && isset($_POST['schedule_id'])){
        $schedule_id = $_POST['schedule_id'];
        $stmt = $pdo->prepare("DELETE FROM schedules WHERE id=? AND instructor_id=?");
        if($stmt->execute([$schedule_id, $instructor_id])){
            $message = "<div class='alert alert-success'>Schedule deleted successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to delete schedule.</div>";
        }
    }
}

// Fetch courses
$coursesStmt = $pdo->prepare("SELECT id, title FROM courses WHERE instructor_id=?");
$coursesStmt->execute([$instructor_id]);
$courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch schedules
$schedulesStmt = $pdo->prepare("
    SELECT s.id, s.day, s.start_time, s.end_time, s.platform_link, c.title AS course_title 
    FROM schedules s 
    JOIN courses c ON s.course_id=c.id 
    WHERE s.instructor_id=? 
    ORDER BY FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), s.start_time ASC
");
$schedulesStmt->execute([$instructor_id]);
$schedules = $schedulesStmt->fetchAll(PDO::FETCH_ASSOC);

function esc($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Schedule</title>
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
    <li><a href="schedule.php" class="active"><i class="fas fa-calendar-alt"></i> Manage Schedule</a></li>
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="settings.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<div class="main">
    <h2 class="mb-4">Manage Schedule</h2>
    <?= $message ?>

    <div class="card">
        <h5>Add New Schedule</h5>
        <form method="post" class="row g-3">
            <input type="hidden" name="action" value="add">
            <div class="col-md-3">
                <label class="form-label">Course</label>
                <select name="course_id" class="form-select" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach($courses as $c): ?>
                        <option value="<?= esc($c['id']) ?>"><?= esc($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Day</label>
                <select name="day" class="form-select" required>
                    <?php foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d): ?>
                        <option value="<?= $d ?>"><?= $d ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Start Time</label>
                <input type="time" name="start_time" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">End Time</label>
                <input type="time" name="end_time" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Zoom / Platform Link</label>
                <input type="url" name="platform_link" class="form-control" placeholder="Enter session link" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Add Schedule</button>
            </div>
        </form>
    </div>
    <td>
    <div class="d-flex flex-column gap-1">
        <button type="submit" name="action" value="edit" class="btn btn-success btn-sm"><i class="fas fa-edit"></i> Update</button>
        <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
        <?php if(!empty($s['platform_link'])): ?>
            <a href="zoom_meeting.php?schedule_id=<?= esc($s['id']) ?>" target="_blank" class="btn btn-primary btn-sm mt-1">
                <i class="fas fa-video"></i> Join Zoom
            </a>
        <?php endif; ?>
    </div>
</td>


    <div class="card">
        <h5>My Schedules</h5>
        <?php if($schedules): ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Day</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Zoom / Link</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($schedules as $s): ?>
                <tr>
                    <form method="post">
                        <input type="hidden" name="schedule_id" value="<?= esc($s['id']) ?>">
                        <td>
                            <select name="course_id" class="form-select form-select-sm" required>
                                <?php foreach($courses as $c): ?>
                                    <option value="<?= esc($c['id']) ?>" <?= $c['title']==$s['course_title']?'selected':'' ?>><?= esc($c['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="day" class="form-select form-select-sm" required>
                                <?php foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d): ?>
                                    <option value="<?= $d ?>" <?= $d==$s['day']?'selected':'' ?>><?= $d ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="time" name="start_time" class="form-control form-control-sm" value="<?= esc($s['start_time']) ?>" required></td>
                        <td><input type="time" name="end_time" class="form-control form-control-sm" value="<?= esc($s['end_time']) ?>" required></td>
                        <td><input type="url" name="platform_link" class="form-control form-control-sm" value="<?= esc($s['platform_link']) ?>" required></td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <button type="submit" name="action" value="edit" class="btn btn-success btn-sm"><i class="fas fa-edit"></i> Update</button>
                                <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
                            </div>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No schedules yet.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
