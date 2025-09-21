<?php
session_start();
if (!isset($_SESSION['instructor'])) {
    header('Location: instructor_login.php');
    exit;
}
require 'includes/config.php';

$instructor_id = $_SESSION['instructor']['id'];
$message = "";

// Handle updates (progress, grade, feedback)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enrollment_id'])) {
    $enrollment_id = (int)$_POST['enrollment_id'];
    $progress      = max(0, min(100, (int)($_POST['progress'] ?? 0)));
    $grade         = trim($_POST['grade'] ?? '');
    $feedback      = trim($_POST['feedback'] ?? '');

    // Update enrollments table
    $updateEnroll = $pdo->prepare("
        UPDATE enrollments e
        JOIN courses c ON e.course_id = c.id
        SET e.progress = ?
        WHERE e.id = ? AND c.instructor_id = ?
    ");
    $ok1 = $updateEnroll->execute([$progress, $enrollment_id, $instructor_id]);

    // Update latest submission (grade + feedback)
    $updateSub = $pdo->prepare("
        UPDATE submissions s
        JOIN enrollments e 
            ON e.student_id = s.user_id 
           AND e.course_id = (SELECT course_id FROM enrollments WHERE id = ? LIMIT 1)
        JOIN courses c ON e.course_id = c.id
        SET s.grade = ?, s.feedback = ?
        WHERE e.id = ? AND c.instructor_id = ?
        ORDER BY s.submitted_at DESC
        LIMIT 1
    ");
    $ok2 = $updateSub->execute([$grade, $feedback, $enrollment_id, $instructor_id]);

    $message = ($ok1 || $ok2) ? "✅ Updated successfully!" : "❌ Failed to update.";
}

// Fetch enrollments
$stmt = $pdo->prepare("
    SELECT e.id AS enrollment_id, s.id AS student_id, s.name, s.email, s.photo,
           c.title AS course_name, e.enrolled_at, e.progress,
           sub.grade, sub.feedback
    FROM enrollments e
    JOIN students s ON e.student_id = s.id
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN (
        SELECT ss.user_id, ss.assignment_id, ss.grade, ss.feedback
        FROM submissions ss
        WHERE ss.id IN (
            SELECT MAX(id) FROM submissions GROUP BY user_id, assignment_id
        )
    ) sub ON sub.user_id = s.id
    WHERE c.instructor_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([$instructor_id]);
$enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instructor - Progress</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { font-family: 'Segoe UI', sans-serif; background:#f8f9fa; margin:0; min-height:100vh; }
.sidebar {
  position: fixed; left:0; top:0; height:100vh; width:240px;
  background: rgba(31,31,47,0.95); color:#fff;
  display:flex; flex-direction:column; box-shadow:2px 0 10px rgba(0,0,0,0.2);
}
.sidebar-header { padding:20px; text-align:center; background:#29293d; border-bottom:1px solid #333; }
.sidebar-header h3 { margin:0; font-size:18px; font-weight:bold; }
.sidebar-menu { list-style:none; margin:0; padding:0; flex:1; }
.sidebar-menu li { width:100%; }
.sidebar-menu a {
  display:flex; align-items:center; gap:10px; padding:14px 20px;
  color:#cfd2dc; text-decoration:none; font-size:15px; transition: all 0.3s ease;
}
.sidebar-menu a i { width:20px; text-align:center; font-size:16px; }
.sidebar-menu a:hover, .sidebar-menu a.active { background:#4e73df; color:#fff; padding-left:25px; }
.sidebar-menu .logout { background:#d9534f; color:#fff; margin-top:auto; }
.sidebar-menu .logout:hover { background:#c9302c; }

.main-content { margin-left:240px; padding:20px; }
.card { border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.08); background:#fff; }
.table th { background:#f1f3f5; }
.table img { object-fit:cover; }

.progress { height:18px; }
.progress-bar { font-size:12px; font-weight:600; }

.dark-mode { background:#121212; color:#f8f9fa; }
.dark-mode .card { background:#1e1e1e; color:#f8f9fa; }
.dark-mode .sidebar { background:#1f1f2c; }
.dark-mode .sidebar a { color:#ddd; }
.dark-mode .sidebar a:hover { background:#333; }
.dark-mode .table th { background:#2a2a2a; color:#fff; }

.search-box { max-width:400px; }
</style>
</head>
<body>

<!-- Dark Mode Toggle -->
<button id="toggleDark" class="btn btn-sm btn-outline-secondary position-fixed top-0 end-0 m-3">🌙</button>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-header">
    <h3>Instructor Panel</h3>
  </div>
  <ul class="sidebar-menu">
    <li><a href="instructor_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="manage_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
    <li><a href="create_course.php"><i class="fas fa-plus-circle"></i> Create Course</a></li>
    <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Manage Schedule</a></li>
    <li><a href="assignments.php"><i class="fas fa-file-alt"></i> Assignments</a></li>
    <li><a href="students.php"><i class="fas fa-users"></i> My Students</a></li>
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="progress.php" class="active"><i class="fas fa-chart-line"></i> Progress</a></li>
    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="settings.php"><i class="fas fa-user-cog"></i> Settings</a></li>
    <li><a href="../logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>📊 Manage Student Progress</h2>
    <input type="text" id="search" class="form-control search-box" placeholder="🔍 Search student or course...">
  </div>

  <?php if($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <div class="card p-3">
    <div class="table-responsive">
      <table class="table table-striped align-middle text-center" id="progressTable">
        <thead>
          <tr>
            <th>Student</th>
            <th>Email</th>
            <th>Course</th>
            <th>Enrolled</th>
            <th>Progress</th>
            <th>Grade</th>
            <th>Feedback</th>
            <th>Update</th>
          </tr>
        </thead>
        <tbody>
          <?php if($enrollments): foreach($enrollments as $row): ?>
          <tr>
            <td>
              <?php if($row['photo'] && file_exists('../uploads/'.$row['photo'])): ?>
                <img src="../uploads/<?= htmlspecialchars($row['photo']) ?>" width="35" height="35" class="rounded-circle me-2">
              <?php else: ?>
                <img src="../default-avatar.png" width="35" height="35" class="rounded-circle me-2">
              <?php endif; ?>
              <?= htmlspecialchars($row['name']) ?>
            </td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['course_name']) ?></td>
            <td><?= htmlspecialchars($row['enrolled_at']) ?></td>
            <td>
              <div class="progress mb-1" style="height:18px;">
                <div class="progress-bar bg-success" style="width: <?= (int)$row['progress'] ?>%;">
                  <?= (int)$row['progress'] ?>%
                </div>
              </div>
            </td>
            <td><?= htmlspecialchars($row['grade'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['feedback'] ?? '-') ?></td>
            <td>
              <form method="POST" class="d-flex flex-column gap-1">
                <input type="hidden" name="enrollment_id" value="<?= $row['enrollment_id'] ?>">
                <input type="number" name="progress" min="0" max="100" value="<?= (int)$row['progress'] ?>" class="form-control form-control-sm" placeholder="Progress %">
                <input type="text" name="grade" value="<?= htmlspecialchars($row['grade'] ?? '') ?>" class="form-control form-control-sm" placeholder="Grade (A, B, etc)">
                <input type="text" name="feedback" value="<?= htmlspecialchars($row['feedback'] ?? '') ?>" class="form-control form-control-sm" placeholder="Feedback">
                <button class="btn btn-sm btn-primary mt-1"><i class="fas fa-save"></i> Save</button>
              </form>
            </td>
          </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="8" class="text-muted">No enrollments found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
// Dark Mode
const toggleBtn = document.getElementById('toggleDark');
const body = document.body;
if(localStorage.getItem("dark-mode")==="enabled"){ 
  body.classList.add("dark-mode"); 
  toggleBtn.textContent="☀️"; 
}
toggleBtn.addEventListener("click", ()=> {
  body.classList.toggle("dark-mode");
  toggleBtn.textContent = body.classList.contains("dark-mode") ? "☀️" : "🌙";
  localStorage.setItem("dark-mode", body.classList.contains("dark-mode")?"enabled":"disabled");
});

// Search Filter
document.getElementById('search').addEventListener('keyup', function(){
  let value = this.value.toLowerCase();
  document.querySelectorAll('#progressTable tbody tr').forEach(row => {
    row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
  });
});
</script>

</body>
</html>
