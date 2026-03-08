<?php
require 'db.php';
require 'functions.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || !isAdmin()) {
    header("Location: index.php");
    exit;
}

$message = '';
$error = '';
$editMode = false;
$editData = ['id' => '', 'title' => '', 'message' => ''];

// 2. Handle DELETE
if (isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM canned_responses WHERE id = ?");
    if ($stmt->execute([$_POST['delete_id']])) {
        $message = "Response deleted successfully.";
    }
}

// 3. Handle CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_response'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['message']); 
    $id = $_POST['id'] ?? null;

    if (empty($title) || empty($content)) {
        $error = "Title and Message are required.";
    } else {
        if (!empty($id)) {
            // Update
            $stmt = $pdo->prepare("UPDATE canned_responses SET title = ?, message = ? WHERE id = ?");
            if ($stmt->execute([$title, $content, $id])) {
                $message = "Response updated successfully.";
                // Reset edit mode
                $editMode = false; 
                $editData = ['id' => '', 'title' => '', 'message' => ''];
            } else {
                $error = "Failed to update.";
            }
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO canned_responses (title, message) VALUES (?, ?)");
            if ($stmt->execute([$title, $content])) {
                $message = "New response saved.";
            } else {
                $error = "Failed to save.";
            }
        }
    }
}

// 4. Handle EDIT Mode (Load Data)
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM canned_responses WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $fetched = $stmt->fetch();
    if ($fetched) {
        $editMode = true;
        $editData = $fetched;
    }
}

// 5. Fetch All
$stmt = $pdo->query("SELECT * FROM canned_responses ORDER BY title ASC");
$responses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Replies - Admin</title>
    <link rel="icon" href="images/prism-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Quill CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <style>
        .ql-toolbar { border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem; background-color: #f9fafb; border-color: #d1d5db; }
        .ql-container { border-bottom-left-radius: 0.5rem; border-bottom-right-radius: 0.5rem; border-color: #d1d5db; font-size: 14px; background: white; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen text-slate-800">

    <!-- Navbar -->
    <nav class="bg-black shadow-lg sticky top-0 z-10 mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="bg-white/10 p-1.5 rounded-lg mr-3">
                        <img src="https://tickets.prismtechnologies.com.ng/images/prism-logo.png" alt="Logo" class="h-6 w-auto">
                    </div>
                    <span class="text-lg sm:text-xl font-bold text-white tracking-wide">DevSupport Admin</span>
                </div>
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="admin_dashboard.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">Dashboard</a>
                    <a href="dashboard.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">All Tickets</a>
                    <a href="settings_canned.php" class="text-purple-400 font-semibold border-b-2 border-purple-500 px-1 pt-1 text-sm">Saved Replies</a>
                    <a href="settings_faq.php" class="text-gray-300 hover:text-white hover:border-gray-500 font-medium px-1 pt-1 text-sm border-b-2 border-transparent transition">Manage FAQs</a>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="logout.php" class="text-sm bg-red-500/10 text-red-500 hover:bg-red-500/20 border border-red-500/20 py-2 px-3 rounded transition font-medium">Logout</a>
                </div>
            </div>
             <!-- Mobile Menu -->
             <div class="flex md:hidden border-t border-gray-800 -mx-4 px-4 bg-gray-900 justify-around">
                 <a href="admin_dashboard.php" class="flex-1 text-center py-3 text-sm font-medium text-gray-400 border-b-2 border-transparent hover:text-gray-200">Admin</a>
                 <a href="settings_canned.php" class="flex-1 text-center py-3 text-sm font-semibold text-purple-400 border-b-2 border-purple-500">Replies</a>
                 <a href="settings_faq.php" class="flex-1 text-center py-3 text-sm font-medium text-gray-400 border-b-2 border-transparent hover:text-gray-200">FAQ</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Manage Saved Replies</h1>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
         <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center shadow-sm">
                 <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column: Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100">
                        <?php echo $editMode ? 'Edit Reply' : 'Add New Reply'; ?>
                    </h3>
                    
                    <form method="POST" id="cannedForm">
                        <input type="hidden" name="save_response" value="1">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($editData['id']); ?>">
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-xs font-bold uppercase mb-2">Title / Shortcut</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($editData['title']); ?>" placeholder="e.g. Password Reset Instructions" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition text-sm font-medium" required>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 text-xs font-bold uppercase mb-2">Response Content</label>
                            <!-- Quill Editor Container -->
                            <div id="editor" style="height: 200px;">
                                <?php echo $editData['message']; // This contains HTML ?>
                            </div>
                            <input type="hidden" name="message" id="messageInput">
                        </div>
                        
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 bg-purple-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-purple-700 transition shadow-sm">
                                <?php echo $editMode ? 'Update Reply' : 'Save Reply'; ?>
                            </button>
                            <?php if($editMode): ?>
                                <a href="settings_canned.php" class="bg-gray-100 text-gray-600 font-medium py-2 px-4 rounded-lg hover:bg-gray-200 transition">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column: List -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-700">Existing Templates</h3>
                        <span class="text-xs text-gray-400 bg-white border border-gray-200 px-2 py-1 rounded-full"><?php echo count($responses); ?> total</span>
                    </div>
                    
                    <?php if(count($responses) > 0): ?>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($responses as $r): ?>
                        <div class="group p-4 hover:bg-gray-50 transition flex justify-between items-start">
                            <div class="pr-4 flex-1">
                                <h4 class="text-sm font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($r['title']); ?></h4>
                                <div class="text-xs text-gray-500 line-clamp-2">
                                    <?php echo strip_tags($r['message']); ?>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                                <!-- Edit Button -->
                                <a href="?edit=<?php echo $r['id']; ?>" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                
                                <!-- Delete Form -->
                                <form method="POST" onsubmit="return confirm('Delete this saved reply?');">
                                    <input type="hidden" name="delete_id" value="<?php echo $r['id']; ?>">
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                        <div class="p-8 text-center text-gray-500 text-sm">
                            No canned responses saved yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- Initialize Quill -->
    <script>
        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Write your template content here...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'link'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });

        // Sync Quill content to hidden input on submit
        document.getElementById('cannedForm').onsubmit = function() {
            var input = document.getElementById('messageInput');
            if (quill.getText().trim().length === 0) {
                alert('Message content cannot be empty.');
                return false;
            }
            input.value = quill.root.innerHTML;
        };
    </script>
</body>
</html>