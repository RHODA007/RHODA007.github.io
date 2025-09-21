<?php 
session_start();
if(!isset($_SESSION['instructor'])) {
    header('Location: instructor_login.php');
    exit;
}
require 'includes/config.php';

$instructor_id = $_SESSION['instructor']['id'];

// Search + Pagination
$search = trim($_GET['search'] ?? '');
$limit = 5;
$page = isset($_GET['page']) && ctype_digit($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

if ($search) {
    $stmt = $pdo->prepare("
        SELECT s.*, c.title AS course_name, e.enrolled_at, e.progress
        FROM enrollments e
        JOIN students s ON e.student_id = s.id
        JOIN courses c ON e.course_id = c.id
        WHERE c.instructor_id = ? AND (s.name LIKE ? OR s.email LIKE ?)
        ORDER BY e.enrolled_at DESC 
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute([$instructor_id, "%$search%", "%$search%"]);

    $totalStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM enrollments e
        JOIN students s ON e.student_id = s.id
        JOIN courses c ON e.course_id = c.id
        WHERE c.instructor_id = ? AND (s.name LIKE ? OR s.email LIKE ?)
    ");
    $totalStmt->execute([$instructor_id, "%$search%", "%$search%"]);
    $total = $totalStmt->fetchColumn();
} else {
    $stmt = $pdo->prepare("
        SELECT s.*, c.title AS course_name, e.enrolled_at, e.progress
        FROM enrollments e
        JOIN students s ON e.student_id = s.id
        JOIN courses c ON e.course_id = c.id
        WHERE c.instructor_id = ?
        ORDER BY e.enrolled_at DESC 
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute([$instructor_id]);

    $totalStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM enrollments e
        JOIN students s ON e.student_id = s.id
        JOIN courses c ON e.course_id = c.id
        WHERE c.instructor_id = ?
    ");
    $totalStmt->execute([$instructor_id]);
    $total = $totalStmt->fetchColumn();
}

$students = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
$totalPages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instructor - My Students</title>
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
.sidebar-menu a:hover, .sidebar-menu a.active { background:#b6c8daff; color:#fff; padding-left:25px; }
.sidebar-menu .logout { background:#d9534f; color:#fff; margin-top:auto; }
.sidebar-menu .logout:hover { background:#c9302c; }

.main-content { margin-left:240px; padding:20px; }
.card { border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.08); background:#fff; }
.table th { background:#f1f3f5; }
.table img { object-fit:cover; }

.pagination .page-link { color:#333; border:none; }
.pagination .active .page-link { background:#0d6efd; color:#fff; }

.progress { height:18px; }
.progress-bar { font-size:12px; font-weight:600; }

.dark-mode { background:#121212; color:#f8f9fa; }
.dark-mode .card { background:#1e1e1e; color:#f8f9fa; }
.dark-mode .sidebar { background:#1f1f2c; }
.dark-mode .sidebar a { color:#ddd; }
.dark-mode .sidebar a:hover { background:#333; }
.dark-mode .table th { background:#2a2a2a; color:#fff; }
</style>
</head>
<body>

<!-- Dark Mode Toggle -->
<button id="toggleDark" class="btn btn-sm btn-outline-secondary position-fixed top-0 end-0 m-3">🌙</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h3>Instructor Panel</h3>
  </div>
  <ul class="sidebar-menu">
    <li><a href="instructor_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="manage_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
    <li><a href="create_course.php"><i class="fas fa-plus-circle"></i> Create Course</a></li>
    <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Manage Schedule</a></li>
    <li><a href="assignments.php"><i class="fas fa-file-alt"></i> Assignments</a></li>
    <li><a href="students.php" class="active"><i class="fas fa-users"></i> My Students</a></li>
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
     <li><a href="progress.php"><i class="fas fa-chart-line"></i> Progress</a></li>
    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="settings.php"><i class="fas fa-user-cog"></i> Settings</a></li>
    <li><a href="../logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <h2 class="mb-4">👨‍🎓 My Students</h2>

  <div class="card p-3 mb-4">
    <form method="GET" class="d-flex justify-content-center">
      <input type="text" class="form-control w-50" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn btn-primary ms-2">Search</button>
    </form>
  </div>

  <div class="card p-3">
    <?php if(!empty($students)): ?>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle text-center">
        <thead>
          <tr>
            <th>#</th><th>Name</th><th>Email</th><th>Phone</th>
            <th>Course</th><th>Enrolled</th><th>Progress</th><th>Photo</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($students as $student): ?>
          <tr>
            <td><?= htmlspecialchars($student['id']) ?></td>
            <td><?= htmlspecialchars($student['name']) ?></td>
            <td><?= htmlspecialchars($student['email']) ?></td>
            <td><?= htmlspecialchars($student['phone']) ?></td>
            <td><?= htmlspecialchars($student['course_name']) ?></td>
            <td><?= htmlspecialchars($student['enrolled_at']) ?></td>
            <td>
              <div class="progress">
                <div class="progress-bar bg-success" role="progressbar" 
                     style="width: <?= (int)$student['progress'] ?>%;" 
                     aria-valuenow="<?= (int)$student['progress'] ?>" 
                     aria-valuemin="0" aria-valuemax="100">
                     <?= (int)$student['progress'] ?>%
                </div>
              </div>
            </td>
            <td>
              <?php if(!empty($student['photo']) && file_exists('../uploads/'.$student['photo'])): ?>
                <img src="../uploads/<?= htmlspecialchars($student['photo']) ?>" width="40" height="40" class="rounded-circle">
              <?php else: ?>
                <img src="../default-avatar.png" width="40" height="40" class="rounded-circle opacity-50">
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <nav class="mt-3">
      <ul class="pagination justify-content-center">
        <?php for($i=1; $i<=$totalPages; $i++): ?>
          <li class="page-item <?= $i==$page?'active':'' ?>">
            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
    <?php else: ?>
      <p class="text-center text-muted">No students found.</p>
    <?php endif; ?>
  </div>
</div>

<script>
const toggleBtn = document.getElementById('toggleDark');
const body = document.body;
if(localStorage.getItem("dark-mode")==="enabled"){ 
  body.classList.add("dark-mode"); 
  toggleBtn.textContent="☀️"; 
}
toggleBtn.addEventListener("click", ()=>{
  body.classList.toggle("dark-mode");
  toggleBtn.textContent = body.classList.contains("dark-mode") ? "☀️" : "🌙";
  localStorage.setItem("dark-mode", body.classList.contains("dark-mode")?"enabled":"disabled");
});
</script>

</body>
</html>
