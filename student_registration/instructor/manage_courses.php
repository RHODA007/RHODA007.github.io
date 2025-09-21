<?php
session_start();
if(!isset($_SESSION['instructor'])){
    header("Location: login.php");
    exit;
}

require '../db_connect.php'; // adjust path

$instructor_id = $_SESSION['instructor']['id'];
$message = "";

// Delete course if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $course_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ? AND instructor_id = ?");
    if ($stmt->execute([$course_id, $instructor_id])) {
        $message = "<div class='alert alert-success'>Course deleted successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error deleting course.</div>";
    }
}

// Fetch courses created by this instructor
$stmt = $pdo->prepare("SELECT * FROM courses WHERE instructor_id = ? ORDER BY created_at DESC");
$stmt->execute([$instructor_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function to safely get column values
function getValue($array, $key) {
    return isset($array[$key]) ? htmlspecialchars($array[$key]) : 'N/A';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage Courses - Instructor</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body {
    display:flex;
    min-height:100vh;
    margin:0;
    font-family:Arial, sans-serif;
    background:#f8f9fa;
}
.sidebar {
    position: fixed;
    left:0; top:0;
    height:100vh; width:240px;
    background:#1f1f2c; color:#fff;
    display:flex; flex-direction:column;
    box-shadow:2px 0 10px rgba(0,0,0,0.2);
}
.sidebar-header { padding:20px; text-align:center; background:#29293d; border-bottom:1px solid #333; }
.sidebar-header h3 { margin:0; font-size:18px; font-weight:bold; }
.sidebar-menu { list-style:none; margin:0; padding:0; flex:1; }
.sidebar-menu li { width:100%; }
.sidebar-menu a { display:flex; align-items:center; gap:10px; padding:14px 20px; color:#cfd2dc; text-decoration:none; font-size:15px; transition:all 0.3s ease; }
.sidebar-menu a:hover, .sidebar-menu a.active { background:#b6c8da; color:#fff; padding-left:25px; }
.sidebar-menu .logout { background:#d9534f; color:#fff; margin-top:auto; }
.sidebar-menu .logout:hover { background:#c9302c; }

.main-content {
    flex-grow: 1;
    padding: 30px;
    background: #f8f9fa;
    margin-left: 240px; /* Add this line to avoid overlapping the sidebar */
}

.card { border-radius:12px; padding:20px; margin-bottom:20px; box-shadow:0 4px 12px rgba(0,0,0,0.08); background:#fff; }
.table th { background:#e9ecef; }
.actions a { margin-right:8px; }
h2 { font-weight:600; margin-bottom:20px; }
.alert { border-radius:10px; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-header"><h3>Instructor Panel</h3></div>
  <ul class="sidebar-menu">
    <li><a href="instructor_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="create_course.php"><i class="fas fa-plus-circle"></i> Create Course</a></li>
    <li><a href="manage_courses.php" class="active"><i class="fas fa-book"></i> My Courses</a></li>
    <li><a href="students.php"><i class="fas fa-users"></i> My Students</a></li>
    <li><a href="assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
    <li><a href="submissions.php"><i class="fas fa-file-upload"></i> Submissions</a></li>
    <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Manage Schedule</a></li>
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="settings.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="container-fluid">
        <h2><i class="fas fa-book"></i> My Courses</h2>
        <?= $message ?>
        <div class="card">
            <?php if(!empty($courses)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Course Title</th>
                            <th>Category</th>
                            <th>Level</th>
                            <th>Duration</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($courses as $course): ?>
                        <tr>
                            <td><?= getValue($course, 'id') ?></td>
                            <td><?= getValue($course, 'title') ?></td>
                            <td><?= getValue($course, 'category') ?></td>
                            <td><?= getValue($course, 'level') ?></td>
                            <td><?= getValue($course, 'duration') ?></td>
                            <td><?= getValue($course, 'created_at') ?></td>
                            <td class="actions">
                                <a href="edit_course.php?id=<?= getValue($course, 'id') ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="manage_courses.php?delete=<?= getValue($course, 'id') ?>" class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this course?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-center text-muted">No courses created yet. <a href="create_course.php">Create one</a>.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
