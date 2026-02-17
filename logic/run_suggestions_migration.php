<?php
require_once 'db_connection.php';

try {
    $sql = file_get_contents(__DIR__ . '/db_migration_suggestions.sql');
    $pdo->exec($sql);
    echo "Migration completed successfully! The anonymous_suggestions table has been created.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
