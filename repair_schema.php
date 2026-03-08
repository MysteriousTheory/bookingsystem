<?php
require 'db.php';

try {
    // 1. Add 'category' column
    echo "Checking 'category' column...\n";
    try {
        $pdo->query("SELECT category FROM tickets LIMIT 1");
        echo "Column 'category' already exists.\n";
    } catch (PDOException $e) {
        $pdo->exec("ALTER TABLE tickets ADD COLUMN category VARCHAR(50) DEFAULT 'General' AFTER title");
        echo "Column 'category' added successfully.\n";
    }

    // 2. Add 'due_date' column (redundant check, but safe)
    echo "Checking 'due_date' column...\n";
    try {
        $pdo->query("SELECT due_date FROM tickets LIMIT 1");
        echo "Column 'due_date' already exists.\n";
    } catch (PDOException $e) {
        $pdo->exec("ALTER TABLE tickets ADD COLUMN due_date DATETIME NULL AFTER priority");
        echo "Column 'due_date' added successfully.\n";
    }

} catch (PDOException $e) {
    die("Repair failed: " . $e->getMessage());
}

