<?php
if(!isset($pageTitle)) { $pageTitle = "RhodaX Tech School"; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Main CSS -->
<style>
body { margin:0; font-family:'Segoe UI', sans-serif; color:#111; background:#f8f8f8; }

/* Navbar */
.navbar {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:1rem 2rem;
    background:#f8f8f8;
    box-shadow:0 2px 5px rgba(0,0,0,0.05);
    position:relative;
    z-index:1000;
}
.navbar .brand { font-weight:700; font-size:1.5rem; color:#111; }
.navbar .nav-links { display:flex; align-items:center; gap:1rem; position:relative; }

/* Navbar links */
.navbar .nav-links > li { list-style:none; position:relative; }
.navbar a {
    text-decoration:none;
    color:#111;
    font-weight:500;
    padding:6px 10px;
    display:inline-block;
    transition: color 0.3s;
}
.navbar a:hover { color:#007bff; }
/* Dropdown container */
.navbar .dropdown {
    position: relative;
}

/* Dropdown container */
.navbar .dropdown {
    position: relative;
}

/* Multi-column dropdown for Courses */
.navbar .dropdown-courses .dropdown-content {
    position: absolute;
    top: 100%;
    left: 0;
    background: #f8f9fa; /* light background */
    border-radius: 6px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    opacity: 0;
    visibility: hidden;
    transform: translateY(15px);
    transition: all 0.3s ease;
    z-index: 100;
    padding: 20px;
    min-width: 750px; /* adjust for 3 columns */
}

/* 3-column layout */
.multi-column {
    display: flex;
    gap: 25px;
}

.multi-column .column {
    display: flex;
    flex-direction: column;
}

/* Courses links */
.multi-column a {
    padding: 8px 0;
    font-family: 'Merriweather', serif; /* Harvard-style */
    font-size: 0.95rem;
    font-weight: 500;
    color: #111;
    transition: background 0.3s, color 0.3s;
}

.multi-column a:hover {
    color: #fff;
    background: #0b3d91; /* dark blue hover */
    border-radius: 4px;
}

/* Single-column dropdowns (More of us, Learn with us) */
.dropdown > ul.dropdown-content {
    position: absolute;
    top: 100%;
    left: 0;
    background: #f8f9fa;
    min-width: 200px;
    border-radius: 6px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all 0.3s ease;
    padding: 10px 0;
}

.dropdown > ul.dropdown-content a {
    display: block;
    padding: 10px 20px;
    font-family: 'Merriweather', serif;
    font-size: 0.95rem;
    font-weight: 500;
    color: #111;
    transition: background 0.3s, color 0.3s;
}

.dropdown > ul.dropdown-content a:hover {
    color: #fff;
    background: #0b3d91;
    border-radius: 4px;
}

/* Show dropdowns on hover */
.navbar .dropdown:hover .dropdown-content {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

/* Responsive adjustments */
@media(max-width: 992px) {
    .multi-column { flex-direction: column; min-width: 100%; gap: 0; }
    .dropdown > ul.dropdown-content { min-width: 100%; }
}

/* Buttons */
.navbar .btn {
    margin-left:1rem;
    border-radius:50px;
    padding:6px 20px;
    font-weight:600;
    transition:0.3s;
}
.navbar .btn:hover { background:#007bff; color:#fff; }

/* Responsive */
@media(max-width:768px) {
    .navbar { flex-direction:column; align-items:flex-start; }
    .navbar .nav-links { flex-direction:column; gap:0.5rem; width:100%; }
    .navbar .dropdown-content { position:relative; transform:none; top:0; left:0; box-shadow:none; }
}

/* Multi-column dropdown (Courses) */
.navbar .dropdown-courses .dropdown-content {
    position: absolute;
    top: 100%;
    left: 0;
    background: #f8f9fa;
    border-radius: 6px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    opacity: 0;
    visibility: hidden;
    transform: translateY(15px);
    transition: all 0.3s ease;
    z-index: 100;
    padding: 20px;
    width: 800px;              /* fixed width for 3 columns */
    max-width: 90vw;           /* prevent overflow on small screens */
}

/* 3-column layout using grid */
.navbar .dropdown-courses .multi-column {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
}

/* Course links */
.navbar .dropdown-courses .multi-column a {
    padding: 5px 0;
    font-family: 'Merriweather', serif;
    font-size: 0.95rem;
    font-weight: 500;
    color: #111;
    transition: background 0.3s, color 0.3s;
    display: block;
}

.navbar .dropdown-courses .multi-column a:hover {
    color: #fff;
    background: #0b3d91;
    border-radius: 4px;
    padding-left: 8px;
}

</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <span class="brand">🎓 RhodaX Tech School</span>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>

        <li class="dropdown dropdown-courses">
  <a href="courses.php">Courses <i class="fas fa-caret-down"></i></a>
  <div class="dropdown-content">
    <div class="multi-column">
      <div class="column">
        <a href="web_dev.php">Web Development</a>
        <a href="python.php">Python Programming</a>
        <a href="data_science.php">Data Science</a>
        <a href="blockchain.php">Blockchain Technology</a>
        <a href="ai.php">Artificial Intelligence</a>
      </div>
      <div class="column">
        <a href="cybersecurity.php">Cybersecurity</a>
        <a href="mobile_app.php">Mobile App Development</a>
        <a href="cloud_computing.php">Cloud Computing</a>
        <a href="ui_ux.php">UI/UX Design</a>
        <a href="game_dev.php">Game Development</a>
      </div>
      <div class="column">
        <a href="robotics.php">Robotics Engineering</a>
        <a href="networking.php">Networking & IT Support</a>
        <a href="ar_vr.php">AR/VR Development</a>
        <a href="quantum.php">Quantum Computing</a>
        <a href="iot.php">IoT & Smart Devices</a>
        <a href="devops.php">DevOps Engineering</a>
      </div>
    </div>
  </div>
</li>



        <li class="dropdown">
            <a href="about.php">More of us <i class="fas fa-caret-down"></i></a>
            <ul class="dropdown-content">
                 <li><a href="about.php">About</a></li>
                <li><a href="history.php">History</a></li>
                <li><a href="vision.php">Vision</a></li>
                <li><a href="mission.php">Mission</a></li>
            </ul>
        </li>
        <li class="dropdown">
            <a href="#">Learn with us <i class="fas fa-caret-down"></i></a>
            <ul class="dropdown-content">
                <li><a href="register.php">Register</a></li>
                <li><a href="login.php">login</a></li>
                <li><a href="stats.php">Stats</a></li>
            </ul>
        </li>

        <li><a href="live_chat.php" class="btn btn-outline-primary">Live Chat</a></li>
        <li><a href="contact.php" class="btn btn-primary">Contacts</a></li>
    </ul>
</nav>
