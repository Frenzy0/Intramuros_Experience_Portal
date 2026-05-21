-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2026 at 07:37 AM
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
-- Database: `intramuros_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `action` varchar(64) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `username`, `action`, `details`, `created_at`) VALUES
(1, 'admin', 'login_success', 'Admin signed in.', '2026-05-05 16:20:09'),
(2, 'admin', 'login_success', 'Admin signed in.', '2026-05-05 16:24:33'),
(3, 'admin', 'feedback_deleted', 'Deleted feedback id=2', '2026-05-05 16:25:02'),
(4, 'ad', 'login_failed', 'Unknown username.', '2026-05-05 16:29:15'),
(5, 'ad', 'login_failed', 'Unknown username.', '2026-05-05 16:29:20'),
(6, 'admin', 'login_success', 'Admin signed in.', '2026-05-05 16:29:27'),
(7, 'admin', 'login_success', 'Admin signed in.', '2026-05-05 16:30:25'),
(8, 'admin', 'login_success', 'Admin signed in.', '2026-05-05 16:36:59'),
(9, 'admin', 'login_success', 'Admin signed in.', '2026-05-05 16:41:32'),
(10, 'admin', 'login_failed', 'Wrong password.', '2026-05-05 16:42:38'),
(11, 'admin', 'login_success', 'Admin signed in.', '2026-05-05 16:42:43'),
(12, 'admin', 'login_success', 'Admin signed in.', '2026-05-05 16:43:35'),
(13, 'admin', 'login_success', 'Admin signed in.', '2026-05-05 16:46:59'),
(14, 'admin', 'login_success', 'Admin signed in.', '2026-05-05 16:50:23'),
(15, 'admin', 'login_failed', 'Wrong password.', '2026-05-05 16:54:43'),
(16, 'admin', 'login_success', 'Admin signed in.', '2026-05-05 16:54:47'),
(17, 'admin', 'feedback_deleted', 'Deleted feedback id=4', '2026-05-05 16:54:50'),
(18, 'admin', 'feedback_deleted', 'Deleted feedback id=3', '2026-05-05 16:54:51'),
(19, 'admin', 'login_success', 'Admin signed in.', '2026-05-05 17:04:12'),
(20, 'admin', 'logout', 'Admin signed out.', '2026-05-05 17:05:21'),
(21, 'admin', 'login_success', 'Admin signed in.', '2026-05-05 17:13:25'),
(22, 'admin', 'logout', 'Admin signed out.', '2026-05-05 17:14:06'),
(23, 'admin', 'login_success', 'Admin signed in.', '2026-05-05 17:16:22'),
(24, 'admin', 'logout', 'Admin signed out.', '2026-05-05 17:16:50'),
(25, 'admin', 'login_success', 'Admin signed in.', '2026-05-09 11:53:58'),
(26, 'admin', 'logout', 'Admin signed out.', '2026-05-09 11:57:54'),
(27, 'admin', 'login_success', 'Admin signed in.', '2026-05-09 12:01:52'),
(28, 'admin', 'logout', 'Admin signed out.', '2026-05-09 12:03:38'),
(29, 'admin', 'login_success', 'Admin signed in.', '2026-05-09 12:21:52'),
(30, 'admin', 'logout', 'Admin signed out.', '2026-05-09 12:22:52'),
(31, 'admin', 'login_success', 'Admin signed in.', '2026-05-09 12:29:11'),
(32, 'admin', 'logout', 'Admin signed out.', '2026-05-09 12:30:13'),
(33, 'admin', 'login_success', 'Admin signed in.', '2026-05-11 00:00:44'),
(34, 'admin', 'logout', 'Admin signed out.', '2026-05-11 00:02:21'),
(35, 'admin', 'login_success', 'Admin signed in.', '2026-05-11 00:28:02'),
(36, 'admin', 'feedback_deleted', 'Deleted feedback id=8', '2026-05-11 00:28:06'),
(37, 'admin', 'logout', 'Admin signed out.', '2026-05-11 00:35:15'),
(38, 'admin', 'login_success', 'Admin signed in.', '2026-05-11 00:37:49'),
(39, 'admin', 'logout', 'Admin signed out.', '2026-05-11 00:41:00'),
(40, 'admin', 'login_success', 'Admin signed in.', '2026-05-11 01:10:26'),
(41, 'admin', 'logout', 'Admin signed out.', '2026-05-11 01:10:39'),
(42, 'admin', 'login_success', 'Admin signed in.', '2026-05-11 01:11:10'),
(43, 'admin', 'logout', 'Admin signed out.', '2026-05-11 01:11:15'),
(44, 'admin', 'login_success', 'Admin signed in.', '2026-05-11 01:12:26'),
(45, 'admin', 'logout', 'Admin signed out.', '2026-05-11 01:12:44'),
(46, 'admin', 'login_success', 'Admin signed in.', '2026-05-11 01:25:03'),
(47, 'admin', 'logout', 'Admin signed out.', '2026-05-11 01:27:23'),
(48, 'admin', 'login_success', 'Admin signed in.', '2026-05-11 01:31:55'),
(49, 'admin', 'logout', 'Admin signed out.', '2026-05-11 01:39:21'),
(50, 'admin', 'login_success', 'Admin signed in.', '2026-05-11 01:43:32'),
(51, 'admin', 'logout', 'Admin signed out.', '2026-05-11 01:49:54'),
(52, 'admin', 'login_failed', 'Wrong password.', '2026-05-11 01:52:00'),
(53, 'admin', 'login_success', 'Admin signed in.', '2026-05-11 01:52:05'),
(54, 'admin', 'logout', 'Admin signed out.', '2026-05-11 02:33:27'),
(55, 'admin', 'login_success', 'Admin signed in.', '2026-05-12 01:49:17'),
(56, 'admin', 'logout', 'Admin signed out.', '2026-05-12 01:49:29'),
(57, 'admin', 'logout', 'Admin signed out.', '2026-05-12 02:11:01'),
(58, '123', 'login_failed', 'Unknown username.', '2026-05-12 02:11:20'),
(59, 'admin', 'login_success', 'Admin signed in.', '2026-05-12 12:45:40'),
(60, 'admin', 'logout', 'Admin signed out.', '2026-05-12 12:46:15'),
(61, 'admin', 'logout', 'Admin signed out.', '2026-05-12 12:51:03'),
(62, 'admin', 'login_success', 'Admin signed in.', '2026-05-12 12:51:25'),
(63, 'admin', 'login_failed', 'Wrong password.', '2026-05-12 13:38:54'),
(64, 'admin', 'login_success', 'Admin signed in.', '2026-05-12 13:39:04'),
(65, 'admin', 'feedback_deleted', 'Deleted feedback id=12', '2026-05-12 13:39:45'),
(66, 'admin', 'logout', 'Admin signed out.', '2026-05-12 13:40:59'),
(67, 'admin', 'login_success', 'Admin signed in.', '2026-05-13 13:58:15'),
(68, 'admin', 'logout', 'Admin signed out.', '2026-05-13 13:59:13'),
(69, 'admin', 'login_success', 'Admin signed in.', '2026-05-13 13:59:34'),
(70, 'admin', 'feedback_deleted', 'Deleted feedback id=10', '2026-05-13 14:00:36'),
(71, 'admin', 'logout', 'Admin signed out.', '2026-05-13 14:01:27'),
(72, 'admin', 'login_success', 'Admin signed in.', '2026-05-13 14:09:15'),
(73, 'admin', 'logout', 'Admin signed out.', '2026-05-13 14:09:40'),
(74, 'admin', 'login_failed', 'Wrong password.', '2026-05-14 10:15:35'),
(75, 'admin', 'login_success', 'Admin signed in.', '2026-05-14 10:15:40'),
(76, 'admin', 'logout', 'Admin signed out.', '2026-05-14 10:15:57'),
(77, 'admin', 'login_failed', 'Wrong password.', '2026-05-14 10:50:14'),
(78, 'admin', 'login_success', 'Admin signed in.', '2026-05-14 10:50:19'),
(79, 'admin', 'logout', 'Admin signed out.', '2026-05-14 10:50:27'),
(80, 'admin', 'login_success', 'Admin signed in.', '2026-05-14 10:50:52'),
(81, 'admin', 'logout', 'Admin signed out.', '2026-05-14 10:51:00'),
(82, 'admin', 'login_success', 'Admin signed in.', '2026-05-14 10:51:19'),
(83, 'admin', 'logout', 'Admin signed out.', '2026-05-14 10:52:02'),
(84, 'admin', 'logout', 'Admin signed out.', '2026-05-14 14:04:32'),
(85, 'admin', 'login_failed', 'Wrong password.', '2026-05-14 14:13:13'),
(86, 'admin', 'login_success', 'Admin signed in.', '2026-05-14 14:13:18'),
(87, 'admin', 'feedback_deleted', 'Deleted feedback id=17', '2026-05-14 14:13:23'),
(88, 'admin', 'feedback_deleted', 'Deleted feedback id=16', '2026-05-14 14:13:25'),
(89, 'admin', 'survey_cleared', 'Cleared 7 survey response(s).', '2026-05-15 05:36:20'),
(90, 'admin', 'logout', 'Admin signed out.', '2026-05-15 05:36:29');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'admin', '$2y$10$8zYNSnMt2iFKpn1K8p9Zs.AwN6JEnANWzg60LDLBr5wNu4Y011Cxq', '2026-05-05 16:20:08');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `nationality` varchar(50) NOT NULL,
  `visit_date` date NOT NULL,
  `cleanliness` int(11) NOT NULL,
  `restroom` int(11) NOT NULL,
  `guides` int(11) NOT NULL,
  `accommodation` int(11) NOT NULL,
  `overall` int(11) NOT NULL,
  `comments` text DEFAULT NULL,
  `average` decimal(3,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `nationality`, `visit_date`, `cleanliness`, `restroom`, `guides`, `accommodation`, `overall`, `comments`, `average`, `created_at`) VALUES
(5, 'Local', '2026-05-04', 3, 2, 4, 3, 4, 'okay naman', 3.20, '2026-05-05 17:02:34'),
(6, 'Foreign', '2026-05-03', 1, 1, 1, 1, 1, 'no map at all', 1.00, '2026-05-05 17:03:40'),
(7, 'Local', '2026-05-03', 4, 3, 4, 3, 2, '', 3.20, '2026-05-11 00:18:45'),
(9, 'Local', '2026-05-03', 4, 2, 3, 2, 3, '', 2.80, '2026-05-11 00:27:08'),
(11, 'Foreign', '2026-05-29', 2, 2, 2, 3, 3, '', 2.40, '2026-05-11 01:12:00'),
(13, 'Local', '2026-05-01', 2, 3, 3, 4, 2, '', 2.80, '2026-05-13 13:55:36'),
(14, 'Foreign', '2026-03-23', 3, 2, 2, 3, 3, '', 2.60, '2026-05-14 07:39:49'),
(15, 'Foreign', '2026-03-27', 3, 2, 3, 3, 2, 'Its all good man', 2.60, '2026-05-14 07:42:33');

-- --------------------------------------------------------

--
-- Table structure for table `survey`
--

CREATE TABLE `survey` (
  `id` int(11) NOT NULL,
  `helpful` varchar(3) NOT NULL,
  `survey_suggestions` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_created` (`created_at`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `survey`
--
ALTER TABLE `survey`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `survey`
--
ALTER TABLE `survey`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
