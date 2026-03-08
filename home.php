<?php
require 'db.php';
require 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user_id'];

// If Admin tries to access the user home, redirect them to admin dashboard
if (isAdmin()) {
    header("Location: admin_dashboard.php");
    exit;
}

// Helper to fetch count
function getCount($pdo, $status = null, $userId = null, $priority = null) {
    $sql = "SELECT COUNT(*) FROM tickets WHERE 1=1";
    $params = [];

    if ($userId) {
        $sql .= " AND user_id = ?";
        $params[] = $userId;
    }
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    if ($priority) {
        $sql .= " AND priority = ?";
        $params[] = $priority;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

// Fetch Stats
$totalTickets = getCount($pdo, null, $userId);
$openTickets = getCount($pdo, 'Open', $userId);
$inProgressTickets = getCount($pdo, 'In Progress', $userId);
$resolvedTickets = getCount($pdo, 'Resolved', $userId);

$highPriority = getCount($pdo, null, $userId, 'High');
$mediumPriority = getCount($pdo, null, $userId, 'Medium');
$lowPriority = getCount($pdo, null, $userId, 'Low');

// Fetch Recent Tickets (Last 3)
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->execute([$userId]);
$recentTickets = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Overview</title>
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
                    <a href="home.php" class="text-purple-400 font-semibold border-b-2 border-purple-500 px-1 pt-1 text-sm">Overview</a>
                    <a href="dashboard.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">My Tickets</a>
                    <a href="profile.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">Profile</a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <span class="text-gray-400 hidden md:block text-sm">Welcome, <span class="text-white font-medium"><?php echo htmlspecialchars($_SESSION['name']); ?></span></span>
                    <a href="logout.php" class="text-sm bg-red-500/10 text-red-500 hover:bg-red-500/20 border border-red-500/20 py-2 px-3 rounded transition font-medium">Logout</a>
                </div>
            </div>
            
            <!-- Mobile Menu Tabs -->
            <div class="flex md:hidden border-t border-gray-800 -mx-4 px-4 bg-gray-900 justify-around">
                 <a href="home.php" class="flex-1 text-center py-3 text-sm font-semibold text-purple-400 border-b-2 border-purple-500">Overview</a>
                 <a href="dashboard.php" class="flex-1 text-center py-3 text-sm font-medium text-gray-400 border-b-2 border-transparent hover:text-gray-200">Tickets</a>
                 <a href="profile.php" class="flex-1 text-center py-3 text-sm font-medium text-gray-400 border-b-2 border-transparent hover:text-gray-200">Profile</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col md:flex-row justify-between items-end md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Dashboard Overview</h1>
                <p class="text-slate-500 text-sm mt-1">Here is the current status of your support requests.</p>
            </div>
            <a href="create_ticket.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-lg shadow-purple-500/30 transition border border-purple-500 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Ticket
            </a>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mb-8">
            
            <!-- Total -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm relative group transition hover:shadow-md hover:-translate-y-1 duration-200">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-sm font-medium text-slate-500">Total Tickets</div>
                        <div class="mt-2 text-3xl font-bold text-slate-800"><?php echo $totalTickets; ?></div>
                    </div>
                    <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Open -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm relative group transition hover:shadow-md hover:-translate-y-1 duration-200">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-sm font-medium text-slate-500">Open</div>
                        <div class="mt-2 text-3xl font-bold text-slate-800"><?php echo $openTickets; ?></div>
                    </div>
                    <div class="p-2 bg-red-50 rounded-lg text-red-600">
                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- In Progress -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm relative group transition hover:shadow-md hover:-translate-y-1 duration-200">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-sm font-medium text-slate-500">In Progress</div>
                        <div class="mt-2 text-3xl font-bold text-slate-800"><?php echo $inProgressTickets; ?></div>
                    </div>
                    <div class="p-2 bg-amber-50 rounded-lg text-amber-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Resolved -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm relative group transition hover:shadow-md hover:-translate-y-1 duration-200">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-sm font-medium text-slate-500">Resolved</div>
                        <div class="mt-2 text-3xl font-bold text-slate-800"><?php echo $resolvedTickets; ?></div>
                    </div>
                    <div class="p-2 bg-green-50 rounded-lg text-green-600">
                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Recent Activity (Spans 2 columns) -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-bold text-slate-800">Recent Activity</h3>
                    <a href="dashboard.php" class="text-sm text-purple-600 hover:text-purple-800 font-medium hover:underline">View All</a>
                </div>
                
                <?php if(count($recentTickets) > 0): ?>
                    <div class="divide-y divide-slate-100">
                        <?php foreach($recentTickets as $t): ?>
                        <a href="view_ticket.php?id=<?php echo $t['id']; ?>" class="block p-4 hover:bg-slate-50 transition group">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-xs font-bold text-slate-400 group-hover:text-purple-500 transition">#<?php echo $t['id']; ?></span>
                                    <h4 class="text-sm font-semibold text-slate-800 mt-0.5 group-hover:text-purple-700 transition"><?php echo htmlspecialchars($t['title']); ?></h4>
                                    <span class="text-xs text-slate-500 mt-1 block"><?php echo date('M d, Y', strtotime($t['created_at'])); ?></span>
                                </div>
                                <div>
                                    <?php 
                                        $badges = [
                                            'Open' => 'bg-red-100 text-red-700 border-red-200',
                                            'In Progress' => 'bg-amber-100 text-amber-700 border-amber-200',
                                            'Resolved' => 'bg-green-100 text-green-700 border-green-200'
                                        ];
                                        $badgeColor = $badges[$t['status']] ?? 'bg-gray-100 text-gray-700';
                                    ?>
                                    <span class="px-2.5 py-1 text-xs rounded-full font-bold border <?php echo $badgeColor; ?>">
                                        <?php echo $t['status']; ?>
                                    </span>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-8 text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 mb-4 text-slate-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        </div>
                        <h3 class="text-slate-900 font-medium">No tickets yet</h3>
                        <p class="text-slate-500 text-sm mt-1">Create your first support ticket to get started.</p>
                        <a href="create_ticket.php" class="mt-4 inline-block text-purple-600 hover:text-purple-800 font-medium text-sm">Create Ticket &rarr;</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column (Priority & Help) -->
            <div class="space-y-6">
                <!-- Priority Breakdown -->
                <div class="bg-white shadow-sm rounded-xl p-6 border border-slate-200">
                    <h3 class="text-md font-bold text-slate-800 mb-4">Ticket Priority</h3>
                    <div class="space-y-5">
                        <div>
                            <div class="flex justify-between text-xs font-semibold uppercase tracking-wide mb-1.5">
                                <span class="text-red-600">High</span>
                                <span class="text-slate-500"><?php echo $highPriority; ?></span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="bg-red-500 h-2 rounded-full" style="width: <?php echo $totalTickets > 0 ? ($highPriority / $totalTickets) * 100 : 0; ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs font-semibold uppercase tracking-wide mb-1.5">
                                <span class="text-amber-600">Medium</span>
                                <span class="text-slate-500"><?php echo $mediumPriority; ?></span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="bg-amber-400 h-2 rounded-full" style="width: <?php echo $totalTickets > 0 ? ($mediumPriority / $totalTickets) * 100 : 0; ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs font-semibold uppercase tracking-wide mb-1.5">
                                <span class="text-blue-600">Low</span>
                                <span class="text-slate-500"><?php echo $lowPriority; ?></span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: <?php echo $totalTickets > 0 ? ($lowPriority / $totalTickets) * 100 : 0; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="bg-gray-900 shadow-xl shadow-purple-900/20 rounded-xl p-6 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-purple-600 rounded-full filter blur-3xl opacity-20 -mr-16 -mt-16"></div>
                    <div class="absolute bottom-0 left-0 w-32 h-32 bg-indigo-600 rounded-full filter blur-3xl opacity-20 -ml-16 -mb-16"></div>
                    
                    <h3 class="font-bold text-lg mb-2 relative z-10">Need Assistance?</h3>
                    <p class="text-slate-300 text-sm mb-4 relative z-10 leading-relaxed">
                        Our support team is available Mon-Fri, 9am - 5pm. Check the FAQ or open a ticket.
                    </p>
                    <a href="faq.php" class="relative z-10 inline-flex items-center text-sm font-bold text-purple-300 hover:text-white transition">
                        View FAQs &rarr;
                    </a>
                </div>
            </div>

        </div>
    </div>
</body>
</html>