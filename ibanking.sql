-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 10, 2025 at 08:27 AM
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
-- Database: `ibanking`
--

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `user_icon` varchar(255) NOT NULL DEFAULT 'default.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`user_id`, `username`, `password_hash`, `full_name`, `email`, `phone`, `balance`, `user_icon`, `created_at`) VALUES
(1, 'nguyenvana', '$2y$10$mBYFC.xN9ZWuVMjt9hSTLuP6X3YEX7x2jAAGE6wrxTtDb19aYyk/G', 'Nguyen Van A', 'a@example.com', '0909123456', 5000000.00, 'default', '2025-08-30 16:27:28'),
(2, 'tranthib', '$2y$10$mBYFC.xN9ZWuVMjt9hSTLuP6X3YEX7x2jAAGE6wrxTtDb19aYyk/G', 'Tran Thi B', 'b@example.com', '0909345678', 2000000.00, 'default', '2025-08-30 16:27:28'),
(3, 'levanc', '$2y$10$mBYFC.xN9ZWuVMjt9hSTLuP6X3YEX7x2jAAGE6wrxTtDb19aYyk/G', 'Le Van C', 'c@example.com', '0909765432', 10000000.00, 'default', '2025-08-30 16:27:28');

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `otp_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otps`
--

INSERT INTO `otps` (`otp_id`, `transaction_id`, `otp_code`, `expires_at`, `is_used`) VALUES
(1, 1, '123456', '2025-08-30 09:05:00', 1),
(2, 2, '654321', '2025-08-30 09:10:00', 1),
(3, 3, '789012', '2025-08-30 09:15:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `full_name`, `email`, `phone`) VALUES
('SV000001', 'Nguyen Van A', 'a@example.com', '0909123456'),
('SV000002', 'Tran Thi B', 'b@example.com', '0909345678'),
('SV000003', 'Le Van C', 'c@example.com', '0909765432');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `payer_id` int(11) NOT NULL,
  `fee_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('success','failed','pending') NOT NULL DEFAULT 'pending',
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `payer_id`, `fee_id`, `amount`, `status`, `note`, `created_at`) VALUES
(1, 1, 1, 10000000.00, 'success', 'Thanh toán học phí HK1 năm học 2025-2026', '2025-08-30 09:00:00'),
(2, 2, 3, 8000000.00, 'failed', 'Thanh toán thất bại do số dư không đủ', '2025-08-30 09:05:00'),
(3, 3, 2, 10500000.00, 'success', 'Thanh toán hộ cho SV000001 học kỳ 2', '2025-08-30 09:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `tuitionfees`
--

CREATE TABLE `tuitionfees` (
  `fee_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `semester` varchar(10) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tuitionfees`
--

INSERT INTO `tuitionfees` (`fee_id`, `student_id`, `semester`, `school_year`, `description`, `amount`, `due_date`, `status`) VALUES
(1, 'SV000001', 'HK1', '2025-2026', 'Học phí HK1 năm học 2025-2026', 10000000.00, '2025-09-30', 'unpaid'),
(2, 'SV000001', 'HK2', '2025-2026', 'Học phí HK2 năm học 2025-2026', 10500000.00, '2026-02-28', 'unpaid'),
(3, 'SV000002', 'HK1', '2025-2026', 'Học phí HK1 năm học 2025-2026', 3000000.00, '2025-09-30', 'unpaid'),
(4, 'SV000003', 'HK1', '2025-2026', 'Học phí HK1 năm học 2025-2026', 9500000.00, '2025-09-30', 'paid');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`otp_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `payer_id` (`payer_id`),
  ADD KEY `fee_id` (`fee_id`);

--
-- Indexes for table `tuitionfees`
--
ALTER TABLE `tuitionfees`
  ADD PRIMARY KEY (`fee_id`),
  ADD KEY `student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `otp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tuitionfees`
--
ALTER TABLE `tuitionfees`
  MODIFY `fee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `otps`
--
ALTER TABLE `otps`
  ADD CONSTRAINT `otps_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`payer_id`) REFERENCES `customer` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`fee_id`) REFERENCES `tuitionfees` (`fee_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tuitionfees`
--
ALTER TABLE `tuitionfees`
  ADD CONSTRAINT `tuitionfees_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
