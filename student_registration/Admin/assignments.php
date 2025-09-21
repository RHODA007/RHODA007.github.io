<?php
session_start();
if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}
require 'db_connect.php';

// Handle assignment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $due_date = $_POST['due_date'] ?? '';
    $course_id = $_POST['course_id'] ?? '';
    $file_path = null;

    if (!empty($_FILES['file']['name'])) {
        $upload_dir = "uploads/assignments/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_name = time() . "_" . basename($_FILES['file']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
            $file_path = $target_file;
        }
    }

    if ($title && $description && $due_date && $course_id) {
        $stmt = $pdo->prepare("INSERT INTO assignments (course_id, title, description, due_date, file_path, created_at) 
                               VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$course_id, $title, $description, $due_date, $file_path]);
        $success = "✅ Assignment posted successfully!";
    } else {
        $error = "⚠ Please fill all fields.";
    }
}

// Fetch courses for dropdown
$courses = $pdo->query("SELECT id, title FROM courses")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all assignments with course names
$assignments = $pdo->query("
    SELECT a.id, a.title, a.description, a.due_date, a.file_path, a.created_at, c.title AS course_name
    FROM assignments a
    LEFT JOIN courses c ON a.course_id = c.id
    ORDER BY a.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Assignments - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { font-family:'Segoe UI', sans-serif; margin:0; min-height:100vh; background:#f8f9fa; }
body.dark-mode { background:#121212; color:#f8f9fa; }

/* Sidebar */
.sidebar {
  position: fixed; left:0; top:0; height:100vh; width:240px;
  background: rgba(31,31,47,0.95); color:#fff; display:flex; flex-direction:column; box-shadow:2px 0 10px rgba(0,0,0,0.2);
}
.sidebar-header { padding:20px; text-align:center; background:#29293d; border-bottom:1px solid #333; }
.sidebar-header h3 { margin:0; font-size:18px; font-weight:bold; }
.sidebar-menu { list-style:none; margin:0; padding:0; flex:1; }
.sidebar-menu li { width:100%; }
.sidebar-menu a { display:flex; align-items:center; gap:10px; padding:14px 20px; color:#cfd2dc; text-decoration:none; font-size:15px; transition:all 0.3s ease; }
.sidebar-menu a i { width:20px; text-align:center; font-size:16px; }
.sidebar-menu a:hover, .sidebar-menu a.active { background:#b6c8daff; color:#fff; padding-left:25px; }
.sidebar-menu .logout { background:#d9534f; color:#fff; margin-top:auto; }
.sidebar-menu .logout:hover { background:#c9302c; }

/* Main Content */
.main-content { margin-left:240px; padding:20px; }
.card { border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.1); background:#fff; }
.dark-mode .card { background:#1e1e1e; color:#f8f9fa; }

/* Navbar */
.navbar { background:#fff; box-shadow:0 2px 6px rgba(0,0,0,0.1); padding:10px 20px; margin-bottom:20px; }
.dark-mode .navbar { background:#1e1e1e; color:#f8f9fa; }

/* Table */
.table th { background:#f1f1f1; }
.dark-mode .table th { background:#2a2a2a; }
.table td { vertical-align: middle; }
</style>
</head>
<body>

<!-- Dark Mode Toggle -->
<button id="toggleDark" class="btn btn-sm btn-outline-secondary position-fixed top-0 end-0 m-3">🌙</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header"><h3>Admin Panel</h3></div>
  <ul class="sidebar-menu">
    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="courses.php"><i class="fas fa-book"></i> Courses</a></li>
    <li><a href="students.php"><i class="fas fa-users"></i> Students</a></li>
    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="assignments.php" class="active"><i class="fas fa-file-alt"></i> Assignments</a></li>
    <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <nav class="navbar">
    <span class="fw-bold fs-5"><i class="fas fa-file-alt"></i> Assignments</span>
  </nav>

  <!-- Post Assignment Form -->
  <div class="card p-4 mb-4">
    <h4 class="mb-3">Post New Assignment</h4>
    <?php if(!empty($success)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif(!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Due Date</label>
        <input type="date" name="due_date" class="form-control" required>
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3" required></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">Course</label>
        <select name="course_id" class="form-select" required>
          <option value="">-- Select Course --</option>
          <?php foreach($courses as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">File (optional)</label>
        <input type="file" name="file" class="form-control">
      </div>
      <div class="col-12 text-end">
        <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Post</button>
      </div>
    </form>
  </div>

  <!-- Existing Assignments Table -->
  <div class="card p-4">
    <h4 class="mb-3">All Assignments</h4>
    <?php if($assignments): ?>
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle text-center">
          <thead>
            <tr>
              <th>#</th>
              <th>Title</th>
              <th>Course</th>
              <th>Due Date</th>
              <th>Created At</th>
              <th>File</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($assignments as $a): ?>
              <tr>
                <td><?= $a['id'] ?></td>
                <td><?= htmlspecialchars($a['title']) ?></td>
                <td><?= htmlspecialchars($a['course_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($a['due_date']) ?></td>
                <td><?= htmlspecialchars($a['created_at']) ?></td>
                <td>
                  <?php if($a['file_path'] && file_exists($a['file_path'])): ?>
                    <a href="<?= $a['file_path'] ?>" target="_blank">View</a>
                  <?php else: ?>
                    N/A
                  <?php endif; ?>
                </td>
                <td>
                  <a href="edit_assignment.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                  <a href="delete_assignment.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger" 
                     onclick="return confirm('Delete this assignment?');">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-center text-muted">No assignments posted yet.</p>
    <?php endif; ?>
  </div>
</div>

<script>
const toggleBtn = document.getElementById('toggleDark');
const body = document.body;
if(localStorage.getItem("dark-mode")==="enabled"){ body.classList.add("dark-mode"); toggleBtn.textContent="☀️"; }
toggleBtn.addEventListener("click", ()=>{
  body.classList.toggle("dark-mode");
  toggleBtn.textContent = body.classList.contains("dark-mode") ? "☀️" : "🌙";
  localStorage.setItem("dark-mode", body.classList.contains("dark-mode")?"enabled":"disabled");
});
</script>
</body>
</html>
