<?php
session_start();
if (!isset($_SESSION['student'])) {
    header("Location: login.php");
    exit;
}

require 'includes/config.php';

$student_id = $_SESSION['student']['id'];

// Get the student's course from students table
$stmt = $pdo->prepare("SELECT course FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student_course = $stmt->fetchColumn();

// Fetch assignments for that course
$stmt = $pdo->prepare("
    SELECT a.*, c.title AS course_title, i.name AS instructor_name
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    JOIN instructors i ON c.instructor_id = i.id
    WHERE c.title = ?
    ORDER BY a.due_date ASC
");
$stmt->execute([$student_course]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard - Assignments</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { background:#f8f9fa; font-family:Poppins,sans-serif; }
.sidebar {
  position: fixed; left: 0; top: 0; height: 100vh; width: 240px;
  background: #1f1f2c; color: #fff; display: flex; flex-direction: column;
}
.sidebar-header { padding: 20px; text-align: center; background: #29293d; border-bottom:1px solid #333; }
.sidebar-header h3 { margin:0; font-size:18px; font-weight:bold; }
.sidebar-menu { list-style:none; margin:0; padding:0; flex:1; }
.sidebar-menu li { width:100%; }
.sidebar-menu a {
  display:flex; align-items:center; gap:10px;
  padding:14px 20px; color:#cfd2dc; text-decoration:none; font-size:15px;
  transition: all 0.3s ease;
}
.sidebar-menu a:hover, .sidebar-menu a.active { background:#0d6efd; color:#fff; }
.sidebar-menu .logout { background:#d9534f; margin-top:auto; }
.sidebar-menu .logout:hover { background:#c9302c; }
.main { margin-left:240px; padding:20px; }
.card { border-radius:12px; }
.table th { background:#e9ecef; }
.status.completed { color:green; font-weight:bold; }
.status.pending { color:orange; font-weight:bold; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-header"><h3>Student Panel</h3></div>
  <ul class="sidebar-menu">
    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="courses.php"><i class="fas fa-book"></i> My Courses</a></li>
    <li><a href="assignments.php" class="active"><i class="fas fa-tasks"></i> Assignments</a></li>
    <li><a href="submissions.php"><i class="fas fa-file-upload"></i> Submissions</a></li>
    <li><a href="exams.php"><i class="fas fa-pencil-alt"></i> Exams</a></li>
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="settings.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main">
  <h2 class="mb-4">📝 My Assignments</h2>

  <div class="card p-4 shadow-sm">
    <?php if($assignments): ?>
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Course</th>
              <th>Instructor</th>
              <th>Title</th>
              <th>Description</th>
              <th>Due Date</th>
              <th>File</th>
              <th>Status</th>
              <th>Created At</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($assignments as $index => $a): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($a['course_title']) ?></td>
                <td><?= htmlspecialchars($a['instructor_name']) ?></td>
                <td><?= htmlspecialchars($a['title']) ?></td>
                <td><?= htmlspecialchars($a['description']) ?></td>
                <td><?= htmlspecialchars($a['due_date']) ?></td>
                <td>
                  <?php if($a['file_path']): ?>
                    <a href="../<?= htmlspecialchars($a['file_path']) ?>" target="_blank">Download</a>
                  <?php else: ?>
                    N/A
                  <?php endif; ?>
                </td>
                <td>
                  <?php
                    $today = date("Y-m-d");
                    if ($a['due_date'] < $today) {
                      echo "<span class='status completed'>Completed</span>";
                    } else {
                      echo "<span class='status pending'>Pending</span>";
                    }
                  ?>
                </td>
                <td><?= htmlspecialchars($a['created_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-muted text-center">No assignments assigned yet.</p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
