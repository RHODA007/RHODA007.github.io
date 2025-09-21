<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: register.php");
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<!-- HERO -->
<section class="container text-center py-5">
    <h1 class="fw-bold text-primary">🌍 Welcome to RhodaX Tech School</h1>
    <p class="lead mb-4">100% Virtual. 100% Practical. <br> Learn from anywhere and build your future in tech 🚀</p>
    <a href="enrollment.php" class="btn btn-primary btn-lg px-4">Join Our Virtual Campus</a>
</section>

<!-- WHY CHOOSE US -->
<section class="container py-5">
    <h2 class="text-center mb-4 fw-bold text-primary">✨ Why Learn With Us?</h2>
    <div class="row g-4 text-center">
        <div class="col-md-4">
            <div class="feature-card p-4 shadow-sm rounded bg-white h-100">
                <i class="fas fa-globe fa-2x text-primary mb-3"></i>
                <h4 class="fw-bold">Global Learning</h4>
                <p>Connect with learners worldwide and grow in a diverse tech community.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card p-4 shadow-sm rounded bg-white h-100">
                <i class="fas fa-chalkboard-teacher fa-2x text-primary mb-3"></i>
                <h4 class="fw-bold">Expert Instructors</h4>
                <p>Learn from industry professionals with years of hands-on experience.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card p-4 shadow-sm rounded bg-white h-100">
                <i class="fas fa-laptop-code fa-2x text-primary mb-3"></i>
                <h4 class="fw-bold">Practical Projects</h4>
                <p>Work on real-world projects to build skills employers value most.</p>
            </div>
        </div>
    </div>
</section>

<!-- VIRTUAL GALLERY -->
<section class="container py-5">
    <h2 class="text-center mb-4 fw-bold text-primary">📸 Our Virtual Experience</h2>
    <div class="row g-4">
        <div class="col-md-3 col-sm-6">
            <img src="assets/images/online-class.avif" class="img-fluid rounded shadow-sm" alt="Online Class">
        </div>
        <div class="col-md-3 col-sm-6">
            <img src="assets/images/virtual-teamwork.avif" class="img-fluid rounded shadow-sm" alt="Teamwork">
        </div>
        <div class="col-md-3 col-sm-6">
            <img src="assets/images/student-learning.avif" class="img-fluid rounded shadow-sm" alt="Student Learning Online">
        </div>
        <div class="col-md-3 col-sm-6">
            <img src="assets/images/project-showcase.avif" class="img-fluid rounded shadow-sm" alt="Project Showcase">
        </div>
    </div>
</section>

<!-- SUCCESS STATS -->
<section class="container py-5">
    <h2 class="text-center mb-4 fw-bold text-primary">Our Achievements</h2>
    <div class="row text-center g-4">
        <div class="col-md-4">
            <div class="p-4 shadow-sm rounded bg-white h-100">
                <h3 class="text-primary fw-bold">500+</h3>
                <p>Active Students</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 shadow-sm rounded bg-white h-100">
                <h3 class="text-primary fw-bold">15+</h3>
                <p>Tech Courses</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 shadow-sm rounded bg-white h-100">
                <h3 class="text-primary fw-bold">100%</h3>
                <p>Virtual Learning</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
