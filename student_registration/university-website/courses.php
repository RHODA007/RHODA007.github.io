<?php 
$pageTitle = "Our Courses - RhodaX Tech School";
include 'includes/header.php'; 
?>

<section class="container py-5">
    <h2 class="text-center mb-4 text-primary fw-bold">Our Courses</h2>
    <p class="text-center mb-5">
        Explore a variety of technology programs designed to give you hands-on skills and real-world experience.
    </p>

    <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <div class="feature-card p-4">
                <i class="fas fa-laptop-code fa-2x mb-3 text-primary"></i>
                <h5>Web Development</h5>
                <p>Learn frontend and backend development with HTML, CSS, JavaScript, PHP, and Node.js through real-world projects.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card p-4">
                <i class="fas fa-robot fa-2x mb-3 text-primary"></i>
                <h5>Artificial Intelligence</h5>
                <p>Master AI concepts, machine learning, neural networks, and AI tools to build intelligent systems.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card p-4">
                <i class="fas fa-shield-alt fa-2x mb-3 text-primary"></i>
                <h5>Cybersecurity</h5>
                <p>Protect systems and networks through ethical hacking, penetration testing, and security protocols.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card p-4">
                <i class="fas fa-database fa-2x mb-3 text-primary"></i>
                <h5>Data Science</h5>
                <p>Analyze and visualize data using Python, R, SQL, and advanced analytics tools.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card p-4">
                <i class="fas fa-mobile-alt fa-2x mb-3 text-primary"></i>
                <h5>Mobile App Development</h5>
                <p>Design and build Android & iOS apps using Flutter and React Native with practical projects.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card p-4">
                <i class="fas fa-cloud fa-2x mb-3 text-primary"></i>
                <h5>Cloud Computing</h5>
                <p>Deploy scalable applications using AWS, Azure, and Google Cloud with modern DevOps practices.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card p-4">
                <i class="fas fa-link fa-2x mb-3 text-primary"></i>
                <h5>Blockchain Technology</h5>
                <p>Understand cryptocurrencies, smart contracts, and decentralized applications.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card p-4">
                <i class="fas fa-pencil-ruler fa-2x mb-3 text-primary"></i>
                <h5>UI/UX Design</h5>
                <p>Create user-centered designs with Figma, Adobe XD, and modern design thinking principles.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card p-4">
                <i class="fas fa-gamepad fa-2x mb-3 text-primary"></i>
                <h5>Game Development</h5>
                <p>Build 2D & 3D games using Unity, Unreal Engine, and C# scripting with practical projects.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card p-4">
                <i class="fas fa-robot fa-2x mb-3 text-primary"></i>
                <h5>Robotics Engineering</h5>
                <p>Learn hardware programming, IoT, and automation to build real robots.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card p-4">
                <i class="fas fa-network-wired fa-2x mb-3 text-primary"></i>
                <h5>Networking & IT Support</h5>
                <p>Get hands-on experience with Cisco, Linux servers, and IT infrastructure management.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card p-4">
                <i class="fas fa-vr-cardboard fa-2x mb-3 text-primary"></i>
                <h5>AR/VR Development</h5>
                <p>Create immersive Augmented and Virtual Reality experiences using modern tools.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card p-4">
                <i class="fas fa-brain fa-2x mb-3 text-primary"></i>
                <h5>Quantum Computing</h5>
                <p>Learn quantum algorithms, qubits, and future computing paradigms.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card p-4">
                <i class="fas fa-satellite-dish fa-2x mb-3 text-primary"></i>
                <h5>IoT & Smart Devices</h5>
                <p>Connect devices, automate tasks, and program smart systems for everyday applications.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card p-4">
                <i class="fas fa-cogs fa-2x mb-3 text-primary"></i>
                <h5>DevOps Engineering</h5>
                <p>Implement CI/CD pipelines, containerization, and cloud automation for scalable apps.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<style>
.feature-card {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(12px);
    border-radius: 15px;
    transition: transform 0.3s, box-shadow 0.3s;
    color: #111;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}

.feature-card h5 {
    font-weight: 600;
    margin-bottom: 12px;
    color: #1e90ff;
}

.feature-card p {
    font-size: 0.95rem;
    line-height: 1.5;
}
</style>
