<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$pageTitle  = 'Add transaction';
$errors     = [];
$values     = ['type' => 'expense', 'transaction_date' => date('Y-m-d')];
$dbError    = null;
$categories = ['income' => [], 'expense' => []];

try {
    $categories = getCategoriesByType();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $values = $_POST; // keep what the user typed

        if (!verifyCsrf()) {
            $errors['form'] = 'Your session expired. Please try again.';
        } else {
            [$data, $errors] = validateTransaction($_POST, $categories);

            if (!$errors) {
                $data['created_at'] = new MongoDB\BSON\UTCDateTime();
                getDatabase()->transactions->insertOne($data);

                setFlash('success', 'Transaction added successfully.');
                header('Location: dashboard.php');
                exit;
            }
        }
    }
} catch (Throwable $error) {
    $dbError = 'Could not save the transaction. Check that MongoDB is running.';
}

$buttonLabel = 'Add transaction';
require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <h1 class="h3 mb-4">Add transaction</h1>

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
