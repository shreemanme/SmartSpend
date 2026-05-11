-- ─────────────────────────────────────────────────────────────────────────────
-- SmartSpend — Database Schema & Seed Data
-- Database:  smartspend
-- Charset:   utf8mb4
-- Import:    phpMyAdmin → Import → select this file → Go
-- ─────────────────────────────────────────────────────────────────────────────

CREATE DATABASE IF NOT EXISTS `smartspend`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `smartspend`;

-- ─────────────────────────────────────────
-- TABLE: tblUser
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tblUser (
    user_id       INT            AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100)   NOT NULL,
    email         VARCHAR(150)   NOT NULL UNIQUE,
    password_hash VARCHAR(255)   NOT NULL,
    created_date  DATE           NOT NULL,
    is_active     TINYINT(1)     NOT NULL DEFAULT 1,
    role          VARCHAR(10)    NOT NULL DEFAULT 'user'
    -- role values: 'user' or 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
-- TABLE: tblCategory
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tblCategory (
    category_id   INT            AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50)    NOT NULL UNIQUE,
    description   VARCHAR(200),
    created_date  DATE           NOT NULL,
    is_active     TINYINT(1)     NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
-- TABLE: tblExpense
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tblExpense (
    expense_id    INT            AUTO_INCREMENT PRIMARY KEY,
    user_id       INT            NOT NULL,
    category_id   INT            NOT NULL,
    amount        DECIMAL(10,2)  NOT NULL,
    expense_date  DATE           NOT NULL,
    description   VARCHAR(255),
    is_deleted    TINYINT(1)     NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id)     REFERENCES tblUser(user_id),
    FOREIGN KEY (category_id) REFERENCES tblCategory(category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
-- TABLE: tblReport
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tblReport (
    report_id      INT           AUTO_INCREMENT PRIMARY KEY,
    user_id        INT           NOT NULL,
    report_name    VARCHAR(100)  NOT NULL,
    date_from      DATE          NOT NULL,
    date_to        DATE          NOT NULL,
    generated_date DATE          NOT NULL,
    is_exported    TINYINT(1)    NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES tblUser(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
-- TABLE: tblAuditLog
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tblAuditLog (
    log_id      INT           AUTO_INCREMENT PRIMARY KEY,
    user_id     INT           NOT NULL,
    expense_id  INT           NULL,
    action_type VARCHAR(10)   NOT NULL,
    -- action_type values: 'CREATE', 'UPDATE', 'DELETE'
    action_date DATE          NOT NULL,
    old_value   TEXT,
    -- old_value: NULL for CREATE, JSON snapshot for UPDATE and DELETE
    is_reviewed TINYINT(1)    NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id)    REFERENCES tblUser(user_id),
    FOREIGN KEY (expense_id) REFERENCES tblExpense(expense_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
-- SEED DATA — Categories
-- ─────────────────────────────────────────
INSERT INTO tblCategory (category_name, description, created_date, is_active) VALUES
('Food',          'Meals, groceries and dining out',    CURDATE(), 1),
('Transport',     'Bus, train, taxi, fuel and travel',  CURDATE(), 1),
('Entertainment', 'Leisure, hobbies, events and games', CURDATE(), 1),
('Housing',       'Rent, utilities and home expenses',  CURDATE(), 1),
('Health',        'Medical, pharmacy and wellness',     CURDATE(), 1),
('Shopping',      'Clothing, electronics and retail',   CURDATE(), 1),
('Education',     'Tuition, books and courses',         CURDATE(), 1),
('Other',         'Miscellaneous expenses',             CURDATE(), 1);

-- ─────────────────────────────────────────
-- SEED DATA — Admin User
-- Password: Admin1234
-- Hash generated with: password_hash('Admin1234', PASSWORD_BCRYPT)
-- ─────────────────────────────────────────
-- Admin login: admin@smartspend.com / Admin1234
INSERT INTO tblUser (full_name, email, password_hash, created_date, is_active, role) VALUES
('Admin', 'admin@smartspend.com',
 '$2y$10$v42bexkNHYWICalS2Qdl7uI.8dSjm8jp4xfx7TpJKWWqzDgPbQG1K',
 CURDATE(), 1, 'admin');
