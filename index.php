<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['email'] = $user['email'];
        
        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: home.php");
        }
        exit;
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Login</title>
    <link rel="icon" href="images/prism-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen flex items-center justify-center font-sans px-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow-2xl p-8 transform transition hover:scale-[1.01] duration-300 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-purple-500 to-indigo-500"></div>
        <div class="text-center mb-8">
             <div class="inline-flex content-center justify-center bg-black p-3 rounded-xl mb-4 shadow-lg shadow-purple-500/20">
                 <img src="https://tickets.prismtechnologies.com.ng/images/prism-logo.png" alt="Logo" class="h-10 w-auto">
            </div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Access Portal</h1>
            <p class="text-gray-500 mt-2 text-sm">Enter your credentials to continue</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r text-sm" role="alert">
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-1" for="email">Email Address</label>
                <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent transition bg-gray-50 hover:bg-white" id="email" name="email" type="email" placeholder="name@company.com" required>
            </div>
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="block text-gray-700 text-sm font-semibold" for="password">Password</label>
                    <a href="forgot_password.php" class="text-xs font-medium text-purple-600 hover:text-purple-500">Forgot Password?</a>
                </div>
                <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent transition bg-gray-50 hover:bg-white" id="password" name="password" type="password" placeholder="••••••••" required>
            </div>
            <div>
                <button class="w-full bg-black hover:bg-gray-800 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-600 transition-all shadow-lg hover:shadow-purple-500/25" type="submit">
                    Sign In
                </button>
            </div>
        </form>
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-600">
                Need an account? 
                <a href="register.php" class="font-bold text-purple-600 hover:text-purple-500 transition-colors">Create one now</a>
            </p>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <a href="faq.php" class="text-gray-400 hover:text-gray-600 text-xs font-medium transition-colors">Browse Knowledge Base</a>
            </div>
        </div>
    </div>
</body>
</html>