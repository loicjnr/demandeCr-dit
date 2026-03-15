<?php
require_once 'db.php';

try {
    $pdo->exec("ALTER TABLE utilisateur ADD COLUMN reset_token VARCHAR(255) NULL");
    echo "reset_token added\n";
    $pdo->exec("ALTER TABLE utilisateur ADD COLUMN reset_expires DATETIME NULL");
    echo "reset_expires added\n";
} catch (PDOException $e) {
    echo "Error or already exists: " . $e->getMessage();
}
?>
