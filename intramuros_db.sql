-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 23, 2026 at 11:51 AM
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
(1, 'admin', 'login_failed', 'Wrong password.', '2026-05-23 09:00:47'),
(2, 'admin', 'login_failed', 'Wrong password.', '2026-05-23 09:00:49'),
(3, 'admin', 'login_failed', 'Wrong password.', '2026-05-23 09:05:27'),
(4, 'admin', 'login_failed', 'Wrong password.', '2026-05-23 09:08:19'),
(5, 'admin', 'login_failed', 'Wrong password.', '2026-05-23 09:08:26'),
(6, 'admin', 'login_failed', 'Wrong password.', '2026-05-23 09:08:27'),
(7, 'admin', 'login_failed', 'Wrong password.', '2026-05-23 09:08:27'),
(8, 'admin', 'login_failed', 'Wrong password.', '2026-05-23 09:09:06'),
(9, 'admin', 'profile_updated', 'Admin created via setup_admin.php', '2026-05-23 09:12:21'),
(10, 'admin', 'login_success', 'Admin signed in.', '2026-05-23 09:12:58'),
(11, 'admin', 'logout', 'Admin signed out.', '2026-05-23 09:15:54'),
(12, 'admin', 'login_success', 'Admin signed in.', '2026-05-23 09:16:06'),
(13, 'admin', 'logout', 'Admin signed out.', '2026-05-23 09:16:56'),
(14, 'admin', 'login_failed', 'Wrong password.', '2026-05-23 09:23:47'),
(15, 'admin', 'login_success', 'Admin signed in.', '2026-05-23 09:23:54'),
(16, 'admin', 'logout', 'Admin signed out.', '2026-05-23 09:24:30'),
(17, 'admin', 'login_failed', 'Wrong password.', '2026-05-23 09:24:35'),
(18, 'admin', 'login_failed', 'Wrong password.', '2026-05-23 09:27:01'),
(19, 'admin', 'login_failed', 'Wrong password.', '2026-05-23 09:44:14');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password_hash`, `created_at`) VALUES
(2, 'admin', '$2y$10$z1vit8IJIdAwXhMal0sb6.jHlZGqI86Gr8PE1IeEwXQNL3IBf5TbG', '2026-05-23 09:12:21');

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
(15, 'Foreign', '2026-03-27', 3, 2, 3, 3, 2, 'Its all good man', 2.60, '2026-05-14 07:42:33'),
(18, 'Local', '2026-05-21', 4, 2, 2, 2, 3, ' is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', 2.60, '2026-05-21 04:49:36'),
(19, 'Foreign', '2026-05-23', 3, 2, 3, 3, 2, 'There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn\'t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.', 2.60, '2026-05-23 08:46:05');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `survey`
--
ALTER TABLE `survey`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
