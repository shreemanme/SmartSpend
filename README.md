# SmartSpend — Personal Expense Tracker

A straightforward web application for tracking and managing personal expenses. Built entirely with PHP, MySQL, and vanilla CSS — no frameworks, no shortcuts.

---

## Features

- **User accounts** — register, log in, and manage your own profile securely
- **Expense tracking** — add, edit, and remove expenses with categories and dates
- **Dashboard** — see how much you've spent this month at a glance
- **Reports** — filter expenses by date range and export to CSV
- **Admin panel** — manage users, categories, and view audit logs

---

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, vanilla JavaScript |
| Backend | PHP 8.x (no framework) |
| Database | MySQL 8.x via phpMyAdmin |
| Server | XAMPP (Apache + MySQL) |

---

## Getting Started

### 1. Clone the repository

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/
git clone <repository_url> SmartSpend
```

### 2. Set up the database

- Start Apache and MySQL in XAMPP
- Open `http://localhost/phpmyadmin`
- Create a new database called `smartspend`
- Import `database/smartspend.sql`

### 3. Check the database config

Open `config/db.php` and make sure the credentials match your local setup:

```php
$host = 'localhost';
$db   = 'smartspend';
$user = 'root';
$pass = '';
```

### 4. Open in browser

Go to `http://localhost/SmartSpend/` — you'll be taken to the login page.

---

## Default Admin Account

The SQL seed file includes a default admin user:

- **Email:** admin@smartspend.com
- **Password:** Admin1234

---

## Security Notes

- Passwords are hashed with bcrypt — never stored in plain text
- All database queries use PDO with prepared statements
- Expenses are soft-deleted (`is_deleted = 1`) rather than removed from the database
- Both client-side and server-side validation are in place

---

SmartSpend © 2026
