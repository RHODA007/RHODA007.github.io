<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
require 'db_connect.php';

// Fetch all students
$students = $pdo->query("SELECT id, name FROM students")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $due_date = $_POST['due_date'] ?? '';

    if ($student_id && $title && $description && $due_date) {
        $stmt = $pdo->prepare("INSERT INTO assignments (student_id, title, description, due_date, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$student_id, $title, $description, $due_date]);

        $success = "✅ Assignment sent successfully!";
    } else {
        $error = "⚠ Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Assignment</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">>
  <style>
    body { font-family: "Segoe UI", sans-serif; background:#f8f9fa; }

    /* Sidebar */
    .sidebar {
      position: fixed; left: 0; top: 0;
      height: 100vh; width: 240px;
      background: #1f1f2cff; color: #fff;
      display: flex; flex-direction: column;
      box-shadow: 2px 0 10px rgba(0,0,0,0.2);
    }
    .sidebar-header { padding: 20px; text-align: center; background: #29293d; border-bottom: 1px solid #333; }
    .sidebar-header h3 { margin: 0; font-size: 18px; font-weight: bold; }
    .sidebar-menu { list-style: none; margin: 0; padding: 0; flex: 1; }
    .sidebar-menu li { width: 100%; }
    .sidebar-menu a {
      display: flex; align-items: center; gap: 10px;
      padding: 14px 20px; color: #cfd2dc; text-decoration: none;
      font-size: 15px; transition: all 0.3s ease;
    }
    .sidebar-menu a i { width: 20px; text-align: center; font-size: 16px; }
    .sidebar-menu a:hover, .sidebar-menu a.active { background: #b6c8daff; color: #fff; padding-left: 25px; }
    .sidebar-menu .logout { background: #d9534f; color: #fff; margin-top: auto; }
    .sidebar-menu .logout:hover { background: #c9302c; }

    /* Main content */
    .main { margin-left: 240px; padding: 20px; }
    .card { border-radius: 8px; }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h3>Admin Panel</h3>
  </div>
  <ul class="sidebar-menu">
    <li><a href="dashboard.php"><i class="fas fa-home"></i> <span class="text">Dashboard</span></a></li>
    <li><a href="students.php"><i class="fas fa-users"></i> <span class="text">Students</span></a></li>
    <li><a href="courses.php"><i class="fas fa-book"></i> <span class="text">Courses</span></a></li>
    <li><a href="assign_course.php"><i class="fas fa-tasks"></i> <span class="text">Assign Courses</span></a></li>
    <li><a href="assignments.php"><i class="fas fa-file-alt"></i> <span class="text">Assignments</span></a></li>
    <li><a href="send_message.php"><i class="fas fa-envelope"></i> <span class="text">Send Message</span></a></li>
    <li><a href="manage_courses.php"><i class="fas fa-cogs"></i> <span class="text">Manage Courses</span></a></li>
    <li><a href="settings.php"><i class="fas fa-user-cog"></i> <span class="text">Admin Settings</span></a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> <span class="text">Logout</span></a></li>
  </ul>
</div>


<!-- Main Content -->
<div class="main">
  <h2 class="mb-4">➕ Add Assignment</h2>

  <?php if (!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
  <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

  <div class="card p-4 shadow-sm">
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Select Student</label>
        <select name="student_id" class="form-select" required>
          <option value="">-- Choose Student --</option>
          <?php foreach($students as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Assignment Title</label>
        <input type="text" name="title" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" rows="4" class="form-control" required></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Due Date</label>
        <input type="date" name="due_date" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-primary w-100">Send Assignment</button>
    </form>
  </div>
</div>

</body>
</html>
