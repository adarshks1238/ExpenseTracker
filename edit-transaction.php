<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$pageTitle  = 'Edit transaction';
$errors     = [];
$values     = [];
$dbError    = null;
$categories = ['income' => [], 'expense' => []];

$id = toObjectId($_GET['id'] ?? null);
if ($id === null) {
    setFlash('danger', 'Invalid transaction ID.');
    header('Location: dashboard.php');
    exit;
}

try {
    $categories   = getCategoriesByType();
    $transactions = getDatabase()->transactions;
    $existing     = $transactions->findOne(['_id' => $id]);

    if ($existing === null) {
        setFlash('danger', 'Transaction not found.');
        header('Location: dashboard.php');
        exit;
    }

    // Pre-fill the form with the saved values
    $values = [
        'title'            => $existing['title'],
        'amount'           => $existing['amount'],
        'type'             => $existing['type'],
        'category'         => $existing['category'],
        'description'      => $existing['description'] ?? '',
        'transaction_date' => $existing['transaction_date']->toDateTime()->format('Y-m-d'),
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $values = $_POST;

        if (!verifyCsrf()) {
            $errors['form'] = 'Your session expired. Please try again.';
        } else {
            [$data, $errors] = validateTransaction($_POST, $categories);

            if (!$errors) {
                $transactions->updateOne(['_id' => $id], ['$set' => $data]);

                setFlash('success', 'Transaction updated successfully.');
                header('Location: dashboard.php');
                exit;
            }
        }
    }
} catch (Throwable $error) {
    $dbError = 'Could not load or update the transaction. Check that MongoDB is running.';
}

$buttonLabel = 'Save changes';
require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <h1 class="h3 mb-4">Edit transaction</h1>

        <?php if ($dbError): ?><div class="alert alert-danger"><?= e($dbError) ?></div><?php endif; ?>
        <?php if (!empty($errors['form'])): ?><div class="alert alert-danger"><?= e($errors['form']) ?></div><?php endif; ?>

        <div class="card">
            <div class="card-body">
                <?php require __DIR__ . '/includes/transaction-form.php'; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
