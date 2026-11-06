<?php
declare(strict_types=1);

/**
 * Small helper functions shared by every page.
 */

require_once __DIR__ . '/../config/database.php';

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Escape text before printing it in HTML (prevents XSS). */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Format money, e.g. ₹1,250.00 */
function money(float $amount): string
{
    return env('CURRENCY_SYMBOL', '₹') . number_format($amount, 2);
}

/* ---------- Flash messages (one-time alerts) ---------- */

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/* ---------- CSRF protection for forms ---------- */

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/* ---------- Dates and IDs ---------- */

/** Convert "2026-11-06" into a DateTimeImmutable (UTC) or null if invalid. */
function parseDate(string $value): ?DateTimeImmutable
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('UTC'));
    $errors = DateTimeImmutable::getLastErrors();

    if ($date === false || ($errors && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
        return null;
    }
    return $date;
}

/** Convert a string into a MongoDB ObjectId, or null if it is not valid. */
function toObjectId(?string $id): ?ObjectId
{
    if ($id === null || !preg_match('/^[a-f0-9]{24}$/i', $id)) {
        return null;
    }
    return new ObjectId($id);
}

/* ---------- Categories ---------- */

/** Returns ['income' => ['Salary', ...], 'expense' => ['Food', ...]] */
function getCategoriesByType(): array
{
    $grouped = ['income' => [], 'expense' => []];
    $cursor = getDatabase()->categories->find([], ['sort' => ['category_name' => 1]]);

    foreach ($cursor as $category) {
        $grouped[$category['type']][] = $category['category_name'];
    }
    return $grouped;
}

/* ---------- Validation ---------- */

/**
 * Validates form input for add/edit.
 * Returns [cleanData, errors].
 */
function validateTransaction(array $input, array $categories): array
{
    $errors = [];

    $title       = trim((string) ($input['title'] ?? ''));
    $amountRaw   = trim((string) ($input['amount'] ?? ''));
    $type        = (string) ($input['type'] ?? '');
    $category    = (string) ($input['category'] ?? '');
    $description = trim((string) ($input['description'] ?? ''));
    $dateRaw     = trim((string) ($input['transaction_date'] ?? ''));

    if ($title === '' || mb_strlen($title) > 100) {
        $errors['title'] = 'Title is required (max 100 characters).';
    }

    if (!is_numeric($amountRaw) || (float) $amountRaw <= 0 || (float) $amountRaw > 1000000000) {
        $errors['amount'] = 'Enter an amount greater than 0.';
    }

    if (!in_array($type, ['income', 'expense'], true)) {
        $errors['type'] = 'Choose income or expense.';
    } elseif (!in_array($category, $categories[$type], true)) {
        $errors['category'] = 'Choose a valid category for this type.';
    }

    if (mb_strlen($description) > 255) {
        $errors['description'] = 'Description can be 255 characters at most.';
    }

    $date = parseDate($dateRaw);
    if ($date === null) {
        $errors['transaction_date'] = 'Enter a valid date.';
    }

    $clean = [
        'title'            => $title,
        'amount'           => round((float) $amountRaw, 2),
        'type'             => $type,
        'category'         => $category,
        'description'      => $description,
        'transaction_date' => $date ? new UTCDateTime($date->getTimestamp() * 1000) : null,
    ];

    return [$clean, $errors];
}
