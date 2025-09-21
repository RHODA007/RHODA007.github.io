<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendWelcomeMail($toEmail, $toName, $role) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Config
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'yourgmail@gmail.com'; // 🔹 your Gmail
        $mail->Password   = 'your-app-password';   // 🔹 Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender & Recipient
        $mail->setFrom('yourgmail@gmail.com', 'RhodaX Tech School');
        $mail->addAddress($toEmail, $toName);

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = "🎉 Welcome to RhodaX Tech School, $toName!";

        // Styled HTML Email
        $mail->Body = "
        <html>
        <head>
          <style>
            body { font-family: Arial, sans-serif; background:#f7f9fc; padding:20px; }
            .container {
                max-width:600px; margin:0 auto; background:#ffffff;
                border-radius:10px; overflow:hidden; box-shadow:0 5px 20px rgba(0,0,0,0.1);
            }
            .header { background:#4a47a3; color:#fff; padding:20px; text-align:center; }
            .header h1 { margin:0; font-size:24px; }
            .content { padding:30px; color:#333; line-height:1.6; }
            .content h2 { color:#4a47a3; }
            .button {
                display:inline-block; padding:12px 25px; margin-top:20px;
                background:#4a47a3; color:#fff !important; text-decoration:none;
                border-radius:5px; font-weight:bold;
            }
            .footer { background:#f1f1f1; padding:15px; text-align:center; font-size:12px; color:#777; }
          </style>
        </head>
        <body>
          <div class='container'>
            <div class='header'>
              <h1>RhodaX Tech School</h1>
            </div>
            <div class='content'>
              <h2>Welcome, $toName! 🎓</h2>
              <p>We’re excited to have you onboard as a <b>$role</b> at <b>RhodaX Tech School</b>.</p>
              <p>You’ve just taken the first step into a world of endless opportunities in technology 🚀.</p>
              <p>Here’s what you can do next:</p>
              <ul>
                <li>Log in to your account</li>
                <li>Access our virtual learning hub</li>
                <li>Connect with mentors and peers worldwide</li>
              </ul>
              <a href='https://yourwebsite.com/login.php' class='button'>Get Started</a>
            </div>
            <div class='footer'>
              &copy; " . date("Y") . " RhodaX Tech School. All rights reserved.
            </div>
          </div>
        </body>
        </html>
        ";

        $mail->AltBody = "Welcome $toName! You are now registered as a $role at RhodaX Tech School. Please log in to get started.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
