<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
require 'db_connect.php';

$success = $error = null;

// ADD COURSE
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_course'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO courses (title, description, instructor, image, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['instructor'] ?? null,
            $_POST['image'] ?? null
        ]);
        $success = "✅ Course added successfully!";
    } catch (Exception $e) {
        $error = "❌ Error adding course: " . $e->getMessage();
    }
}

// DELETE COURSE
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        header("Location: manage_courses.php?deleted=1");
        exit;
    } catch (Exception $e) {
        $error = "❌ Error deleting course: " . $e->getMessage();
    }
}

// UPDATE COURSE
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_course'])) {
    try {
        $stmt = $pdo->prepare("UPDATE courses SET title=?, description=?, instructor=?, image=? WHERE id=?");
        $stmt->execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['instructor'] ?? null,
            $_POST['image'] ?? null,
            $_POST['id']
        ]);
        $success = "💾 Course updated successfully!";
    } catch (Exception $e) {
        $error = "❌ Error updating course: " . $e->getMessage();
    }
}

// FETCH COURSE IF EDITING
$editCourse = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editCourse = $stmt->fetch(PDO::FETCH_ASSOC);
}

// FETCH ALL COURSES
$courses = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>📚 Manage Courses</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f8f9fa;
    margin: 0; min-height: 100vh;
}
body.dark-mode { background: #121212; color: #f8f9fa; }

/* Sidebar */
.sidebar {
    position: fixed; top: 0; left: 0;
    width: 240px; height: 100vh;
    background: #1f1f2c;
    color: #fff;
    display: flex; flex-direction: column;
    box-shadow: 2px 0 10px rgba(0,0,0,0.2);
    transition: all 0.3s;
}
.sidebar-header { padding: 20px; text-align: center; background: #29293d; border-bottom: 1px solid #333; }
.sidebar-header h3 { margin:0; font-size:18px; font-weight:bold; }
.sidebar-menu { list-style: none; padding:0; margin:0; flex:1; }
.sidebar-menu li { width:100%; }
.sidebar-menu a {
    display:flex; align-items:center; gap:10px;
    padding:14px 20px; color:#cfd2dc; text-decoration:none; font-size:15px;
    transition: all 0.3s ease;
}
.sidebar-menu a i { width:20px; text-align:center; font-size:16px; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: #4b6cb7; color:#fff; padding-left:25px; }
.sidebar-menu .logout { background:#d9534f; color:#fff; margin-top:auto; }
.sidebar-menu .logout:hover { background:#c9302c; }
.dark-mode .sidebar { background:#1e1e1e; }
.dark-mode .sidebar a { color:#ddd; }
.dark-mode .sidebar a:hover { background:#333; }

/* Main Content */
.main-content { margin-left:240px; padding:20px; transition: all 0.3s; }
.card { border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
.dark-mode .card { background:#1e1e1e; color:#f8f9fa; }
.btn { border-radius:8px; }
table { background:#fff; border-radius:10px; overflow:hidden; }
.table-dark { background:#343a40; color:#fff; }
.dark-mode .table-dark { background:#2a2a2a; }
</style>
</head>
<body>

<!-- Dark Mode Toggle -->
<button id="toggleDark" class="btn btn-sm btn-outline-secondary position-fixed top-0 end-0 m-3">🌙</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header"><h3>Admin Panel</h3></div>
  <ul class="sidebar-menu">
    <li><a href="dashboard.php"><i class="fas fa-home"></i> <span class="text">Dashboard</span></a></li>
    <li><a href="students.php"><i class="fas fa-users"></i> <span class="text">Students</span></a></li>
    <li><a href="courses.php"><i class="fas fa-book"></i> <span class="text">Courses</span></a></li>
    <li><a href="assign_course.php"><i class="fas fa-tasks"></i> <span class="text">Assign Courses</span></a></li>
    <li><a href="assignments.php"><i class="fas fa-file-alt"></i> <span class="text">Assignments</span></a></li>
    <li><a href="send_message.php"><i class="fas fa-envelope"></i> <span class="text">Send Message</span></a></li>
    <li><a href="manage_courses.php" class="active"><i class="fas fa-cogs"></i> <span class="text">Manage Courses</span></a></li>
    <li><a href="settings.php"><i class="fas fa-user-cog"></i> <span class="text">Admin Settings</span></a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> <span class="text">Logout</span></a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <h2 class="mb-4 text-center">📚 Manage Courses</h2>

    <!-- Alerts -->
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php elseif (isset($_GET['deleted'])): ?>
        <div class="alert alert-warning">🗑️ Course deleted successfully!</div>
    <?php endif; ?>

    <!-- Add/Edit Form -->
    <div class="card p-4 mb-5">
        <h4><?= $editCourse ? "✏️ Edit Course" : "➕ Add New Course" ?></h4>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $editCourse['id'] ?? '' ?>">
            <div class="mb-3">
                <label class="form-label">Course Title</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editCourse['title'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($editCourse['description'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Instructor (optional)</label>
                <input type="text" name="instructor" class="form-control" value="<?= htmlspecialchars($editCourse['instructor'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Image URL (optional)</label>
                <input type="text" name="image" class="form-control" value="<?= htmlspecialchars($editCourse['image'] ?? '') ?>">
            </div>
            <button type="submit" name="<?= $editCourse ? 'update_course' : 'add_course' ?>" class="btn btn-primary w-100">
                <?= $editCourse ? '💾 Update Course' : '➕ Add Course' ?>
            </button>
            <?php if ($editCourse): ?>
                <a href="manage_courses.php" class="btn btn-secondary w-100 mt-2">❌ Cancel</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Course List -->
    <h4 class="mb-3">All Courses</h4>
    <table class="table table-hover table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Instructor</th>
                <th>Created</th>
                <th width="180">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($courses as $course): ?>
            <tr>
                <td><?= $course['id'] ?></td>
                <td><?= htmlspecialchars($course['title']) ?></td>
                <td><?= htmlspecialchars($course['instructor'] ?? "N/A") ?></td>
                <td><?= $course['created_at'] ?></td>
                <td>
                    <a href="?edit=<?= $course['id'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                    <a href="?delete=<?= $course['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this course?')">🗑️ Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($courses)): ?>
            <tr><td colspan="5" class="text-center text-muted">No courses found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
const toggleBtn = document.getElementById('toggleDark');
const body = document.body;
if(localStorage.getItem("dark-mode")==="enabled"){ 
    body.classList.add("dark-mode"); toggleBtn.textContent="☀️"; 
}
toggleBtn.addEventListener("click", ()=>{
    body.classList.toggle("dark-mode");
    toggleBtn.textContent = body.classList.contains("dark-mode") ? "☀️" : "🌙";
    localStorage.setItem("dark-mode", body.classList.contains("dark-mode")?"enabled":"disabled");
});
const sidebar=document.getElementById('sidebar');
</script>
</body>
</html>
