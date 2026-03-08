<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $name = trim($_POST['name']);
    $domain = trim($_POST['domain']);
    
    // Basic validation
    if (empty($email) || empty($password) || empty($name)) {
        $error = "All fields are required.";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered.";
        } else {
            // Hash password and insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $pdo->prepare("INSERT INTO users (email, password, name, role, domain) VALUES (?, ?, ?, 'user', ?)");
                $stmt->execute([$email, $hashed_password, $name, $domain]);
                
                // Send Welcome Email
                require_once 'functions.php';
                sendNotificationEmail($email, "Welcome to Prism Ticket", "Hello $name,\n\nWelcome to Prism Ticket Support Center! You can now login to create and view support tickets.\n\nLogin here: http://{$_SERVER['HTTP_HOST']}/prism_ticket/");

                // Redirect to login
                header("Location: index.php?registered=1");
                exit;
            } catch (PDOException $e) {
                $error = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link rel="icon" href="images/prism-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen flex items-center justify-center font-sans px-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow-2xl p-8 transform transition hover:scale-[1.01] duration-300 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-purple-500 to-indigo-500"></div>
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Join the Network</h1>
            <p class="text-gray-500 mt-2 text-sm">Create your account to get support</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r text-sm" role="alert">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-1" for="name">Full Name</label>
                <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent transition bg-gray-50 hover:bg-white" id="name" name="name" type="text" placeholder="John Doe" required>
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-1" for="email">Email Address</label>
                <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent transition bg-gray-50 hover:bg-white" id="email" name="email" type="email" placeholder="you@company.com" required>
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-1" for="domain">Company Domain / Website</label>
                <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent transition bg-gray-50 hover:bg-white" id="domain" name="domain" type="text" placeholder="example.com">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-1" for="password">Password</label>
                <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent transition bg-gray-50 hover:bg-white" id="password" name="password" type="password" placeholder="******************" required>
            </div>
            <div class="pt-2">
                <button class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all shadow-lg shadow-purple-500/30" type="submit">
                    Create Account
                </button>
            </div>
            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    Already have an account? 
                    <a href="index.php" class="font-bold text-black hover:text-purple-600 transition-colors">Login</a>
                </p>
            </div>
        </form>
    </div>
</body>
</html>
