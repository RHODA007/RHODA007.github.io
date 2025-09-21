<?php
session_start();
if (!isset($_SESSION['student'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/config.php';

// Student info
$student_id = $_SESSION['student']['id'];
$studentStmt = $pdo->prepare("SELECT id, name, email, profile_pic FROM students WHERE id = ?");
$studentStmt->execute([$student_id]);
$student = $studentStmt->fetch(PDO::FETCH_ASSOC);

// Function for escaping output
function esc($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Progress</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<style>
body { font-family:'Segoe UI', sans-serif; margin:0; background:#f8f9fa; }
body.dark-mode { background:#121212; color:#f8f9fa; }

/* Sidebar */
.sidebar { position:fixed; left:0; top:0; height:100vh; width:240px; background:#1f1f2f; color:#fff; display:flex; flex-direction:column; }
.sidebar-header { padding:20px; text-align:center; background:#29293d; }
.sidebar-header h3 { margin:0; font-size:18px; }
.sidebar-menu { list-style:none; margin:0; padding:0; flex:1; }
.sidebar-menu a { display:flex; align-items:center; gap:10px; padding:14px 20px; color:#cfd2dc; text-decoration:none; font-size:15px; transition:0.3s; }
.sidebar-menu a i { width:20px; text-align:center; }
.sidebar-menu a:hover, .sidebar-menu a.active { background:#4f46e5; color:#fff; }
.sidebar-menu .logout { background:#d9534f; margin-top:auto; }
.sidebar-menu .logout:hover { background:#c9302c; }

/* Main Content */
.main-content { margin-left:240px; padding:20px; }
.card-chart { background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow:0 3px 10px rgba(0,0,0,0.1); }
.dark-mode .card-chart { background:#1e1e1e; color:#f8f9fa; }
.progress-bar { transition:width 0.6s ease; }
.badge { font-size:0.8rem; }
</style>
</head>
<body>
<button id="toggleDark" class="btn btn-sm btn-outline-secondary position-fixed top-0 end-0 m-3">🌙</button>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-header"><h3>Student Panel</h3></div>
  <ul class="sidebar-menu">
    <li><a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="my_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
    <li><a href="progress.php" class="active"><i class="fas fa-chart-line"></i> Progress</a></li>
    <li><a href="submissions.php"><i class="fas fa-file-upload"></i> Submissions</a></li>
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <h2><i class="fas fa-chart-pie"></i> Live Course Progress</h2>
    <div id="progress-container">
        <!-- AJAX loads student course progress here -->
        <p>Loading progress...</p>
    </div>
</div>

<script>
// Dark mode toggle
const toggleBtn=document.getElementById('toggleDark');const body=document.body;
if(localStorage.getItem("dark-mode")==="enabled"){body.classList.add("dark-mode");toggleBtn.textContent="☀️";}
toggleBtn.addEventListener("click",()=>{body.classList.toggle("dark-mode");toggleBtn.textContent=body.classList.contains("dark-mode")?"☀️":"🌙";localStorage.setItem("dark-mode",body.classList.contains("dark-mode")?"enabled":"disabled");});

// Fetch student progress (AJAX)
function loadProgress(){
  $.get("student_api_progress.php", function(data){
      $("#progress-container").html(data);
  });
}
loadProgress();
setInterval(loadProgress, 10000); // refresh every 10s
</script>
</body>
</html>
