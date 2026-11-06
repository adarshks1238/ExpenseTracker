<?php
/**
 * Form shared by add-transaction.php and edit-transaction.php.
 * Expects: $values, $errors, $categories, $buttonLabel
 */
$fieldError = fn(string $field) => $errors[$field] ?? null;
?>
<form method="post" novalidate id="transactionForm"
      data-categories="<?= e(json_encode($categories)) ?>"
      data-selected="<?= e($values['category'] ?? '') ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

    <div class="row g-3">
        <div class="col-md-6">
            <label for="title" class="form-label">Title</label>
            <input type="text" id="title" name="title" maxlength="100"
                   class="form-control <?= $fieldError('title') ? 'is-invalid' : '' ?>"
                   value="<?= e($values['title'] ?? '') ?>" placeholder="e.g. Grocery shopping" required>
            <div class="invalid-feedback"><?= e($fieldError('title')) ?></div>
        </div>

        <div class="col-md-6">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0.01"
                   class="form-control <?= $fieldError('amount') ? 'is-invalid' : '' ?>"
                   value="<?= e((string) ($values['amount'] ?? '')) ?>" placeholder="0.00" required>
            <div class="invalid-feedback"><?= e($fieldError('amount')) ?></div>
        </div>

        <div class="col-md-4">
            <label for="type" class="form-label">Type</label>
            <select id="type" name="type" class="form-select <?= $fieldError('type') ? 'is-invalid' : '' ?>" required>
                <option value="expense" <?= ($values['type'] ?? 'expense') === 'expense' ? 'selected' : '' ?>>Expense</option>
                <option value="income" <?= ($values['type'] ?? '') === 'income' ? 'selected' : '' ?>>Income</option>
            </select>
            <div class="invalid-feedback"><?= e($fieldError('type')) ?></div>
        </div>

        <div class="col-md-4">
            <label for="category" class="form-label">Category</label>
            <select id="category" name="category" class="form-select <?= $fieldError('category') ? 'is-invalid' : '' ?>" required></select>
            <div class="invalid-feedback"><?= e($fieldError('category')) ?></div>
        </div>

        <div class="col-md-4">
            <label for="transaction_date" class="form-label">Date</label>
            <input type="date" id="transaction_date" name="transaction_date"
                   class="form-control <?= $fieldError('transaction_date') ? 'is-invalid' : '' ?>"
                   value="<?= e($values['transaction_date'] ?? date('Y-m-d')) ?>" required>
            <div class="invalid-feedback"><?= e($fieldError('transaction_date')) ?></div>
        </div>

        <div class="col-12">
            <label for="description" class="form-label">Description <span class="text-muted">(optional)</span></label>
            <textarea id="description" name="description" rows="3" maxlength="255"
                      class="form-control <?= $fieldError('description') ? 'is-invalid' : '' ?>"><?= e($values['description'] ?? '') ?></textarea>
            <div class="invalid-feedback"><?= e($fieldError('description')) ?></div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><?= e($buttonLabel) ?></button>
        <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
