-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th9 30, 2025 lúc 12:23 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `ibanking`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customer`
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
-- Đang đổ dữ liệu cho bảng `customer`
--

INSERT INTO `customer` (`user_id`, `username`, `password_hash`, `full_name`, `email`, `phone`, `balance`, `user_icon`, `created_at`) VALUES
(1, 'johnsmith', '$2y$10$cDPpWRE55i7OJQLUcYEKMOKXNu2cdQKX3TZRE73WSmQwJrqecEso2', 'John Smith', 'phanvanduong1223456@gmail.com', '5551234599', 7000.00, 'ava_b.png', '2025-09-28 13:34:18'),
(2, 'janedoe', '$2y$10$mBYFC.xN9ZWuVMjt9hSTLuP6X3YEX7x2jAAGE6wrxTtDb19aYyk/G', 'Jane Doe', 'phanduong19032023@gmail.com', '5559876543', 10000.00, 'default.png', '2025-09-28 13:34:18'),
(3, 'bobbrown', '$2y$10$mBYFC.xN9ZWuVMjt9hSTLuP6X3YEX7x2jAAGE6wrxTtDb19aYyk/G', 'Bob Brown', 'bob.brown@example.com', '5552468101', 12000.00, 'default.png', '2025-09-28 13:34:18');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `otps`
--

CREATE TABLE `otps` (
  `otp_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_used` tinyint(1) DEFAULT 0,
  `attempts` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `otps`
--

INSERT INTO `otps` (`otp_id`, `transaction_id`, `otp_code`, `expires_at`, `is_used`, `attempts`) VALUES
(135, 144, '475914', '2025-09-30 10:19:34', 1, 0),
(136, 145, '740260', '2025-09-30 10:22:14', 1, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `students`
--

CREATE TABLE `students` (
  `student_id` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `students`
--

INSERT INTO `students` (`student_id`, `full_name`, `email`, `phone`) VALUES
('STU00001', 'Alice Johnson', 'alice.johnson@example.com', '5551112222'),
('STU00002', 'Michael Lee', 'michael.lee@example.com', '5553334444'),
('STU00003', 'Emma Davis', 'emma.davis@example.com', '5555556666');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `transactions`
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
-- Đang đổ dữ liệu cho bảng `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `payer_id`, `fee_id`, `amount`, `status`, `note`, `created_at`) VALUES
(144, 1, 3, 3000.00, 'failed', 'User returned to payment page.', '2025-09-30 10:17:16'),
(145, 1, 3, 3000.00, 'success', 'Tuition payment completed for student Michael Lee - Semester Fall, Year 2025-2026', '2025-09-30 10:20:41');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tuitionfees`
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
-- Đang đổ dữ liệu cho bảng `tuitionfees`
--

INSERT INTO `tuitionfees` (`fee_id`, `student_id`, `semester`, `school_year`, `description`, `amount`, `due_date`, `status`) VALUES
(1, 'STU00001', 'Fall', '2025-2026', 'Tuition fee for Fall semester 2025-2026', 10000.00, '2025-09-30', 'unpaid'),
(2, 'STU00001', 'Spring', '2025-2026', 'Tuition fee for Spring semester 2025-2026', 10500.00, '2026-02-28', 'unpaid'),
(3, 'STU00002', 'Fall', '2025-2026', 'Tuition fee for Fall semester 2025-2026', 3000.00, '2025-09-30', 'paid'),
(4, 'STU00003', 'Fall', '2025-2026', 'Tuition fee for Fall semester 2025-2026', 9500.00, '2025-09-30', 'paid');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Chỉ mục cho bảng `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`otp_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Chỉ mục cho bảng `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`);

--
-- Chỉ mục cho bảng `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `payer_id` (`payer_id`),
  ADD KEY `fee_id` (`fee_id`);

--
-- Chỉ mục cho bảng `tuitionfees`
--
ALTER TABLE `tuitionfees`
  ADD PRIMARY KEY (`fee_id`),
  ADD KEY `student_id` (`student_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `customer`
--
ALTER TABLE `customer`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `otps`
--
ALTER TABLE `otps`
  MODIFY `otp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=137;

--
-- AUTO_INCREMENT cho bảng `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT cho bảng `tuitionfees`
--
ALTER TABLE `tuitionfees`
  MODIFY `fee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `otps`
--
ALTER TABLE `otps`
  ADD CONSTRAINT `otps_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`payer_id`) REFERENCES `customer` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`fee_id`) REFERENCES `tuitionfees` (`fee_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `tuitionfees`
--
ALTER TABLE `tuitionfees`
  ADD CONSTRAINT `tuitionfees_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
