-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2026 at 08:00 AM
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
(38, 'March 1-31', 1000.00, '2026-03-01', '2026-03-31', 5, 2, 'Inactive', '2026-03-12 05:39:40', '2026-03-12 05:39:40'),
(39, 'March 25-30', 501.00, '2026-03-25', '2026-03-30', 1, 2, 'Inactive', '2026-03-20 17:08:26', '2026-03-20 17:08:26'),
(41, 'February 1 - 15', 400.00, '2026-02-01', '2026-02-15', 1, 2, 'Inactive', '2026-02-14 02:14:43', '2026-02-14 02:14:43'),
(43, 'April 1st Week', 1000.00, '2026-04-01', '2026-04-04', 1, 2, 'Inactive', '2026-04-01 12:41:34', '2026-04-01 12:41:34'),
(46, 'April 2nd Week', 1500.00, '2026-04-05', '2026-04-11', 1, 2, 'Inactive', '2026-04-07 15:21:00', '2026-04-07 15:21:00'),
(47, 'Allowance ni Aljon', 5000.00, '2026-04-01', '2026-04-30', 4, 2, 'Active', '2026-04-16 13:57:20', '2026-04-16 13:57:20'),
(48, 'Budget ni King', 6000.00, '2026-04-01', '2026-04-30', 5, 2, 'Active', '2026-04-16 14:15:09', '2026-04-16 14:15:09'),
(49, 'Budget ni Guian', 2500.00, '2026-04-01', '2026-04-30', 1, 2, 'Active', '2026-04-16 14:15:41', '2026-04-16 14:15:41'),
(50, 'for 1 Week', 3000.00, '2026-04-20', '2026-04-30', 33, 32, 'Active', '2026-04-20 05:17:33', '2026-04-20 05:17:33'),
(51, 'FOr 2 weeks', 6000.00, '2026-05-01', '2026-05-05', 33, 32, 'Inactive', '2026-04-20 05:21:48', '2026-04-20 05:21:48'),
(52, 'Tuition', 1000.00, '2026-05-01', '2026-05-08', 33, 32, 'Inactive', '2026-04-20 05:22:50', '2026-04-20 05:22:50');

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
(83, 1, 35, 1, 'Jollibee', 10.00, 1, NULL, '2026-03-16', '2026-03-16 04:39:29', '2026-03-16 04:39:29'),
(84, 1, 41, 1, 'Chicken Joy', 99.00, 1, NULL, '2026-02-11', '2026-02-11 02:15:22', '2026-02-11 02:15:22'),
(85, 1, 41, 1, 'Taxi', 200.00, 1, NULL, '2026-02-11', '2026-02-11 02:15:43', '2026-02-11 02:15:43'),
(86, 1, 35, 8, 'Purchased items from SM Department Store', 20.00, 1, NULL, '2026-03-19', '2026-03-18 16:24:35', '2026-03-18 16:24:35'),
(87, 5, 38, 2, 'Nagpa Gas ko sa Shell sa akong motor', 86.00, 1, NULL, '2026-03-24', '2026-03-24 06:55:59', '2026-03-24 06:55:59'),
(91, 1, 43, 1, 'Rice', 70.00, 1, NULL, '2026-04-01', '2026-04-01 13:54:19', '2026-04-01 13:54:19'),
(92, 1, 43, 4, 'Internet', 99.00, 3, NULL, '2026-04-02', '2026-04-02 07:31:29', '2026-04-02 07:31:29'),
(93, 1, 43, 2, 'Gas', 50.00, 6, NULL, '2026-04-02', '2026-04-02 07:31:51', '2026-04-02 07:31:51'),
(94, 1, 43, 6, 'Book', 30.00, 5, 'uploads/69ce72088abe9-b811fa4ddaa6e2793d2cb22171686ea2.jpg', '2026-04-02', '2026-04-02 07:32:30', '2026-04-02 13:41:28'),
(95, 1, 43, 2, 'Jollibee', 10.00, 1, 'uploads/1775136547_mtlqh1hgajq91.jpg', '2026-04-02', '2026-04-02 13:29:07', '2026-04-02 13:29:46'),
(97, 1, 43, 10, 'Pacifica Agrivet Supplies, Inc.', 416.00, 1, 'uploads/1775139752_1278048_orig.jpg', '2026-04-02', '2026-04-02 14:22:32', '2026-04-02 14:22:32'),
(99, 1, 46, 1, 'Manok', 100.00, 1, NULL, '2026-04-08', '2026-04-07 16:28:12', '2026-04-07 16:28:12'),
(100, 1, 46, 1, 'Chicken', 300.00, 6, NULL, '2026-04-07', '2026-04-07 16:51:56', '2026-04-07 16:51:56'),
(103, 1, 49, 1, 'sample', 100.00, 1, NULL, '2026-04-18', '2026-04-18 14:36:31', '2026-04-18 14:36:31'),
(104, 1, 49, 5, 'sample 2', 200.00, 5, NULL, '2026-04-18', '2026-04-18 14:36:42', '2026-04-18 14:36:42'),
(105, 1, 49, 1, 'Mang Inasal', 1000.00, 1, NULL, '2026-04-18', '2026-04-18 17:06:38', '2026-04-18 17:06:38'),
(107, 1, 49, 5, 'sample 3', 10.00, 1, NULL, '2026-04-19', '2026-04-19 06:31:49', '2026-04-19 06:31:49'),
(108, 1, 49, 6, 'sample 4', 10.00, 1, NULL, '2026-04-19', '2026-04-19 06:32:02', '2026-04-19 06:32:02'),
(109, 1, 49, 8, 'sample 5', 5.00, 1, NULL, '2026-04-19', '2026-04-19 06:32:28', '2026-04-19 06:32:28'),
(110, 1, 49, 7, 'hi', 5.00, 5, NULL, '2026-04-19', '2026-04-19 07:20:56', '2026-04-19 07:20:56'),
(111, 1, 49, 9, 'hallu', 5.00, 2, NULL, '2026-04-19', '2026-04-19 07:21:36', '2026-04-19 07:21:36'),
(112, 1, 49, 1, 'Malunggay Pandesal', 50.00, 6, NULL, '2026-04-19', '2026-04-19 13:46:38', '2026-04-19 13:46:38'),
(116, 1, 49, 1, 'Angels Burger', 80.00, 1, NULL, '2026-04-20', '2026-04-20 00:53:29', '2026-04-20 00:53:29'),
(119, 33, 50, 4, 'Kuryente', 1564.00, 1, NULL, '2026-04-20', '2026-04-20 05:28:15', '2026-04-20 05:28:15');

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
(33, 99, 1, 1, 0.00, 'paid', '2026-04-07 16:28:12'),
(34, 99, 1, 5, 50.00, 'unpaid', '2026-04-07 16:28:12'),
(35, 100, 1, 2, 100.00, 'unpaid', '2026-04-07 16:51:56'),
(36, 100, 1, 3, 100.00, 'unpaid', '2026-04-07 16:51:56'),
(49, 105, 1, 21, 42.86, 'unpaid', '2026-04-19 03:10:41'),
(50, 105, 1, 1, 0.00, 'paid', '2026-04-19 03:10:41'),
(51, 105, 1, 5, 142.86, 'unpaid', '2026-04-19 03:10:41'),
(52, 105, 1, 4, 142.86, 'unpaid', '2026-04-19 03:10:41'),
(53, 105, 1, 17, 142.86, 'unpaid', '2026-04-19 03:10:41'),
(54, 105, 1, 7, 142.86, 'unpaid', '2026-04-19 03:10:41'),
(55, 112, 1, 3, 20.00, 'unpaid', '2026-04-19 13:46:38'),
(56, 112, 1, 23, 10.00, 'unpaid', '2026-04-19 13:46:38'),
(57, 116, 1, 23, 30.00, 'unpaid', '2026-04-20 00:53:29'),
(58, 116, 1, 24, 30.00, 'unpaid', '2026-04-20 00:53:29'),
(61, 119, 33, 25, 0.00, 'paid', '2026-04-20 05:28:15');

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
(17, 1, 'Lawrence Sumbi Logged in As Spender', '2026-03-29 00:02:14'),
(18, 1, 'Lawrence Sumbi Logged in As Spender', '2026-03-31 21:57:39'),
(19, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-03-31 22:56:29'),
(20, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-03-31 23:00:13'),
(21, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-03-31 23:01:28'),
(22, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-03-31 23:02:32'),
(23, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-03-31 23:11:54'),
(24, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-03-31 23:16:41'),
(25, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-01 14:41:25'),
(26, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-01 14:41:47'),
(27, 7, 'Sponsor User Logged in As Sponsor', '2026-04-01 14:49:43'),
(28, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-01 14:56:11'),
(29, 7, 'Sponsor User Logged in As Sponsor', '2026-04-01 14:59:14'),
(30, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-01 15:42:48'),
(31, 1, 'Lawrence Sumbi Deleted a Scheduled Payment: ', '2026-04-01 19:06:34'),
(32, 1, 'Lawrence Sumbi Deleted a Scheduled Payment: ', '2026-04-01 19:06:36'),
(33, 1, 'Lawrence Sumbi Scheduled a Payment: Holy Thursday (Spender)', '2026-04-01 19:06:45'),
(34, 1, 'Lawrence Sumbi Scheduled a Payment: Holy Friday (Spender)', '2026-04-01 19:17:22'),
(35, 1, 'User Updated: 4', '2026-04-01 20:17:55'),
(36, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-01 20:20:10'),
(37, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-01 20:26:56'),
(38, 1, 'User Updated: Load', '2026-04-01 20:28:29'),
(39, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-01 20:39:39'),
(40, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-01 20:39:57'),
(41, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: April 1st Week Allowance', '2026-04-01 20:41:34'),
(42, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-01 20:41:45'),
(43, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: April 1st Week', '2026-04-01 20:42:25'),
(44, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-01 20:49:08'),
(45, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-01 20:50:19'),
(46, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-01 20:50:43'),
(47, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-01 20:51:36'),
(48, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-01 21:24:05'),
(49, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-01 21:52:46'),
(50, 1, 'Lawrence Sumbi Added an Expense: Rice - ?70.00', '2026-04-01 21:54:19'),
(51, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: April Budget 1stW', '2026-04-02 12:46:51'),
(52, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: Sample', '2026-04-02 12:51:55'),
(53, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-02 12:56:06'),
(54, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-02 12:56:14'),
(55, 5, 'King James Logged in As Spender', '2026-04-02 12:56:32'),
(56, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: Sample', '2026-04-02 12:57:32'),
(57, 2, 'Patricia Ann Mae Obaob Deleted an Allowance: ', '2026-04-02 12:57:41'),
(58, 2, 'Patricia Ann Mae Obaob Deleted an Allowance: ', '2026-04-02 12:57:54'),
(59, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: March 1-31', '2026-03-31 13:14:35'),
(60, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-02 14:01:22'),
(61, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-02 14:01:43'),
(62, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-02 14:44:23'),
(63, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-02 14:45:24'),
(64, 5, 'King James Logged in As Spender', '2026-04-02 14:46:01'),
(65, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-02 14:46:47'),
(66, 1, 'Lawrence Sumbi Added a Person: OA', '2026-04-02 15:16:42'),
(67, 1, 'Lawrence Sumbi Added a Person: HAHAHHAHA', '2026-04-02 15:21:39'),
(68, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-02 15:28:44'),
(69, 1, 'Lawrence Sumbi Added an Expense: Internet - ?99.00', '2026-04-02 15:31:29'),
(70, 1, 'Lawrence Sumbi Added an Expense: Gas - ?50.00', '2026-04-02 15:31:51'),
(71, 1, 'Lawrence Sumbi Added an Expense: Book - ?30.00', '2026-04-02 15:32:30'),
(72, 1, 'Lawrence Sumbi Added an Expense: Jollibee - ?10.00', '2026-04-02 21:29:07'),
(73, 1, 'Lawrence Sumbi Updated the Expense: Jollibee to ?10.00', '2026-04-02 21:29:46'),
(74, 1, 'Lawrence Sumbi Updated the Expense: Book to ?30.00', '2026-04-02 21:41:28'),
(75, 1, 'Lawrence Sumbi Added an Expense: Ventures - ?190.00', '2026-04-02 21:59:09'),
(76, 1, 'Lawrence Sumbi Deleted the Expense: ', '2026-04-02 21:59:12'),
(77, 1, 'Lawrence Sumbi Added an Expense: Pacifica Agrivet Supplies, Inc. - ?416.00', '2026-04-02 22:22:32'),
(78, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-03 19:20:30'),
(79, 1, 'Lawrence Sumbi Scheduled a Payment: Black Saturday (Spender)', '2026-04-03 19:21:02'),
(80, 1, 'Lawrence Sumbi Scheduled a Payment: Lunes (Spender)', '2026-04-03 19:31:45'),
(81, 1, 'Lawrence Sumbi Scheduled a Payment: Martes (Spender)', '2026-04-03 19:54:56'),
(82, 4, 'Aljon Paragoso Logged in As Spender', '2026-04-03 20:47:20'),
(83, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-03 20:48:18'),
(84, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-03 20:49:05'),
(85, 2, 'Patricia Ann Mae Obaob Sent an Invitation:  Sponsor', '2026-04-03 20:57:23'),
(86, 8, 'sample Logged in As Spender', '2026-04-03 20:57:40'),
(87, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-03 20:58:15'),
(88, 2, 'Patricia Ann Mae Obaob Sent an Invitation:  Sponsor', '2026-04-03 20:58:27'),
(89, 2, 'Patricia Ann Mae Obaob Sent an Invitation:  Sponsor', '2026-04-03 20:58:33'),
(90, 8, 'sample Logged in As Spender', '2026-04-03 20:58:41'),
(91, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-03 20:58:58'),
(92, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-03 21:31:30'),
(93, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-03 21:32:39'),
(94, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-03 21:33:12'),
(95, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-03 21:35:17'),
(96, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-03 21:52:26'),
(97, 4, 'Aljon Paragoso Logged in As Spender', '2026-04-03 21:55:27'),
(98, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-03 21:56:05'),
(99, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-03 22:02:06'),
(100, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-03 22:04:09'),
(101, 7, 'Sponsor User Logged in As Sponsor', '2026-04-03 22:05:02'),
(102, 7, 'Sponsor User Sent an Invitation:  Sponsor', '2026-04-03 22:05:14'),
(103, 7, 'Sponsor User Sent an Invitation:  Sponsor', '2026-04-03 22:05:23'),
(104, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-03 22:05:51'),
(105, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-03 22:10:25'),
(106, 7, 'Sponsor User Logged in As Sponsor', '2026-04-03 22:10:48'),
(107, 7, 'Sponsor User Sent an Invitation:  Sponsor', '2026-04-03 22:10:58'),
(108, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-03 22:11:06'),
(109, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-07 12:35:03'),
(110, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-07 12:39:38'),
(111, 2, 'Patricia Ann Mae Obaob Sent an Invitation:  Sponsor', '2026-04-07 12:41:08'),
(112, 3, 'Dranreb Misa Logged in As Spender', '2026-04-07 12:41:27'),
(113, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-07 12:47:59'),
(114, 3, 'Dranreb Misa Logged in As Spender', '2026-04-07 12:49:21'),
(115, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-07 12:51:19'),
(116, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-07 13:08:00'),
(117, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-07 13:09:14'),
(118, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-07 13:11:11'),
(119, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-07 13:12:18'),
(120, 1, 'Lawrence Sumbi Marked an Owe Expense as Paid/Settled:  - ?0.00', '2026-04-07 13:23:02'),
(121, 1, 'Lawrence Sumbi Added a Person: Bag o', '2026-04-07 13:45:51'),
(122, 1, 'Lawrence Sumbi Deleted a Person: ', '2026-04-07 13:48:34'),
(123, 1, 'Lawrence Sumbi Added a Person: Bag o', '2026-04-07 13:49:07'),
(124, 1, 'Lawrence Sumbi Deleted a Person: ', '2026-04-07 13:54:41'),
(125, 1, 'Lawrence Sumbi Added a Person: Bag o', '2026-04-07 13:54:58'),
(126, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-07 22:37:30'),
(127, 4, 'Aljon Paragoso Logged in As Spender', '2026-04-07 22:49:36'),
(128, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-07 22:52:53'),
(129, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-07 23:20:30'),
(130, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: April 2nd Week', '2026-04-07 23:21:00'),
(131, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-07 23:21:15'),
(132, 1, 'Lawrence Sumbi Added or Updated an Expense: Manok - ?100.00', '2026-04-07 23:41:15'),
(133, 1, 'Lawrence Sumbi settled ?20.00 for \'Manok\'. Remaining: ?30.00', '2026-04-08 00:23:34'),
(134, 1, 'Lawrence Sumbi settled ?10.00 for \'Manok\'. Remaining: ?20.00', '2026-04-08 00:24:10'),
(135, 1, 'Lawrence Sumbi Deleted an Expense:  - ?0.00', '2026-04-08 00:26:50'),
(136, 1, 'Lawrence Sumbi Added or Updated an Expense: Manok - ?100.00', '2026-04-08 00:28:12'),
(137, 1, 'Lawrence Sumbi settled ?20.00 for \'Manok\'. Remaining: ?30.00', '2026-04-08 00:47:21'),
(138, 1, 'Lawrence Sumbi settled ?30.00 for \'Manok\'. Remaining: ?0.00', '2026-04-08 00:48:12'),
(139, 1, 'Lawrence Sumbi Deleted an Expense:  - ?0.00', '2026-04-08 00:48:27'),
(140, 1, 'Lawrence Sumbi Deleted an Expense:  - ?0.00', '2026-04-08 00:48:29'),
(141, 1, 'Lawrence Sumbi Deleted an Expense:  - ?0.00', '2026-04-08 00:48:31'),
(142, 5, 'King James Logged in As Spender', '2026-04-08 00:49:04'),
(143, 5, 'King James Deleted an Expense:  - ?0.00', '2026-04-08 00:49:22'),
(144, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-08 00:49:31'),
(145, 1, 'Lawrence Sumbi Added or Updated an Expense: Chicken - ?300.00', '2026-04-08 00:51:56'),
(146, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-11 20:22:42'),
(147, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-12 11:39:43'),
(148, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-12 19:37:40'),
(149, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-12 20:26:32'),
(150, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-12 23:26:03'),
(151, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-15 23:46:51'),
(152, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-16 09:34:12'),
(153, 23, 'admin Logged in As Admin', '2026-04-16 10:07:28'),
(154, 23, 'admin Logged in As Admin', '2026-04-16 10:09:57'),
(155, 23, 'admin Logged in As Admin', '2026-04-16 10:11:02'),
(156, 23, 'admin Logged in As Admin', '2026-04-16 11:29:55'),
(157, 4, 'Aljon Paragoso Logged in As Spender', '2026-04-16 20:22:21'),
(158, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-16 20:22:52'),
(159, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-16 20:23:27'),
(160, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-16 20:26:26'),
(161, 4, 'Aljon Paragoso Logged in As Spender', '2026-04-16 20:27:53'),
(162, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-16 20:29:12'),
(163, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-16 20:30:22'),
(164, 4, 'Aljon Paragoso Logged in As Spender', '2026-04-16 21:01:27'),
(165, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-16 21:15:33'),
(166, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-16 21:42:11'),
(167, 4, 'Aljon Paragoso Logged in As Spender', '2026-04-16 21:55:28'),
(168, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-16 21:55:58'),
(169, 2, 'Patricia Ann Mae Obaob Sent an Invitation:  Sponsor', '2026-04-16 21:56:06'),
(170, 4, 'Aljon Paragoso Logged in As Spender', '2026-04-16 21:56:22'),
(171, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-16 21:56:37'),
(172, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: Allowance ni Aljon', '2026-04-16 21:57:20'),
(173, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-16 22:11:07'),
(174, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-16 22:14:02'),
(175, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-16 22:14:11'),
(176, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: King James Allowance April', '2026-04-16 22:15:09'),
(177, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: Allowance ni Guian sa April', '2026-04-16 22:15:41'),
(178, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-16 22:27:02'),
(179, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-17 09:28:45'),
(180, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-17 09:30:25'),
(181, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-17 10:00:10'),
(182, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-17 10:00:37'),
(183, 1, 'Lawrence Sumbi Added an Expense: hi - ?100.00', '2026-04-18 22:14:30'),
(184, 1, 'Lawrence Sumbi Deleted the Expense: ', '2026-04-18 22:14:44'),
(185, 1, 'Lawrence Sumbi Added an Expense: sample - ?100.00', '2026-04-18 22:29:02'),
(186, 1, 'Lawrence Sumbi Deleted the Expense: ', '2026-04-18 22:29:34'),
(187, 4, 'Aljon Paragoso Logged in As Spender', '2026-04-18 22:32:15'),
(188, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-18 22:32:44'),
(189, 1, 'Lawrence Sumbi Added an Expense: sample - ?100.00', '2026-04-18 22:36:31'),
(190, 1, 'Lawrence Sumbi Added an Expense: sample 2 - ?200.00', '2026-04-18 22:36:42'),
(191, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-18 22:38:46'),
(192, 1, 'User Scheduled: Something', '2026-04-18 22:59:14'),
(193, 4, 'Aljon Paragoso Logged in As Spender', '2026-04-18 23:08:09'),
(194, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-18 23:10:13'),
(195, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-18 23:25:26'),
(196, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: Allowance Guian April', '2026-04-18 23:25:40'),
(197, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: King Allowance April', '2026-04-18 23:25:50'),
(198, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: Allowanz Guian April', '2026-04-18 23:26:27'),
(199, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-18 23:26:38'),
(200, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-18 23:28:17'),
(201, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: Budget Guian April', '2026-04-18 23:28:35'),
(202, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-18 23:28:45'),
(203, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-18 23:29:01'),
(204, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: Kwarta Guian April', '2026-04-18 23:29:14'),
(205, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: King Kwarta April', '2026-04-18 23:29:23'),
(206, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: Allowance ni Guian', '2026-04-18 23:29:45'),
(207, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: Allowance ni King', '2026-04-18 23:29:54'),
(208, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-18 23:30:03'),
(209, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-18 23:30:13'),
(210, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: Budget ni Guian', '2026-04-18 23:30:21'),
(211, 2, 'Patricia Ann Mae Obaob Added or Updated an Allowance: Budget ni King', '2026-04-18 23:30:27'),
(212, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-18 23:30:39'),
(213, 1, 'Lawrence Sumbi Scheduled a Payment: hi (Spender)', '2026-04-18 23:55:19'),
(214, 1, 'Lawrence Sumbi Scheduled a Payment: hello (Spender)', '2026-04-18 23:58:09'),
(215, 1, 'Lawrence Sumbi Deleted a Scheduled Payment: ', '2026-04-19 00:02:32'),
(216, 1, 'Lawrence Sumbi Deleted a Scheduled Payment: ', '2026-04-19 00:02:35'),
(217, 1, 'Lawrence Sumbi Deleted a Scheduled Payment: ', '2026-04-19 00:11:59'),
(218, 1, 'Lawrence Sumbi Scheduled a Payment: hi dam (Spender)', '2026-04-19 00:12:10'),
(219, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-19 00:19:23'),
(220, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 00:23:31'),
(221, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-19 00:29:05'),
(222, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 00:29:18'),
(223, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-19 00:31:09'),
(224, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 00:32:02'),
(225, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-19 00:34:01'),
(226, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 00:35:45'),
(227, 4, 'Aljon Paragoso Logged in As Spender', '2026-04-19 00:37:46'),
(228, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 00:38:24'),
(229, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-19 00:52:36'),
(230, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 00:52:50'),
(231, 4, 'Aljon Paragoso Logged in As Spender', '2026-04-19 00:53:18'),
(232, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 00:55:00'),
(233, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-19 00:55:43'),
(234, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 00:57:24'),
(235, 4, 'Aljon Paragoso Logged in As Spender', '2026-04-19 00:58:16'),
(236, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 01:00:46'),
(237, 4, 'Aljon Paragoso Logged in As Spender', '2026-04-19 01:01:19'),
(238, 4, 'Aljon Paragoso Added a Person: hi', '2026-04-19 01:01:28'),
(239, 4, 'Aljon Paragoso Updated a Person: hallu', '2026-04-19 01:02:12'),
(240, 4, 'Aljon Paragoso Deleted a Person: ', '2026-04-19 01:02:18'),
(241, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 01:02:29'),
(242, 1, 'Lawrence Sumbi Added/Updated Expense: HAHAHAH - ?1,000.00', '2026-04-19 01:07:13'),
(243, 1, 'Lawrence Sumbi settled ?100.00 for \'HAHAHAH\'. Remaining: ?42.86', '2026-04-19 01:08:11'),
(244, 23, 'admin Logged in As Admin', '2026-04-19 01:25:59'),
(245, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 10:11:10'),
(246, 4, 'Aljon Paragoso Logged in As Spender', '2026-04-19 11:01:54'),
(247, 4, 'Aljon Paragoso Added/Updated Expense: hi - ?100.00', '2026-04-19 11:04:04'),
(248, 4, 'Aljon Paragoso Deleted the Expense: ', '2026-04-19 11:04:14'),
(249, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 11:07:03'),
(250, 1, 'Lawrence Sumbi Added/Updated Expense: Mang Inasal - ?1,000.00', '2026-04-19 11:10:13'),
(251, 1, 'Lawrence Sumbi Added/Updated Expense: Mang Inasal - ?1,000.00', '2026-04-19 11:11:15'),
(252, 1, 'Lawrence Sumbi Updated a Person: OA ko', '2026-04-19 11:26:57'),
(253, 4, 'Aljon Paragoso Logged in As Spender', '2026-04-19 13:47:06'),
(254, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 13:47:48'),
(255, 4, 'Aljon Paragoso Logged in As Spender', '2026-04-19 13:54:12'),
(256, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 14:02:02'),
(257, 1, 'Lawrence Sumbi Added an Expense: sample 3 - ?10.00', '2026-04-19 14:31:49'),
(258, 1, 'Lawrence Sumbi Added an Expense: sample 4 - ?10.00', '2026-04-19 14:32:02'),
(259, 1, 'Lawrence Sumbi Added an Expense: sample 5 - ?5.00', '2026-04-19 14:32:28'),
(260, 4, 'Aljon Paragoso Logged in As Spender', '2026-04-19 14:39:43'),
(261, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 14:41:29'),
(262, 1, 'Lawrence Sumbi Added an Expense: hi - ?5.00', '2026-04-19 15:20:56'),
(263, 1, 'Lawrence Sumbi Added an Expense: hallu - ?5.00', '2026-04-19 15:21:36'),
(264, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-19 15:32:59'),
(265, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 15:34:50'),
(266, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-19 15:52:29'),
(267, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 15:58:17'),
(268, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 21:17:35'),
(269, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 21:38:21'),
(270, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 21:44:47'),
(271, 1, 'Lawrence Sumbi settled ?100.00 for \'Mang Inasal\'. Remaining: ?42.86', '2026-04-19 21:45:11'),
(272, 1, 'Lawrence Sumbi settled ?100.00 for \'Mang Inasal\'. Remaining: ?0.00', '2026-04-19 21:45:18'),
(273, 1, 'Lawrence Sumbi Added a Person: Patpat Obaob', '2026-04-19 21:45:56'),
(274, 1, 'Lawrence Sumbi Added/Updated Expense: Malunggay Pandesal - ?50.00', '2026-04-19 21:46:50'),
(275, 1, 'A Friend (via Email) settled ?10.00 for \'Malunggay Pandesal\'. Remaining: ?10.00', '2026-04-19 21:47:39'),
(276, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 22:01:48'),
(277, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-19 22:13:10'),
(278, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 22:20:05'),
(279, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-19 22:35:42'),
(280, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 22:45:12'),
(281, 3, 'Dranreb Misa Logged in As Spender', '2026-04-19 22:56:01'),
(282, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-19 22:59:21'),
(283, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-19 23:01:00'),
(284, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-19 23:02:35'),
(285, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-20 00:08:54'),
(286, 1, 'Lawrence Sumbi Added an Expense: Lapas - ?1,200.00', '2026-04-20 00:12:38'),
(287, 1, 'Lawrence Sumbi Added an Expense: baby - ?50.00', '2026-04-20 00:13:56'),
(288, 1, 'Lawrence Sumbi Deleted the Expense: ', '2026-04-20 00:14:27'),
(289, 1, 'Lawrence Sumbi Deleted the Expense: ', '2026-04-20 00:14:29'),
(290, 1, 'Lawrence Sumbi Added an Expense: Lapas - ?1,200.00', '2026-04-20 00:33:38'),
(291, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-20 00:34:30'),
(292, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-20 00:57:38'),
(293, 23, 'admin Logged in As Admin', '2026-04-20 00:59:27'),
(294, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-20 01:01:53'),
(295, 1, 'Lawrence Sumbi Deleted the Expense: ', '2026-04-20 01:02:37'),
(296, 1, 'Lawrence Sumbi Deleted a Scheduled Payment: ', '2026-04-20 01:03:32'),
(297, 1, 'Lawrence Sumbi Deleted a Scheduled Payment: ', '2026-04-20 01:03:37'),
(298, 1, 'Lawrence Sumbi Deleted a Scheduled Payment: ', '2026-04-20 01:03:41'),
(299, 1, 'Lawrence Sumbi Deleted a Scheduled Payment: ', '2026-04-20 01:04:01'),
(300, 1, 'Lawrence Sumbi Deleted a Scheduled Payment: ', '2026-04-20 01:04:02'),
(301, 1, 'Lawrence Sumbi Scheduled a Payment: Defense (Spender)', '2026-04-20 01:04:27'),
(302, 1, 'Lawrence Sumbi Scheduled a Payment: Internet Bill (Spender)', '2026-04-20 01:05:05'),
(303, 1, 'Lawrence Sumbi Scheduled a Payment: Electricity (Spender)', '2026-04-20 01:06:00'),
(304, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-20 01:17:09'),
(305, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-20 01:17:40'),
(306, 1, 'Lawrence Sumbi Scheduled a Payment: Google Subscription (Spender)', '2026-04-20 01:24:01'),
(307, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-20 08:50:51'),
(308, 1, 'Lawrence Sumbi Added a Person: Rowena Sumbi', '2026-04-20 08:51:54'),
(309, 1, 'Lawrence Sumbi Added/Updated Expense: Angels Burger - ?80.00', '2026-04-20 08:53:40'),
(310, 2, 'Patricia Ann Mae Obaob Logged in As Sponsor', '2026-04-20 11:15:20'),
(311, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-20 11:44:10'),
(312, 23, 'admin Logged in As Admin', '2026-04-20 11:55:04'),
(313, 23, 'admin Logged in As Admin', '2026-04-20 11:55:51'),
(314, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-20 11:57:41'),
(315, 1, 'Lawrence Sumbi Scheduled a Payment: sabon (Spender)', '2026-04-20 12:14:29'),
(316, 1, 'Lawrence Sumbi Scheduled a Payment: sabon (Spender)', '2026-04-20 12:14:40'),
(317, 1, 'Lawrence Sumbi Scheduled a Payment: sabon (Spender)', '2026-04-20 12:14:56'),
(318, 1, 'Lawrence Sumbi Deleted a Scheduled Payment: ', '2026-04-20 12:16:19'),
(319, 1, 'Lawrence Sumbi Deleted a Scheduled Payment: ', '2026-04-20 12:16:21'),
(320, 1, 'Lawrence Sumbi Deleted a Scheduled Payment: ', '2026-04-20 12:16:23'),
(321, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-20 12:22:27'),
(322, 1, 'Lawrence Sumbi Scheduled a Payment: Sabon (Spender)', '2026-04-20 12:22:48'),
(323, 1, 'Lawrence Sumbi Scheduled a Payment: Sabon (Spender)', '2026-05-20 12:24:06'),
(324, 1, 'Lawrence Sumbi Logged in As Spender', '2026-06-20 12:24:42'),
(325, 1, 'Lawrence Sumbi Scheduled a Payment: Sabon (Spender)', '2026-06-20 12:24:53'),
(326, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-20 12:25:53'),
(327, 1, 'Lawrence Sumbi Logged in As Spender', '2026-07-20 12:26:09'),
(328, 1, 'Lawrence Sumbi Scheduled a Payment: Sabon (Spender)', '2026-07-20 12:26:53'),
(329, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-20 12:28:43'),
(330, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-20 12:29:03'),
(331, 1, 'Lawrence Sumbi Scheduled a Payment: Tuition (Spender)', '2026-04-20 12:30:28'),
(332, 1, 'Lawrence Sumbi Scheduled a Payment: Tuition (Spender)', '2026-05-15 12:31:26'),
(333, 1, 'Lawrence Sumbi Scheduled a Payment: Tuition (Spender)', '2026-06-10 12:32:13'),
(334, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-20 12:35:40'),
(335, 1, 'Lawrence Sumbi Scheduled a Payment: Miwassco (Spender)', '2026-04-20 12:51:49'),
(336, 1, 'Lawrence Sumbi Logged in As Spender', '2026-05-20 12:51:58'),
(337, 1, 'Lawrence Sumbi Logged in As Spender', '2026-05-12 12:52:28'),
(338, 1, 'Lawrence Sumbi Scheduled a Payment: Miwassco (Spender)', '2026-05-12 12:53:11'),
(339, 1, 'Lawrence Sumbi Logged in As Spender', '2026-06-12 12:53:36'),
(340, 1, 'Lawrence Sumbi Scheduled a Payment: Miwassco (Spender)', '2026-06-12 12:53:56'),
(341, 1, 'Lawrence Sumbi Logged in As Spender', '2026-07-12 12:55:29'),
(344, 32, 'JemaJema Logged in As ', '2026-04-20 13:13:16'),
(345, 33, 'WewJema Logged in As ', '2026-04-20 13:14:47'),
(346, 32, 'JemaJema Logged in As Sponsor', '2026-04-20 13:15:05'),
(347, 32, 'JemaJema Sent an Invitation:  Sponsor', '2026-04-20 13:15:25'),
(348, 33, 'WewJema Logged in As Spender', '2026-04-20 13:15:43'),
(349, 32, 'JemaJema Added or Updated an Allowance: for 1 Week', '2026-04-20 13:17:33'),
(350, 32, 'JemaJema Added or Updated an Allowance: FOr 2 weeks', '2026-04-20 13:21:48'),
(351, 32, 'JemaJema Added or Updated an Allowance: Tuition', '2026-04-20 13:22:50'),
(352, 33, 'WewJema Added a Person: Jema', '2026-04-20 13:24:11'),
(353, 33, 'WewJema Added/Updated Expense: Kuryente MA - ?1,546.00', '2026-04-20 13:24:48'),
(354, 33, 'WewJema Added/Updated Expense: Kuryente MA - ?1,546.00', '2026-04-20 13:24:53'),
(355, 33, 'WewJema Deleted an Expense:  - ?0.00', '2026-04-20 13:24:59'),
(356, 33, 'WewJema Deleted an Expense:  - ?0.00', '2026-04-20 13:27:32'),
(357, 33, 'WewJema Logged in As Spender', '2026-04-20 13:27:42'),
(358, 33, 'WewJema Added/Updated Expense: Kuryente - ?1,564.00', '2026-04-20 13:28:21'),
(359, 33, 'A Friend (via Email) settled ?782.00 for \'Kuryente\'. Remaining: ?0.00', '2026-04-20 13:29:11'),
(360, 1, 'Lawrence Sumbi Logged in As Spender', '2026-04-20 13:33:28');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
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
(19, 4, 2, 'invite', 'You have been invited by Patricia Ann Mae Obaob. Click accept to join.', 'read', '2026-03-26 09:58:08'),
(20, 1, 2, 'invite', 'You have been invited by Patricia Ann Mae Obaob. Click accept to join.', 'read', '2026-03-27 13:55:32'),
(21, 2, 1, '', 'Lawrence Sumbi has accepted your invitation and is now linked to your account.', 'read', '2026-03-27 13:55:55'),
(22, 8, 2, 'invite', 'You have been invited by Patricia Ann Mae Obaob. Click accept to join.', 'read', '2026-04-03 12:53:05'),
(23, 8, 2, 'invite', 'You have been invited by Patricia Ann Mae Obaob. Click accept to join.', 'read', '2026-04-03 12:55:12'),
(24, 8, 2, 'invite', 'You have been invited by Patricia Ann Mae Obaob. Click accept to join.', 'read', '2026-04-03 12:56:40'),
(25, 8, 2, 'invite', 'You have been invited by Patricia Ann Mae Obaob. Click accept to join.', 'read', '2026-04-03 12:57:23'),
(26, 8, 2, 'invite', 'You have been invited by Patricia Ann Mae Obaob. Click accept to join.', 'unread', '2026-04-03 12:58:33'),
(128, 4, 7, 'invite', 'You have been invited by Sponsor User. Click accept to join.', 'read', '2026-04-03 14:05:23'),
(129, 1, 7, 'invite', 'You have been invited by Sponsor User. Click accept to join.', 'read', '2026-04-03 14:10:58'),
(130, 3, 2, 'invite', 'You have been invited by Patricia Ann Mae Obaob. Click accept to join.', 'unread', '2026-04-07 04:41:08'),
(131, 4, 2, 'invite', 'You have been invited by Patricia Ann Mae Obaob. Click accept to join.', 'read', '2026-04-16 13:56:06'),
(132, 2, 4, '', 'Aljon Paragoso has accepted your invitation and is now linked to your account.', 'read', '2026-04-16 13:56:27'),
(133, 33, 32, 'invite', 'You have been invited by JemaJema. Click accept to join.', 'read', '2026-04-20 05:15:25'),
(134, 32, 33, '', 'WewJema has accepted your invitation and is now linked to your account.', 'unread', '2026-04-20 05:15:50');

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
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `people`
--

INSERT INTO `people` (`id`, `user_id`, `name`, `email`, `created_at`) VALUES
(1, 1, 'Dray Misa', 'draymisa@gmail.com', '2026-03-15 12:36:14'),
(2, 1, 'Jaylon Mantillas', 'jaylonmantillas@gmail.com', '2026-03-15 12:39:38'),
(3, 1, 'Jaymaica Narvasa', 'jaymaicanarvasa@gmail.com', '2026-03-15 12:59:43'),
(4, 1, 'Jay Cabatuan', 'jaycabatuan@gmail.com', '2026-03-15 13:23:43'),
(5, 1, 'Ivan Laluna', 'ivanlaluna@gmail.com', '2026-03-15 13:23:50'),
(7, 1, 'Sample', 'sample@gmail.com', '2026-03-24 05:00:58'),
(8, 1, 'Sample2', 'sample2@gmail.com', '2026-03-24 06:59:13'),
(9, 1, 'Sample3', 'sample3@gmail.com', '2026-03-25 13:54:22'),
(13, 5, 'Emman', 'emman@gmail.com', '2026-03-25 14:19:40'),
(14, 5, 'Lenzey', 'lenzey@gmail.com', '2026-03-25 14:19:45'),
(15, 5, 'Mary Divine', 'marydivine@gmail.com', '2026-03-25 14:19:52'),
(16, 5, 'Lloyd Junrex', 'lloydjunrex@gmail.com', '2026-03-25 14:22:48'),
(17, 1, 'OA ko', 'oa@gmail.com', '2026-04-02 07:16:42'),
(18, 1, 'HAHAHHAHA', 'hahah@gmail.com', '2026-04-02 07:21:39'),
(21, 1, 'Bag o', 'bago@gmail.com', '2026-04-07 05:54:58'),
(23, 1, 'Patpat Obaob', 'patriciaannmaeobaob721@gmail.com', '2026-04-19 13:45:56'),
(24, 1, 'Rowena Sumbi', 'rowenasumbi5@gmail.com', '2026-04-20 00:51:54'),
(25, 33, 'Jema', 'maicamaica211@gmail.com', '2026-04-20 05:24:11');

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
(11, 1, 'Load', 100.00, '2026-02-21', '2026-04-01', 8, 2, '2026-02-21 12:49:21', '2026-02-21 12:49:21'),
(13, 1, 'sample', 100.00, '2026-02-20', '2026-02-14', 1, 2, '2026-02-21 12:53:24', '2026-02-21 12:53:24'),
(15, 1, 'data', 40.00, '2026-02-21', '2026-02-22', 1, 2, '2026-02-21 12:55:05', '2026-02-21 12:55:05'),
(16, 1, '28', 28.00, '2026-02-28', '2026-02-21', 1, 2, '2026-02-21 13:00:07', '2026-02-21 13:00:07'),
(19, 1, 'Loklok', 149.00, '2026-02-22', NULL, NULL, 3, '2026-02-21 14:45:12', '2026-02-21 14:45:12'),
(20, 1, '280', 280.00, '2026-02-28', '2026-02-22', 2, 2, '2026-02-21 15:11:01', '2026-02-21 15:11:01'),
(22, 1, '4', 4.00, '2026-02-04', '2026-04-01', 7, 2, '2026-02-22 04:45:24', '2026-02-22 04:45:24'),
(24, 1, 'sample', 100.00, '2026-03-01', '2026-02-22', 1, 2, '2026-02-22 08:09:48', '2026-02-22 08:09:48'),
(29, 1, 'Water Bill', 143.00, '2026-03-28', NULL, NULL, 3, '2026-03-11 10:09:32', '2026-03-11 10:09:32'),
(31, 1, 'Sample', 10.00, '2026-03-25', NULL, NULL, 3, '2026-03-12 15:25:57', '2026-03-12 15:25:57'),
(37, 1, 'Holy Friday', 50.00, '2026-04-03', NULL, NULL, 3, '2026-04-01 11:17:22', '2026-04-01 11:17:22'),
(39, 1, 'Lunes', 10.00, '2026-04-05', NULL, NULL, 3, '2026-04-03 11:31:45', '2026-04-03 11:31:45'),
(40, 1, 'Martes', 20.00, '2026-04-06', NULL, NULL, 3, '2026-04-03 11:54:56', '2026-04-03 11:54:56'),
(45, 1, 'Defense', 78.00, '2026-04-20', NULL, NULL, 1, '2026-04-19 17:04:27', '2026-04-19 17:04:27'),
(46, 1, 'Internet Bill', 1499.00, '2026-04-21', NULL, NULL, 1, '2026-04-19 17:05:05', '2026-04-19 17:05:05'),
(47, 1, 'Electricity', 419.00, '2026-04-30', NULL, NULL, 1, '2026-04-19 17:06:00', '2026-04-19 17:06:00'),
(48, 1, 'Google Subscription', 119.00, '2026-04-25', NULL, NULL, 1, '2026-04-19 17:24:01', '2026-04-19 17:24:01'),
(52, 1, 'Sabon', 10.00, '2026-04-27', NULL, NULL, 1, '2026-04-20 04:22:48', '2026-04-20 04:22:48'),
(53, 1, 'Sabon', 10.00, '2026-05-27', NULL, NULL, 1, '2026-05-20 04:24:06', '2026-05-20 04:24:06'),
(54, 1, 'Sabon', 10.00, '2026-06-27', NULL, NULL, 1, '2026-06-20 04:24:53', '2026-06-20 04:24:53'),
(55, 1, 'Sabon', 10.00, '2026-07-27', NULL, NULL, 1, '2026-07-20 04:26:53', '2026-07-20 04:26:53'),
(56, 1, 'Tuition', 5600.00, '2026-04-15', NULL, NULL, 3, '2026-04-20 04:30:28', '2026-04-20 04:30:28'),
(57, 1, 'Tuition', 5600.00, '2026-05-15', NULL, NULL, 1, '2026-05-15 04:31:26', '2026-05-15 04:31:26'),
(58, 1, 'Tuition', 2600.00, '2026-06-15', NULL, NULL, 1, '2026-06-10 04:32:13', '2026-06-10 04:32:13'),
(59, 1, 'Miwassco', 143.00, '2026-04-18', NULL, NULL, 3, '2026-04-20 04:51:49', '2026-04-20 04:51:49'),
(60, 1, 'Miwassco', 143.00, '2026-05-18', NULL, NULL, 1, '2026-05-12 04:53:11', '2026-05-12 04:53:11'),
(61, 1, 'Miwassco', 143.00, '2026-06-18', NULL, NULL, 1, '2026-06-12 04:53:56', '2026-06-12 04:53:56');

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
(13, 2, 1, '2026-03-27 13:55:55'),
(14, 2, 4, '2026-04-16 13:56:27'),
(15, 32, 33, '2026-04-20 05:15:50');

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
  `phone` varchar(11) NOT NULL,
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

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `role`, `phone`, `profile_pic`, `verification_code`, `is_verified`, `reset_token`, `reset_expiry`, `created_at`) VALUES
(1, 'Lawrence Sumbi', 'guiansumbi@gmail.com', '$2y$10$cKIACjgglVTrRjDZaKSZjulwkdA0CIDUwOBkU12h2PPGKK03U0aT6', 'spender', '09753140724', 'profile/1776346365_69e0e4fd0cf8b.jpg', NULL, 1, NULL, NULL, '2026-02-07 17:08:23'),
(2, 'Patricia Ann Mae Obaob', 'patriciaannmaeobaob721@gmail.com', '$2y$10$gVokRQej23KaxSKKUZPiSOn/mL5IE0kvfoGyPRTZfN1in/ZpNAQku', 'sponsor', '09059641855', 'profile/1773380531_69b3a3b3f074d.jpg', NULL, 1, NULL, NULL, '2026-02-08 11:52:14'),
(3, 'Dranreb Misa', 'draymisa@gmail.com', '$2y$10$Gw3YeLEMfsCIOPV3xFs5h.jClSQLC9rilddvuzZ063CceY9/IVgue', 'spender', '', '', NULL, 1, NULL, NULL, '2026-02-09 00:03:22'),
(4, 'Aljon Paragoso', 'aljon@gmail.com', '$2y$10$Wt8Xf9aFRGG6zRdmdsfP1.bzpQS9xPfN/20Rsf.l7gb5ivx7H.t8u', 'spender', '', '', NULL, 1, NULL, NULL, '2026-02-10 02:32:42'),
(5, 'King James', 'king@gmail.com', '$2y$10$gEXzRZe1Yx1W/lVHDmk8ju/S//8ksu6iLCjJaJyxXhy3lWOLBBTw6', 'spender', '', '', NULL, 1, NULL, NULL, '2026-02-23 08:07:35'),
(6, 'sample1', 'sample1@gmail.com', '$2y$10$XkOSorJMOiPKBmZhqqGwWuRJKdGV5PzCQo3XrueMu3cK8GnHfyOJy', 'spender', '', 'profile/1773497217_69b56b816f5b2.jpg', NULL, 1, NULL, NULL, '2026-03-14 13:30:53'),
(7, 'Sponsor User', 'sponsor@gmail.com', '$2y$10$Lr9hm92Iha4J2va9tH7XYOhEiGgscf6qmvHn8oZ16WKrGTBtrsEI6', 'sponsor', '', '', NULL, 1, NULL, NULL, '2026-03-14 16:24:13'),
(8, 'sample', 'sample2@gmail.com', '$2y$10$COoyyUVfJ8VQcPRk6Kf78eXV1YMs./Xs2zIct38PqfacTCewGRKoe', 'spender', '', '', NULL, 1, NULL, NULL, '2026-03-24 01:27:28'),
(9, 'sample3', 'sample3@gmail.com', '$2y$10$W.Q5X/WGz2KLXTv.c59FxeAwfrhvKU4PQbZTPXP/AMkx8r9Icv1qS', '', '', '', NULL, 1, NULL, NULL, '2026-03-25 00:05:13'),
(10, 'sample4', 'sample4@gmail.com', '$2y$10$kVcs8DgHEugTzGtvsaRiSOi7ZgTxkEE7qHPOGQD/1WYc5FOrpObru', 'sponsor', '', 'profile/1774416092_69c370dcc7ae4.jpg', NULL, 1, NULL, NULL, '2026-03-25 00:20:48'),
(11, 'sample5', 'sample5@gmail.com', '$2y$10$JqvDRqR2nlCJsG1IZqvclO444PsfqvzEuUmD8P8CCKzJLZD9asD9W', '', '', '', NULL, 1, NULL, NULL, '2026-03-25 00:22:06'),
(23, 'Administrator', 'admin@gmail.com', '$2y$10$NI0LVSCAQZPYUzR2pSBMF.L9Rxuv.Aywv4EfjYqgOtiJbCKJUtc/C', 'admin', '09161612488', '', NULL, 1, NULL, NULL, '2026-04-16 01:09:57'),
(31, 'Rowena Sumbi', 'rowenasumbi5@gmail.com', '$2y$10$OAmsWKziRD3b4/tujJBvneO4SrbkOoVEsPG3NgkqR/pWERaf/Ovm2', '', '', '', '717558', 0, NULL, NULL, '2026-04-20 05:11:43'),
(32, 'JemaJema', 'maicamaica211@gmail.com', '$2y$10$0CVi0w7BVI6KDDs1aHHcq.dZ4BwCt6PiqiGdZ2utIB1Dzyl1nrGna', 'sponsor', '', '', NULL, 1, NULL, NULL, '2026-04-20 05:12:32'),
(33, 'WewJema', 'narvasa0529@gmail.com', '$2y$10$ntHxYaHMcCOqIiOyTCIuyOsXueXOlJ0FH/gz5nADtfj3rTnCkaFX6', 'spender', '', '', NULL, 1, NULL, NULL, '2026-04-20 05:14:17');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `expense_shares`
--
ALTER TABLE `expense_shares`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=361;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT for table `payment_method`
--
ALTER TABLE `payment_method`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `people`
--
ALTER TABLE `people`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `scheduled_payments`
--
ALTER TABLE `scheduled_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `sponsor_spender`
--
ALTER TABLE `sponsor_spender`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

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
