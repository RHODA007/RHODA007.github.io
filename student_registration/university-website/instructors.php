<?php
$pageTitle = "Expert Instructors - RhodaX Tech School";
include 'includes/header.php';
?>

<section class="container py-5">
    <!-- Hero / Feature Section -->
    <div class="text-center mb-5">
        <h1 class="fw-bold text-primary mb-3">👨‍🏫 Expert Instructors</h1>
        <p class="text-muted mb-4">
            Learn from experienced tech professionals who guide you every step of the way. Gain practical skills, mentorship, and industry insights from experts.
        </p>
        <a href="courses.php" class="btn btn-primary btn-lg rounded-3">View Courses</a>
    </div>

    <!-- Instructor Highlights -->
    <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                <img src="assets/images/instructor1.jpg" class="rounded-circle mb-3" alt="Instructor 1" style="width:100px; height:100px; object-fit:cover;">
                <h5>Jane Doe</h5>
                <p class="text-muted">Full-Stack Developer & AI Enthusiast with 8+ years of industry experience.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                <img src="assets/images/instructor2.jpg" class="rounded-circle mb-3" alt="Instructor 2" style="width:100px; height:100px; object-fit:cover;">
                <h5>John Smith</h5>
                <p class="text-muted">Cybersecurity Expert & Cloud Architect, helping students build real-world solutions.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                <img src="assets/images/instructor3.jpg" class="rounded-circle mb-3" alt="Instructor 3" style="width:100px; height:100px; object-fit:cover;">
                <h5>Mary Johnson</h5>
                <p class="text-muted">Data Scientist & Machine Learning Engineer, specializing in AI-driven projects.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
