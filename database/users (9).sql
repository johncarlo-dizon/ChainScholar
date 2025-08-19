-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2025 at 02:10 PM
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
-- Database: `chainscholar`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `role` enum('ADMIN','STUDENT','ADVISER') NOT NULL DEFAULT 'STUDENT',
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `role`, `password`, `avatar`, `department`, `specialization`, `remember_token`, `created_at`, `updated_at`) VALUES
(6, 'Juan', 'student2@gmail.com', NULL, 'STUDENT', '$2y$12$S6V.NX6GBEce0ENaqPQrNuopV/chV.IafZqEn7XlTUMRQMziPRwp6', NULL, NULL, NULL, NULL, '2025-08-15 19:44:45', '2025-08-15 19:44:45'),
(7, 'Pedro', 'student1@gmail.com', NULL, 'STUDENT', '$2y$12$S6V.NX6GBEce0ENaqPQrNuopV/chV.IafZqEn7XlTUMRQMziPRwp6', NULL, NULL, NULL, NULL, '2025-08-15 19:44:45', '2025-08-15 19:44:45'),
(8, 'Jc', 'admin@gmail.com', NULL, 'ADMIN', '$2y$12$S6V.NX6GBEce0ENaqPQrNuopV/chV.IafZqEn7XlTUMRQMziPRwp6', NULL, NULL, NULL, NULL, '2025-08-15 19:44:45', '2025-08-15 19:44:45'),
(9, 'Aron', 'adviser1@gmail.com', NULL, 'ADVISER', '$2y$12$TngIlt/AXWDNiDjNa7W5ceP2fexcpspOQfVwVwjifdoOZP82HQPG.', NULL, NULL, NULL, NULL, '2025-08-15 20:00:18', '2025-08-15 20:00:18'),
(10, 'Joel', 'adviser2@gmail.com', NULL, 'ADVISER', '$2y$12$TngIlt/AXWDNiDjNa7W5ceP2fexcpspOQfVwVwjifdoOZP82HQPG.', NULL, NULL, NULL, NULL, '2025-08-15 20:00:18', '2025-08-15 20:00:18'),
(11, 'Atasha', 'adviser3@gmail.com', NULL, 'ADVISER', '$2y$12$TngIlt/AXWDNiDjNa7W5ceP2fexcpspOQfVwVwjifdoOZP82HQPG.', NULL, NULL, NULL, NULL, '2025-08-15 20:00:18', '2025-08-15 20:00:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
