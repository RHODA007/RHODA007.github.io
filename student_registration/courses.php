<?php
session_start();
if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}

require 'db_connect.php';

// Updated tech skill courses
$courseList = ['Web Development','Data Science','AI & ML','Cybersecurity','UI/UX Design'];
$courseStats = [];
foreach($courseList as $course){
    $courseStats[$course] = $pdo->query("SELECT COUNT(*) FROM students WHERE course='$course'")->fetchColumn();
}

// Total students for percentage calculation
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tech Skills</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #89f7fe, #66a6ff);
    margin:0; min-height:100vh; transition: background 0.5s, color 0.5s; overflow-x:hidden;
}
body.dark-mode { background:#0e0e1f; color:#f1f1f1; }
body.dark-mode::before { content:''; position:fixed; top:0; left:0; width:100%; height:100%; background:url('https://i.ibb.co/4NQdD7L/moon-stars.png') center/cover no-repeat; z-index:-1; opacity:0.4; }

/* Navbar */
.navbar { backdrop-filter: blur(12px); background: rgba(255,255,255,0.2); box-shadow:0 4px 30px rgba(0,0,0,0.1); padding:10px 20px; position:sticky; top:0; z-index:100; display:flex; justify-content:space-between; align-items:center;}
.dark-mode .navbar { background: rgba(30,30,30,0.7); }

/* Sidebar */
.sidebar { position:fixed; top:60px; left:0; width:220px; height:100%; background: rgba(255,255,255,0.2); backdrop-filter: blur(15px); padding:20px; transition:all 0.3s; overflow-y:auto; }
.sidebar a { display:flex; align-items:center; color:#333; padding:10px 15px; border-radius:10px; margin-bottom:10px; text-decoration:none; transition:0.3s; }
.sidebar a span.icon{ font-size:18px; margin-right:12px; }
.sidebar a:hover{ background: rgba(255,255,255,0.3); }
.dark-mode .sidebar { background: rgba(30,30,30,0.7); }
.dark-mode .sidebar a{ color:#f1f1f1; }
.dark-mode .sidebar a:hover{ background: rgba(255,255,255,0.2); }

/* Collapsed Sidebar */
.sidebar.collapsed{ width:70px; }
.sidebar.collapsed a span.text{ display:none; }
.sidebar.collapsed a{ justify-content:center; }

/* Main Content */
.main-content{ margin-left:240px; padding:20px; transition: margin-left 0.3s; }
.sidebar.collapsed ~ .main-content{ margin-left:80px; }

/* Glass Cards */
.glass-card { background: rgba(255,255,255,0.2); border-radius:20px; backdrop-filter: blur(15px); box-shadow:0 10px 30px rgba(0,0,0,0.1); padding:25px; margin-bottom:20px; color:#333; transition:0.3s; display:flex; align-items:center; justify-content:space-between; }
.glass-card:hover{ transform: translateY(-5px); }
.dark-mode .glass-card{ background: rgba(30,30,30,0.7); color:#f1f1f1; }

/* Toggle Button */
.toggle-btn{ position:fixed; top:20px; right:20px; border:none; border-radius:30px; padding:8px 18px; cursor:pointer; font-size:14px; font-weight:600; background:#fff; transition:0.3s; z-index:1000; }
.dark-mode .toggle-btn{ background:#444; color:#fff; }

/* Progress Ring */
.progress-ring { width: 50px; height: 50px; }
.progress-percentage { font-size: 12px; margin-left:10px; }

/* Responsive */
@media(max-width:992px){
    .sidebar{ left:-220px; }
    .sidebar.active{ left:0; }
    .main-content{ margin-left:0; }
}
</style>
</head>
<body>

<!-- Dark Mode Toggle -->
<button id="toggleDark" class="toggle-btn">🌙 Dark Mode</button>

<!-- Navbar -->
<nav class="navbar">
    <span class="fw-bold fs-4">💻 Tech Skills</span>
    <span class="hamburger" id="hamburger">☰</span>
</nav>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <a href="dashboard.php"><span class="icon">🏠</span><span class="text">Dashboard</span></a>
    <a href="courses.php"><span class="icon">📚</span><span class="text">Courses</span></a>
    <a href="view.php"><span class="icon">👨‍🎓</span><span class="text">Students</span></a>
    <a href="reports.php"><span class="icon">📊</span><span class="text">Reports</span></a>
    <a href="settings.php"><span class="icon">⚙️</span><span class="text">Settings</span></a>
    <a href="logout.php"><span class="icon">🚪</span><span class="text">Logout</span></a>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="d-flex flex-wrap gap-3 mb-4">
        <?php foreach($courseStats as $course => $count):
            $completion = $totalStudents ? round($count/$totalStudents*100) : 0;
        ?>
        <div class="glass-card flex-fill">
            <div class="d-flex align-items-center">
                <canvas id="courseChart<?= $course ?>" class="progress-ring"></canvas>
                <span class="progress-percentage"><?= $completion ?>%</span>
            </div>
            <div>
                <h5><?= $course ?></h5>
                <h6><?= $count ?> Students</h6>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Optional Table -->
    <div class="glass-card">
        <h5>Course Details</h5>
        <table class="table table-borderless text-center mt-3">
            <thead>
                <tr>
                    <th>Skill</th>
                    <th>Number of Students</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($courseStats as $course => $count): ?>
                <tr>
                    <td><?= $course ?></td>
                    <td><?= $count ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Dark Mode
const toggleBtn = document.getElementById('toggleDark');
const body = document.body;
if(localStorage.getItem("dark-mode") === "enabled"){
    body.classList.add("dark-mode");
    toggleBtn.textContent="☀️ Light Mode";
}
toggleBtn.addEventListener("click",()=>{
    body.classList.toggle("dark-mode");
    if(body.classList.contains("dark-mode")){
        localStorage.setItem("dark-mode","enabled");
        toggleBtn.textContent="☀️ Light Mode";
    } else{
        localStorage.setItem("dark-mode","disabled");
        toggleBtn.textContent="🌙 Dark Mode";
    }
});

// Sidebar
const hamburger = document.getElementById('hamburger');
const sidebar = document.getElementById('sidebar');
hamburger.addEventListener('click',()=>{ sidebar.classList.toggle('active'); });
sidebar.addEventListener('dblclick',()=>{ sidebar.classList.toggle('collapsed'); });

// Tech Course Progress Rings
<?php foreach($courseStats as $course => $count):
    $completion = $totalStudents ? round($count/$totalStudents*100) : 0;
?>
new Chart(document.getElementById("courseChart<?= $course ?>").getContext('2d'),{
    type:'doughnut',
    data:{
        labels:['Completed','Remaining'],
        datasets:[{
            data:[<?= $completion ?>, <?= 100-$completion ?>],
            backgroundColor:['#66a6ff','rgba(255,255,255,0.2)'],
            borderWidth:0
        }]
    },
    options:{
        cutout:'75%',
        plugins:{
            legend:{ display:false },
            tooltip:{ enabled:false }
        }
    }
});
<?php endforeach; ?>
</script>

</body>
</html>
