<?php
session_start();
if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}
require 'db_connect.php';

$success = '';
$error = '';

// Fetch instructors
$instructors = $pdo->query("SELECT id, name, email FROM instructors")->fetchAll(PDO::FETCH_ASSOC);

// Handle instructor assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'], $_POST['instructor_id'])) {
    $course_id     = $_POST['course_id'];
    $instructor_id = $_POST['instructor_id'];

    if ($course_id && $instructor_id) {
        $stmt = $pdo->prepare("UPDATE courses SET instructor_id = ? WHERE id = ?");
        $stmt->execute([$instructor_id, $course_id]);
        $success = "✅ Instructor assigned successfully!";
    } else {
        $error = "⚠ Please select both course and instructor.";
    }
}

// Stats
$totalCourses    = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalInstructors= $pdo->query("SELECT COUNT(*) FROM instructors")->fetchColumn();
$totalEnrollments= $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();

// Fetch courses with details
$courses = $pdo->query("
    SELECT c.id, c.title, c.category, c.level, c.duration, c.created_at, c.status,
           i.name AS instructor_name, i.email AS instructor_email,
           (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS enrolled_students
    FROM courses c
    LEFT JOIN instructors i ON c.instructor_id = i.id
    ORDER BY c.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

function esc($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

// AI suggestions (simple rules)
$popularCourses = array_slice(array_filter($courses, fn($c)=>$c['enrolled_students'] > 10),0,3);
$lowEnroll      = array_slice(array_filter($courses, fn($c)=>$c['enrolled_students'] <= 3),0,3);
$unassigned     = array_slice(array_filter($courses, fn($c)=>empty($c['instructor_name'])),0,3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Courses - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { font-family:'Segoe UI', sans-serif; margin:0; min-height:100vh; background:#f8f9fa; }
body.dark-mode { background:#121212; color:#f8f9fa; }

/* Sidebar */
.sidebar { position:fixed; left:0; top:0; height:100vh; width:240px;
  background:rgba(31,31,47,0.95); color:#fff; display:flex; flex-direction:column;
  box-shadow:2px 0 10px rgba(0,0,0,0.2);}
.sidebar-header { padding:20px; text-align:center; background:#29293d; border-bottom:1px solid #333; }
.sidebar-header h3 { margin:0; font-size:18px; font-weight:bold; }
.sidebar-menu { list-style:none; margin:0; padding:0; flex:1; }
.sidebar-menu li { width:100%; }
.sidebar-menu a { display:flex; align-items:center; gap:10px; padding:14px 20px; color:#cfd2dc;
  text-decoration:none; font-size:15px; transition:all 0.3s ease; }
.sidebar-menu a i { width:20px; text-align:center; font-size:16px; }
.sidebar-menu a:hover, .sidebar-menu a.active { background:#b6c8daff; color:#fff; padding-left:25px; }
.sidebar-menu .logout { background:#d9534f; color:#fff; margin-top:auto; }
.sidebar-menu .logout:hover { background:#c9302c; }

/* Main Content */
.main-content { margin-left:240px; padding:20px; }
.card { border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.1); background:#fff; }
.dark-mode .card { background:#1e1e1e; color:#f8f9fa; }

/* Stats */
.stats-card { display:flex; align-items:center; justify-content:space-between; padding:20px;
  border-radius:10px; color:#fff; }
.stats-card i { font-size:30px; }
.bg-blue { background:#0d6efd; }
.bg-green { background:#198754; }
.bg-orange { background:#fd7e14; }

/* Table */
.table th { background:#f1f1f1; }
.dark-mode .table th { background:#2a2a2a; }
.table td { vertical-align: middle; }
.badge { font-size:0.85em; }
</style>
</head>
<body>

<!-- Dark Mode Toggle -->
<button id="toggleDark" class="btn btn-sm btn-outline-secondary position-fixed top-0 end-0 m-3">🌙</button>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-header"><h3>Admin Panel</h3></div>
  <ul class="sidebar-menu">
    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="courses.php" class="active"><i class="fas fa-book"></i> Courses</a></li>
    <li><a href="students.php"><i class="fas fa-users"></i> Students</a></li>
    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="assignments.php"><i class="fas fa-file-alt"></i> Assignments</a></li>
    <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <nav class="navbar bg-white shadow-sm mb-4">
    <span class="fw-bold fs-5"><i class="fas fa-book"></i> Courses</span>
  </nav>

  <!-- Stats -->
  <div class="row mb-4">
    <div class="col-md-4"><div class="stats-card bg-blue">
      <div><h5><?=esc($totalCourses)?> Courses</h5></div><i class="fas fa-book"></i>
    </div></div>
    <div class="col-md-4"><div class="stats-card bg-green">
      <div><h5><?=esc($totalInstructors)?> Instructors</h5></div><i class="fas fa-chalkboard-teacher"></i>
    </div></div>
    <div class="col-md-4"><div class="stats-card bg-orange">
      <div><h5><?=esc($totalEnrollments)?> Enrollments</h5></div><i class="fas fa-users"></i>
    </div></div>
  </div>

  <!-- Alerts -->
  <?php if(!empty($success)): ?><div class="alert alert-success"><?=esc($success)?></div><?php endif; ?>
  <?php if(!empty($error)): ?><div class="alert alert-danger"><?=esc($error)?></div><?php endif; ?>

  <!-- Search & Export -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <input type="text" id="searchInput" class="form-control w-50" placeholder="Search courses...">
    <div>
      <button class="btn btn-sm btn-outline-primary" onclick="exportTable('csv')"><i class="fas fa-file-csv"></i> CSV</button>
      <button class="btn btn-sm btn-outline-danger" onclick="window.print()"><i class="fas fa-file-pdf"></i> PDF</button>
    </div>
  </div>

  <!-- Courses Table -->
  <div class="card p-3 mb-4">
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle text-center" id="coursesTable">
        <thead>
          <tr>
            <th>#</th><th>Title</th><th>Category</th><th>Level</th>
            <th>Duration</th><th>Instructor</th><th>Enrolled</th>
            <th>Status</th><th>Assign/Reassign</th><th>Created</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($courses as $i=>$c): ?>
          <tr>
            <td><?=$i+1?></td>
            <td><?=esc($c['title'])?></td>
            <td><?=esc($c['category'])?></td>
            <td><?=esc($c['level'])?></td>
            <td><?=esc($c['duration'])?></td>
            <td>
              <?php if($c['instructor_name']): ?>
                <?=esc($c['instructor_name'])?><br><small><?=esc($c['instructor_email'])?></small>
              <?php else: ?><span class="badge bg-secondary">Unassigned</span><?php endif; ?>
            </td>
            <td><span class="badge bg-info"><?=esc($c['enrolled_students'])?></span></td>
            <td>
              <?php if($c['status']==='active'): ?>
                <span class="badge bg-success">Active</span>
              <?php elseif($c['status']==='draft'): ?>
                <span class="badge bg-warning text-dark">Draft</span>
              <?php else: ?>
                <span class="badge bg-dark">Archived</span>
              <?php endif; ?>
            </td>
            <td>
              <form method="POST" class="d-flex">
                <input type="hidden" name="course_id" value="<?=esc($c['id'])?>">
                <select name="instructor_id" class="form-select form-select-sm me-2" required>
                  <option value="">-- Select --</option>
                  <?php foreach($instructors as $inst): ?>
                    <option value="<?=esc($inst['id'])?>" <?=($c['instructor_name']==$inst['name']?'selected':'')?>>
                      <?=esc($inst['name'])?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Save</button>
              </form>
            </td>
            <td><?=esc($c['created_at'])?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- AI Suggestions -->
  <div class="card p-4">
    <h4><i class="fas fa-lightbulb"></i> AI Suggestions</h4>
    <div class="row">
      <div class="col-md-4"><h6>🔥 Popular Courses</h6>
        <ul><?php foreach($popularCourses as $pc): ?><li><?=esc($pc['title'])?> (<?=esc($pc['enrolled_students'])?>)</li><?php endforeach; ?></ul>
      </div>
      <div class="col-md-4"><h6>📉 Low Enrollments</h6>
        <ul><?php foreach($lowEnroll as $lc): ?><li><?=esc($lc['title'])?> (<?=esc($lc['enrolled_students'])?>)</li><?php endforeach; ?></ul>
      </div>
      <div class="col-md-4"><h6>👩‍🏫 Needs Instructor</h6>
        <ul><?php foreach($unassigned as $uc): ?><li><?=esc($uc['title'])?></li><?php endforeach; ?></ul>
      </div>
    </div>
  </div>
</div>

<script>
// Dark mode toggle
const toggleBtn=document.getElementById('toggleDark'); const body=document.body;
if(localStorage.getItem("dark-mode")==="enabled"){body.classList.add("dark-mode");toggleBtn.textContent="☀️";}
toggleBtn.addEventListener("click",()=>{body.classList.toggle("dark-mode");
  toggleBtn.textContent=body.classList.contains("dark-mode")?"☀️":"🌙";
  localStorage.setItem("dark-mode",body.classList.contains("dark-mode")?"enabled":"disabled");});

// Search filter
document.getElementById('searchInput').addEventListener('keyup', function(){
  let filter=this.value.toLowerCase();
  document.querySelectorAll("#coursesTable tbody tr").forEach(row=>{
    row.style.display=row.textContent.toLowerCase().includes(filter)?'':'none';
  });
});

// Export to CSV
function exportTable(type){
  if(type==='csv'){
    let rows=[...document.querySelectorAll("#coursesTable tr")];
    let csv=rows.map(r=>[...r.children].map(td=>td.innerText).join(",")).join("\n");
    let blob=new Blob([csv],{type:"text/csv"}); let a=document.createElement("a");
    a.href=URL.createObjectURL(blob); a.download="courses.csv"; a.click();
  }
}
</script>
</body>
</html>
