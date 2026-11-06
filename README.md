# Personal Expense Tracker

A simple single-user expense tracker built with **Core PHP**, **MongoDB** and **Bootstrap 5**.

## Features
- Add, view, edit and delete income and expense transactions
- Categories (Food, Travel, Shopping, Bills, Salary, ...) that change with the transaction type
- Total income, total expenses and available balance
- Filter transactions by type, category and date range
- Input validation, HTML escaping, CSRF protection and database error handling

## Tech stack
| Layer    | Technology                                      |
|----------|-------------------------------------------------|
| Frontend | HTML5, CSS3, JavaScript, Bootstrap 5            |
| Backend  | Core PHP 8.1+, MongoDB PHP Library, Composer    |
| Database | MongoDB Community Server (`expense_tracker`)    |

## Requirements
- PHP 8.1 or newer with the **mongodb** extension enabled
- Composer
- MongoDB Community Server running locally

## Setup
1. Clone the repository and open it in VS Code:
   ```bash
   git clone https://github.com/<your-username>/expense-tracker.git
   cd expense-tracker
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Create your environment file and edit the values if needed:
   ```bash
   cp .env.example .env
   ```
   (On Windows PowerShell: `copy .env.example .env`)
4. Start MongoDB, then run the PHP built-in server:
   ```bash
   php -S localhost:8000
   ```
5. Open <http://localhost:8000/test-connection.php> to check the database connection,
   then <http://localhost:8000/> to use the app.

The database and the default categories are created automatically on first run.

## Environment variables (`.env`)
| Variable            | Purpose                                         | Default                      |
|---------------------|-------------------------------------------------|------------------------------|
| `MONGO_URI`         | MongoDB connection string                       | `mongodb://127.0.0.1:27017`  |
| `MONGO_DB_NAME`     | Database name                                   | `expense_tracker`            |
| `MONGO_USERNAME`    | Username (leave empty if no authentication)     | empty                        |
| `MONGO_PASSWORD`    | Password (leave empty if no authentication)     | empty                        |
| `MONGO_AUTH_SOURCE` | Authentication database                         | `admin`                      |
| `CURRENCY_SYMBOL`   | Currency symbol shown in the UI                 | `₹`                          |

The `.env` file is listed in `.gitignore`, so your password is never pushed to GitHub.

## Database structure
**transactions**: `_id`, `title`, `amount`, `type`, `category`, `description`, `transaction_date`, `created_at`

**categories**: `_id`, `category_name`, `type`

## Project structure
```
expense-tracker/
├── index.php               # redirects to the dashboard
├── dashboard.php           # totals, filters, transaction table
├── add-transaction.php
├── edit-transaction.php
├── delete-transaction.php
├── test-connection.php     # checks the MongoDB connection
├── config/database.php     # reusable MongoDB connection (reads .env)
├── includes/               # header, footer, form partial, helper functions
├── assets/css, assets/js
├── composer.json
├── .env.example
└── README.md
```

## Troubleshooting
- **"Class MongoDB\Client not found"**: run `composer install`.
- **"The mongodb extension is not loaded"**: enable `extension=mongodb` in `php.ini`, restart the terminal, and check with `php -m`.
- **Connection refused**: make sure the MongoDB service is running on port 27017.
