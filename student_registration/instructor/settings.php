<?php
session_start();
require 'includes/config.php';

// Ensure instructor is logged in
if (!isset($_SESSION['instructor'])) {
    header('Location: login.php');
    exit;
}

// Get instructor ID from session
$instructor_id = $_SESSION['instructor']['id'];
$message = "";

// Fetch instructor info from the instructor table
$stmt = $pdo->prepare("SELECT * FROM instructors WHERE id = ?");
$stmt->execute([$instructor_id]);
$instructor = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle case where instructor is not found
if (!$instructor) {
    session_destroy();
    header("Location: login.php?error=notfound");
    exit;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password']; // optional

    // Check if email is already used by another instructor
    $checkStmt = $pdo->prepare("SELECT id FROM instructors WHERE email = ? AND id != ?");
    $checkStmt->execute([$email, $instructor_id]);

    if ($checkStmt->rowCount() > 0) {
        $message = "<div class='alert alert-danger'>Email already in use by another account.</div>";
    } else {
        if (!empty($password)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE instructors SET name = ?, email = ?, password = ? WHERE id = ?");
            $success = $updateStmt->execute([$name, $email, $passwordHash, $instructor_id]);
        } else {
            $updateStmt = $pdo->prepare("UPDATE instructors SET name = ?, email = ? WHERE id = ?");
            $success = $updateStmt->execute([$name, $email, $instructor_id]);
        }

        if ($success) {
            $message = "<div class='alert alert-success'>Profile updated successfully.</div>";
            // Refresh instructor info
            $stmt->execute([$instructor_id]);
            $instructor = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $message = "<div class='alert alert-danger'>Failed to update profile.</div>";
        }
    }
}

// Escape output helper
function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>⚙️ Instructor Settings</title>
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
.card { border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.08); background:#fff; }
.dark-mode .card { background:#1e1e1e; color:#f8f9fa; }

/* Profile Picture */
.profile-pic img { width:100px; height:100px; border-radius:50%; object-fit:cover; }

/* Navbar */
.navbar { background:#fff; box-shadow:0 2px 6px rgba(0,0,0,0.1); padding:10px 20px; }
.dark-mode .navbar { background:#1e1e1e; color:#f8f9fa; }
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
    <li><a href="create_course.php"><i class="fas fa-plus-circle"></i> Create Course</a></li>
    <li><a href="manage_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
    <li><a href="students.php"><i class="fas fa-users"></i> My Students</a></li>
    <li><a href="assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
    <li><a href="submissions.php"><i class="fas fa-file-upload"></i> Submissions</a></li>
    <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Manage Schedule</a></li>
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="settings.php" class="active"><i class="fas fa-cogs"></i> Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="card p-4" style="max-width:600px; margin:auto;">
    <h4 class="mb-3">Instructor Settings</h4>

    <?php if(isset($_GET['success'])): ?>
      <div class="alert alert-success">✅ Profile updated successfully!</div>
    <?php elseif(isset($_GET['error']) && $_GET['error']==='exists'): ?>
      <div class="alert alert-danger">❌ Email already exists!</div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3 text-center profile-pic">
        <?php if(!empty($instructor['profile_pic'])): ?>
          <img src="<?= esc($instructor['profile_pic']) ?>" alt="Profile Picture">
          <div>
            <button type="submit" name="delete_pic" class="btn btn-sm btn-outline-danger mt-2">Remove Picture</button>
          </div>
        <?php else: ?>
          <img src="default.png" alt="Default Profile">
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="<?= esc($instructor['name']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= esc($instructor['email']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">New Password (leave blank to keep current)</label>
        <input type="password" name="password" class="form-control">
      </div>

      <div class="mb-3">
        <label class="form-label">Profile Picture</label>
        <input type="file" name="profile_pic" accept="image/*" class="form-control">
      </div>

      <button type="submit" class="btn btn-primary w-100">Save Changes</button>
    </form>
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