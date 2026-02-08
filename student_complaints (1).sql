-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 04, 2026 at 08:11 PM
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
-- Database: `student_complaints`
--

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','in_progress','resolved','rejected') DEFAULT 'pending',
  `assigned_to` int(11) DEFAULT NULL,
  `admin_remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `student_id`, `category`, `description`, `status`, `assigned_to`, `admin_remarks`, `created_at`, `updated_at`) VALUES
(1, 6, 'academic', 'macalinka soo dhhhy', 'resolved', NULL, 'ustaad waa la xaliyry', '2026-01-24 15:03:50', '2026-01-24 15:07:40'),
(2, 7, 'finance', 'ustaad 539 macquul maha 300 lahaaya', 'resolved', NULL, '100 iska bixi ustaad', '2026-01-24 15:10:50', '2026-01-24 15:13:21'),
(3, 8, 'academic', 'mafahmo', 'resolved', NULL, 'waa xalinay', '2026-01-25 09:07:02', '2026-01-25 09:10:34'),
(4, 9, 'finance', 'waxaan ka cawanayaaa lacagta', 'resolved', NULL, 'waa jibwaaye lacagta bixi', '2026-01-25 09:12:43', '2026-01-25 09:16:08'),
(5, 10, 'academic', 'anigoo ahmed semistre 7 class pt12 xisada aan php macalinka si fcn wax uma sharxo', 'rejected', NULL, 'kormeereenaa', '2026-01-25 09:21:34', '2026-01-25 09:32:59'),
(6, 10, 'finance', 'bishaan shiid waqti dheer noo dhara', 'rejected', NULL, 'taas macquul maho', '2026-01-25 09:21:53', '2026-01-25 09:24:15'),
(7, 10, 'library', 'nadhaafad malaho', 'resolved', NULL, 'xalin', '2026-01-25 09:22:11', '2026-01-25 09:24:30'),
(8, 10, 'it', 'si fcn nooma shaqeyaan', 'resolved', NULL, 'xalin', '2026-01-25 09:22:33', '2026-01-25 09:24:45'),
(9, 11, 'library', 'nadaaafad xumo ayaa ka jirta qolka library', 'resolved', NULL, 'waala haagin dooonaaa', '2026-01-25 09:29:34', '2026-01-25 09:32:33'),
(10, 12, 'finance', 'lacagta ma awooodi', 'rejected', NULL, 'jaamacada ii bax ardayda ha igu dirin', '2026-01-25 10:00:05', '2026-01-25 10:02:10'),
(11, 13, 'finance', 'waxaan ka cawanayaaa lacagta jaamacada 300 mana awoodi', 'rejected', NULL, 'maya', '2026-01-26 07:04:33', '2026-01-31 12:14:23'),
(12, 14, 'finance', 'anigo ah m.exd waxaan ka cawanayaa lacagta jaamacada ee aheyd 350 ma awooodi', 'rejected', NULL, 'maamulka la xariir', '2026-01-26 07:34:03', '2026-01-26 07:36:03'),
(13, 16, 'finance', 'waxaa ka cabanayaa lacagata jamcada', 'rejected', NULL, 'maya', '2026-01-26 12:15:34', '2026-01-26 12:18:06'),
(17, 13, 'academic', 'waxan ka cabanayaa maadada MIS', 'in_progress', NULL, '', '2026-02-04 13:34:33', '2026-02-04 13:36:49');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 6, 'Your complaint #1 status has been updated to: Resolved', 0, '2026-01-24 15:07:40'),
(2, 7, 'Your complaint #2 status has been updated to: Pending', 0, '2026-01-24 15:13:02'),
(3, 7, 'Your complaint #2 status has been updated to: Resolved', 0, '2026-01-24 15:13:21'),
(4, 8, 'Your complaint #3 status has been updated to: Pending', 0, '2026-01-25 09:10:18'),
(5, 8, 'Your complaint #3 status has been updated to: Resolved', 0, '2026-01-25 09:10:34'),
(6, 9, 'Your complaint #4 status has been updated to: Pending', 0, '2026-01-25 09:15:47'),
(7, 9, 'Your complaint #4 status has been updated to: Resolved', 0, '2026-01-25 09:16:08'),
(8, 10, 'Your complaint #5 status has been updated to: In progress', 0, '2026-01-25 09:23:54'),
(9, 10, 'Your complaint #6 status has been updated to: Rejected', 0, '2026-01-25 09:24:15'),
(10, 10, 'Your complaint #7 status has been updated to: Resolved', 0, '2026-01-25 09:24:30'),
(11, 10, 'Your complaint #8 status has been updated to: Resolved', 0, '2026-01-25 09:24:45'),
(12, 11, 'Your complaint #9 status has been updated to: Resolved', 0, '2026-01-25 09:32:33'),
(13, 10, 'Your complaint #5 status has been updated to: Rejected', 0, '2026-01-25 09:33:00'),
(14, 12, 'Your complaint #10 status has been updated to: Rejected', 0, '2026-01-25 10:02:10'),
(15, 13, 'Your complaint #11 status has been updated to: In progress', 0, '2026-01-26 07:06:45'),
(16, 14, 'Your complaint #12 status has been updated to: Rejected', 0, '2026-01-26 07:36:03'),
(17, 16, 'Your complaint #13 status has been updated to: Pending', 0, '2026-01-26 12:17:23'),
(18, 16, 'Your complaint #13 status has been updated to: Pending', 0, '2026-01-26 12:17:41'),
(19, 16, 'Your complaint #13 status has been updated to: Rejected', 0, '2026-01-26 12:18:07'),
(20, 13, 'Your complaint #11 status has been updated to: In progress', 0, '2026-01-31 12:14:07'),
(21, 13, 'Your complaint #11 status has been updated to: Rejected', 0, '2026-01-31 12:14:23'),
(22, 13, 'Your complaint #17 status has been updated to: In progress', 0, '2026-02-04 13:36:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin','staff') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(2, 'staff1', 'staff@example.com', '$2y$10$8W3WpBwO2W5kF.P.T.tO.eR7eK4v5w9z8e4v5w9z8e4v5w9z8e4v5', 'staff', '2025-12-22 17:42:51'),
(3, 'sadam', 'sadam@gmail.com', '$2y$10$ms2vkw9BkDgXr3O45s6zMOfw4cG7AnB5pZJ/4KgHq9CzGWQbNrglu', 'student', '2025-12-22 17:47:09'),
(5, 'admin', 'admin@example.com', '$2y$10$hp03aWT5QQjPYixcSTfOgeGcKcW9YNhzAD7s0fXlRP0.mmmdiTT6y', 'admin', '2025-12-22 17:48:22'),
(6, 'lp', 'lp@gmail.com', '$2y$10$yTF4HkAXTg6I85kKaeLcqOIG14SGUOmXkBWMDcC/Gkaos3s7cwbJ2', 'student', '2026-01-24 15:03:18'),
(7, 'rabsho', 'abdi@gmail.com', '$2y$10$geG13b45sW2uMx7IDpRrw.OXG3BMAo2O3WVQlMWSp6ZtFojmIJ3hS', 'student', '2026-01-24 15:09:15'),
(8, 'yahye', 'yahye@example.com', '$2y$10$aYkHeN/ZxFWG6fwpyD1u0eD2MmQA97MNyBLh126lMNSSsZIt71PSa', 'student', '2026-01-25 09:06:11'),
(9, 'xasan', 'xasan@example.com', '$2y$10$QxxctpLFg/12WCKIU0S.deoMzlGf1XQ3NtBWlfkby50qpKrLCnaJ.', 'student', '2026-01-25 09:11:54'),
(10, 'qaasim', 'qoslaaye@example.com', '$2y$10$mvwNMsitFe0CBinp6ZD0D.WjjEz8lRVcrQ/oyJ6zzzsj/MZLXrBhm', 'student', '2026-01-25 09:18:47'),
(11, 'c/naasir', 'seedi@example.com', '$2y$10$g9SV03t/bcxmc2O4PsU21OrG6CmQlI1OrtvBaHBrZufu62faT6GSi', 'student', '2026-01-25 09:27:45'),
(12, 'liibaan c/fataax', 'bukow@example.com', '$2y$10$CpzHkRYspd/xHmQGc9hwyeAOs9BkgizywneyBg/jgmPOLYGf02qUK', 'student', '2026-01-25 09:57:06'),
(13, 'amal hasan', 'amal@gmail.com', '$2y$10$t.lo3gZZNwdmhRZYoRFLUOkvniMKK1TT4bLkpgphGCRGhLODwQ6Me', 'student', '2026-01-26 07:03:03'),
(14, 'mohamed', 'eko@example.com', '$2y$10$qxgaU1y6P2CHJl206t0zqOMq206oflI1kJUQuHAg3bf010ssXlmLK', 'student', '2026-01-26 07:33:02'),
(15, 'sahra', 'sahra@example.com', '$2y$10$liLPRABGWmxqkZ/67AlmB.sEWNFz2G6ESuc4F.pSrDzSIdNLG4lKy', 'student', '2026-01-26 08:27:26'),
(16, 'aniso cali', 'aniso@example.com', '$2y$10$OzFbRR.CamfOLc72tbRM/O3kb9fp9mnhst0OMqbJjpxz895dE26u2', 'student', '2026-01-26 12:14:03'),
(17, 'Ali@gmail.com', 'ali@example.com', '$2y$10$ukT7E4gQYhFGoLfS0iCn7OxabD5YaiVjTBDL7V7oDvtO2POiZa/PW', 'student', '2026-01-31 12:13:08'),
(18, 'ali', 'Ali@gmail.com', '$2y$10$v8BDnig3rc63onvUUpuZ1.pvIu2r3g2BqmLuEEw5JjC7TFAaolcja', 'student', '2026-02-04 10:25:22'),
(20, 'amal hasan', 'amal@example.com', '$2y$10$Js4y/rTKsktyIVBJAM2SIuqGu3/7cyUBEFUmxXq8OH.356lJSiX66', 'student', '2026-02-04 13:18:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
