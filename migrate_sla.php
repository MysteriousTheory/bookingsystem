<?php
require 'db.php';

try {
    // Add due_date column if it doesn't exist
    $sql = "ALTER TABLE tickets ADD COLUMN due_date DATETIME NULL AFTER priority";
    $pdo->exec($sql);
    echo "Column 'due_date' added successfully to 'tickets' table.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Column 'due_date' already exists.\n";
    } else {
        die("Migration failed: " . $e->getMessage());
    }
}
?>
