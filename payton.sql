-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 28, 2026 at 05:07 PM
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
  `budget_name` varchar(255) NOT NULL,
  `budget_amount` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `user_id` int(11) NOT NULL,
  `sponsor_id` int(11) NOT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `budget`
--

INSERT INTO `budget` (`id`, `budget_name`, `budget_amount`, `start_date`, `end_date`, `user_id`, `sponsor_id`, `status`, `created_at`, `updated_at`) VALUES
(35, 'March 10-20', 1000.00, '2026-03-10', '2026-03-20', 1, 2, 'Inactive', '2026-03-11 10:07:19', '2026-03-12 04:42:36'),
(38, 'March 1-31', 10000.00, '2026-03-01', '2026-03-31', 5, 2, 'Active', '2026-03-12 05:39:40', '2026-03-12 05:39:40'),
(39, 'March 25-30', 501.00, '2026-03-25', '2026-03-30', 1, 2, 'Active', '2026-03-20 17:08:26', '2026-03-20 17:08:26'),
(41, 'February 1 - 15', 400.00, '2026-02-01', '2026-02-15', 1, 2, 'Inactive', '2026-02-14 02:14:43', '2026-02-14 02:14:43');

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
(5, 'Personal Care'),
(6, 'Education'),
(7, 'Entertainment'),
(8, 'Shopping'),
(9, 'Savings'),
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
(58, 1, 35, 1, 'Jollibee', 100.00, 1, NULL, '2026-03-11', '2026-03-11 10:07:43', '2026-03-11 10:07:43'),
(59, 1, 35, 2, 'Gas Motor', 150.00, 1, NULL, '2026-03-12', '2026-03-12 05:41:04', '2026-03-12 05:41:04'),
(60, 1, 35, 1, 'Chowking', 200.00, 1, 'uploads/69b2d4381216b-receipt-768x992.jpg', '2026-03-12', '2026-03-12 05:41:16', '2026-03-12 14:56:56'),
(61, 5, 38, 4, 'Water Bill', 143.00, 1, NULL, '2026-03-12', '2026-03-12 05:44:06', '2026-03-12 05:44:35'),
(62, 5, 38, 1, 'Mcdo', 149.00, 1, NULL, '2026-03-12', '2026-03-12 05:44:49', '2026-03-12 05:44:49'),
(63, 5, 38, 2, 'Minglanilla - SanFernando', 25.00, 1, NULL, '2026-03-12', '2026-03-12 05:45:19', '2026-03-12 06:10:22'),
(64, 1, 35, 4, 'Internet Bill', 199.00, 7, NULL, '2026-03-12', '2026-03-12 06:10:50', '2026-03-12 14:41:35'),
(65, 1, 35, 10, 'Sample', 10.00, 1, NULL, '2026-03-12', '2026-03-12 14:42:26', '2026-03-12 14:42:26'),
(66, 1, 35, 10, 'Sample 2', 10.00, 1, NULL, '2026-03-12', '2026-03-12 14:42:42', '2026-03-12 14:42:42'),
(67, 1, 39, 1, 'Mang Inasal', 126.00, 1, NULL, '2026-03-25', '2026-03-24 17:09:54', '2026-03-28 15:08:52'),
(76, 1, 35, 1, 'Mang Inasal', 100.00, 1, NULL, '2026-03-15', '2026-03-15 13:19:24', '2026-03-15 13:19:24'),
(77, 1, 35, 1, 'Snack', 50.00, 1, NULL, '2026-03-15', '2026-03-15 13:24:15', '2026-03-15 13:24:15'),
(79, 1, 35, 1, 'Softdrink', 25.00, 1, NULL, '2026-03-15', '2026-03-15 13:51:26', '2026-03-15 13:51:26'),
(83, 1, 35, 1, 'Jollibee', 10.00, 1, NULL, '2026-03-16', '2026-03-16 04:39:29', '2026-03-16 04:39:29'),
(84, 1, 41, 1, 'Chicken Joy', 99.00, 1, NULL, '2026-02-11', '2026-02-11 02:15:22', '2026-02-11 02:15:22'),
(85, 1, 41, 1, 'Taxi', 200.00, 1, NULL, '2026-02-11', '2026-02-11 02:15:43', '2026-02-11 02:15:43'),
(86, 1, 35, 8, 'Purchased items from SM Department Store', 20.00, 1, NULL, '2026-03-19', '2026-03-18 16:24:35', '2026-03-18 16:24:35'),
(87, 5, 38, 2, 'Nagpa Gas ko sa Shell sa akong motor', 86.00, 1, NULL, '2026-03-24', '2026-03-24 06:55:59', '2026-03-24 06:55:59'),
(90, 5, 38, 1, 'Chowking', 300.00, 1, NULL, '2026-03-25', '2026-03-25 14:23:17', '2026-03-25 14:23:17');

-- --------------------------------------------------------

--
-- Table structure for table `expense_shares`
--

CREATE TABLE `expense_shares` (
  `id` int(11) NOT NULL,
  `expense_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `people_id` int(11) NOT NULL,
  `amount_owed` decimal(10,2) NOT NULL,
  `status` enum('unpaid','paid') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expense_shares`
--

INSERT INTO `expense_shares` (`id`, `expense_id`, `user_id`, `people_id`, `amount_owed`, `status`, `created_at`) VALUES
(11, 76, 1, 1, 25.00, 'paid', '2026-03-15 13:19:24'),
(12, 76, 1, 2, 25.00, 'paid', '2026-03-15 13:19:24'),
(13, 76, 1, 3, 25.00, 'unpaid', '2026-03-15 13:19:24'),
(14, 77, 1, 5, 16.67, 'unpaid', '2026-03-15 13:24:15'),
(15, 77, 1, 4, 16.67, 'paid', '2026-03-15 13:24:15'),
(18, 79, 1, 1, 10.00, 'unpaid', '2026-03-15 13:51:26'),
(19, 79, 1, 4, 10.00, 'unpaid', '2026-03-15 13:51:26'),
(29, 90, 5, 14, 50.00, 'paid', '2026-03-25 14:23:17'),
(30, 90, 5, 16, 150.00, 'unpaid', '2026-03-25 14:23:17');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `action`, `created_at`) VALUES
(10, 1, 'Lawrence Sumbi Logged in As Spender', '2026-03-28 22:53:45'),
(11, 1, 'Lawrence Sumbi Updated the Expense: Mang Inasal to ?126.00', '2026-03-28 23:08:52'),
(12, 1, 'Lawrence Sumbi Logged in As Spender', '2026-03-28 23:28:55'),
(13, 1, 'Lawrence Sumbi Deleted a Scheduled Payment: ', '2026-03-28 23:29:10'),
(14, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-03-28 23:49:01'),
(15, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: March 25-30', '2026-03-28 23:50:27'),
(16, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-03-29 00:01:58'),
(17, 1, 'Lawrence Sumbi Logged in As Spender', '2026-03-29 00:02:14');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `type` enum('invite','info') NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `parent_id`, `type`, `message`, `status`, `created_at`) VALUES
(5, 1, 2, 'invite', 'You have been invited by Patricia Ann Mae Obaob. Click accept to join.', 'read', '2026-03-08 14:40:39'),
(6, 5, 2, 'invite', 'You have been invited by Patricia Ann Mae Obaob. Click accept to join.', 'read', '2026-03-11 09:36:00'),
(7, 4, 2, 'invite', 'You have been invited by Patricia Ann Mae Obaob. Click accept to join.', 'read', '2026-03-14 04:31:29'),
(8, 6, 10, 'invite', 'You have been invited by sample4. Click accept to join.', 'read', '2026-03-25 01:03:21'),
(9, 6, 10, 'invite', 'You have been invited by sample4. Click accept to join.', 'read', '2026-03-25 01:08:11'),
(10, 6, 10, 'invite', 'You have been invited by sample4. Click accept to join.', 'read', '2026-03-25 01:36:09'),
(11, 6, 10, 'invite', 'You have been invited by sample4. Click accept to join.', 'read', '2026-03-25 02:16:24'),
(12, 6, 10, 'invite', 'You have been invited by sample4. Click accept to join.', 'read', '2026-03-25 02:40:03'),
(14, 6, 10, 'invite', 'You have been invited by sample4. Click accept to join.', 'read', '2026-03-25 02:41:15'),
(16, 6, 10, 'invite', 'You have been invited by sample4. Click accept to join.', 'read', '2026-03-25 02:45:46'),
(17, 6, 10, 'invite', 'You have been invited by sample4. Click accept to join.', 'read', '2026-03-25 02:46:13'),
(18, 10, 6, '', 'sample1 has accepted your invitation and is now linked to your account.', 'read', '2026-03-25 02:46:18'),
(19, 4, 2, 'invite', 'You have been invited by Patricia Ann Mae Obaob. Click accept to join.', 'unread', '2026-03-26 09:58:08'),
(20, 1, 2, 'invite', 'You have been invited by Patricia Ann Mae Obaob. Click accept to join.', 'read', '2026-03-27 13:55:32'),
(21, 2, 1, '', 'Lawrence Sumbi has accepted your invitation and is now linked to your account.', 'read', '2026-03-27 13:55:55');

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
-- Table structure for table `people`
--

CREATE TABLE `people` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `people`
--

INSERT INTO `people` (`id`, `user_id`, `name`, `created_at`) VALUES
(1, 1, 'Dray Misa', '2026-03-15 12:36:14'),
(2, 1, 'Jaylon Mantillas', '2026-03-15 12:39:38'),
(3, 1, 'Jaymaica Narvasa', '2026-03-15 12:59:43'),
(4, 1, 'Jay Cabatuan', '2026-03-15 13:23:43'),
(5, 1, 'Ivan Laluna', '2026-03-15 13:23:50'),
(7, 1, 'Sample', '2026-03-24 05:00:58'),
(8, 1, 'Sample2', '2026-03-24 06:59:13'),
(9, 1, 'Sample3', '2026-03-25 13:54:22'),
(13, 5, 'Emman', '2026-03-25 14:19:40'),
(14, 5, 'Lenzey', '2026-03-25 14:19:45'),
(15, 5, 'Mary Divine', '2026-03-25 14:19:52'),
(16, 5, 'Lloyd Junrex', '2026-03-25 14:22:48');

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
(11, 1, 'Load', 100.00, '2026-02-21', NULL, NULL, 3, '2026-02-21 12:49:21', '2026-02-21 12:49:21'),
(13, 1, 'sample', 100.00, '2026-02-20', '2026-02-14', 1, 2, '2026-02-21 12:53:24', '2026-02-21 12:53:24'),
(14, 1, '25', 100.00, '2026-02-27', NULL, NULL, 3, '2026-02-21 12:54:53', '2026-02-21 12:54:53'),
(15, 1, 'data', 40.00, '2026-02-21', '2026-02-22', 1, 2, '2026-02-21 12:55:05', '2026-02-21 12:55:05'),
(16, 1, '28', 28.00, '2026-02-28', '2026-02-21', 1, 2, '2026-02-21 13:00:07', '2026-02-21 13:00:07'),
(19, 1, 'Loklok', 149.00, '2026-02-22', NULL, NULL, 3, '2026-02-21 14:45:12', '2026-02-21 14:45:12'),
(20, 1, '280', 280.00, '2026-02-28', '2026-02-22', 2, 2, '2026-02-21 15:11:01', '2026-02-21 15:11:01'),
(22, 1, '4', 4.00, '2026-02-04', NULL, NULL, 3, '2026-02-22 04:45:24', '2026-02-22 04:45:24'),
(24, 1, 'sample', 100.00, '2026-03-01', '2026-02-22', 1, 2, '2026-02-22 08:09:48', '2026-02-22 08:09:48'),
(29, 1, 'Water Bill', 143.00, '2026-03-28', NULL, NULL, 1, '2026-03-11 10:09:32', '2026-03-11 10:09:32'),
(31, 1, 'Sample', 10.00, '2026-03-25', NULL, NULL, 1, '2026-03-12 15:25:57', '2026-03-12 15:25:57'),
(32, 1, '16', 160.00, '2026-03-16', NULL, NULL, 1, '2026-03-14 18:17:45', '2026-03-14 18:17:45');

-- --------------------------------------------------------

--
-- Table structure for table `sponsor_spender`
--

CREATE TABLE `sponsor_spender` (
  `id` int(11) NOT NULL,
  `sponsor_id` int(11) NOT NULL,
  `spender_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sponsor_spender`
--

INSERT INTO `sponsor_spender` (`id`, `sponsor_id`, `spender_id`, `created_at`) VALUES
(5, 2, 5, '2026-03-11 09:37:00'),
(12, 10, 6, '2026-03-25 02:46:18'),
(13, 2, 1, '2026-03-27 13:55:55');

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
  `verification_code` varchar(6) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `role`, `profile_pic`, `verification_code`, `is_verified`, `reset_token`, `reset_expiry`, `created_at`) VALUES
(1, 'Lawrence Sumbi', 'guiansumbi@gmail.com', '$2y$10$cKIACjgglVTrRjDZaKSZjulwkdA0CIDUwOBkU12h2PPGKK03U0aT6', 'spender', 'profile/1773301216_69b26de0c5031.jpg', NULL, 1, NULL, NULL, '2026-02-07 17:08:23'),
(2, 'Patricia Ann Mae Obaob', 'patriciaannmaeobaob721@gmail.com', '$2y$10$gVokRQej23KaxSKKUZPiSOn/mL5IE0kvfoGyPRTZfN1in/ZpNAQku', 'sponsor', 'profile/1773380531_69b3a3b3f074d.jpg', NULL, 1, NULL, NULL, '2026-02-08 11:52:14'),
(3, 'Dranreb Misa', 'draymisa@gmail.com', '$2y$10$Gw3YeLEMfsCIOPV3xFs5h.jClSQLC9rilddvuzZ063CceY9/IVgue', 'spender', '', NULL, 1, NULL, NULL, '2026-02-09 00:03:22'),
(4, 'Aljon Paragoso', 'aljon@gmail.com', '$2y$10$Wt8Xf9aFRGG6zRdmdsfP1.bzpQS9xPfN/20Rsf.l7gb5ivx7H.t8u', 'spender', '', NULL, 1, NULL, NULL, '2026-02-10 02:32:42'),
(5, 'King James', 'king@gmail.com', '$2y$10$gEXzRZe1Yx1W/lVHDmk8ju/S//8ksu6iLCjJaJyxXhy3lWOLBBTw6', 'spender', '', NULL, 1, NULL, NULL, '2026-02-23 08:07:35'),
(6, 'sample1', 'sample1@gmail.com', '$2y$10$XkOSorJMOiPKBmZhqqGwWuRJKdGV5PzCQo3XrueMu3cK8GnHfyOJy', 'spender', 'profile/1773497217_69b56b816f5b2.jpg', NULL, 1, NULL, NULL, '2026-03-14 13:30:53'),
(7, 'Sponsor User', 'sponsor@gmail.com', '$2y$10$Lr9hm92Iha4J2va9tH7XYOhEiGgscf6qmvHn8oZ16WKrGTBtrsEI6', 'sponsor', '', NULL, 1, NULL, NULL, '2026-03-14 16:24:13'),
(8, 'sample', 'sample2@gmail.com', '$2y$10$COoyyUVfJ8VQcPRk6Kf78eXV1YMs./Xs2zIct38PqfacTCewGRKoe', 'spender', '', NULL, 1, NULL, NULL, '2026-03-24 01:27:28'),
(9, 'sample3', 'sample3@gmail.com', '$2y$10$W.Q5X/WGz2KLXTv.c59FxeAwfrhvKU4PQbZTPXP/AMkx8r9Icv1qS', '', '', NULL, 1, NULL, NULL, '2026-03-25 00:05:13'),
(10, 'sample4', 'sample4@gmail.com', '$2y$10$kVcs8DgHEugTzGtvsaRiSOi7ZgTxkEE7qHPOGQD/1WYc5FOrpObru', 'sponsor', 'profile/1774416092_69c370dcc7ae4.jpg', NULL, 1, NULL, NULL, '2026-03-25 00:20:48'),
(11, 'sample5', 'sample5@gmail.com', '$2y$10$JqvDRqR2nlCJsG1IZqvclO444PsfqvzEuUmD8P8CCKzJLZD9asD9W', '', '', NULL, 1, NULL, NULL, '2026-03-25 00:22:06'),
(22, 'Rowena Sumbi', 'rowenasumbi5@gmail.com', '$2y$10$tz.XeuMiVTzrEkxx4NAk6uLFePdLZdIKjccl9dYi7vK78x3F99s1e', 'sponsor', '', NULL, 1, NULL, NULL, '2026-03-26 13:08:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `budget`
--
ALTER TABLE `budget`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sponsor_id` (`sponsor_id`);

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
-- Indexes for table `expense_shares`
--
ALTER TABLE `expense_shares`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expense_id` (`expense_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `people_id` (`people_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `payment_method`
--
ALTER TABLE `payment_method`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `people`
--
ALTER TABLE `people`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `scheduled_payments`
--
ALTER TABLE `scheduled_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `due_status_id` (`due_status_id`),
  ADD KEY `payment_method_id` (`payment_method_id`);

--
-- Indexes for table `sponsor_spender`
--
ALTER TABLE `sponsor_spender`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sponsor_id` (`sponsor_id`),
  ADD KEY `spender_id` (`spender_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `expense_shares`
--
ALTER TABLE `expense_shares`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `payment_method`
--
ALTER TABLE `payment_method`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `people`
--
ALTER TABLE `people`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `scheduled_payments`
--
ALTER TABLE `scheduled_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `sponsor_spender`
--
ALTER TABLE `sponsor_spender`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `budget`
--
ALTER TABLE `budget`
  ADD CONSTRAINT `budget_sponsor_id` FOREIGN KEY (`sponsor_id`) REFERENCES `users` (`id`),
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
-- Constraints for table `expense_shares`
--
ALTER TABLE `expense_shares`
  ADD CONSTRAINT `expense_shares_expense_id_fr` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `expense_shares_people_id_fr` FOREIGN KEY (`people_id`) REFERENCES `people` (`id`),
  ADD CONSTRAINT `expense_shares_user_id_fr` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_user_id_fr` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_parent_id_fr` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `notifications_user_id_fr` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `people`
--
ALTER TABLE `people`
  ADD CONSTRAINT `people_user_id_fr` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `scheduled_payments`
--
ALTER TABLE `scheduled_payments`
  ADD CONSTRAINT `scheduled_payments_due_status_id_fr` FOREIGN KEY (`due_status_id`) REFERENCES `due_status` (`id`),
  ADD CONSTRAINT `scheduled_payments_payment_method_id_fr` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`),
  ADD CONSTRAINT `scheduled_payments_user_id_fr` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `sponsor_spender`
--
ALTER TABLE `sponsor_spender`
  ADD CONSTRAINT `sponsor_spender_spender_id_fr` FOREIGN KEY (`spender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `sponsor_spender_sponsor_id_fr` FOREIGN KEY (`sponsor_id`) REFERENCES `users` (`id`);

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
