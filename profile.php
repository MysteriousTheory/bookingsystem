<?php
require 'db.php';
require 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch User Data
$stmt = $pdo->prepare("SELECT name, email, domain, password, role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- Update General Info ---
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $domain = trim($_POST['domain']);

        if (empty($name)) {
            $error = "Name cannot be empty.";
        } else {
            // Update DB
            $stmt = $pdo->prepare("UPDATE users SET name = ?, domain = ? WHERE id = ?");
            if ($stmt->execute([$name, $domain, $userId])) {
                $_SESSION['name'] = $name; // Update session
                $user['name'] = $name;     // Refresh local variable
                $user['domain'] = $domain;
                $success = "Profile updated successfully.";
            } else {
                $error = "Failed to update profile.";
            }
        }
    }

    // --- Change Password ---
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if (empty($currentPassword) || empty($newPassword)) {
            $error = "All password fields are required.";
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $error = "Current password is incorrect.";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "New passwords do not match.";
        } elseif (strlen($newPassword) < 6) {
            $error = "Password must be at least 6 characters long.";
        } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$newHash, $userId])) {
                $success = "Password changed successfully.";
            } else {
                $error = "Failed to change password.";
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
    <title>My Profile</title>
    <link rel="icon" href="images/prism-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #f8fafc; font-family: sans-serif; }
    </style>
</head>
<body class="text-slate-800">

    <!-- Navbar -->
    <nav class="bg-black shadow-lg sticky top-0 z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="bg-white/10 p-1.5 rounded-lg mr-3">
                        <img src="https://tickets.prismtechnologies.com.ng/images/prism-logo.png" alt="Logo" class="h-6 w-auto">
                    </div>
                    <span class="text-lg sm:text-xl font-bold text-white tracking-wide">DevSupport</span>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <?php if (isAdmin()): ?>
                        <a href="admin_dashboard.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">Dashboard</a>
                        <a href="dashboard.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">All Tickets</a>
                    <?php else: ?>
                        <a href="home.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">Overview</a>
                        <a href="dashboard.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">My Tickets</a>
                    <?php endif; ?>
                    <a href="profile.php" class="text-purple-400 font-semibold border-b-2 border-purple-500 px-1 pt-1 text-sm">Profile</a>
                </div>

                <div class="flex items-center space-x-4">
                    <span class="text-gray-400 hidden md:block text-sm">Hello, <span class="text-white font-medium"><?php echo htmlspecialchars($_SESSION['name']); ?></span></span>
                    <a href="logout.php" class="text-sm bg-red-500/10 text-red-500 hover:bg-red-500/20 border border-red-500/20 py-2 px-3 rounded transition font-medium">Logout</a>
                </div>
            </div>
            
             <!-- Mobile Menu Tabs -->
             <div class="flex md:hidden border-t border-gray-800 -mx-4 px-4 bg-gray-900 justify-around">
                 <a href="home.php" class="flex-1 text-center py-3 text-sm font-medium text-gray-400 border-b-2 border-transparent hover:text-gray-200">Overview</a>
                 <a href="dashboard.php" class="flex-1 text-center py-3 text-sm font-medium text-gray-400 border-b-2 border-transparent hover:text-gray-200">Tickets</a>
                 <a href="profile.php" class="flex-1 text-center py-3 text-sm font-semibold text-purple-400 border-b-2 border-purple-500">Profile</a>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <h1 class="text-2xl font-bold text-slate-900 mb-6">Account Settings</h1>

        <!-- Messages -->
        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Profile Header Card -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-8 flex items-center gap-6">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name']); ?>&background=random&color=fff&size=128" 
                 alt="Profile" 
                 class="w-20 h-20 rounded-full shadow-md border-2 border-white">
            
            <div>
                <h2 class="text-xl font-bold text-slate-800"><?php echo htmlspecialchars($user['name']); ?></h2>
                <div class="flex items-center gap-3 mt-1 text-sm">
                    <span class="text-slate-500"><?php echo htmlspecialchars($user['email']); ?></span>
                    <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-xs font-bold border border-slate-200 uppercase tracking-wide">
                        <?php echo htmlspecialchars($user['role'] ?? 'User'); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Update Profile Form -->
            <div class="bg-white shadow-sm border border-slate-200 rounded-xl p-6 h-fit">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800">General Information</h3>
                    <span class="text-xs text-slate-400 bg-slate-50 px-2 py-1 rounded">Public Info</span>
                </div>
                
                <form method="POST">
                    <div class="mb-5">
                        <label class="block text-slate-700 text-sm font-semibold mb-2">Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" 
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition text-sm">
                    </div>
                    <div class="mb-5">
                        <label class="block text-slate-700 text-sm font-semibold mb-2">Email Address</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                               class="w-full px-4 py-2 border border-slate-200 bg-slate-50 text-slate-500 rounded-lg cursor-not-allowed text-sm" disabled>
                    </div>
                    <div class="mb-6">
                        <label class="block text-slate-700 text-sm font-semibold mb-2">Company Domain</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-slate-400 text-sm">https://</span>
                            </div>
                            <input type="text" name="domain" value="<?php echo htmlspecialchars($user['domain'] ?? ''); ?>" 
                                   class="w-full pl-16 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition text-sm" placeholder="example.com">
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold px-4 py-2.5 rounded-lg transition shadow-sm hover:shadow-purple-500/20">
                        Save Changes
                    </button>
                </form>
            </div>

            <!-- Change Password Form -->
            <div class="bg-white shadow-sm border border-slate-200 rounded-xl p-6 h-fit">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800">Security</h3>
                    <span class="text-xs text-slate-400 bg-slate-50 px-2 py-1 rounded">Private</span>
                </div>

                <form method="POST">
                    <div class="mb-5">
                        <label class="block text-slate-700 text-sm font-semibold mb-2">Current Password</label>
                        <input type="password" name="current_password" required 
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition text-sm">
                    </div>
                    <div class="mb-5">
                        <label class="block text-slate-700 text-sm font-semibold mb-2">New Password</label>
                        <input type="password" name="new_password" required placeholder="Min 6 characters" 
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition text-sm">
                    </div>
                    <div class="mb-6">
                        <label class="block text-slate-700 text-sm font-semibold mb-2">Confirm New Password</label>
                        <input type="password" name="confirm_password" required 
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition text-sm">
                    </div>
                    <button type="submit" name="change_password" class="w-full bg-white border border-slate-300 text-slate-700 font-bold px-4 py-2.5 rounded-lg hover:bg-slate-50 hover:text-purple-600 hover:border-purple-300 transition">
                        Update Password
                    </button>
                </form>
            </div>

        </div>
    </div>
</body>
</html>