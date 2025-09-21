<?php
session_start();
if(!isset($_SESSION['instructor'])){
    header('Location: login.php');
    exit;
}

require 'includes/config.php'; // adjust path

$instructor_id = $_SESSION['instructor']['id'];
$message = "";

// Delete assignment if requested
if(isset($_GET['delete']) && is_numeric($_GET['delete'])){
    $assignment_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("
        DELETE FROM assignments 
        WHERE id = ? 
        AND course_id IN (SELECT id FROM courses WHERE instructor_id = ?)
    ");
    if($stmt->execute([$assignment_id, $instructor_id])){
        $message = "<div class='alert alert-success'>Assignment deleted successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error deleting assignment.</div>";
    }
}

// Fetch instructor courses for filter
$stmt = $pdo->prepare("SELECT id, title FROM courses WHERE instructor_id=? ORDER BY title ASC");
$stmt->execute([$instructor_id]);
$coursesList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle course filter
$filterCourseId = isset($_GET['course']) && is_numeric($_GET['course']) ? (int)$_GET['course'] : 0;

// Fetch assignments (filtered if course selected)
$sql = "
    SELECT a.*, c.title AS course_title, u.name AS student_name
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    JOIN users u ON a.student_id = u.id
    WHERE c.instructor_id = ?
";
$params = [$instructor_id];

if($filterCourseId){
    $sql .= " AND c.id = ?";
    $params[] = $filterCourseId;
}

$sql .= " ORDER BY a.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Safe value function
function getValue($array, $key){
    return isset($array[$key]) ? htmlspecialchars($array[$key]) : 'N/A';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assignments - Instructor</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { background:#f8f9fa; margin:0; font-family:Arial,sans-serif; }
.sidebar { position: fixed; left: 0; top: 0; height: 100vh; width: 240px; background: #1f1f2c; color: #fff; display: flex; flex-direction: column; box-shadow: 2px 0 10px rgba(0,0,0,0.2); z-index: 1000; }
.sidebar-header { padding: 20px; text-align: center; background: #29293d; border-bottom: 1px solid #333; }
.sidebar-header h3 { margin: 0; font-size: 18px; font-weight: bold; }
.sidebar-menu { list-style: none; margin: 0; padding: 0; flex: 1; }
.sidebar-menu li { width: 100%; }
.sidebar-menu a { display: flex; align-items: center; gap: 10px; padding: 14px 20px; color: #cfd2dc; text-decoration: none; font-size: 15px; transition: all 0.3s ease; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: #b6c8da; color: #fff; padding-left: 25px; }
.sidebar-menu .logout { background: #d9534f; color: #fff; margin-top: auto; }
.sidebar-menu .logout:hover { background: #c9302c; }
.main { margin-left:240px; padding:20px; }
.card { border-radius:8px; }
.table th { background:#e9ecef; }
.actions a { margin-right:8px; }
</style>
</head>
<body>

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
    <li><a href="assignments.php" class="active"><i class="fas fa-tasks"></i> Assignments</a></li>
    <li><a href="submissions.php"><i class="fas fa-file-upload"></i> Submissions</a></li>
    <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Manage Schedule</a></li>
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="settings.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main">
  <h2 class="mb-4">Assignments</h2>
  <?= $message ?>

  <!-- Filter -->
  <form method="get" class="mb-3 d-flex align-items-center gap-2">
    <label for="course" class="form-label mb-0">Filter by Course:</label>
    <select name="course" id="course" class="form-select w-auto">
      <option value="0">All Courses</option>
      <?php foreach($coursesList as $c): ?>
        <option value="<?= $c['id'] ?>" <?= ($filterCourseId == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['title']) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
  </form>

  <div class="card p-3 shadow-sm">
    <a href="create_assignment.php" class="btn btn-sm btn-primary mb-3"><i class="fas fa-plus"></i> Add New Assignment</a>
    <?php if($assignments): ?>
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Student</th>
              <th>Course</th>
              <th>Title</th>
              <th>Description</th>
              <th>Due Date</th>
              <th>File</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($assignments as $a): ?>
            <tr>
              <td><?= getValue($a,'id') ?></td>
              <td><?= getValue($a,'student_name') ?></td>
              <td><?= getValue($a,'course_title') ?></td>
              <td><?= getValue($a,'title') ?></td>
              <td><?= htmlspecialchars(substr($a['description'],0,50)) ?>...</td>
              <td><?= getValue($a,'due_date') ?></td>
              <td>
                <?php if(!empty($a['file_path'])): ?>
                  <a href="../<?= htmlspecialchars($a['file_path']) ?>" target="_blank">View File</a>
                <?php else: ?>
                  N/A
                <?php endif; ?>
              </td>
              <td><?= getValue($a,'created_at') ?></td>
              <td class="actions">
                <a href="edit_assignment.php?id=<?= getValue($a,'id') ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                <a href="assignments.php?delete=<?= getValue($a,'id') ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this assignment?')"><i class="fas fa-trash"></i> Delete</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-muted">No assignments found for enrolled students in your courses.</p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
