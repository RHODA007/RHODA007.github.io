<?php
$pageTitle = "RhodaX Tech School";
include 'includes/header.php';
?>
<boby>
<!-- Hero Section -->
<section class="hero">
    <div class="bg-slide" style="background-image:url('assets/images/campus1.avif');"></div>
    <div class="bg-slide" style="background-image:url('assets/images/campus2.avif');"></div>
    <div class="bg-slide" style="background-image:url('assets/images/campus3.avif');"></div>

    <div class="hero-content text-center">
        <h1>Empowering Students to <span id="hero-word" class="text-primary">Innovate</span></h1>
        <p class="text-skyblue">Join the tech revolution at RhodaX Online Tech School</p>
        <a href="register.php" class="btn btn-skyblue">Get Started</a>
    </div>
</section>

<!-- Features Section -->
<section class="features py-5" style="background:#f9f9f9;">
    <div class="container">
        <div class="row g-4 text-center justify-content-center">
            <div class="col-md-4 col-sm-6 col-12">
                <div class="feature-card border-top border-primary">
                    <i class="fas fa-laptop-code fa-3x mb-3 text-primary"></i>
                    <h5 class="feature-title text-primary">Expert Instructors</h5>
                    <p class="feature-desc">Learn from experienced tech professionals who guide you every step of the way.</p>
                    <a href="courses.php" class="feature-btn btn-skyblue">View Courses</a>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12">
                <div class="feature-card border-top border-skyblue">
                    <i class="fas fa-lightbulb fa-3x mb-3 text-skyblue"></i>
                    <h5 class="feature-title text-skyblue">Innovative Curriculum</h5>
                    <p class="feature-desc">Up-to-date courses designed to prepare you for the future of technology.</p>
                    <a href="about.php" class="feature-btn btn-skyblue">Learn More</a>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12">
                <div class="feature-card border-top border-primary">
                    <i class="fas fa-book fa-3x mb-3 text-primary"></i>
                    <h5 class="feature-title text-primary">Extensive Resources</h5>
                    <p class="feature-desc">Access a vast library of learning materials, exercises, and projects.</p>
                    <a href="resources.php" class="feature-btn btn-skyblue">Explore</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Courses -->
<section class="popular-courses py-5 container">
    <h3 class="text-center mb-4 text-primary fw-bold">Our Programs</h3>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="course-card border-top border-skyblue">
                <img src="assets/images/course1.avif" class="course-img" alt="Web Development">
                <h5 class="text-primary">Web Development</h5>
                <p>Learn to build websites using modern tools and frameworks.</p>
                <small>Instructor: John Smith</small>
                <a href="web_dev.php" class="btn btn-skyblue mt-2">Join Now</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="course-card border-top border-primary">
                <img src="assets/images/course2.webp" class="course-img" alt="Python Programming">
                <h5 class="text-primary">Python Programming</h5>
                <p>Master Python from scratch and build real-world projects.</p>
                <small>Instructor: Jane Doe</small>
                <a href="python.php" class="btn btn-skyblue mt-2">Join Now</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="course-card border-top border-skyblue">
                <img src="assets/images/course3.avif" class="course-img" alt="Data Science">
                <h5 class="text-primary">Data Science</h5>
                <p>Analyze data, visualize insights, and make data-driven decisions.</p>
                <small>Instructor: Alice Brown</small>
                <a href="data_science.php" class="btn btn-skyblue mt-2">Join Now</a>
            </div>
        </div>
    </div>
</section>

<!-- Instructors Section -->
<section id="instructors" class="py-5" style="background:#f9f9f9;">
    <div class="container">
        <h2 class="text-center mb-5 text-primary fw-bold">Meet Our Faculty Exparts</h2>
        <div class="row justify-content-center">
            <div class="col-md-4 col-sm-6 col-12 text-center mb-4">
                <div class="faculty-card border-top border-skyblue">
                    <img src="assets/images/john.avif" alt="John Smith" class="faculty-img">
                    <h5 class="faculty-name text-primary">John Smith</h5>
                    <p class="faculty-bio">Expert in Web Development with over 10 years of teaching experience.</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12 text-center mb-4">
                <div class="faculty-card border-top border-primary">
                    <img src="assets/images/jane.avif" alt="Jane Doe" class="faculty-img">
                    <h5 class="faculty-name text-skyblue">Jane Doe</h5>
                    <p class="faculty-bio">Python specialist focused on practical, real-world applications.</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12 text-center mb-4">
                <div class="faculty-card border-top border-skyblue">
                    <img src="assets/images/alice.avif" alt="Alice Brown" class="faculty-img">
                    <h5 class="faculty-name text-primary">Alice Brown</h5>
                    <p class="faculty-bio">Data Science guru, helping students make sense of complex data.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials py-5 container">
    <h3 class="text-center mb-4 text-primary fw-bold">What Our Students Say</h3>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="testimonial-card text-center p-4 border-top border-primary">
                <img src="assets/images/testimonial1.avif" alt="Rhoda Victor" class="rounded-circle mb-3" style="width:80px; height:80px; object-fit:cover;">
                <p class="fst-italic text-skyblue">"Amazing online courses that boosted my career!"</p>
                <h6 class="fw-bold mt-2 text-primary">Rhoda Victor</h6>
                <small class="text-muted">Web Development</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="testimonial-card text-center p-4 border-top border-skyblue">
                <img src="assets/images/testimonial2.avif" alt="Balogun Michel" class="rounded-circle mb-3" style="width:80px; height:80px; object-fit:cover;">
                <p class="fst-italic text-skyblue">"Learned so much from the instructors. Highly recommend!"</p>
                <h6 class="fw-bold mt-2 text-primary">Victor Emmanuel</h6>
                <small class="text-muted">Python Programming</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="testimonial-card text-center p-4 border-top border-primary">
                <img src="assets/images/testimonial3.avif" alt="Moses Monday" class="rounded-circle mb-3" style="width:80px; height:80px; object-fit:cover;">
                <p class="fst-italic text-skyblue">"The courses are clear, practical, and engaging."</p>
                <h6 class="fw-bold mt-2 text-primary">New</h6>
                <small class="text-muted">Data Science</small>
            </div>
        </div>
    </div>
</section>

<!-- Upcoming Events -->
<section class="events py-5 container">
    <h3 class="text-center mb-4 text-primary fw-bold">Upcoming Events</h3>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="event-card text-center p-3 border-top border-skyblue">
                <img src="assets/images/events1.avif" alt="Hackathon 2025" class="img-fluid mb-3 rounded">
                <h5 class="text-primary">Hackathon 2025</h5>
                <small>Sep 15, 2025</small>
                <p>Join our 48-hour coding challenge!</p>
                <a href="hackathon.php" class="btn btn-outline-skyblue btn-sm mt-2">Learn More</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="event-card text-center p-3 border-top border-primary">
                <img src="assets/images/events2.avif" alt="AI Workshop" class="img-fluid mb-3 rounded">
                <h5 class="text-primary">AI Workshop</h5>
                <small>Oct 01, 2025</small>
                <p>Learn AI from experts online.</p>
                <a href="ai_workshop.php" class="btn btn-outline-skyblue btn-sm mt-2">Learn More</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="event-card text-center p-3 border-top border-skyblue">
                <img src="assets/images/events3.avif" alt="Career Fair" class="img-fluid mb-3 rounded">
                <h5 class="text-primary">Career Fair</h5>
                <small>Nov 05, 2025</small>
                <p>Meet top companies hiring graduates this year.</p>
                <a href="career.php" class="btn btn-outline-skyblue btn-sm mt-2">Learn More</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- Inline Styles -->
<style>
/* Hero Section */
.text-primary { color:#0d6efd !important; } /* Blue */
.text-skyblue { color:#33cfff !important; }  /* Skyblue */
.btn-skyblue { background: linear-gradient(135deg, #33cfff, #0d6efd); color:#fff; border:none; border-radius:50px; transition:0.3s; }
.btn-skyblue:hover { background: linear-gradient(135deg, #0d6efd, #33cfff); color:#fff; }

.btn-outline-skyblue { border:2px solid #33cfff; color:#33cfff; border-radius:50px; transition:0.3s; }
.btn-outline-skyblue:hover { background:#33cfff; color:#fff; }

.border-primary { border-top:4px solid #0d6efd; }
.border-skyblue { border-top:4px solid #33cfff; }

/* Hero Section */
.hero { position:relative; height:80vh; display:flex; align-items:center; justify-content:center; text-align:center; overflow:hidden; color:#111; }
.bg-slide { position:absolute; top:0; left:0; width:100%; height:100%; background-size:cover; background-position:center; transition:opacity 1s ease-in-out; z-index:-1; }
.hero::before { content:''; position:absolute; top:0; left:0; width:100%; height:100%; background: rgba(245,245,245,0.7); z-index:0; }
.hero-content { position:relative; z-index:1; }
.hero-content h1 { font-size:2.8rem; font-weight:700; }
.hero-content p { font-size:1.1rem; margin:15px 0; }
.hero-content .btn { padding:10px 25px; font-weight:600; border-radius:50px; }

/* Feature, Course, Faculty, Testimonial, Event cards same hover + shadow as original */
.feature-card, .course-card, .faculty-card, .testimonial-card, .event-card { background:#fff; border-radius:15px; padding:20px; box-shadow:0 4px 15px rgba(0,0,0,0.08); transition: transform 0.3s, box-shadow 0.3s; }
.feature-card:hover, .course-card:hover, .faculty-card:hover, .testimonial-card:hover, .event-card:hover { transform:translateY(-5px); box-shadow:0 8px 25px rgba(0,0,0,0.15); }
.course-img { width:100%; height:180px; object-fit:cover; border-radius:8px; margin-bottom:15px; }
.faculty-img { width:120px; height:120px; border-radius:50%; object-fit:cover; margin-bottom:15px; border:2px solid #ddd; }

/* Footer Simplified */
footer { background:#f1f1f1; color:#555; padding:40px 0; font-size:0.9rem; }
footer a { color:#555; text-decoration:none; }
footer a:hover { color:#111; }
</style>
</body>


<!-- Hero Carousel JS -->
<script>
const slides = document.querySelectorAll('.bg-slide');
const heroWords = ["Innovate","Learn","Code","Create","Succeed"];
let currentSlide = 0;
let currentWord = 0;

function showNextSlide() {
    slides.forEach((slide,i)=> slide.style.opacity=(i===currentSlide?'1':'0'));
    document.getElementById('hero-word').textContent = heroWords[currentWord];
    currentSlide=(currentSlide+1)%slides.length;
    currentWord=(currentWord+1)%heroWords.length;
}

setInterval(showNextSlide,5000);
</script>
