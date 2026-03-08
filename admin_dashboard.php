<?php
require 'db.php';
require 'functions.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || !isAdmin()) {
    header("Location: index.php");
    exit;
}

// 2. Helper Functions for Data
function getCount($pdo, $sql, $params = []) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

// 3. Fetch Key Metrics
$totalTickets = getCount($pdo, "SELECT COUNT(*) FROM tickets");
$openTickets = getCount($pdo, "SELECT COUNT(*) FROM tickets WHERE status = 'Open'");
$progressTickets = getCount($pdo, "SELECT COUNT(*) FROM tickets WHERE status = 'In Progress'");
$resolvedTickets = getCount($pdo, "SELECT COUNT(*) FROM tickets WHERE status = 'Resolved'");

// 4. Fetch Recent Activity (Limit 5)
$stmt = $pdo->query("
    SELECT t.*, u.name as user_name, a.name as assignee_name 
    FROM tickets t 
    JOIN users u ON t.user_id = u.id 
    LEFT JOIN users a ON t.assigned_to = a.id
    ORDER BY t.created_at DESC LIMIT 5
");
$recentTickets = $stmt->fetchAll();

// 5. Fetch Agent Workload
// Counts how many non-resolved tickets each admin has
$stmt = $pdo->query("
    SELECT u.id, u.name, COUNT(t.id) as ticket_count 
    FROM users u 
    LEFT JOIN tickets t ON u.id = t.assigned_to AND t.status != 'Resolved'
    WHERE u.role = 'admin' 
    GROUP BY u.id 
    ORDER BY ticket_count DESC 
    LIMIT 4
");
$agentStats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="icon" href="images/prism-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js for Graphs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
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
                    <!-- Admin Links -->
                    <a href="admin_dashboard.php" class="text-purple-400 font-semibold border-b-2 border-purple-500 px-1 pt-1 text-sm">Dashboard</a>
                    <a href="dashboard.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">All Tickets</a>
                    <a href="settings_canned.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">Saved Replies</a>
                    <a href="settings_faq.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">Manage FAQs</a>
                    <a href="profile.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">Profile</a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <span class="text-gray-400 hidden md:block text-sm">Hello, <span class="text-white font-medium"><?php echo htmlspecialchars($_SESSION['name']); ?></span></span>
                    <a href="logout.php" class="text-sm bg-red-500/10 text-red-500 hover:bg-red-500/20 border border-red-500/20 py-2 px-3 rounded transition font-medium">Logout</a>
                </div>
            </div>
             <!-- Mobile Menu Tabs -->
             <div class="flex md:hidden border-t border-gray-800 -mx-4 px-4 bg-gray-900 justify-around">
                 <a href="admin_dashboard.php" class="flex-1 text-center py-3 text-sm font-semibold text-purple-400 border-b-2 border-purple-500">Dashboard</a>
                 <a href="dashboard.php" class="flex-1 text-center py-3 text-sm font-medium text-gray-400 border-b-2 border-transparent hover:text-gray-200">Tickets</a>
                 <a href="profile.php" class="flex-1 text-center py-3 text-sm font-medium text-gray-400 border-b-2 border-transparent hover:text-gray-200">Profile</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <h1 class="text-2xl font-bold text-slate-900 mb-6">Admin Dashboard Overview</h1>

        <!-- 1. STATS CARDS ROW -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            
            <!-- Total Tickets -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Tickets</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-2"><?php echo number_format($totalTickets); ?></h3>
                        <p class="text-xs font-medium text-green-600 mt-2 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"/></svg>
                            5% from last week
                        </p>
                    </div>
                    <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Open Tickets -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Open Tickets</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-2"><?php echo number_format($openTickets); ?></h3>
                        <p class="text-xs font-medium text-red-500 mt-2 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z"/></svg>
                            2% from last week
                        </p>
                    </div>
                    <div class="p-2 bg-red-50 rounded-lg text-red-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- In Progress -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-slate-500">In Progress</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-2"><?php echo number_format($progressTickets); ?></h3>
                        <div class="h-4"></div> <!-- Spacer to match height -->
                    </div>
                    <div class="p-2 bg-yellow-50 rounded-lg text-yellow-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Resolved -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Resolved Tickets</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-2"><?php echo number_format($resolvedTickets); ?></h3>
                        <div class="h-4"></div> <!-- Spacer -->
                    </div>
                    <div class="p-2 bg-green-50 rounded-lg text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. CHARTS & METRICS ROW -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            
            <!-- Line Chart (Ticket Volume) - Spans 2 Columns -->
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-slate-800">Ticket Volume Over Time</h3>
                    <select class="text-sm border-slate-300 border rounded-md text-slate-500 px-2 py-1 outline-none">
                        <option>Last 30 days</option>
                        <option>Last 7 days</option>
                    </select>
                </div>
                <div class="relative h-64 w-full">
                    <canvas id="volumeChart"></canvas>
                </div>
            </div>

            <!-- Status Pie Chart & Agent List - Spans 1 Column -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex flex-col gap-8">
                
                <!-- Pie Chart -->
                <div>
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Tickets by Status</h3>
                    <div class="h-40 flex justify-center">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                <!-- Agent Workload (UPDATED) -->
                <div>
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Agent Workload Overview</h3>
                    <div class="space-y-6">
                        <?php foreach($agentStats as $agent): ?>
                        <div class="flex items-center gap-4">
                            <!-- Avatar (using name to generate) -->
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($agent['name']); ?>&background=random&color=fff&size=64" 
                                 alt="<?php echo htmlspecialchars($agent['name']); ?>" 
                                 class="w-10 h-10 rounded-full object-cover shadow-sm">
                            
                            <!-- Content -->
                            <div class="flex-1">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($agent['name']); ?></span>
                                    <span class="text-xs text-slate-500 font-medium"><?php echo $agent['ticket_count']; ?> tickets</span>
                                </div>
                                
                                <?php 
                                    // Progress Calculation (Max 30 for demo scale)
                                    $percent = min(100, ($agent['ticket_count'] / 30) * 100); 
                                ?>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-500 ease-out" style="width: <?php echo $percent; ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php if(count($agentStats) == 0): ?>
                            <div class="text-center py-4 text-sm text-slate-400">
                                No agents currently assigned.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. TABLES & ACTIONS ROW -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Recently Updated Table - Spans 2 Columns -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800">Recently Updated Tickets</h3>
                    <a href="dashboard.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">ID #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Last Updated</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Assignee</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <?php foreach($recentTickets as $t): ?>
                            <tr class="hover:bg-slate-50 transition cursor-pointer" onclick="window.location='view_ticket.php?id=<?php echo $t['id']; ?>'">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">T-<?php echo $t['id']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700"><?php echo htmlspecialchars(substr($t['title'], 0, 30)) . (strlen($t['title'])>30?'...':''); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                        $badges = [
                                            'Open' => 'bg-red-100 text-red-700',
                                            'In Progress' => 'bg-orange-100 text-orange-700',
                                            'Resolved' => 'bg-green-100 text-green-700'
                                        ];
                                        $badgeColor = $badges[$t['status']] ?? 'bg-gray-100 text-gray-700';
                                    ?>
                                    <span class="px-2 py-1 text-xs rounded-md font-medium <?php echo $badgeColor; ?>">
                                        <?php echo $t['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    <?php 
                                        $time = strtotime($t['created_at']);
                                        echo date('M d, H:i', $time); 
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    <?php echo htmlspecialchars($t['assignee_name'] ?? 'Unassigned'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 h-fit">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="create_ticket.php" class="flex items-center w-full py-2.5 px-4 border border-slate-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition group">
                        <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Create New Ticket
                    </a>
                    <a href="#" class="flex items-center w-full py-2.5 px-4 border border-slate-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition group">
                        <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Manage Agents
                    </a>
                    <a href="#" class="flex items-center w-full py-2.5 px-4 border border-slate-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition group">
                         <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Generate Report
                    </a>
                    <a href="#" class="flex items-center w-full py-2.5 px-4 border border-slate-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition group">
                        <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        View Knowledge Base
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- Chart Configuration Script -->
    <script>
        // 1. Line Chart (Ticket Volume)
        const ctxVolume = document.getElementById('volumeChart').getContext('2d');
        new Chart(ctxVolume, {
            type: 'line',
            data: {
                labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Today'],
                datasets: [{
                    label: 'Tickets Created',
                    data: [12, 19, 13, 15, 22, 24, 18], // Dummy data
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // 2. Pie Chart (Status)
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Open', 'In Progress', 'Resolved'],
                datasets: [{
                    data: [<?php echo $openTickets; ?>, <?php echo $progressTickets; ?>, <?php echo $resolvedTickets; ?>],
                    backgroundColor: ['#ef4444', '#f59e0b', '#10b981'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        position: 'right',
                        labels: { boxWidth: 12, usePointStyle: true, font: { size: 11 } }
                    } 
                },
                cutout: '75%'
            }
        });
    </script>
</body>
</html>