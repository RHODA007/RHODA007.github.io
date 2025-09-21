<?php
session_start();
require_once 'includes/config.php';

if (!isset($_GET['reference'])) {
    die("No payment reference supplied.");
}

$reference = $_GET['reference'];
$student_id = $_SESSION['student_id'];

// 🔹 Verify payment with Paystack API
$paystackSecret = "sk_test_xxxxxxxxxxxxx"; // replace with your secret key
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/verify/" . $reference);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $paystackSecret"
]);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result && isset($result['data']['status']) && $result['data']['status'] === 'success') {
    $amount = $result['data']['amount'] / 100; // convert kobo to naira
    $status = "success";

    // Save payment into DB
    $stmt = $pdo->prepare("INSERT INTO payments (student_id, amount, reference, status) VALUES (?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE status=VALUES(status)");
    $stmt->execute([$student_id, $amount, $reference, $status]);

    // ✅ Redirect to dashboard
    header("Location: student_dashboard.php?payment=success");
    exit;
} else {
    // Payment failed
    $stmt = $pdo->prepare("INSERT INTO payments (student_id, amount, reference, status) VALUES (?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE status=VALUES(status)");
    $stmt->execute([$student_id, 0, $reference, 'failed']);

    header("Location: enrollment.php?payment=failed");
    exit;
}
