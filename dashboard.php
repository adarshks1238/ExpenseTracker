<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

use MongoDB\BSON\UTCDateTime;

$pageTitle    = 'Dashboard';
$dbError      = null;
$totalIncome  = 0.0;
$totalExpense = 0.0;
$transactions = [];
$categoryList = [];

// ----- Read filters from the URL (GET) -----
$filterType     = in_array($_GET['type'] ?? '', ['income', 'expense'], true) ? $_GET['type'] : '';
$filterCategory = trim((string) ($_GET['category'] ?? ''));
$filterFrom     = trim((string) ($_GET['from'] ?? ''));
$filterTo       = trim((string) ($_GET['to'] ?? ''));

$query = [];
if ($filterType !== '') {
    $query['type'] = $filterType;
}
if ($filterCategory !== '') {
    $query['category'] = $filterCategory;
}

$dateQuery = [];
if (($from = parseDate($filterFrom)) !== null) {
    $dateQuery['$gte'] = new UTCDateTime($from->getTimestamp() * 1000);
}
if (($to = parseDate($filterTo)) !== null) {
    // include the whole "to" day
    $dateQuery['$lt'] = new UTCDateTime(($to->getTimestamp() + 86400) * 1000);
}
if ($dateQuery) {
    $query['transaction_date'] = $dateQuery;
}

// ----- Fetch data from MongoDB -----
try {
    $db = getDatabase();

    // Total income and expenses (all transactions, not filtered)
    $totals = $db->transactions->aggregate([
        ['$group' => ['_id' => '$type', 'total' => ['$sum' => '$amount']]],
    ]);
    foreach ($totals as $row) {
        if ($row['_id'] === 'income') {
            $totalIncome = (float) $row['total'];
        } elseif ($row['_id'] === 'expense') {
            $totalExpense = (float) $row['total'];
        }
    }

    $transactions = $db->transactions->find($query, [
        'sort' => ['transaction_date' => -1, 'created_at' => -1],
    ])->toArray();

    $categoryList = $db->categories->distinct('category_name');
    sort($categoryList);
} catch (Throwable $error) {
    $dbError = 'Could not load data from MongoDB. Check that MongoDB is running and your .env settings are correct.';
}

$balance    = $totalIncome - $totalExpense;
$isFiltered = ($filterType !== '' || $filterCategory !== '' || $filterFrom !== '' || $filterTo !== '');

require __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Dashboard</h1>
    <a href="add-transaction.php" class="btn btn-primary">Add transaction</a>
</div>

<?php if ($dbError): ?>
    <div class="alert alert-danger"><?= e($dbError) ?></div>
<?php endif; ?>

<!-- Summary cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card summary-card border-start border-4 border-success h-100">
            <div class="card-body">
                <div class="text-muted small">Total income</div>
                <div class="fs-3 fw-semibold text-success"><?= e(money($totalIncome)) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card summary-card border-start border-4 border-danger h-100">
            <div class="card-body">
                <div class="text-muted small">Total expenses</div>
                <div class="fs-3 fw-semibold text-danger"><?= e(money($totalExpense)) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card summary-card border-start border-4 border-primary h-100">
            <div class="card-body">
                <div class="text-muted small">Available balance</div>
                <div class="fs-3 fw-semibold <?= $balance < 0 ? 'text-danger' : 'text-primary' ?>"><?= e(money($balance)) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label for="f-type" class="form-label">Type</label>
                <select id="f-type" name="type" class="form-select">
                    <option value="">All</option>
                    <option value="income" <?= $filterType === 'income' ? 'selected' : '' ?>>Income</option>
                    <option value="expense" <?= $filterType === 'expense' ? 'selected' : '' ?>>Expense</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="f-category" class="form-label">Category</label>
                <select id="f-category" name="category" class="form-select">
                    <option value="">All categories</option>
                    <?php foreach ($categoryList as $name): ?>
                        <option value="<?= e($name) ?>" <?= $filterCategory === $name ? 'selected' : '' ?>><?= e($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="f-from" class="form-label">From</label>
                <input type="date" id="f-from" name="from" class="form-control" value="<?= e($filterFrom) ?>">
            </div>
            <div class="col-md-2">
                <label for="f-to" class="form-label">To</label>
                <input type="date" id="f-to" name="to" class="form-control" value="<?= e($filterTo) ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Apply filters</button>
                <a href="dashboard.php" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Transaction history -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Transaction history</span>
        <span class="text-muted small"><?= count($transactions) ?> <?= count($transactions) === 1 ? 'transaction' : 'transactions' ?></span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$transactions): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <?= $isFiltered ? 'No transactions match these filters.' : 'No transactions yet. Add your first one to get started.' ?>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($transactions as $tx): ?>
                <?php $isIncome = $tx['type'] === 'income'; ?>
                <tr>
                    <td class="text-nowrap"><?= e($tx['transaction_date']->toDateTime()->format('d M Y')) ?></td>
                    <td>
                        <div class="fw-medium"><?= e($tx['title']) ?></div>
                        <?php if (!empty($tx['description'])): ?>
                            <div class="small text-muted"><?= e($tx['description']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= e($tx['category']) ?></td>
                    <td>
                        <span class="badge <?= $isIncome ? 'text-bg-success' : 'text-bg-danger' ?>">
                            <?= $isIncome ? 'Income' : 'Expense' ?>
                        </span>
                    </td>
                    <td class="text-end fw-semibold text-nowrap <?= $isIncome ? 'text-success' : 'text-danger' ?>">
                        <?= $isIncome ? '+' : '-' ?><?= e(money((float) $tx['amount'])) ?>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="edit-transaction.php?id=<?= e((string) $tx['_id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form method="post" action="delete-transaction.php" class="d-inline js-delete-form">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="id" value="<?= e((string) $tx['_id']) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
