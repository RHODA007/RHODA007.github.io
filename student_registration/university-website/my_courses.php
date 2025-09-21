<?php
session_start();
require_once 'includes/config.php';

// Make sure student is logged in
if (!isset($_SESSION['student'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student']['id'];

// Fetch courses the student is enrolled in
$stmt = $pdo->prepare("
    SELECT c.id, c.title AS course_title, c.description, c.category, c.level, 
           e.progress, e.status
    FROM courses c
    JOIN enrollments e ON e.course_id = c.id
    WHERE e.student_id = :student_id
    ORDER BY c.created_at DESC
");
$stmt->execute(['student_id' => $student_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

function esc($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Courses - Student Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { background:#f8f9fa; font-family:Arial,sans-serif; margin:0; }
.sidebar { position: fixed; left:0; top:0; height:100vh; width:240px; background:#1f1f2c; color:#fff; display:flex; flex-direction:column; box-shadow:2px 0 10px rgba(0,0,0,0.2); }
.sidebar-header { padding:20px; text-align:center; background:#29293d; border-bottom:1px solid #333; }
.sidebar-header h3 { margin:0; font-size:18px; font-weight:bold; }
.sidebar-menu { list-style:none; margin:0; padding:0; flex:1; }
.sidebar-menu li { width:100%; }
.sidebar-menu a { display:flex; align-items:center; gap:10px; padding:14px 20px; color:#cfd2dc; text-decoration:none; font-size:15px; transition:all 0.3s ease; }
.sidebar-menu a:hover, .sidebar-menu a.active { background:#b6c8da; color:#fff; padding-left:25px; }
.sidebar-menu .logout { background:#d9534f; color:#fff; margin-top:auto; }
.sidebar-menu .logout:hover { background:#c9302c; }
.main { margin-left:240px; padding:20px; }
.card { border-radius:8px; padding:20px; margin-bottom:20px; box-shadow:0px 4px 8px rgba(0,0,0,0.1); }
.course-card { transition: transform 0.3s; }
.course-card:hover { transform: scale(1.02); }
.progress { height:18px; }
</style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-header"><h3>Student Panel</h3></div>
  <ul class="sidebar-menu">
    <li><a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="my_courses.php" class="active"><i class="fas fa-book"></i> My Courses</a></li>
    <li><a href="progress.php"><i class="fas fa-chart-line"></i> Progress</a></li>
    <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> My Schedule</a></li>
    <li><a href="verify_certificate.php"><i class="fas fa-check-circle"></i> Verification</a></li> <!-- ✅ New link -->
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<div class="main">
  <h2 class="mb-4">📚 My Courses</h2>

  <!-- Search & Filter -->
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
      <input type="text" id="searchBox" class="form-control w-50 mb-2" placeholder="Search courses...">
      <select id="filterSelect" class="form-select w-auto mb-2">
          <option value="all">All</option>
          <option value="completed">Completed</option>
          <option value="inprogress">In Progress</option>
          <option value="notstarted">Not Started</option>
      </select>
  </div>

  <div class="row" id="courseContainer">
    <?php if ($courses): ?>
      <?php foreach ($courses as $course): ?>
        <div class="col-md-4 mb-4 course-card" 
             data-title="<?= strtolower(esc($course['course_title'])); ?>" 
             data-progress="<?= (int)$course['progress']; ?>">

          <div class="card h-100">
            <div class="card-body">
              <h5 class="card-title">
                <?= esc($course['course_title']); ?>
                <?php if ((int)$course['progress'] === 100): ?>
                    <span class="badge bg-success">Completed</span>
                <?php endif; ?>
              </h5>
              <p class="card-text"><?= esc($course['description']); ?></p>

              <!-- Progress Bar -->
              <div class="progress mb-2">
                <div class="progress-bar bg-info" role="progressbar" 
                     style="width: <?= (int)$course['progress']; ?>%" 
                     aria-valuenow="<?= (int)$course['progress']; ?>" 
                     aria-valuemin="0" aria-valuemax="100">
                  <?= (int)$course['progress']; ?>%
                </div>
              </div>

              <!-- Buttons -->
              <a href="course_view.php?id=<?= $course['id']; ?>" class="btn btn-primary btn-sm">View</a>
              <a href="download_material.php?id=<?= $course['id']; ?>&type=pdf" class="btn btn-secondary btn-sm">PDF</a>
              <a href="download_material.php?id=<?= $course['id']; ?>&type=video" class="btn btn-secondary btn-sm">Video</a>

              <?php if ((int)$course['progress'] === 100): ?>
                  <a href="download_certificate.php?course_id=<?= $course['id']; ?>" class="btn btn-success btn-sm">Certificate</a>
              <?php endif; ?>

              <?php if ($course['status'] === 'enrolled'): ?>
                  <a href="unenroll.php?id=<?= $course['id']; ?>" 
                     class="btn btn-outline-danger btn-sm"
                     onclick="return confirm('Are you sure you want to unenroll?');">Unenroll</a>
              <?php else: ?>
                  <a href="enroll.php?id=<?= $course['id']; ?>" class="btn btn-outline-success btn-sm">Enroll Now</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No courses enrolled yet.</p>
    <?php endif; ?>
  </div>
</div>

<script>
// Search + Filter
const searchBox = document.getElementById("searchBox");
const filterSelect = document.getElementById("filterSelect");
const cards = document.querySelectorAll(".course-card");

function filterCourses() {
  const query = searchBox.value.toLowerCase();
  const filter = filterSelect.value;

  cards.forEach(card => {
    const title = card.dataset.title;
    const progress = parseInt(card.dataset.progress);
    let visible = true;

    if (query && !title.includes(query)) visible = false;
    if (filter === "completed" && progress < 100) visible = false;
    if (filter === "inprogress" && (progress === 0 || progress === 100)) visible = false;
    if (filter === "notstarted" && progress > 0) visible = false;

    card.style.display = visible ? "block" : "none";
  });
}

searchBox.addEventListener("input", filterCourses);
filterSelect.addEventListener("change", filterCourses);
</script>

</body>
</html>
