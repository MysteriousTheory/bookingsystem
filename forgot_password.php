<?php
require 'db.php';
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $token = bin2hex(random_bytes(16));
        $token_hash = hash('sha256', $token);
        $expiry = date("Y-m-d H:i:s", time() + 60 * 60); // 1 hour expiry

        $stmt = $pdo->prepare("UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE id = ?");
        $stmt->execute([$token_hash, $expiry, $user['id']]);

        $resetLink = "http://{$_SERVER['HTTP_HOST']}/reset_password.php?token=$token&email=$email";
        
        sendNotificationEmail($email, "Password Reset Request", "Hi {$user['name']},\n\nClick the link below to reset your password:\n\n$resetLink\n\nIf you did not request this, please ignore this email.");
        
        $message = "If an account exists for that email, we have sent a password reset link.";
    } else {
        // Same message to prevent user enumeration
        $message = "If an account exists for that email, we have sent a password reset link."; 
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="icon" href="images/prism-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen flex items-center justify-center font-sans px-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow-2xl p-8 transform transition hover:scale-[1.01] duration-300 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-purple-500 to-blue-500"></div>
        <div class="text-center mb-8">
            <div class="inline-flex content-center justify-center bg-black p-3 rounded-xl mb-4 shadow-lg shadow-purple-500/20">
                 <img src="https://tickets.prismtechnologies.com.ng/images/prism-logo.png" alt="Logo" class="h-10 w-auto">
            </div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Reset Password</h1>
            <p class="text-sm text-gray-500 mt-2">Enter your email to receive a reset link</p>
        </div>
        
        <?php if(isset($message)): ?>
            <div class="bg-purple-50 border-l-4 border-purple-500 text-purple-700 p-4 mb-6 rounded shadow-sm text-sm">
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST">
             <div class="mb-5">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                <input class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" name="email" type="email" required placeholder="name@example.com">
            </div>
            <button class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg hover:shadow-purple-500/30 transition transform hover:-translate-y-0.5" type="submit">
                Send Reset Link
            </button>
        </form>
        <div class="mt-6 text-center">
            <a href="index.php" class="text-sm text-gray-500 hover:text-purple-600 font-medium transition">Back to Login</a>
        </div>
    </div>
</body>
</html>
