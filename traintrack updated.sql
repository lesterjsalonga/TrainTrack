-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2025 at 03:33 PM
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
-- Database: `traintrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `created_at`, `profile_image`) VALUES
(1, 'lester', '$2y$10$BS64QheXLps2Y2wgPaXBXeoUFXYhZqmsLx93wIECbW.f0Y3yrL0z2', NULL, '2025-05-19 06:01:31', NULL),
(2, 'superadmin', '$2y$10$xmc7LZE4DtzZkom7HuAIieQxEaKyFTKTNsMi25WnJTYTng.IRZl5a', NULL, '2025-05-19 06:01:31', NULL),
(3, 'etann', '$2y$10$GsY.fE.k2EN6BspkvmWQw.LmJpVcxSbpvsVKcaVKvIihNyHD0rcxu', NULL, '2025-05-19 06:01:31', NULL),
(4, 'super lester', '$2y$10$TFM/GUOLDcb8FF6ydfxK0em3OZwH8Uov6qMZYOfNYog9Lmf73k.DW', NULL, '2025-05-19 06:01:31', NULL),
(5, 'admin123', '$2y$10$9WnAfoaqtp8oHdKXtYJtUeIGLpuu5kYe0xfcOFGNhEjeIBhmXVnOa', NULL, '2025-05-19 06:01:31', NULL),
(6, 'Admin', '$2y$10$vmritU6tLyqUZ1TTT1CVA.x.FvDc1bpXQ8hDrDE4v4vUwtEphGUJS', NULL, '2025-05-19 06:01:31', NULL),
(7, 'admin staff', '$2y$10$6B/ewJXYXlYAZ7Wh4OXWje7O421a/81Z3Ce1i9Sg7qi01S9SoGKDG', NULL, '2025-05-19 06:01:31', 'uploads/admin_profiles/admin_682ad0d3079f45.85141403.jpg'),
(8, 'Mayihitsdiff', '$2y$10$LibnCouJY/4b2Ygf48Bau.qITxUP1xbY0pjBKd3JzBV8/uEQTeyZO', NULL, '2025-05-19 07:48:40', NULL),
(9, 'Mayi2004', '$2y$10$nznLSmnC9PoqIOM8IfFm.uP.cmnQXwQ2fedN21K8Z7F60S/QUlAX.', NULL, '2025-05-19 07:49:01', 'uploads/admin_profiles/admin_682ae30ad41591.62436907.jpg'),
(10, 'stark', '$2y$10$K9VsxSRQOomF2RkDKpoZye0o7nxaWl8RtWCwa0bprQJ6/I56vFSgW', NULL, '2025-05-19 14:25:06', 'uploads/admin_profiles/admin_682b3f5c0c0421.97223674.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(50) DEFAULT NULL,
  `status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `venue`, `max_participants`, `created_at`, `created_by`, `status`) VALUES
(1, 'AI Webinar', 'join us', '2025-06-07 23:11:00', 'Enchanted Kingdom', 21, '2025-05-19 15:11:36', 'stark', 'upcoming');

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `ticket_number` varchar(20) DEFAULT NULL,
  `status` enum('registered','attended','cancelled') DEFAULT 'registered'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_registrations`
--

INSERT INTO `event_registrations` (`id`, `event_id`, `student_id`, `registration_date`, `ticket_number`, `status`) VALUES
(1, 1, '456', '2025-05-19 15:25:58', 'TKT-2C3A476D', 'registered');

-- --------------------------------------------------------

--
-- Table structure for table `meetings`
--

CREATE TABLE `meetings` (
  `id` int(11) NOT NULL,
  `resume_id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `meeting_date` datetime NOT NULL,
  `meeting_type` enum('interview','consultation') NOT NULL,
  `meeting_platform` varchar(100) NOT NULL,
  `meeting_link` varchar(255) DEFAULT NULL,
  `meeting_notes` text DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meetings`
--

INSERT INTO `meetings` (`id`, `resume_id`, `student_id`, `meeting_date`, `meeting_type`, `meeting_platform`, `meeting_link`, `meeting_notes`, `status`, `created_at`) VALUES
(4, 26, '456', '2025-06-06 23:27:00', 'interview', 'In-person', '', 'Meet me at room 201', 'scheduled', '2025-05-19 15:28:01');

-- --------------------------------------------------------

--
-- Table structure for table `resumes`
--

CREATE TABLE `resumes` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resumes`
--

INSERT INTO `resumes` (`id`, `student_id`, `file_name`, `original_name`, `status`, `uploaded_at`, `remark`) VALUES
(6, '2022-01200', '2022-01200_1747050775.pdf', '', 'approved', '2025-05-12 11:52:55', NULL),
(7, '2022-00360', '2022-00360_1747050890.pdf', '', 'approved', '2025-05-12 11:54:50', NULL),
(8, '001', '001_1747125509.pdf', '', 'approved', '2025-05-13 08:38:29', NULL),
(10, '2022-123', '2022-123_1747631974.docx', '', 'rejected', '2025-05-19 05:19:34', 'ay hindi pala very good'),
(12, '2025-01200', '2025-01200_1747641582.docx', '', 'pending', '2025-05-19 07:59:42', ''),
(13, '2025-01200', '2025-01200_1747641595.docx', '', 'pending', '2025-05-19 07:59:55', ''),
(14, '2025-01200', '2025-01200_1747641597.docx', '', 'pending', '2025-05-19 07:59:57', ''),
(15, '2025-01200', '2025-01200_1747641697.pdf', '', 'pending', '2025-05-19 08:01:37', ''),
(16, '2025-01200', '2025-01200_1747641707.pdf', '', 'pending', '2025-05-19 08:01:47', ''),
(17, '2025-01200', '2025-01200_1747641742.docx', '', 'pending', '2025-05-19 08:02:22', ''),
(18, '2025-01200', '2025-01200_1747641779.pdf', '', 'pending', '2025-05-19 08:02:59', ''),
(19, '2025-01200', '2025-01200_1747641951.pdf', '', 'pending', '2025-05-19 08:05:51', ''),
(20, '2025-01200', '2025-01200_1747641969.pdf', '', 'pending', '2025-05-19 08:06:09', ''),
(21, '2025-01200', '2025-01200_1747641974.pdf', '', 'pending', '2025-05-19 08:06:14', ''),
(22, '2025-01200', '2025-01200_1747641984.pdf', '', 'rejected', '2025-05-19 08:06:24', 'no'),
(23, '2022-123', '2022-123_1747642016.pdf', '', 'approved', '2025-05-19 08:06:56', 'Good'),
(26, '456', '456_1747668396.pdf', '', 'approved', '2025-05-19 15:26:36', 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `name`, `email`, `password`, `created_at`) VALUES
(1, '2022-01200', 'Trishia Mae Manabat', 'trishiamae.manabat@gmail.com', '$2y$10$drPzTwuklLbYIgFYL5ke1OLCACEA6p8luHPTJTelF23ytNz8DOKgy', '2025-05-12 10:43:13'),
(2, '2022-00360', 'Cristan Irish Legaspi', 'cristanirish@gmail.com', '$2y$10$XnFCW50iqs2ICYX504XBB.C7OzGrwz986YBnV4Q9N6fE6lH8f.Zu6', '2025-05-12 11:54:13'),
(3, '001', 'ariana grande', 'ariana@gmail.com', '$2y$10$1376eTh1OcwJwITmGr/IT.nE6NgP/YpXOdHKzhaOq2pl29pHHx8ue', '2025-05-13 08:38:13'),
(4, '2022-123', 'Mayihitsdiff', 'mayi@gmail.com', '$2y$10$tVArPfjTENwf3h.OGbbgQOOsfRzNIsIM92iSfaWaJClLyOQ35f5Z6', '2025-05-19 04:49:59'),
(5, '2025-01200', 'Trishia Mae Landicho', 'iya@gmail.com', '$2y$10$g09VUy1rJpFD4JL0jvjyTe2gkUTF54BHlSxDWZpudqDTv4kB5EqyO', '2025-05-19 07:55:44'),
(6, '123', 'stark1', '123@mail.com', '$2y$10$7pocVX/t6DbB/7aB3FEIteltNlJc5ANopVl06U9Koj8GkqSEU.rJO', '2025-05-19 14:28:16'),
(7, '456', 'John', '456@mail.com', '$2y$10$F3rW8NBYCPdVgXeYb10adOcmyZxiLWJj1pwgaFc8VK7C30ALc./Li', '2025-05-19 15:25:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_number` (`ticket_number`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `meetings`
--
ALTER TABLE `meetings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resume_id` (`resume_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `resumes`
--
ALTER TABLE `resumes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `meetings`
--
ALTER TABLE `meetings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `resumes`
--
ALTER TABLE `resumes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`username`);

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_registrations_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `meetings`
--
ALTER TABLE `meetings`
  ADD CONSTRAINT `meetings_ibfk_1` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meetings_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `resumes`
--
ALTER TABLE `resumes`
  ADD CONSTRAINT `resumes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
