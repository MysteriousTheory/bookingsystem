<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php';

function getEmailTemplate($title, $message) {
    // Placeholder logo (replace with actual URL)
    $logoUrl = "https://tickets.prismtechnologies.com.ng/images/prism-logo.png"; 
    $year = date('Y');
    
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background-color: #4f46e5; padding: 20px; text-align: center; }
        .header img { height: 50px; border-radius: 5px; }
        .content { padding: 30px; color: #374151; line-height: 1.6; }
        .content h2 { margin-top: 0; color: #111827; }
        .footer { background-color: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }
        .btn { display: inline-block; background-color: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="$logoUrl" alt="Logo">
        </div>
        <div class="content">
            <h2>$title</h2>
            <p>$message</p>
            <a href="http://{$_SERVER['HTTP_HOST']}" class="btn">View Ticket</a>
        </div>
        <div class="footer">
            &copy; $year Prism Technologies. All rights reserved.
        </div>
    </div>
</body>
</html>
HTML;
}

function sendNotificationEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output if needed
        $mail->isSMTP();
        $mail->Host       = getenv('SMTP_HOST') ?: 'smtp.example.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USER') ?: 'user@example.com';
        $mail->Password   = getenv('SMTP_PASS') ?: 'secret';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = getenv('SMTP_PORT') ?: 587;

        // Recipients
        $fromEmail = getenv('SMTP_FROM_EMAIL') ?: 'no-reply@example.com';
        $fromName = getenv('SMTP_FROM_NAME') ?: 'Support System';
        
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);
        $mail->addCC('info@prismtechnologies.com.ng');

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = getEmailTemplate($subject, nl2br($message));
        $mail->AltBody = strip_tags($message);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Basic HTML Sanitizer for Rich Text
function cleanHtml($html) {
    // 1. Strip disallowed tags
    $allowed_tags = '<p><br><b><i><u><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><code><pre>';
    $clean = strip_tags($html, $allowed_tags);
    
    // 2. Remove potential XSS attributes (basic regex)
    // Removes on* events (onclick, onload) and javascript: uris
    $clean = preg_replace('/(<[^>]+) on[a-z]+="[^"]*"/i', '$1', $clean); 
    $clean = preg_replace('/javascript:[^"]*/i', '', $clean);
    
    return $clean;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: index.php");
        exit;
    }
}
?>
