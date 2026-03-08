<?php
require 'db.php';
require 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// --- 1. Filter Inputs ---
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$assigned_filter = $_GET['assigned'] ?? ''; // 'me' or empty
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// --- 2. Build Base Query Conditions ---
$whereClause = " WHERE 1=1";
$params = [];

// Admin Restriction OR Assigned Filter
if (!isAdmin()) {
    $whereClause .= " AND t.user_id = ?";
    $params[] = $_SESSION['user_id'];
} elseif ($assigned_filter === 'me') {
    // Admin filtering by "Assigned to Me"
    $whereClause .= " AND t.assigned_to = ?";
    $params[] = $_SESSION['user_id'];
}

// Search Filter
if ($search) {
    $whereClause .= " AND (t.title LIKE ? OR t.id = ?)";
    $params[] = "%$search%";
    $params[] = $search;
}

// Status Filter
if ($status) {
    $whereClause .= " AND t.status = ?";
    $params[] = $status;
}

// Category Filter
if ($category) {
    $whereClause .= " AND t.category = ?";
    $params[] = $category;
}

// --- 3. Get Total Count (For Pagination) ---
// We need a separate set of params for the count query to avoid binding limit/offset
$countParams = $params; 

$countSql = "SELECT COUNT(*) FROM tickets t JOIN users u ON t.user_id = u.id $whereClause";
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($countParams);
$total_records = $stmtCount->fetchColumn();
$total_pages = ceil($total_records / $limit);

// --- 4. Get Tickets (Main Query) ---
$sql = "SELECT t.*, u.name as user_name, u.domain as user_domain, a.name as assigned_name 
        FROM tickets t 
        JOIN users u ON t.user_id = u.id 
        LEFT JOIN users a ON t.assigned_to = a.id
        $whereClause 
        ORDER BY t.created_at DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Tickets</title>
    <link rel="icon" href="images/prism-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { background-color: #f8fafc; font-family: sans-serif; }</style>
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
                        <a href="dashboard.php" class="text-purple-400 font-semibold border-b-2 border-purple-500 px-1 pt-1 text-sm">All Tickets</a>
                        <a href="settings_canned.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">Saved Replies</a>
                        <a href="settings_faq.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">Manage FAQs</a>
                    <?php else: ?>
                        <a href="home.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">Overview</a>
                        <a href="dashboard.php" class="text-purple-400 font-semibold border-b-2 border-purple-500 px-1 pt-1 text-sm">My Tickets</a>
                    <?php endif; ?>
                    <a href="profile.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">Profile</a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <span class="text-gray-400 hidden md:block text-sm">Hello, <span class="text-white font-medium"><?php echo htmlspecialchars($_SESSION['name']); ?></span></span>
                    <a href="logout.php" class="text-sm bg-red-500/10 text-red-500 hover:bg-red-500/20 border border-red-500/20 py-2 px-3 rounded transition font-medium">Logout</a>
                </div>
            </div>
             <!-- Mobile Menu Tabs -->
             <div class="flex md:hidden border-t border-gray-800 -mx-4 px-4 bg-gray-900 justify-around">
                 <a href="home.php" class="flex-1 text-center py-3 text-sm font-medium text-gray-400 border-b-2 border-transparent hover:text-gray-200">Home</a>
                 <a href="dashboard.php" class="flex-1 text-center py-3 text-sm font-semibold text-purple-400 border-b-2 border-purple-500">Tickets</a>
                 <a href="profile.php" class="flex-1 text-center py-3 text-sm font-medium text-gray-400 border-b-2 border-transparent hover:text-gray-200">Profile</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-end md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900"><?php echo isAdmin() ? 'All Support Tickets' : 'My Support Tickets'; ?></h1>
                <p class="text-slate-500 text-sm mt-1">Manage and track your support requests.</p>
            </div>
            <?php if(!isAdmin()): ?>
            <a href="create_ticket.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-lg hover:shadow-purple-500/30 transition flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Ticket
            </a>
            <?php endif; ?>
        </div>

        <!-- Filters -->
        <div class="bg-white p-5 rounded-xl shadow-sm mb-8 border border-slate-200">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                
                <!-- Search -->
                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="ID or Subject..." class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>
                </div>
                
                <!-- Status -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Status</label>
                    <select name="status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">All</option>
                        <option value="Open" <?php echo $status == 'Open' ? 'selected' : ''; ?>>Open</option>
                        <option value="In Progress" <?php echo $status == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Resolved" <?php echo $status == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                    </select>
                </div>

                <!-- Category -->
                <div class="md:col-span-3">
                     <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Category</label>
                     <select name="category" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">All</option>
                        <option value="General" <?php echo $category == 'General' ? 'selected' : ''; ?>>General</option>
                        <option value="Technical Support" <?php echo $category == 'Technical Support' ? 'selected' : ''; ?>>Technical Support</option>
                        <option value="Billing" <?php echo $category == 'Billing' ? 'selected' : ''; ?>>Billing</option>
                        <option value="Feature Request" <?php echo $category == 'Feature Request' ? 'selected' : ''; ?>>Feature Request</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="md:col-span-3 flex gap-2">
                    <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white font-medium py-2 px-4 rounded-lg text-sm transition shadow-sm">
                        Filter
                    </button>
                    <?php if($search || $status || $category || $assigned_filter): ?>
                    <a href="dashboard.php" class="flex-none bg-white text-slate-600 hover:bg-slate-50 border border-slate-300 font-medium py-2 px-4 rounded-lg text-sm transition">
                        Clear
                    </a>
                    <?php endif; ?>
                </div>
            </form>
            
            <?php if(isAdmin()): ?>
            <div class="mt-4 pt-4 border-t border-slate-100 flex justify-end">
                 <a href="?assigned=me" class="<?php echo $assigned_filter === 'me' ? 'bg-purple-50 text-purple-700 border-purple-200 ring-1 ring-purple-200' : 'text-slate-500 hover:text-slate-800'; ?> text-xs font-medium px-3 py-1.5 rounded-md transition flex items-center">
                    <span class="w-2 h-2 rounded-full <?php echo $assigned_filter === 'me' ? 'bg-purple-500' : 'bg-slate-300'; ?> mr-2"></span>
                    Show Assigned to Me
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Ticket List -->
        <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-slate-200">
            <?php if (count($tickets) > 0): ?>
                
                <!-- DESKTOP TABLE -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">ID</th>
                                <?php if(isAdmin()): ?>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Assigned</th>
                                <?php endif; ?>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-1/3">Subject</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Updated</th>
                                <th class="px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <?php foreach ($tickets as $ticket): ?>
                            <tr class="hover:bg-slate-50 transition cursor-pointer group" onclick="window.location='view_ticket.php?id=<?php echo $ticket['id']; ?>'">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-500">#<?php echo $ticket['id']; ?></td>
                                
                                <?php if(isAdmin()): ?>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($ticket['user_name'] ?? 'Unknown'); ?></div>
                                    <div class="text-xs text-slate-400"><?php echo htmlspecialchars($ticket['user_domain'] ?? ''); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if(!empty($ticket['assigned_name'])): ?>
                                        <div class="flex items-center">
                                            <div class="h-6 w-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-[10px] font-bold mr-2">
                                                <?php echo strtoupper(substr($ticket['assigned_name'], 0, 1)); ?>
                                            </div>
                                            <span class="text-sm text-slate-600"><?php echo htmlspecialchars($ticket['assigned_name']); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400 border border-slate-200 px-2 py-1 rounded">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>

                                <td class="px-6 py-4 text-sm font-medium text-slate-900 truncate max-w-xs group-hover:text-purple-600 transition">
                                    <?php echo htmlspecialchars($ticket['title']); ?>
                                    <div class="text-xs text-slate-400 font-normal mt-0.5"><?php echo htmlspecialchars($ticket['category'] ?? 'General'); ?></div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                        $statusClass = match($ticket['status']) {
                                            'Open' => 'bg-red-50 text-red-700 border border-red-100',
                                            'In Progress' => 'bg-amber-50 text-amber-700 border border-amber-100',
                                            'Resolved' => 'bg-green-50 text-green-700 border border-green-100',
                                            default => 'bg-gray-50'
                                        };
                                    ?>
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full <?php echo $statusClass; ?>">
                                        <?php echo $ticket['status']; ?>
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                     <?php 
                                        $pClass = match($ticket['priority']) {
                                            'High' => 'text-red-600 bg-red-50',
                                            'Medium' => 'text-amber-600 bg-amber-50',
                                            'Low' => 'text-blue-600 bg-blue-50',
                                        };
                                    ?>
                                    <span class="px-2 py-1 text-xs font-medium rounded <?php echo $pClass; ?>">
                                        <?php echo $ticket['priority']; ?>
                                    </span>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    <?php echo date('M d, H:i', strtotime($ticket['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <svg class="w-5 h-5 text-slate-300 group-hover:text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- MOBILE LIST -->
                <div class="block sm:hidden divide-y divide-slate-100">
                    <?php foreach ($tickets as $ticket): ?>
                    <a href="view_ticket.php?id=<?php echo $ticket['id']; ?>" class="block p-4 bg-white hover:bg-slate-50 transition">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="text-xs font-bold text-slate-400">#<?php echo $ticket['id']; ?></span>
                                <h4 class="text-sm font-bold text-slate-800 mt-1 line-clamp-2"><?php echo htmlspecialchars($ticket['title']); ?></h4>
                            </div>
                            <?php 
                                $statusClass = match($ticket['status']) {
                                    'Open' => 'bg-red-100 text-red-700',
                                    'In Progress' => 'bg-amber-100 text-amber-700',
                                    'Resolved' => 'bg-green-100 text-green-700',
                                };
                            ?>
                            <span class="px-2 py-1 text-[10px] font-bold uppercase rounded ml-2 whitespace-nowrap <?php echo $statusClass; ?>">
                                <?php echo $ticket['status']; ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-end mt-3">
                            <div class="text-xs text-slate-500">
                                <div><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></div>
                                <?php if(isAdmin()): ?>
                                    <div class="mt-1 text-purple-600 font-medium"><?php echo htmlspecialchars($ticket['user_name']); ?></div>
                                <?php endif; ?>
                            </div>
                            <span class="text-xs font-medium text-purple-600 flex items-center">
                                View Details <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <!-- Empty State -->
                <div class="flex flex-col items-center justify-center py-20 px-4 text-center">
                    <div class="bg-slate-50 rounded-full p-4 mb-4">
                        <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">No tickets found</h3>
                    <p class="mt-1 text-sm text-slate-500">Try adjusting your filters or search terms.</p>
                </div>
            <?php endif; ?>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="bg-slate-50 px-4 py-3 flex items-center justify-between border-t border-slate-200 sm:px-6">
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-slate-500">
                            Page <span class="font-medium"><?php echo $page; ?></span> of <span class="font-medium"><?php echo $total_pages; ?></span>
                        </p>
                    </div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-slate-300 bg-white text-sm font-medium text-slate-500 hover:bg-slate-50">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-slate-300 bg-white text-sm font-medium text-slate-500 hover:bg-slate-50">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
</body>
</html>