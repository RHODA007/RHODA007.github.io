<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['instructor'])) {
    header("Location: login.php");
    exit;
}

$instructor_id = $_SESSION['instructor']['id'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_name = trim($_POST['group_name']);
    $course_id = $_POST['course_id'] ?? null;
    $student_ids = $_POST['students'] ?? [];

    if (empty($group_name)) {
        $error = "Group name cannot be empty.";
    } elseif (empty($course_id)) {
        $error = "Please select a course.";
    } elseif (empty($student_ids)) {
        $error = "Please select at least one student.";
    } else {
        try {
            // Insert group
            $stmt = $pdo->prepare("
                INSERT INTO class_groups (group_name, course_id, created_by, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$group_name, $course_id, $instructor_id]);
            $group_id = $pdo->lastInsertId();

            // Add instructor to group members
            $stmtMember = $pdo->prepare("
                INSERT INTO class_group_members (group_id, user_id, role, added_at)
                VALUES (?, ?, 'instructor', NOW())
            ");
            $stmtMember->execute([$group_id, $instructor_id]);

            // Add selected students to group members
            $stmtMember = $pdo->prepare("
                INSERT INTO class_group_members (group_id, user_id, role, added_at)
                VALUES (?, ?, 'student', NOW())
            ");
            foreach ($student_ids as $sid) {
                $stmtMember->execute([$group_id, $sid]);
            }

            $success = "Class group created successfully!";
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch courses created by instructor
$stmtCourses = $pdo->prepare("SELECT * FROM courses WHERE instructor_id = ?");
$stmtCourses->execute([$instructor_id]);
$courses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);

// Fetch students enrolled in instructor's courses
$stmtStudents = $pdo->prepare("
    SELECT DISTINCT u.id, u.name, u.photo, GROUP_CONCAT(c.title SEPARATOR ', ') AS courses
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ?
    GROUP BY u.id
    ORDER BY u.name
");
$stmtStudents->execute([$instructor_id]);
$students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

// Fetch existing groups
$stmtGroups = $pdo->prepare("
    SELECT g.*, c.title AS course_title 
    FROM class_groups g 
    LEFT JOIN courses c ON g.course_id = c.id 
    WHERE g.created_by = ? 
    ORDER BY g.created_at DESC
");
$stmtGroups->execute([$instructor_id]);
$groups = $stmtGroups->fetchAll(PDO::FETCH_ASSOC);

function esc($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Class Group</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body{background:#f8f9fa;font-family:Poppins,sans-serif;}
.sidebar{position:fixed;left:0;top:0;height:100vh;width:240px;background:#1f1f2c;color:#fff;display:flex;flex-direction:column;}
.sidebar-header{padding:20px;text-align:center;background:#29293d;}
.sidebar-menu{list-style:none;padding:0;margin:0;flex:1;}
.sidebar-menu a{display:flex;align-items:center;gap:10px;padding:12px 20px;color:#cfd2dc;text-decoration:none;}
.sidebar-menu a:hover,.sidebar-menu a.active{background:#0d6efd;color:#fff;}
.main{margin-left:240px;padding:20px;}
.card{border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.1);margin-bottom:20px;}
.student-list{max-height:300px;overflow-y:auto;}
.student-item{display:flex;align-items:center;gap:10px;padding:8px;border-bottom:1px solid #eee;}
.student-item img{width:40px;height:40px;border-radius:50%;object-fit:cover;}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-header"><h3>Instructor Panel</h3></div>
  <ul class="sidebar-menu">
    <li><a href="instructor_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="create_course.php"><i class="fas fa-plus-circle"></i> Create Course</a></li>
    <li><a href="manage_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
    <li><a href="students.php"><i class="fas fa-users"></i> My Students</a></li>
    <li><a href="create_group.php" class="active"><i class="fas fa-users-cog"></i> Class Groups</a></li>
    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<div class="main">
  <div class="card p-4">
    <h3 class="mb-4 text-center">Create Class Group</h3>

    <?php if ($error): ?>
      <div class="alert alert-danger text-center"><?= esc($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success text-center"><?= esc($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label class="form-label">Group Name</label>
        <input type="text" name="group_name" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Select Course</label>
        <select name="course_id" class="form-control" required>
          <option value="">-- Select a course --</option>
          <?php foreach($courses as $c): ?>
            <option value="<?= $c['id'] ?>"><?= esc($c['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <h5>Select Students:</h5>
      <div class="student-list mb-3">
        <?php if ($students): ?>
          <?php foreach($students as $s): ?>
            <div class="student-item">
              <input type="checkbox" name="students[]" value="<?= $s['id'] ?>">
              <img src="<?= $s['photo'] ?: 'https://via.placeholder.com/40' ?>" alt="Student">
              <div>
                <strong><?= esc($s['name']) ?></strong><br>
                <small class="text-muted"><?= esc($s['courses']) ?></small>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-muted">No students enrolled in your courses yet.</p>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn btn-dark w-100"><i class="fas fa-plus"></i> Create Group</button>
    </form>
  </div>

  <div class="card p-4">
    <h4>My Groups</h4>
    <?php if ($groups): ?>
      <ul class="list-group">
        <?php foreach ($groups as $g): ?>
          <li class="list-group-item">
            <?= esc($g['group_name']) ?> 
            <small class="text-muted">(Course: <?= esc($g['course_title'] ?? 'N/A') ?>, Created: <?= $g['created_at'] ?>)</small>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="text-muted">No groups created yet.</p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
