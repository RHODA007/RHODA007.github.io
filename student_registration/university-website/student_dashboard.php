<?php
session_start();
require_once "includes/config.php";

// Simulate logged-in student (replace with real session data)
$student_id = $_SESSION['student']['id'] ?? 1;

// Fetch student name
$stmt = $pdo->prepare("SELECT name FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();
$student_name = $student['name'] ?? "Student";

// Fetch stats
$totalCourses = $pdo->query("SELECT COUNT(*) FROM enrollments WHERE student_id = $student_id")->fetchColumn();
$totalCertificates = $pdo->query("SELECT COUNT(*) FROM certificates WHERE student_id = $student_id")->fetchColumn();
$totalAssignments = $pdo->query("SELECT COUNT(*) FROM assignments WHERE student_id = $student_id")->fetchColumn();
$progress = rand(40, 90); // Placeholder, compute actual progress
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { background: #f5f6fa; }
    .sidebar {
        width: 250px;
        position: fixed;
        top: 0; left: 0;
        height: 100%; background: #343a40; color: white;
        padding-top: 20px;
    }
    .sidebar a {
        display: block; padding: 12px; color: #ddd;
        text-decoration: none; margin: 5px 0;
    }
    .sidebar a:hover { background: #495057; color: #fff; }
    .content {
        margin-left: 260px; padding: 20px;
    }
    .sidebar-menu .logout { background:#d9534f; color:#fff; margin-top:auto; }
.sidebar-menu .logout:hover { background:#c9302c; }
    .card-stat {
        padding: 20px; border-radius: 12px;
        color: white; margin-bottom: 20px;
    }
    .stat-courses { background: #0d6efd; }
    .stat-certificates { background: #198754; }
    .stat-assignments { background: #ffc107; }
    .stat-progress { background: #6f42c1; }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h4 class="text-center mb-4"><i class="fa-solid fa-graduation-cap"></i> MySchool</h4>
    <a href="student_dashboard.php"><i class="fa-solid fa-home"></i> Dashboard</a>
    <a href="my_courses.php"><i class="fa-solid fa-book"></i> My Courses</a>
    <a href="assignments.php"><i class="fa-solid fa-tasks"></i> Assignments</a>
    <a href="certificates.php"><i class="fa-solid fa-certificate"></i> Certificates</a>
    <a href="verify_certificate.php"><i class="fa-solid fa-check-circle"></i> Verify Certificate</a>
    <a href="messages.php"><i class="fa-solid fa-envelope"></i> Messages</a>
    <a href="calendar.php"><i class="fa-solid fa-calendar"></i> Calendar</a>
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </div>

  <!-- Main Content -->
  <div class="content">
    <h2>👋 Welcome, <?= htmlspecialchars($student_name) ?>!</h2>
    <p class="text-muted">Here’s your learning summary and updates.</p>

    <!-- Stats Cards -->
    <div class="row">
      <div class="col-md-3">
        <div class="card-stat stat-courses">
          <h5><i class="fa-solid fa-book"></i> Courses</h5>
          <h3><?= $totalCourses ?></h3>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card-stat stat-certificates">
          <h5><i class="fa-solid fa-award"></i> Certificates</h5>
          <h3><?= $totalCertificates ?></h3>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card-stat stat-assignments">
          <h5><i class="fa-solid fa-tasks"></i> Assignments</h5>
          <h3><?= $totalAssignments ?></h3>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card-stat stat-progress">
          <h5><i class="fa-solid fa-chart-line"></i> Progress</h5>
          <h3><?= $progress ?>%</h3>
        </div>
      </div>
    </div>

    <!-- Progress Chart -->
    <div class="card mt-4 p-3 shadow-sm">
      <h5><i class="fa-solid fa-chart-pie"></i> Learning Progress</h5>
      <canvas id="progressChart"></canvas>
    </div>

    <!-- Upcoming Deadlines -->
    <div class="card mt-4 p-3 shadow-sm">
      <h5><i class="fa-solid fa-calendar-day"></i> Upcoming Deadlines</h5>
      <ul>
        <li>Assignment 1 - <b>Web Dev</b> (Due: 25th Sept)</li>
        <li>Midterm Exam - <b>Data Science</b> (Due: 30th Sept)</li>
      </ul>
    </div>

  </div>

  <script>
    const ctx = document.getElementById('progressChart');
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Completed', 'Remaining'],
        datasets: [{
          data: [<?= $progress ?>, <?= 100 - $progress ?>],
          backgroundColor: ['#0d6efd', '#dee2e6']
        }]
      }
    });
  </script>
</body>
</html>
