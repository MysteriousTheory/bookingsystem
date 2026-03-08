<?php
require 'db.php';
require 'functions.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Admins generally shouldn't create tickets for themselves in this flow, but if they do, redirect or allow.
// Assuming admins use the same form for now, or redirect to admin dashboard:
if (isAdmin()) {
    // Optional: Redirect admin to dashboard if they shouldn't create tickets here
    // header("Location: admin_dashboard.php");
    // exit;
}

// 2. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $description = trim($_POST['description']);
    $priority = $_POST['priority'];
    
    // SLA / Due Date Calculation
    $dueDate = new DateTime();
    switch ($priority) {
        case 'High':
            $dueDate->modify('+24 hours');
            break;
        case 'Medium':
            $dueDate->modify('+48 hours');
            break;
        case 'Low':
            $dueDate->modify('+5 days');
            break;
        default:
            $dueDate->modify('+48 hours');
    }
    $dueDateStr = $dueDate->format('Y-m-d H:i:s');
    
    // File Upload Handling
    $attachmentPath = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'zip'];
        $filename = $_FILES['attachment']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed) && $_FILES['attachment']['size'] <= 5242880) { // 5MB limit
            $newName = uniqid() . '.' . $ext;
            $destination = 'uploads/' . $newName;
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $destination)) {
                $attachmentPath = $destination;
            }
        }
    }

    // Insert Ticket
    $stmt = $pdo->prepare("INSERT INTO tickets (user_id, title, category, description, priority, attachment, due_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $title, $category, $description, $priority, $attachmentPath, $dueDateStr]);
    $ticket_id = $pdo->lastInsertId();

    // Email Notification to Admin (Placeholder)
    $adminEmail = 'admin@example.com'; 
    $attachmentMsg = $attachmentPath ? "\n\n(Attachment included)" : "";
    // sendNotificationEmail($adminEmail, "New Ticket #$ticket_id: $title", "User {$_SESSION['name']} created a new ticket.\n\nPriority: $priority\n\n$description$attachmentMsg");

    // Email Notification to User (Placeholder)
    if (isset($_SESSION['email'])) {
         // sendNotificationEmail($_SESSION['email'], "Ticket #$ticket_id Created", "We received your ticket: $title. We will get back to you shortly.");
    }

    // Redirect to the new ticket view
    header("Location: view_ticket.php?id=" . $ticket_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Ticket</title>
    <link rel="icon" href="images/prism-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { background-color: #f8fafc; font-family: sans-serif; }</style>
</head>
<body class="text-slate-800">

    <!-- Navbar -->
    <nav class="bg-black shadow-lg sticky top-0 z-20 mb-8">
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
                    <a href="home.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">Overview</a>
                    <a href="dashboard.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">My Tickets</a>
                    <a href="profile.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">Profile</a>
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
                 <a href="profile.php" class="flex-1 text-center py-3 text-sm font-medium text-gray-400 border-b-2 border-transparent hover:text-gray-200">Profile</a>
            </div>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">
        
        <div class="mb-6 flex justify-between items-end">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Submit a Request</h1>
                <p class="text-slate-500 text-sm mt-1">We are here to help. Please fill out the form below.</p>
            </div>
            <a href="dashboard.php" class="text-sm text-purple-600 hover:text-purple-800 hover:underline font-medium">&larr; Cancel</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <form method="POST" class="p-6 md:p-8" enctype="multipart/form-data">
                
                <!-- Subject -->
                <div class="mb-6">
                    <label class="block text-slate-700 text-sm font-bold mb-2" for="title">Subject</label>
                    <input class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition text-sm" name="title" type="text" required placeholder="Brief summary of the issue">
                </div>
                
                <!-- Grid: Category & Priority -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-slate-700 text-sm font-bold mb-2" for="category">Category</label>
                        <select class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition text-sm bg-white" name="category">
                            <option value="General">General Inquiry</option>
                            <option value="Technical Support">Technical Support</option>
                            <option value="Billing">Billing Issue</option>
                            <option value="Feature Request">Feature Request</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-700 text-sm font-bold mb-2" for="priority">Priority</label>
                        <select class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition text-sm bg-white" name="priority">
                            <option value="Low">Low - Minor issue</option>
                            <option value="Medium" selected>Medium - Features affected</option>
                            <option value="High">High - System down / Critical</option>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label class="block text-slate-700 text-sm font-bold mb-2" for="description">Description</label>
                    <textarea class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 h-40 transition text-sm" name="description" required placeholder="Please describe the issue in detail..."></textarea>
                </div>

                <!-- Attachment -->
                <div class="mb-8">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Attachment (Optional)</label>
                    <div class="flex items-center justify-center w-full">
                        <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100 transition">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-3 text-slate-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                </svg>
                                <p class="text-sm text-slate-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                <p class="text-xs text-slate-500 mt-1">SVG, PNG, JPG or PDF (MAX. 5MB)</p>
                            </div>
                            <input id="dropzone-file" name="attachment" type="file" class="hidden" />
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <button type="submit" class="bg-purple-600 text-white font-bold py-2.5 px-8 rounded-lg hover:bg-purple-700 transition shadow-lg shadow-purple-500/30 text-sm">
                        Submit Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Simple script to show filename after selection
        document.getElementById('dropzone-file').addEventListener('change', function(e) {
            if (e.target.files[0]) {
                const fileName = e.target.files[0].name;
                const label = this.previousElementSibling.querySelector('p span');
                label.textContent = "Selected: " + fileName;
                label.parentElement.nextElementSibling.style.display = 'none'; // hide format text
            }
        });
    </script>
</body>
</html>