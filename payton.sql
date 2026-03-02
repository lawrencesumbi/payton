-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 02, 2026 at 06:49 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `payton`
--

-- --------------------------------------------------------

--
-- Table structure for table `budget`
--

CREATE TABLE `budget` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `budget_name` varchar(255) NOT NULL,
  `budget_amount` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `budget`
--

INSERT INTO `budget` (`id`, `user_id`, `budget_name`, `budget_amount`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(15, 1, 'February 1-7', 1000.00, '2026-02-01', '2026-02-07', 'Inactive', '2026-02-06 13:33:19', '2026-02-06 13:33:19'),
(16, 1, 'February 8 - 14', 2000.00, '2026-02-08', '2026-02-14', 'Inactive', '2026-02-08 13:38:22', '2026-02-08 13:38:22'),
(22, 1, 'February 15 - 20', 500.00, '2026-02-15', '2026-02-20', 'Inactive', '2026-02-20 00:47:40', '2026-02-20 00:47:40'),
(23, 1, 'March 2 - 10', 1000.00, '2026-03-02', '2026-03-10', 'Active', '2026-03-01 16:52:40', '2026-03-01 16:52:40');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `category_name`) VALUES
(1, 'Food & Dining'),
(2, 'Transportation'),
(3, 'Housing / Rent'),
(4, 'Bills & Utilities'),
(5, 'Health & Personal Care'),
(6, 'Education'),
(7, 'Entertainment & Leisure'),
(8, 'Shopping'),
(9, 'Savings & Investments'),
(10, 'Miscellaneous');

-- --------------------------------------------------------

--
-- Table structure for table `due_status`
--

CREATE TABLE `due_status` (
  `id` int(11) NOT NULL,
  `due_status_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `due_status`
--

INSERT INTO `due_status` (`id`, `due_status_name`) VALUES
(1, 'unpaid'),
(2, 'paid'),
(3, 'overdue');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `budget_id` int(11) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method_id` int(11) NOT NULL,
  `receipt_upload` varchar(255) DEFAULT NULL,
  `expense_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `user_id`, `budget_id`, `category_id`, `description`, `amount`, `payment_method_id`, `receipt_upload`, `expense_date`, `created_at`, `updated_at`) VALUES
(27, 1, 15, 1, 'Jollibee', 156.00, 1, NULL, '2026-02-06', '2026-02-06 13:33:47', '2026-02-06 13:33:47'),
(28, 1, 15, 1, 'Burger', 44.00, 1, NULL, '2026-02-06', '2026-02-06 13:34:24', '2026-02-06 13:34:24'),
(29, 1, 15, 2, 'Gasoline', 800.00, 1, NULL, '2026-02-06', '2026-02-06 13:34:57', '2026-02-06 13:34:57'),
(30, 1, 16, 1, 'Manok', 100.00, 1, NULL, '2026-02-08', '2026-02-08 13:39:24', '2026-02-08 13:39:24'),
(31, 1, 16, 10, 'Subscription', 199.00, 1, NULL, '2026-02-08', '2026-02-08 13:50:55', '2026-02-08 13:50:55'),
(32, 1, 16, 9, 'Savings', 500.00, 1, NULL, '2026-02-08', '2026-02-08 13:53:49', '2026-02-08 13:53:49'),
(33, 1, 16, 2, 'Motor Parts', 201.00, 1, NULL, '2026-02-08', '2026-02-08 14:57:06', '2026-02-08 14:57:06'),
(34, 1, 23, 10, 'Mang Inasal', 300.00, 1, NULL, '2026-03-02', '2026-03-02 01:17:47', '2026-03-02 01:17:47'),
(36, 1, 23, 2, 'Gas', 100.00, 1, NULL, '2026-03-02', '2026-03-02 01:56:26', '2026-03-02 01:56:26'),
(37, 1, 23, 1, 'Jollibee', 10.00, 1, NULL, '2026-03-02', '2026-03-02 03:30:18', '2026-03-02 03:30:18'),
(38, 1, 23, 1, 'Mcdo', 10.00, 1, NULL, '2026-03-02', '2026-03-02 03:30:25', '2026-03-02 03:30:25'),
(39, 1, 23, 10, 'Chowking', 10.00, 1, 'uploads/69a504e21d7a8-receipt-768x992.jpg', '2026-03-02', '2026-03-02 03:30:34', '2026-03-02 03:32:50'),
(40, 1, 23, 1, 'Ngohiong', 10.00, 1, NULL, '2026-03-02', '2026-03-02 03:30:45', '2026-03-02 03:30:45'),
(41, 1, 23, 10, 'Lumpia', 10.00, 1, NULL, '2026-03-02', '2026-03-02 03:30:54', '2026-03-02 03:30:54'),
(42, 1, 23, 1, 'Angels Burger', 10.00, 1, NULL, '2026-03-02', '2026-03-02 03:34:42', '2026-03-02 03:34:42'),
(43, 1, 23, 1, 'Hotdog', 10.00, 1, NULL, '2026-03-02', '2026-03-02 03:46:32', '2026-03-02 03:46:32'),
(44, 1, 23, 7, 'Roblox', 30.00, 4, NULL, '2026-03-02', '2026-03-02 04:46:26', '2026-03-02 04:46:26');

-- --------------------------------------------------------

--
-- Table structure for table `payment_method`
--

CREATE TABLE `payment_method` (
  `id` int(11) NOT NULL,
  `payment_method_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_method`
--

INSERT INTO `payment_method` (`id`, `payment_method_name`) VALUES
(1, 'Cash'),
(2, 'Debit Card'),
(3, 'Credit Card'),
(4, 'GCash'),
(5, 'Maya / Paymaya'),
(6, 'Bank Transfer'),
(7, 'Online Payment'),
(8, 'Check');

-- --------------------------------------------------------

--
-- Table structure for table `scheduled_payments`
--

CREATE TABLE `scheduled_payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `paid_date` date DEFAULT NULL,
  `payment_method_id` int(11) DEFAULT NULL,
  `due_status_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scheduled_payments`
--

INSERT INTO `scheduled_payments` (`id`, `user_id`, `payment_name`, `amount`, `due_date`, `paid_date`, `payment_method_id`, `due_status_id`, `created_at`, `updated_at`) VALUES
(10, 1, 'Tuition', 2000.00, '2026-02-14', '2026-02-19', 5, 2, '2026-02-21 12:48:51', '2026-02-21 12:48:51'),
(11, 1, 'Load', 100.00, '2026-02-21', NULL, NULL, 3, '2026-02-21 12:49:21', '2026-02-21 12:49:21'),
(13, 1, 'sample', 100.00, '2026-02-20', '2026-02-14', 1, 2, '2026-02-21 12:53:24', '2026-02-21 12:53:24'),
(14, 1, '25', 100.00, '2026-02-27', NULL, NULL, 3, '2026-02-21 12:54:53', '2026-02-21 12:54:53'),
(15, 1, 'data', 40.00, '2026-02-21', '2026-02-22', 1, 2, '2026-02-21 12:55:05', '2026-02-21 12:55:05'),
(16, 1, '28', 28.00, '2026-02-28', '2026-02-21', 1, 2, '2026-02-21 13:00:07', '2026-02-21 13:00:07'),
(19, 1, 'Loklok', 149.00, '2026-02-22', NULL, NULL, 3, '2026-02-21 14:45:12', '2026-02-21 14:45:12'),
(20, 1, '280', 280.00, '2026-02-28', '2026-02-22', 2, 2, '2026-02-21 15:11:01', '2026-02-21 15:11:01'),
(22, 1, '4', 4.00, '2026-02-04', NULL, NULL, 3, '2026-02-22 04:45:24', '2026-02-22 04:45:24'),
(24, 1, 'sample', 100.00, '2026-03-01', '2026-02-22', 1, 2, '2026-02-22 08:09:48', '2026-02-22 08:09:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `profile_pic` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `role`, `profile_pic`, `created_at`) VALUES
(1, 'Lawrence Sumbi', 'guiansumbi@gmail.com', '$2y$10$cKIACjgglVTrRjDZaKSZjulwkdA0CIDUwOBkU12h2PPGKK03U0aT6', 'spender', 'profile/1771773625_699b1eb98867d.jpg', '2026-02-07 17:08:23'),
(2, 'Patricia Ann Mae Obaob', 'patriciaannmaeobaob721@gmail.com', '$2y$10$gVokRQej23KaxSKKUZPiSOn/mL5IE0kvfoGyPRTZfN1in/ZpNAQku', 'sponsor', '', '2026-02-08 11:52:14'),
(3, 'Dranreb Misa', 'draymisa@gmail.com', '$2y$10$Gw3YeLEMfsCIOPV3xFs5h.jClSQLC9rilddvuzZ063CceY9/IVgue', 'spender', '', '2026-02-09 00:03:22'),
(4, 'Aljon Paragoso', 'aljon@gmail.com', '$2y$10$Wt8Xf9aFRGG6zRdmdsfP1.bzpQS9xPfN/20Rsf.l7gb5ivx7H.t8u', 'spender', '', '2026-02-10 02:32:42'),
(5, 'King James', 'king@gmail.com', '$2y$10$gEXzRZe1Yx1W/lVHDmk8ju/S//8ksu6iLCjJaJyxXhy3lWOLBBTw6', 'spender', '', '2026-02-23 08:07:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `budget`
--
ALTER TABLE `budget`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `due_status`
--
ALTER TABLE `due_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `description` (`description`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `payment_method_id` (`payment_method_id`),
  ADD KEY `budget_id` (`budget_id`);

--
-- Indexes for table `payment_method`
--
ALTER TABLE `payment_method`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scheduled_payments`
--
ALTER TABLE `scheduled_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `due_status_id` (`due_status_id`),
  ADD KEY `payment_method_id` (`payment_method_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `budget`
--
ALTER TABLE `budget`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `due_status`
--
ALTER TABLE `due_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `payment_method`
--
ALTER TABLE `payment_method`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `scheduled_payments`
--
ALTER TABLE `scheduled_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `budget`
--
ALTER TABLE `budget`
  ADD CONSTRAINT `budget_user_id_fr` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_budget_id_fr` FOREIGN KEY (`budget_id`) REFERENCES `budget` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `expenses_category_id_fr` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`),
  ADD CONSTRAINT `expenses_payment_method_id_fr` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`),
  ADD CONSTRAINT `expenses_user_id_fr` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `scheduled_payments`
--
ALTER TABLE `scheduled_payments`
  ADD CONSTRAINT `scheduled_payments_due_status_id_fr` FOREIGN KEY (`due_status_id`) REFERENCES `due_status` (`id`),
  ADD CONSTRAINT `scheduled_payments_payment_method_id_fr` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`),
  ADD CONSTRAINT `scheduled_payments_user_id_fr` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `auto_mark_overdue` ON SCHEDULE EVERY 1 DAY STARTS '2026-02-23 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE scheduled_payments
SET due_status_id = 3
WHERE paid_date IS NULL
AND due_date < CURDATE()$$

CREATE DEFINER=`root`@`localhost` EVENT `update_budget_status` ON SCHEDULE EVERY 1 DAY STARTS '2026-02-08 22:29:03' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE budget
    SET status = 'Inactive'
    WHERE end_date < CURDATE()
      AND status != 'Inactive'$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
