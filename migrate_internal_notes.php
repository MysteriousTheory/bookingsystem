<?php
require 'db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS internal_notes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ticket_id INT NOT NULL,
        user_id INT NOT NULL,
        note TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    echo "Table 'internal_notes' created successfully.\n";
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
?>
