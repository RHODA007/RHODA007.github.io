<?php
session_start();
if(!isset($_SESSION['student'])){
    header("Location: login.php");
    exit;
}

require_once 'includes/config.php';
$student_id = $_SESSION['student']['id'];

// Fetch student info
$stmt = $pdo->prepare("SELECT * FROM students WHERE id=?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>⚙️ Settings</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { font-family:'Segoe UI', sans-serif; margin:0; min-height:100vh; background:#f8f9fa; }
body.dark-mode { background:#121212; color:#f8f9fa; }
/* Sidebar */
.sidebar { position: fixed; left:0; top:0; height:100vh; width:240px; background: rgba(31,31,47,0.95); color:#fff; display:flex; flex-direction:column; box-shadow:2px 0 10px rgba(0,0,0,0.2); }
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
<div class="sidebar" id="sidebar">
  <div class="sidebar-header"><h3>Student Panel</h3></div>
  <ul class="sidebar-menu">
    <li><a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="my_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
    <li><a href="progress.php"><i class="fas fa-chart-line"></i> Progress</a></li>
    <li><a href="submissions.php"><i class="fas fa-file-upload"></i> Submissions</a></li>
    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="settings.php" class="active"><i class="fas fa-cogs"></i> Settings</a></li>
    <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="card p-4" style="max-width:600px; margin:auto;">
    <h4 class="mb-3">Student Settings</h4>

    <div id="alert"></div>

    <!-- Profile Picture + Name/Email -->
    <form id="profileForm" enctype="multipart/form-data">
      <div class="mb-3 text-center profile-pic">
        <img id="profileImg" src="<?= $student['profile_pic'] ? 'uploads/'.$student['profile_pic']:'default.png' ?>" alt="Profile">
        <div class="mt-2">
          <button type="button" id="removePicBtn" class="btn btn-sm btn-outline-danger">Remove Picture</button>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($student['name']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Profile Picture</label>
        <input type="file" name="profile_pic" class="form-control">
      </div>
      <button type="submit" class="btn btn-primary w-100">Update Profile</button>
    </form>

    <hr>

    <!-- Change Password -->
    <form id="passwordForm">
      <div class="mb-3">
        <label>Current Password</label>
        <input type="password" name="current_password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>New Password</label>
        <input type="password" name="new_password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-success w-100">Change Password</button>
    </form>

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

// AJAX helpers
function showAlert(msg,type='success'){
  document.getElementById('alert').innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
}

// Update Profile AJAX
document.getElementById('profileForm').addEventListener('submit', function(e){
  e.preventDefault();
  let formData = new FormData(this);
  fetch('update_system.php', {method:'POST', body: formData})
    .then(res=>res.json())
    .then(data=>{
      showAlert(data.message,data.status==='success'?'success':'danger');
      if(data.status==='success' && data.file){
        document.getElementById('profileImg').src = 'uploads/'+data.file;
      }
    });
});

// Remove Profile Picture AJAX
document.getElementById('removePicBtn').addEventListener('click', function(){
  let formData = new FormData();
  formData.append('remove_pic',1);
  fetch('update_system.php',{method:'POST',body:formData})
    .then(res=>res.json())
    .then(data=>{
      showAlert(data.message,data.status==='success'?'success':'danger');
      if(data.status==='success'){
        document.getElementById('profileImg').src = 'default.png';
      }
    });
});

// Change Password AJAX
document.getElementById('passwordForm').addEventListener('submit', function(e){
  e.preventDefault();
  let formData = new FormData(this);
  fetch('update_system.php', {method:'POST', body: formData})
    .then(res=>res.json())
    .then(data=>{
      showAlert(data.message,data.status==='success'?'success':'danger');
      if(data.status==='success') this.reset();
    });
});
</script>

</body>
</html>
