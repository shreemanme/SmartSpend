-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 11, 2026 at 09:46 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smartspend`
--
CREATE DATABASE IF NOT EXISTS `smartspend` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `smartspend`;
-- --------------------------------------------------------

--
-- Table structure for table `tblAuditLog`
--

CREATE TABLE `tblAuditLog` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expense_id` int(11) DEFAULT NULL,
  `action_type` varchar(10) NOT NULL,
  `action_date` date NOT NULL,
  `old_value` text DEFAULT NULL,
  `is_reviewed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblAuditLog`
--

INSERT INTO `tblAuditLog` (`log_id`, `user_id`, `expense_id`, `action_type`, `action_date`, `old_value`, `is_reviewed`) VALUES
(9, 2, 5, 'CREATE', '2026-05-08', NULL, 0),
(12, 2, 5, 'UPDATE', '2026-05-08', '{\"expense_id\":5,\"user_id\":2,\"category_id\":10,\"amount\":\"3000.00\",\"expense_date\":\"2026-05-06\",\"description\":\"\",\"is_deleted\":0}', 0),
(13, 4, 6, 'CREATE', '2026-05-11', NULL, 0),
(14, 4, 7, 'CREATE', '2026-05-11', NULL, 1),
(17, 4, 8, 'CREATE', '2026-05-11', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tblCategory`
--

CREATE TABLE `tblCategory` (
  `category_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `category_name` varchar(50) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `created_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblCategory`
--

INSERT INTO `tblCategory` (`category_id`, `user_id`, `category_name`, `description`, `created_date`, `is_active`) VALUES
(10, 2, 'Food', '', '2026-05-08', 0),
(11, 2, 'Travel', '', '2026-05-08', 1),
(12, 2, 'Shopping', '', '2026-05-08', 1),
(13, 4, 'Food', '', '2026-05-11', 1),
(14, 4, 'Travel', '', '2026-05-11', 1),
(15, 4, 'Rent', '', '2026-05-11', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tblExpense`
--

CREATE TABLE `tblExpense` (
  `expense_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblExpense`
--

INSERT INTO `tblExpense` (`expense_id`, `user_id`, `category_id`, `amount`, `expense_date`, `description`, `is_deleted`) VALUES
(5, 2, 11, 3000.00, '2026-05-06', 'Hey', 0),
(6, 4, 13, 500.00, '2026-05-02', 'Momo', 0),
(7, 4, 14, 900.00, '2026-05-07', 'Berlin', 0),
(8, 4, 15, 900.00, '2026-05-03', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tblReport`
--

CREATE TABLE `tblReport` (
  `report_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `report_name` varchar(100) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `generated_date` date NOT NULL,
  `is_exported` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblReport`
--

INSERT INTO `tblReport` (`report_id`, `user_id`, `report_name`, `date_from`, `date_to`, `generated_date`, `is_exported`) VALUES
(1, 4, 'MYReport', '2026-04-01', '2026-05-07', '2026-05-07', 1),
(3, 2, 'VOXY', '2026-05-01', '2026-05-31', '2026-05-08', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tblUser`
--

CREATE TABLE `tblUser` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `role` varchar(10) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblUser`
--

INSERT INTO `tblUser` (`user_id`, `full_name`, `email`, `password_hash`, `created_date`, `is_active`, `role`) VALUES
(1, 'Admin', 'admin@smartspend.com', '$2y$10$v42bexkNHYWICalS2Qdl7uI.8dSjm8jp4xfx7TpJKWWqzDgPbQG1K', '2026-04-30', 1, 'admin'),
(2, 'Shreeman Bhandari', 'shreeman@gmail.com', '$2y$10$URdhsbh1Tn0A4i.dpMWlOuxo2Pb3ECfXoRzeZtrKlSymnsIwcuGmq', '2026-05-01', 1, 'user'),
(3, 'Nandan Yadav', 'nandan@gmail.com', '$2y$10$q2gwYw.C.93zUa3RoYjiZ.vrhfqh9IbPrQwQk6TAvg00ZFhuZladi', '2026-05-01', 1, 'user'),
(4, 'Bibek Timsena', 'bibek@gmail.com', '$2y$10$mCcWCIkchvEujUP2mG0Db.74I5IZusStUjMUgSZCqrmbPVXGeZtMy', '2026-05-01', 1, 'user'),
(5, 'Ratnesh', 'rat@gmail.com', '$2y$10$pPI4PxrAnOFlpOdTfljYhe7kRXDFOsW2/71YUbAw1bfyiygvTlWYS', '2026-05-01', 0, 'user'),
(6, 'Shreeman', 'shree@out.com', '$2y$10$F6ko9uEPukldouZLvPx1h.NwNrKLyT4u6V9rLEw5rqkqDGHoblpcO', '2026-05-06', 1, 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblAuditLog`
--
ALTER TABLE `tblAuditLog`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tblauditlog_ibfk_2` (`expense_id`);

--
-- Indexes for table `tblCategory`
--
ALTER TABLE `tblCategory`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `idx_cat_user` (`category_name`,`user_id`),
  ADD KEY `fk_cat_user` (`user_id`);

--
-- Indexes for table `tblExpense`
--
ALTER TABLE `tblExpense`
  ADD PRIMARY KEY (`expense_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `tblReport`
--
ALTER TABLE `tblReport`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tblUser`
--
ALTER TABLE `tblUser`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblAuditLog`
--
ALTER TABLE `tblAuditLog`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `tblCategory`
--
ALTER TABLE `tblCategory`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tblExpense`
--
ALTER TABLE `tblExpense`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tblReport`
--
ALTER TABLE `tblReport`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tblUser`
--
ALTER TABLE `tblUser`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblAuditLog`
--
ALTER TABLE `tblAuditLog`
  ADD CONSTRAINT `tblauditlog_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tblUser` (`user_id`),
  ADD CONSTRAINT `tblauditlog_ibfk_2` FOREIGN KEY (`expense_id`) REFERENCES `tblExpense` (`expense_id`) ON DELETE SET NULL;

--
-- Constraints for table `tblCategory`
--
ALTER TABLE `tblCategory`
  ADD CONSTRAINT `fk_cat_user` FOREIGN KEY (`user_id`) REFERENCES `tblUser` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `tblExpense`
--
ALTER TABLE `tblExpense`
  ADD CONSTRAINT `tblexpense_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tblUser` (`user_id`),
  ADD CONSTRAINT `tblexpense_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `tblCategory` (`category_id`);

--
-- Constraints for table `tblReport`
--
ALTER TABLE `tblReport`
  ADD CONSTRAINT `tblreport_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tblUser` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
