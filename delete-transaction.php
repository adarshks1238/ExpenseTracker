<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

// Deleting must be a POST request with a valid CSRF token.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf()) {
    setFlash('danger', 'Invalid request.');
    header('Location: dashboard.php');
    exit;
}

$id = toObjectId($_POST['id'] ?? null);

if ($id === null) {
    setFlash('danger', 'Invalid transaction ID.');
    header('Location: dashboard.php');
    exit;
}

try {
    $result = getDatabase()->transactions->deleteOne(['_id' => $id]);

    if ($result->getDeletedCount() === 1) {
        setFlash('success', 'Transaction deleted.');
    } else {
        setFlash('warning', 'Transaction was not found.');
    }
} catch (Throwable $error) {
    setFlash('danger', 'Could not delete the transaction. Check that MongoDB is running.');
}

header('Location: dashboard.php');
exit;
