<<?php
session_start();
if(!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require 'db_connect.php';

// Existing counts
$admin_id = $_SESSION['admin'];

$totalUnreadMessages = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
$totalUnreadMessages->execute([$admin_id]);
$totalUnreadMessages = $totalUnreadMessages->fetchColumn();

$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalCourses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalAssignments = $pdo->query("SELECT COUNT(*) FROM assignments")->fetchColumn();

// ✅ New: Instructors counts
$totalInstructorsStmt = $pdo->query("SELECT COUNT(*) FROM instructors");
$totalInstructors = $totalInstructorsStmt->fetchColumn();

$pendingInstructorsStmt = $pdo->query("SELECT COUNT(*) FROM instructors WHERE approved = 0");
$pendingInstructors = $pendingInstructorsStmt->fetchColumn();
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Home</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f4f6f9;
    margin:0;
    padding:20px;
}
h1 { text-align:center; margin-bottom:40px; color:#333; }

.dashboard-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:25px;
    margin-bottom:40px;
}
.card-instructors { background:#6f42c1; color:#fff; }


.card-summary {
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
    padding:25px;
    text-align:center;
    transition:0.3s;
}
.card-summary:hover { transform: translateY(-5px); box-shadow:0 8px 20px rgba(0,0,0,0.15); }

.card-summary i { font-size:2.5rem; margin-bottom:15px; }
.card-students { background:#0d6efd; color:#fff; }
.card-courses { background:#198754; color:#fff; }
.card-assignments { background:#ffc107; color:#000; }
.card-messages { background:#dc3545; color:#fff; }

.btn-custom {
    border-radius:8px;
    padding:10px 20px;
    font-weight:600;
    transition:0.3s;
}
.btn-custom:hover { transform: scale(1.05); }

.actions { display:flex; flex-wrap:wrap; gap:20px; justify-content:center; }
.actions a { min-width:180px; }
</style>
</head>
<body>

<h1>🏠 Admin Dashboard</h1>

<div class="dashboard-grid">
    <div class="card-summary card-students">
        <i class="fas fa-users"></i>
        <h3><?= $totalStudents ?></h3>
        <p>Total Students</p>
        <a href="students.php" class="btn btn-light btn-custom">View</a>
    </div>
    <div class="card-summary card-instructors" style="background:#6f42c1; color:#fff;">
    <i class="fas fa-chalkboard-teacher"></i>
    <h3><?= $totalInstructors ?></h3>
    <p>Total Instructors</p>
    <p class="text-warning" style="font-size:0.9rem;"><?= $pendingInstructors ?> Pending Approval</p>
    <a href="instructor_permission.php" class="btn btn-light btn-custom">Review Instructors</a>
</div>

    <div class="card-summary card-courses">
        <i class="fas fa-book"></i>
        <h3><?= $totalCourses ?></h3>
        <p>Total Courses</p>
        <a href="courses.php" class="btn btn-light btn-custom">View</a>
    </div>
    <div class="card-summary card-assignments">
        <i class="fas fa-file-alt"></i>
        <h3><?= $totalAssignments ?></h3>
        <p>Total Assignments</p>
        <a href="assignments.php" class="btn btn-dark btn-custom">View</a>
    </div>
    <div class="card-summary card-messages">
    <i class="fas fa-envelope"></i>
    <h3><?= $totalUnreadMessages ?></h3>
    <p>Unread Messages</p>
    <a href="messages.php" class="btn btn-light btn-custom">View Inbox</a>
</div>

</div>

<div class="actions">
    <a href="register.php" class="btn btn-primary btn-custom">Register Student</a>
    <a href="dashboard.php" class="btn btn-success btn-custom"> Admin Dashboard</a>
    <a href="assign_course.php" class="btn btn-warning btn-custom"> Assign Courses</a>
    <a href="export.php" class="btn btn-info btn-custom"> Export PDF</a>
</div>

</body>
</html>
