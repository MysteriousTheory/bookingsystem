<?php
require 'db.php';
require 'functions.php';

$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? ''; // Adding email to URL helps lookup but checking hash is key

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $token = $_POST['token'];
    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $token_hash = hash('sha256', $token);
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token_hash = ? AND reset_token_expires_at > NOW()");
        $stmt->execute([$token_hash]);
        $user = $stmt->fetch();
        
        if ($user) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?");
            $stmt->execute([$hashed_password, $user['id']]);
            
            sendNotificationEmail($user['email'], "Password Changed", "Your password has been successfully updated.");
            
            header("Location: index.php?password_reset=1");
            exit;
        } else {
            $error = "Invalid or expired token.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set New Password</title>
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
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">New Password</h1>
            <p class="text-sm text-gray-500 mt-2">Create a secure password</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm text-sm">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <div class="mb-5">
                <label class="block text-gray-700 text-sm font-bold mb-2">New Password</label>
                <input class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" name="password" type="password" required placeholder="••••••••">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                <input class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" name="confirm_password" type="password" required placeholder="••••••••">
            </div>
            
            <button class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg hover:shadow-purple-500/30 transition transform hover:-translate-y-0.5" type="submit">
                Update Password
            </button>
        </form>
    </div>
</body>
</html>
