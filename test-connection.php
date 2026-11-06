<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';

// Quick check that PHP can talk to MongoDB. Open: http://localhost:8000/test-connection.php
header('Content-Type: text/plain; charset=utf-8');

if (!extension_loaded('mongodb')) {
    exit("FAIL: The PHP 'mongodb' extension is not loaded. Enable it in php.ini.\n");
}

try {
    $db = getDatabase();
    $db->command(['ping' => 1]);
    echo "SUCCESS: Connected to MongoDB.\n";
    echo 'Database: ' . $db->getDatabaseName() . "\n";
    echo 'Categories in database: ' . $db->categories->countDocuments([]) . "\n";
    echo 'Transactions in database: ' . $db->transactions->countDocuments([]) . "\n";
} catch (Throwable $error) {
    echo "FAIL: Could not connect to MongoDB.\n";
    echo $error->getMessage() . "\n";
}
