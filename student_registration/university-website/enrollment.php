<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: register.php");
    exit;
}

// Example courses and their fees (in Naira)
$courses = [
    "Web Development" => 50000,
    "Artificial Intelligence" => 70000,
    "Cybersecurity" => 60000,
    "Data Science" => 75000,
    "Mobile App Development" => 55000,
    "Cloud Computing" => 65000,
    "Blockchain Technology" => 80000,
    "UI/UX Design" => 45000,
    "Game Development" => 70000,
    "Robotics Engineering" => 85000,
    "Networking & IT Support" => 50000,
    "AR/VR Development" => 90000,
    "Quantum Computing" => 120000,
    "IoT & Smart Devices" => 60000,
    "DevOps Engineering" => 70000
];

// Get student info
$student_email = $_SESSION['email'];
$student_name  = $_SESSION['student'];
$student_id    = $_SESSION['student_id'];

// Default course (or get from database/session/form)
$selected_course = $_GET['course'] ?? "Web Development"; 
$session_fee = $courses[$selected_course] ?? 50000;
?>


<?php include 'includes/header.php'; ?>
<section class="container py-5">
    <h2 class="text-center text-primary fw-bold">💳 Enrollment & Session Fees</h2>
    <p class="text-center mb-4">Complete your enrollment by paying the session fee.</p>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow p-4 text-center">
                <h4 class="mb-3">Hello, <?= htmlspecialchars($student_name) ?> 👋</h4>
                <p>Selected Course: <strong><?= htmlspecialchars($selected_course) ?></strong></p>
                <p>Course enrollment fee: <strong>₦<?= number_format($session_fee, 2) ?></strong></p>

                <button id="payBtn" class="btn btn-success w-100 py-2 fw-bold">Pay with Flutterwave</button>
            </div>
        </div>
    </div>
</section>

<!-- Flutterwave Inline JS -->
<script src="https://checkout.flutterwave.com/v3.js"></script>
<script>
document.getElementById("payBtn").addEventListener("click", function () {
    FlutterwaveCheckout({
        public_key: "FLWPUBK_TEST-f355aac214454453205ce04a479e5df4-X",
        tx_ref: "tx-" + Date.now(),
        amount: <?= $session_fee ?>,
        currency: "NGN",
        payment_options: "card, banktransfer, ussd",
        redirect_url: "payment_callback.php", // after payment, handle response here
        customer: {
            email: "<?= $student_email ?>",
            name: "<?= $student_name ?>",
        },
        meta: {
            student_id: "<?= $student_id ?>",
            course: "<?= $selected_course ?>"
        },
        customizations: {
            title: "Enrollment Payment",
            description: "Payment for <?= $selected_course ?>",
            logo: "https://yourdomain.com/logo.png"
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
