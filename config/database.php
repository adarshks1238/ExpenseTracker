<?php
declare(strict_types=1);

/**
 * Reusable MongoDB connection.
 * Settings (including the password) are read from the .env file.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use MongoDB\Client;
use MongoDB\Database;

// Load variables from .env (safeLoad = no crash if the file is missing)
Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

/** Read an environment value with a fallback. */
function env(string $key, string $default = ''): string
{
    $value = $_ENV[$key] ?? getenv($key);
    return ($value === false || $value === null || $value === '') ? $default : (string) $value;
}

/** Returns the expense_tracker database (connection is created once). */
function getDatabase(): Database
{
    static $database = null;

    if ($database !== null) {
        return $database;
    }

    $uriOptions = [];
    $username = env('MONGO_USERNAME');

    if ($username !== '') {
        $uriOptions['username']   = $username;
        $uriOptions['password']   = env('MONGO_PASSWORD');
        $uriOptions['authSource'] = env('MONGO_AUTH_SOURCE', 'admin');
    }

    // typeMap makes MongoDB return plain PHP arrays (easier for beginners)
    $driverOptions = [
        'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
    ];

    $client = new Client(env('MONGO_URI', 'mongodb://127.0.0.1:27017'), $uriOptions, $driverOptions);
    $database = $client->selectDatabase(env('MONGO_DB_NAME', 'expense_tracker'));

    seedCategories($database);

    return $database;
}

/** Insert default categories the first time the app runs. */
function seedCategories(Database $db): void
{
    $categories = $db->categories;

    if ($categories->countDocuments([]) > 0) {
        return;
    }

    $defaults = [
        ['Food', 'expense'], ['Travel', 'expense'], ['Shopping', 'expense'],
        ['Bills', 'expense'], ['Health', 'expense'], ['Entertainment', 'expense'],
        ['Education', 'expense'], ['Other', 'expense'],
        ['Salary', 'income'], ['Freelance', 'income'], ['Investment', 'income'],
        ['Gift', 'income'], ['Other', 'income'],
    ];

    $documents = array_map(
        fn(array $row) => ['category_name' => $row[0], 'type' => $row[1]],
        $defaults
    );

    $categories->insertMany($documents);
}
