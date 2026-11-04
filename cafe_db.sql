-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2026 at 01:27 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cafe_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `customer_debts`
--

CREATE TABLE `customer_debts` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `remaining_amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `date` date NOT NULL,
  `status` enum('سداد جزئي','معلق','خالص') DEFAULT 'معلق',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_debts`
--

INSERT INTO `customer_debts` (`id`, `customer_name`, `total_amount`, `paid_amount`, `remaining_amount`, `description`, `date`, `status`, `created_at`) VALUES
(6, 'ابو كريم1', 1200.00, 1200.00, 0.00, '0', '2026-11-03', 'خالص', '2026-11-03 20:28:08'),
(7, 'ابو كريم', 1000.00, 1000.00, 0.00, '0', '2026-11-04', 'خالص', '2026-11-04 07:55:58'),
(8, 'عمرو جهمي', 500.00, 0.00, 500.00, '0', '2026-11-04', 'معلق', '2026-11-04 08:18:05');

-- --------------------------------------------------------

--
-- Table structure for table `daily`
--

CREATE TABLE `daily` (
  `id` int(11) NOT NULL,
  `details` text DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `daily`
--

INSERT INTO `daily` (`id`, `details`, `amount`, `date`, `created_at`) VALUES
(5, 'إيراد مبيعات يومية', 5000.00, '2026-11-03', '2026-11-03 20:27:15'),
(6, 'سداد دفعة من دين العميل: ابو كريم1', 500.00, '2026-11-03', '2026-11-03 20:28:08'),
(7, 'تسوية/سداد ديون العميل: ابو كريم1', 1200.00, '2026-11-03', '2026-11-03 20:30:59'),
(8, 'صرف: لبن', -120.00, '2026-11-03', '2026-11-03 20:31:43'),
(9, 'دفعة جزئية للمورد (تسجيل جديد): ahmed', -6000.00, '2026-11-03', '2026-11-03 21:20:42'),
(10, 'سداد قسط/جزء للمورد: ahmed', -2000.00, '2026-11-03', '2026-11-03 21:20:51'),
(11, 'سداد دفعة من دين العميل: ابو كريم', 800.00, '2026-11-04', '2026-11-04 07:55:58'),
(12, 'إيراد مبيعات يومية', 1200.00, '2026-11-04', '2026-11-04 07:56:26'),
(13, 'سداد قسط للمورد: ahmed', -4000.00, '2026-11-04', '2026-11-04 07:57:34'),
(14, 'إيراد مبيعات يومية', 12000.00, '2026-11-04', '2026-11-04 08:17:02'),
(15, 'صرف: لبن', -110.00, '2026-11-04', '2026-11-04 08:17:22'),
(16, 'صرف: بن', -250.00, '2026-11-04', '2026-11-04 08:17:32'),
(17, 'تسوية/سداد ديون العميل: ابو كريم', 1000.00, '2026-11-04', '2026-11-04 08:17:46'),
(18, 'دفعة جزئية للمورد: سيد', -3250.00, '2026-11-04', '2026-11-04 08:19:02');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `title`, `amount`, `date`, `created_at`) VALUES
(2, 'لبن', 120.00, '2026-11-03', '2026-11-03 20:31:43'),
(3, 'لبن', 110.00, '2026-11-04', '2026-11-04 08:17:22'),
(4, 'بن', 250.00, '2026-11-04', '2026-11-04 08:17:32');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `total_debt` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `remaining_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `supplier_name`, `phone`, `total_debt`, `paid_amount`, `remaining_amount`, `description`, `date`, `created_at`) VALUES
(1, 'ahmed', '01092078155', 12000.00, 12000.00, 0.00, 'ماية', '2026-11-03', '2026-11-03 21:20:42'),
(2, 'سيد', '', 6500.00, 3250.00, 3250.00, '', '2026-11-04', '2026-11-04 08:19:02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'eshoo', '123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customer_debts`
--
ALTER TABLE `customer_debts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `daily`
--
ALTER TABLE `daily`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customer_debts`
--
ALTER TABLE `customer_debts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `daily`
--
ALTER TABLE `daily`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
