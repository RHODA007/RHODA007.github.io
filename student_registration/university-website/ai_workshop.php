<?php
$pageTitle = "AI Workshop - RhodaX Tech School";
include 'includes/header.php';
?>

<section class="container py-5">
    <!-- Hero Section -->
    <div class="text-center mb-5">
        <h1 class="fw-bold text-primary mb-3">🤖 AI Workshop 2025</h1>
        <p class="text-muted mb-4">
            Learn the fundamentals and advanced concepts of Artificial Intelligence in a hands-on workshop. Explore AI, Machine Learning, and Deep Learning with real-world projects.
        </p>
        <a href="#register" class="btn btn-primary btn-lg rounded-3">Join the Workshop</a>
    </div>

    <!-- Workshop Features -->
    <div class="row g-4 justify-content-center mb-5">
        <div class="col-md-4">
            <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                <i class="fas fa-brain fa-2x mb-3 text-primary"></i>
                <h5>Hands-on Learning</h5>
                <p>Work with AI libraries, tools, and datasets to build real projects.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                <i class="fas fa-robot fa-2x mb-3 text-primary"></i>
                <h5>Expert Mentors</h5>
                <p>Learn from experienced AI professionals and industry experts.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                <i class="fas fa-project-diagram fa-2x mb-3 text-primary"></i>
                <h5>Project-based Approach</h5>
                <p>Build AI projects and showcase your skills in practical applications.</p>
            </div>
        </div>
    </div>

    <!-- Workshop Schedule -->
    <div class="mb-5">
        <h2 class="text-center fw-bold text-primary mb-4">Workshop Schedule</h2>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                    <h5>Day 1: Introduction to AI</h5>
                    <p>Understanding AI concepts, history, and applications in different industries.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                    <h5>Day 2: Machine Learning</h5>
                    <p>Learn algorithms, model training, and hands-on coding exercises.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                    <h5>Day 3: Deep Learning & Projects</h5>
                    <p>Build neural networks, apply deep learning, and complete final projects.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Section -->
    <div id="register" class="text-center">
        <h2 class="fw-bold text-primary mb-4">Register for AI Workshop</h2>
        <p class="text-muted mb-4">Secure your spot now and start your AI journey!</p>
        <a href="register.php" class="btn btn-success btn-lg rounded-3">Register Now</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
