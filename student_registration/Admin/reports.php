<?php
session_start();
if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}

require 'db_connect.php';

// ================== COURSE STATS ================== //
// Search & Sorting
$search = trim($_GET['search'] ?? '');
$sort   = in_array($_GET['sort'] ?? '', ['title', 'students', 'assignments']) ? $_GET['sort'] : 'students';
$order  = ($_GET['order'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

// Fetch course stats
$query = "
    SELECT c.id, c.title, 
           COUNT(sc.student_id) AS student_count,
           (SELECT COUNT(*) FROM assignments a WHERE a.course_id = c.id) AS assignment_count
    FROM courses c
    LEFT JOIN student_courses sc ON c.id = sc.course_id
";

$params = [];
if($search){
    $query .= " WHERE c.title LIKE ? ";
    $params[] = "%$search%";
}

$query .= " GROUP BY c.id ";

switch($sort){
    case 'title': $query .= " ORDER BY c.title $order"; break;
    case 'students': $query .= " ORDER BY student_count $order"; break;
    case 'assignments': $query .= " ORDER BY assignment_count $order"; break;
}

$courseStats = $pdo->prepare($query);
$courseStats->execute($params);
$courseStats = $courseStats->fetchAll(PDO::FETCH_ASSOC);

// Totals
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalCourses  = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalAssignments = $pdo->query("SELECT COUNT(*) FROM assignments")->fetchColumn();
$popularCourse = $courseStats[0]['title'] ?? 'N/A';
$popularCount  = $courseStats[0]['student_count'] ?? 0;

// ================== PAYMENTS ================== //
$stmt = $pdo->query("
    SELECT p.*, s.name, s.email, s.course 
    FROM payments p 
    JOIN students s ON p.student_id = s.id 
    ORDER BY p.created_at DESC
");
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalPayments = $pdo->query("SELECT COUNT(*) FROM payments")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(amount) FROM payments WHERE status='success'")->fetchColumn() ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📊 Reports - Admin Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background:#f8f9fa; margin:0; min-height:100vh; }
body.dark-mode { background:#121212; color:#f8f9fa; }

.sidebar {
  position: fixed; top:0; left:0; width:240px; height:100vh;
  background:#1f1f2c; color:#fff; display:flex; flex-direction:column;
  box-shadow:2px 0 10px rgba(0,0,0,0.2); padding-top:20px;
}
.sidebar a { display:flex; align-items:center; gap:10px; padding:14px 20px; color:#cfd2dc; text-decoration:none; font-size:15px; transition:0.3s; }
.sidebar a.active, .sidebar a:hover { background:#0d6efd; color:#fff; padding-left:25px; }
.sidebar .logout { margin-top:auto; background:#d9534f; color:#fff; }
.sidebar .logout:hover { background:#c9302c; }

.main-content { margin-left:240px; padding:20px; }
.card-summary { background:#fff; border-radius:8px; padding:20px; text-align:center; box-shadow:0 2px 6px rgba(0,0,0,0.1); margin-bottom:20px; }
.card-summary h4 { font-size:1.1rem; margin-bottom:10px; }
.card-summary p { font-size:1.3rem; font-weight:600; color:#0d6efd; }

.table th { background:#f1f1f1; cursor:pointer; }
.dark-mode .card-summary { background:#1e1e1e; color:#f8f9fa; }
.dark-mode .table th { background:#2a2a2a; color:#fff; }
.dark-mode .table td { color:#f8f9fa; }

.status-success { color:green; font-weight:bold; }
.status-failed { color:red; font-weight:bold; }
.status-pending { color:orange; font-weight:bold; }
.sort-arrow { font-size:0.7rem; margin-left:5px; }
</style>
</head>
<body>

<button id="toggleDark" class="btn btn-sm btn-outline-secondary position-fixed top-0 end-0 m-3">🌙</button>

<div class="sidebar">
  <h3 class="px-3">📘 Admin Panel</h3>
  <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
  <a href="students.php"><i class="fas fa-users"></i> Students</a>
  <a href="courses.php"><i class="fas fa-book"></i> Courses</a>
  <a href="assign_course.php"><i class="fas fa-tasks"></i> Assign Courses</a>
  <a href="assignments.php"><i class="fas fa-file-alt"></i> Assignments</a>
  <a href="send_message.php"><i class="fas fa-envelope"></i> Send Message</a>
  <a href="manage_courses.php"><i class="fas fa-cogs"></i> Manage Courses</a>
  <a href="reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a>
  <a href="settings.php"><i class="fas fa-user-cog"></i> Admin Settings</a>
  <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
  <h2 class="mb-4">📊 Admin Reports</h2>

  <!-- SUMMARY CARDS -->
  <div class="row mb-4">
    <div class="col-md-2"><div class="card-summary"><h4>Total Students</h4><p><?= $totalStudents ?></p></div></div>
    <div class="col-md-2"><div class="card-summary"><h4>Total Courses</h4><p><?= $totalCourses ?></p></div></div>
    <div class="col-md-2"><div class="card-summary"><h4>Total Assignments</h4><p><?= $totalAssignments ?></p></div></div>
    <div class="col-md-3"><div class="card-summary"><h4>Most Popular</h4><p><?= htmlspecialchars($popularCourse) ?> (<?= $popularCount ?>)</p></div></div>
    <div class="col-md-1"><div class="card-summary"><h4>Payments</h4><p><?= $totalPayments ?></p></div></div>
    <div class="col-md-2"><div class="card-summary"><h4>Revenue</h4><p>₦<?= number_format($totalRevenue,2) ?></p></div></div>
  </div>

  <!-- COURSES REPORT -->
  <div class="card p-3 mb-4">
    <h5>📚 Courses Report</h5>
    <form method="GET" class="d-flex mb-3 justify-content-between">
      <input type="text" name="search" class="form-control w-50" placeholder="Search by course" value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn btn-primary ms-2">Search</button>
    </form>

    <div class="table-responsive">
      <table class="table table-striped table-hover text-center">
        <thead>
          <tr>
            <?php
            $columns = ['title'=>'Course Title','students'=>'Students','assignments'=>'Assignments'];
            foreach($columns as $key=>$label){
                $arrow = ($sort==$key) ? ($order=='ASC' ? '▲':'▼') : '';
                $newOrder = ($sort==$key && $order=='ASC') ? 'desc':'asc';
                echo "<th><a href='?search=".urlencode($search)."&sort=$key&order=$newOrder'>$label <span class='sort-arrow'>$arrow</span></a></th>";
            }
            ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach($courseStats as $course): ?>
            <tr>
              <td><?= htmlspecialchars($course['title']) ?></td>
              <td><?= $course['student_count'] ?></td>
              <td><?= $course['assignment_count'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- PAYMENTS REPORT -->
  <div class="card p-3 mb-4">
    <h5>💳 Payments Report</h5>
    <?php if (count($payments) > 0): ?>
      <div class="table-responsive">
        <table class="table table-striped table-hover text-center">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Student</th>
              <th>Email</th>
              <th>Course</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Reference</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payments as $i => $p): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= htmlspecialchars($p['email']) ?></td>
                <td><?= htmlspecialchars($p['course']) ?></td>
                <td>₦<?= number_format($p['amount'], 2) ?></td>
                <td class="status-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></td>
                <td><?= htmlspecialchars($p['reference']) ?></td>
                <td><?= date("M j, Y g:ia", strtotime($p['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-muted">No payments found yet.</p>
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
