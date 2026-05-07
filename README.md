# SmartSpend — Personal Expense Tracker

A straightforward, professional web application for tracking and managing personal expenses. Built entirely with PHP, MySQL, and vanilla CSS — ensuring a clean, lightweight architecture with no external framework dependencies.

![SmartSpend Banner](./assets/img/SmartSpend.svg) <!-- Assuming logo is here, else it's just a fallback -->

---

## 🌟 Key Features

- **User Authentication** — Secure registration, login, and profile management with hashed passwords.
- **Expense Tracking** — Comprehensive tools to add, view, edit, and safely soft-delete expenses, complete with category and date selection.
- **Interactive Dashboard** — Real-time insights showing current month's spending, total expenses, and top spending categories.
- **Custom Reports & Export** — Generate custom expense reports by date range and instantly export data to CSV.
- **Admin Control Panel** — Dedicated portal for system administrators to manage users, configure expense categories, view system-wide statistics, and monitor activity through a detailed audit log.
- **Responsive Design** — Fully mobile-responsive interface optimized for desktop, tablet, and mobile viewing.

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| **Frontend** | HTML5, Vanilla CSS3, Vanilla JavaScript |
| **Backend** | PHP 8.x (Custom MVC-like structure, No frameworks) |
| **Database** | MySQL 8.x via phpMyAdmin (PDO) |
| **Server** | XAMPP (Apache + MySQL) |

---

## 📁 Project Structure

The codebase is organized modularly by feature to maintain clean separation of concerns:

```text
SmartSpend/
├── account/            # User profile management (update details, password, close account)
├── admin/              # Admin control panel (users, categories, system stats, audit log)
├── assets/             # Static assets
│   ├── css/            # Vanilla CSS stylesheets (home.css, style.css)
│   ├── img/            # Brand assets and images
│   └── js/             # Frontend JavaScript interactions
├── auth/               # Authentication logic (login, register, logout)
├── config/             # Global configuration (db.php)
├── dashboard/          # User's primary landing view and summary statistics
├── database/           # SQL dump files for initialization (smartspend.sql)
├── expenses/           # Core expense logic (add, edit, list, delete)
├── includes/           # Shared UI components (header.php, footer.php)
├── reports/            # Reporting engine and CSV export logic
├── home.php            # Public landing page for unauthenticated visitors
├── index.php           # Application entry point / router
└── README.md           # Project documentation
```

*(Note: Weekly Sprint Journals from the development team are also kept in the root directory for academic/tracking purposes).*

---

## 🚀 Getting Started

### 1. Clone the repository

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/
git clone <repository_url> SmartSpend
```

### 2. Set up the database

- Start **Apache** and **MySQL** in your XAMPP Control Panel.
- Open `http://localhost/phpmyadmin` in your browser.
- Create a new database called `smartspend`.
- Import the schema by uploading `database/smartspend.sql`.

### 3. Check the database configuration

Open `config/db.php` and verify the credentials match your local MySQL setup:

```php
$host = 'localhost';
$db   = 'smartspend';
$user = 'root';
$pass = ''; // Leave blank for default XAMPP setups
```

### 4. Open in browser

Navigate to `http://localhost/SmartSpend/` — you'll see the landing page and can proceed to register or log in.

---

## 🔐 Default Admin Account

To access the admin panel immediately, the `smartspend.sql` file seeds a default administrator account:

- **Email:** `admin@smartspend.com`
- **Password:** `Admin1234`

---

## 🛡️ Security & Architecture Notes

- **Password Hashing:** Passwords are cryptographically hashed using `PASSWORD_BCRYPT` before storage.
- **Prepared Statements:** All database interactions strictly use PHP PDO prepared statements to prevent SQL Injection.
- **Soft Deletion:** Expenses and users are never permanently dropped; instead, `is_deleted = 1` or `is_active = 0` flags are used to preserve data integrity and audit trails.
- **State-Changing Actions:** Operations like toggling categories or deleting records strictly use `POST` handlers, preventing accidental modifications via `GET` requests.
- **Centralized Sessions:** Flash messages and session validation are handled uniformly in `includes/header.php`.

---

**SmartSpend © 2026** | Designed & Built for CTEC2713
