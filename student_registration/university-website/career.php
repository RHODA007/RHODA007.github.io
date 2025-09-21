<?php
$pageTitle = "Career Fair - RhodaX Tech School";
include 'includes/header.php';
?>

<section class="container py-5">
    <!-- Hero Section -->
    <div class="text-center mb-5">
        <h1 class="fw-bold text-primary mb-3">💼 Career Fair 2025</h1>
        <p class="text-muted mb-4">
            Connect with top companies, explore job opportunities, and kickstart your career. Meet recruiters, attend workshops, and get hired!
        </p>
        <a href="#register" class="btn btn-primary btn-lg rounded-3">Register to Attend</a>
    </div>

    <!-- Event Highlights -->
    <div class="row g-4 justify-content-center mb-5">
        <div class="col-md-4">
            <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                <i class="fas fa-building fa-2x mb-3 text-primary"></i>
                <h5>Top Companies</h5>
                <p>Meet recruiters from leading tech, finance, and consulting companies hiring graduates this year.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                <i class="fas fa-handshake fa-2x mb-3 text-primary"></i>
                <h5>Networking Opportunities</h5>
                <p>Interact with industry professionals, mentors, and fellow students to build connections.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                <i class="fas fa-lightbulb fa-2x mb-3 text-primary"></i>
                <h5>Workshops & Talks</h5>
                <p>Attend career workshops, resume reviews, and panel discussions to prepare for the job market.</p>
            </div>
        </div>
    </div>

    <!-- Event Schedule -->
    <div class="mb-5">
        <h2 class="text-center fw-bold text-primary mb-4">Career Fair Schedule</h2>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                    <h5>Day 1: Company Presentations</h5>
                    <p>Learn about companies, job roles, and expectations from recruiters.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                    <h5>Day 2: Networking & Mentorship</h5>
                    <p>Meet professionals, get mentorship, and discuss career paths.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card p-4 shadow-sm rounded-4 bg-white text-center h-100">
                    <h5>Day 3: Job Interviews</h5>
                    <p>Participate in on-site interviews and secure your first role after graduation.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Section -->
    <div id="register" class="text-center">
        <h2 class="fw-bold text-primary mb-4">Register for Career Fair</h2>
        <p class="text-muted mb-4">Secure your spot and connect with top recruiters!</p>
        <a href="register.php" class="btn btn-success btn-lg rounded-3">Register Now</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
