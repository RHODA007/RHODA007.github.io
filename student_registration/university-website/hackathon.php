<?php
$pageTitle = "Hackathon Event - RhodaX Tech School";
include 'includes/header.php';
?>

<section class="container py-5">
    <!-- Hero Section -->
    <div class="text-center mb-5">
        <h1 class="fw-bold text-primary mb-3">🚀 RhodaX Hackathon 2025</h1>
        <p class="text-muted mb-4">
            Join the ultimate coding challenge! Collaborate, innovate, and compete with students from all over. Build projects, win prizes, and showcase your skills.
        </p>
        <a href="#register" class="btn btn-primary btn-lg rounded-3">Register Now</a>
    </div>

    <!-- Event Features -->
    <div class="row g-4 justify-content-center mb-5">
        <div class="col-md-4">
            <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                <i class="fas fa-users fa-2x mb-3 text-primary"></i>
                <h5>Team Collaboration</h5>
                <p>Form teams of up to 4 members to work on innovative solutions.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                <i class="fas fa-lightbulb fa-2x mb-3 text-primary"></i>
                <h5>Innovative Projects</h5>
                <p>Create projects using cutting-edge tech: AI, Web, Mobile, and more.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                <i class="fas fa-trophy fa-2x mb-3 text-primary"></i>
                <h5>Win Prizes</h5>
                <p>Top projects will get cash prizes, internships, and certificates.</p>
            </div>
        </div>
    </div>

    <!-- Event Schedule -->
    <div class="mb-5">
        <h2 class="text-center fw-bold text-primary mb-4">Event Schedule</h2>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                    <h5>Day 1: Kickoff</h5>
                    <p>Introduction, team formation, and problem statements.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                    <h5>Day 2: Coding</h5>
                    <p>Teams work on their projects with mentorship and guidance.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                    <h5>Day 3: Presentations</h5>
                    <p>Project demos, judging, and award ceremony.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Section -->
    <div id="register" class="text-center">
        <h2 class="fw-bold text-primary mb-4">Register for Hackathon</h2>
        <p class="text-muted mb-4">Fill in your details to join the event!</p>
        <a href="register.php" class="btn btn-success btn-lg rounded-3">Register Now</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
