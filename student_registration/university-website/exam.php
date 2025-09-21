<?php
session_start();
if(!isset($_SESSION['student'])) { 
  header("Location: login.php"); 
  exit; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>📝 My Examinations</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <?php include 'sidebar.php'; ?>

  <div class="dashboard-content">
    <h1>📝 My Examinations</h1>
    <p>Track your upcoming and completed exams here.</p>

    <div class="page-card">
      <h2>Upcoming Exams</h2>
      <table class="styled-table">
        <thead>
          <tr>
            <th>Course</th>
            <th>Date</th>
            <th>Time</th>
            <th>Venue</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Engineering Graphics</td>
            <td>20 Sept 2025</td>
            <td>10:00 AM</td>
            <td>Hall A</td>
          </tr>
          <tr>
            <td>Mathematical Engineering</td>
            <td>25 Sept 2025</td>
            <td>2:00 PM</td>
            <td>Hall B</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
