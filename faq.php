<?php
require 'db.php';
require 'functions.php';

$stmt = $pdo->query("SELECT * FROM faqs ORDER BY created_at DESC");
$faqs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Help Center</title>
    <link rel="icon" href="images/prism-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function toggleFaq(id) {
            const element = document.getElementById('answer-' + id);
            const icon = document.getElementById('icon-' + id);
            if (element.classList.contains('hidden')) {
                element.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                element.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-black shadow-lg sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="bg-white/10 p-1.5 rounded-lg mr-3">
                        <img src="https://tickets.prismtechnologies.com.ng/images/prism-logo.png" alt="Logo" class="h-6 w-auto">
                    </div>
                    <span class="text-lg sm:text-xl font-bold text-white tracking-wide">DevSupport</span>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if(isAdmin()): ?>
                             <a href="admin_dashboard.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">Dashboard</a>
                             <a href="dashboard.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">All Tickets</a>
                        <?php else: ?>
                             <a href="home.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">Overview</a>
                             <a href="dashboard.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">My Tickets</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="index.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">Login</a>
                        <a href="register.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">Register</a>
                    <?php endif; ?>
                    <a href="faq.php" class="text-purple-400 font-semibold border-b-2 border-purple-500 px-1 pt-1 text-sm">FAQ</a>
                </div>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                <div class="flex items-center space-x-4">
                    <a href="profile.php" class="text-sm text-gray-400 hover:text-purple-400 font-medium hidden md:block transition">Profile</a>
                    <a href="logout.php" class="text-sm bg-red-500/10 text-red-500 hover:bg-red-500/20 border border-red-500/20 py-2 px-3 rounded transition font-medium">Logout</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center mb-12">
            <h1 class="text-3xl font-extrabold text-gray-800 sm:text-4xl tracking-tight">Frequently Asked Questions</h1>
            <p class="mt-4 text-lg text-gray-500">Quick answers to common questions about our services.</p>
        </div>

        <div class="space-y-4">
            <?php foreach ($faqs as $f): ?>
            <div class="bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md transition duration-200">
                <button onclick="toggleFaq(<?php echo $f['id']; ?>)" class="w-full px-6 py-4 text-left flex justify-between items-center focus:outline-none group">
                    <span class="font-bold text-gray-800 group-hover:text-purple-700 transition"><?php echo htmlspecialchars($f['question']); ?></span>
                    <svg id="icon-<?php echo $f['id']; ?>" class="w-5 h-5 text-gray-400 group-hover:text-purple-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div id="answer-<?php echo $f['id']; ?>" class="hidden px-6 pb-6 text-gray-600 border-t border-gray-50">
                    <div class="pt-4 leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($f['answer'])); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if(count($faqs) == 0): ?>
                <div class="text-center text-gray-500 py-8 italic">No FAQs available yet.</div>
            <?php endif; ?>
        </div>
        
        <div class="mt-12 text-center">
             <p class="text-gray-600">Still have questions?</p>
             <a href="create_ticket.php" class="mt-2 inline-flex items-center text-purple-600 hover:text-purple-800 font-bold hover:underline transition">
                 Submit a Ticket 
                 <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
             </a>
        </div>

    </div>
</body>
</html>
