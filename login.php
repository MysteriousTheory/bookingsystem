<?php
session_start();
require 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Username and password are required.";
    } else {
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FlightBookingSystem</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Login to FlightBookingSystem</h1>
        <p>Access your dashboard to book and manage flights.</p>
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] === 'registered'): ?>
            <p style="color: #28a745; font-weight: bold;">Registration successful! Please login.</p>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <p style="color: #dc3545;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Login</button>
            <div style="text-align: center; margin-top: 15px;">
                <p>Don't have an account? <a href="register.php" style="color:var(--primary); font-weight:bold;">Register here</a></p>
                <a href="index.php" class="back-link">&larr; Back to Home</a>
            </div>
        </form>
    </div>
</body>
</html>
