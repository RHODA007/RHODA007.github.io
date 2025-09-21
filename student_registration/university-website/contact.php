<?php
$pageTitle = "Contact Us - RhodaX Tech School";
include 'includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $secretKey = "6LdCVMorAAAAAAY0_5kssY87tEHpDGLsubouXcWD"; // Your secret key
    $captchaResponse = $_POST['g-recaptcha-response'] ?? '';

    // Verify with Google
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$captchaResponse}");
    $responseData = json_decode($verify);

    if ($responseData->success) {
        // ✅ reCAPTCHA passed
        $name = htmlspecialchars($_POST['name']);
        $email = htmlspecialchars($_POST['email']);
        $message = htmlspecialchars($_POST['message']);

        // Example: send email or save to DB
        // mail("youremail@domain.com", "New Contact Message", "From: $name <$email>\n\n$message");

        $success = "✅ Your message has been sent successfully!";
    } else {
        $error = "⚠️ Please confirm you are not a robot.";
    }
}
?>

<section class="container py-5">
    <h2 class="text-center mb-4 text-primary fw-bold">Contact Us</h2>
    <p class="text-center mb-5">
        We’d love to hear from you! Reach out anytime, and we'll get back to you as soon as possible.
    </p>

    <div class="row g-4 justify-content-center">
        <!-- Contact Form -->
        <div class="col-md-6">
            <div class="feature-card p-4 glass-card">
                <h5>Send Us a Message</h5>

                <?php if(!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php elseif(!empty($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label"><i class="fa fa-user"></i> Your Name</label>
                        <input type="text" class="form-control" name="name" placeholder="Enter your name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fa fa-envelope"></i> Email Address</label>
                        <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fa fa-comment"></i> Message</label>
                        <textarea class="form-control" name="message" placeholder="Write your message..." rows="5" required></textarea>
                    </div>

                    <!-- Google reCAPTCHA -->
                    <div class="g-recaptcha mb-3" data-sitekey="6LdCVMorAAAAAKrdfzXvYsSLYncRy0mmH73U1Ehw"></div>

                    <button type="submit" class="btn btn-primary w-100">Send Message</button>
                </form>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="col-md-4">
            <div class="feature-card p-4 glass-card">
                <h5>Contact Information</h5>
                <p><i class="fa fa-envelope"></i> <strong>Email:</strong> 
                    <a href="mailto:teddysassy67@gmail.com" style="text-decoration:underline;">teddysassy67@gmail.com</a>
                </p>
                <p><i class="fa fa-phone"></i> <strong>Phone:</strong> +234 815 518 3456</p>
                <p><i class="fa fa-map-marker-alt"></i> <strong>Address:</strong> 66, igbe road, Auchi, Benin City 312101, Edo</p>
            </div><br>
             <!-- WhatsApp Box -->
    <div class="feature-card p-4 glass-card">
        <h5>Chat with Us</h5>
        <p>Need quick help? Reach us directly on WhatsApp!</p>
        <a href="https://wa.me/2348155183456" 
           target="_blank" 
           class="btn btn-success w-100">
           <i class="fa fa-whatsapp"></i> Chat on WhatsApp
        </a>
    </div>
</div>
        </div>
    </div>


    <!-- Google Map Section -->
    <div class="row mt-4 justify-content-center">
        <div class="col-md-10">
            <div class="feature-card p-3 glass-card">
                <h5 class="mb-3">Find Us on the Map</h5>
                <div style="width: 100%; height: 400px;">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3940.523245783415!2d6.2682!3d7.0741!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1043f29c2f00e9c7%3A0xf65d8c1f6d64b4b!2s66%20Igbe%20Rd%2C%20Auchi%2C%20Edo!5e0!3m2!1sen!2sng!4v1694365740000!5m2!1sen!2sng" 
                        width="100%" 
                        height="100%" 
                        style="border:0; border-radius:12px;" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>


<?php include 'includes/footer.php'; ?>

<!-- Google reCAPTCHA API -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<style>
/* Glass Card Styling */
.glass-card {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(12px);
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    color: #111;
}

.glass-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.feature-card h5 {
    font-weight: 600;
    margin-bottom: 15px;
    color: #1e90ff;
}

.feature-card p, .feature-card a {
    font-size: 0.95rem;
    line-height: 1.5;
    color: #111;
}

.feature-card a:hover {
    color: #007bff;
    text-decoration: none;
}
</style>
