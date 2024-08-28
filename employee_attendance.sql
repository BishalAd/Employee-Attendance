-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 28, 2024 at 06:05 AM
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
-- Database: `employee_attendance`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `status` enum('Present','Absent','Leave') DEFAULT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `username`, `date`, `status`, `time_in`, `time_out`, `reason`) VALUES
(1, 'AdBishal', '2024-08-25', 'Present', '18:04:25', '18:56:26', NULL),
(2, 'xyz', '2024-08-25', 'Absent', NULL, NULL, 'feel sick'),
(3, 'abc', '2024-08-25', 'Leave', '18:41:38', '18:41:53', NULL),
(4, 'aaa', '2024-08-25', 'Present', '18:46:00', '19:08:07', NULL),
(5, 'AdBishal', '2024-08-25', 'Absent', NULL, NULL, 'feel sick'),
(6, 'AdBishal', '2024-08-25', 'Absent', NULL, NULL, 'feel sick'),
(7, 'yyy', '2024-08-26', 'Present', '05:04:18', '05:36:29', NULL),
(8, 'AdBishal', '2024-08-26', 'Absent', NULL, NULL, 'sick'),
(9, 'aaa', '2024-08-26', 'Present', '05:42:42', NULL, NULL),
(10, 'aaa', '2024-08-27', 'Present', '03:55:43', NULL, NULL),
(11, 'yyy', '2024-08-28', 'Present', '05:03:46', NULL, NULL),
(12, 'aaa', '2024-08-28', 'Present', '05:04:34', '09:03:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` int(11) NOT NULL,
  `holiday_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `holidays`
--

INSERT INTO `holidays` (`id`, `holiday_date`, `description`) VALUES
(1, '2024-08-26', NULL),
(2, '2024-08-26', NULL),
(3, '2024-08-26', NULL),
(4, '2024-08-26', NULL),
(5, '2024-08-26', NULL),
(6, '2024-08-26', NULL),
(7, '2024-08-26', NULL),
(8, '2024-08-26', NULL),
(9, '2024-08-26', NULL),
(10, '2024-08-26', NULL),
(11, '2024-08-26', NULL),
(12, '2024-08-26', NULL),
(13, '2024-08-26', NULL),
(14, '2024-08-26', NULL),
(15, '2024-08-26', NULL),
(16, '2024-08-26', NULL),
(17, '2024-08-26', NULL),
(18, '2024-08-27', NULL),
(19, '2024-08-27', NULL),
(20, '2024-08-27', NULL),
(21, '2024-08-27', NULL),
(22, '2024-08-27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `username`, `message`, `created_at`) VALUES
(2, 'yyy', 'hh', '2024-08-25 23:30:16'),
(3, 'aaa', 'abc test', '2024-08-25 23:57:53'),
(4, 'aaa', 'i am present today', '2024-08-26 22:10:55'),
(5, 'yyy', 'K ko holiday ho rw.', '2024-08-26 22:42:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `role` enum('admin','employee') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `role`, `created_at`) VALUES
(1, 'admin', 'admin123', 'Administrator', 'admin', '2024-08-25 15:55:21'),
(3, 'AdBishal', '123', 'Bishal', 'employee', '2024-08-25 16:03:24'),
(4, 'xyz', 'xyz', 'xyz', 'employee', '2024-08-25 16:32:39'),
(6, 'abc', 'abc', 'abc', 'employee', '2024-08-25 16:41:06'),
(7, 'aaa', 'aaa', 'aaa', 'employee', '2024-08-25 16:45:33'),
(10, 'yyy', 'yyy', 'yyy', 'employee', '2024-08-26 02:57:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
