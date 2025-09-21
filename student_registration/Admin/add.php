<?php
session_start();
if(!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// ✅ Tech courses
$courses = ['Web Development','Data Science','AI & ML','Cybersecurity','UI/UX Design'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Student</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #89f7fe, #66a6ff);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
      transition: background 0.5s ease, color 0.5s ease;
    }
    .glass-card {
      background: rgba(255, 255, 255, 0.25);
      border-radius: 20px;
      backdrop-filter: blur(12px);
      box-shadow: 0 8px 32px rgba(31, 38, 135, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.18);
      padding: 30px;
      width: 400px;
      animation: fadeInUp 1s ease;
    }
    h2 { text-align:center; margin-bottom:20px; font-weight:600; }
    @keyframes fadeInUp { 0% {opacity:0; transform:translateY(40px);} 100% {opacity:1; transform:translateY(0);} }
    .dark-mode {
      background: linear-gradient(135deg, #1e1e1e, #121212);
      color: #f1f1f1;
    }
    .dark-mode .glass-card {
      background: rgba(30,30,30,0.8);
      border: 1px solid rgba(255,255,255,0.1);
      box-shadow: 0 8px 32px rgba(0,0,0,0.6);
    }
    .dark-mode input, .dark-mode select, .dark-mode .form-control {
      background: rgba(50,50,50,0.8);
      color: #fff;
      border: 1px solid #444;
    }
    .toggle-btn {
      position: absolute;
      top: 20px; right: 20px;
      border: none; background: #fff;
      border-radius: 30px; padding: 6px 14px;
      cursor: pointer; font-size: 14px; font-weight: 600;
      transition: background 0.3s;
    }
    .dark-mode .toggle-btn { background: #444; color: #fff; }
  </style>
</head>
<body>

<button id="toggleDark" class="toggle-btn">🌙 Dark Mode</button>

<div class="glass-card">
  <h2>➕ Register Student</h2>
  <form action="register.php" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Full Name</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Phone</label>
      <input type="text" name="phone" class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">Date of Birth</label>
      <input type="date" name="dob" class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">Gender</label>
      <select name="gender" class="form-control" required>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Profile Photo</label>
      <input type="file" name="photo" class="form-control" accept="image/*">
    </div>
    <div class="mb-3">
      <label class="form-label">Course</label>
      <select name="course" class="form-control" required>
        <option value="">Select Course</option>
        <?php foreach($courses as $course): ?>
          <option value="<?= $course ?>"><?= $course ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-success w-100">Register</button>
    <a href="index.php" class="btn btn-secondary w-100 mt-2">⬅ Back</a>
  </form>
</div>

<script>
const toggleBtn = document.getElementById('toggleDark');
const body = document.body;

if (localStorage.getItem("dark-mode") === "enabled") {
    body.classList.add("dark-mode");
    toggleBtn.textContent = "☀️ Light Mode";
}

toggleBtn.addEventListener("click", () => {
    body.classList.toggle("dark-mode");
    toggleBtn.textContent = body.classList.contains("dark-mode") ? "☀️ Light Mode" : "🌙 Dark Mode";
});
</script>

</body>
</html>
