<?php
session_start();
if (!isset($_SESSION['student'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/config.php';

// Optional: you could fetch student-specific events later
$student_id = $_SESSION['student']['id'];
$studentStmt = $pdo->prepare("SELECT name FROM students WHERE id = ?");
$studentStmt->execute([$student_id]);
$student = $studentStmt->fetch(PDO::FETCH_ASSOC);

function esc($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Calendar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body{margin:0;font-family:system-ui,Segoe UI,Roboto;display:flex;min-height:100vh;background:#f6f8fb;}
.sidebar{width:240px;background:#fff;border-right:1px solid rgba(0,0,0,.06);padding:20px;position:fixed;inset:0 auto 0 0;}
.sidebar .brand{font-weight:700;margin-bottom:20px;}
.nav-link{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;color:#444;text-decoration:none;margin-bottom:8px;transition:.25s;}
.nav-link:hover{background:rgba(79,70,229,0.1);color:#4f46e5;}
.main{margin-left:240px;padding:28px;width:100%;}
.card{background:#fff;border:1px solid rgba(0,0,0,.06);border-radius:12px;padding:16px;margin-bottom:20px;}
</style>
</head>
<body>

<aside class="sidebar">
    <div class="brand"><i class="fa-solid fa-graduation-cap"></i> MySchool</div>
    <a class="nav-link" href="student_dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
    <a class="nav-link" href="my_courses.php"><i class="fa-solid fa-book"></i> My Courses</a>
    <a class="nav-link" href="progress.php"><i class="fa-solid fa-chart-line"></i> Progress</a>
    <a class="nav-link" href="submissions.php"><i class="fa-solid fa-file-upload"></i> Submissions</a>
    <a class="nav-link" href="messages.php"><i class="fa-solid fa-envelope"></i> Messages</a>
    <a class="nav-link" href="calendar.php"><i class="fa-solid fa-calendar-days"></i> Calendar</a>
    <a class="nav-link" href="settings.php"><i class="fa-solid fa-user-cog"></i> Settings</a>
    <a class="nav-link" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</aside>

<main class="main">
    <h2>My Calendar</h2>
    <div class="card">
        <iframe src="https://calendar.google.com/calendar/embed?src=en.nigerian%23holiday%40group.v.calendar.google.com&ctz=Africa%2FLagos" 
                style="border:0" width="100%" height="600" frameborder="0" scrolling="no"></iframe>
    </div>
</main>

</body>
</html>
