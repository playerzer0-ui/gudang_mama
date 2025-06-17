-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 17, 2025 at 07:06 AM
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
-- Database: `database_gudang`
--
CREATE DATABASE IF NOT EXISTS `database_gudang` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `database_gudang`;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `customerCode` varchar(10) NOT NULL,
  `customerName` varchar(100) DEFAULT NULL,
  `customerAddress` varchar(120) DEFAULT NULL,
  `customerNPWP` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customerCode`, `customerName`, `customerAddress`, `customerNPWP`) VALUES
('DED', 'dedi', 'jln', '123.111.111.111-111'),
('NON', 'none', 'none', '000.111.111.111-111'),
('TOM', 'tomi', 'jln seen', '123.112.111.111-111'),
('ZEN', 'zeno', 'jln asdf', '123.001.111.111-111');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `nomor_surat_jalan` varchar(80) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `no_invoice` varchar(80) DEFAULT NULL,
  `no_faktur` varchar(80) DEFAULT NULL,
  `no_moving` varchar(80) DEFAULT NULL,
  `tax` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`nomor_surat_jalan`, `invoice_date`, `no_invoice`, `no_faktur`, `no_moving`, `tax`) VALUES
('H810005211', '2024-08-08', 'N810005183', '11.111.111.100-1221', '-', 11),
('H810005212', '2024-08-09', 'N810005184', '11.111.111.100-1221', '-', 11),
('H810005213', '2024-08-10', 'N810005185', '11.111.111.100-1221', '-', 11),
('1/SJK/NON/08/2024', '2024-08-08', '1/INV/NON/08/2024', '1123', '-', 0),
('2/SJK/NON/08/2024', '2024-08-09', '2/INV/NON/08/2024', '6666', '-', 0),
('3/SJK/NON/08/2024', '2024-08-10', '3/INV/NON/08/2024', '7890', '-', 0),
('-', '2024-08-07', '1/INV/APA/08/2024', '11.111.111.100-1221', '1/SJP/APA/08/2024', 11),
('-', '2024-08-08', '2/INV/APA/08/2024', '11.111.111.100-1221', '2/SJP/APA/08/2024', 11),
('1/SJT/APA/08/2024', '2024-08-08', '3/INV/APA/08/2024', '11.111.111.100-1221', '-', 11),
('2/SJT/APA/08/2024', '2024-08-09', '6/INV/APA/08/2024', '11.111.111.100-1221', '-', 11),
('3/SJT/APA/08/2024', '2024-08-10', '5/INV/APA/08/2024', '11.111.111.100-1221', '-', 11);

-- --------------------------------------------------------

--
-- Table structure for table `movings`
--

DROP TABLE IF EXISTS `movings`;
CREATE TABLE `movings` (
  `no_moving` varchar(80) NOT NULL,
  `moving_date` date DEFAULT NULL,
  `storageCodeSender` varchar(10) DEFAULT NULL,
  `storageCodeReceiver` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movings`
--

INSERT INTO `movings` (`no_moving`, `moving_date`, `storageCodeSender`, `storageCodeReceiver`) VALUES
('-', NULL, 'NON', 'NON'),
('1/SJP/APA/08/2024', '2024-08-07', 'APA', 'BBB'),
('1/SJP/RBL/08/2024', '2024-08-16', 'RBL', 'RQQ'),
('2/SJP/APA/08/2024', '2024-08-08', 'APA', 'BBB');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `nomor_surat_jalan` varchar(80) NOT NULL,
  `storageCode` varchar(10) DEFAULT NULL,
  `no_LPB` varchar(80) DEFAULT NULL,
  `no_truk` varchar(80) DEFAULT NULL,
  `vendorCode` varchar(10) DEFAULT NULL,
  `customerCode` varchar(10) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `purchase_order` varchar(80) DEFAULT NULL,
  `status_mode` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`nomor_surat_jalan`, `storageCode`, `no_LPB`, `no_truk`, `vendorCode`, `customerCode`, `order_date`, `purchase_order`, `status_mode`) VALUES
('-', 'NON', '-', '-', 'NON', 'NON', NULL, '-', 0),
('1/SJK/NON/08/2024', 'NON', NULL, 'L 333 YY', 'NON', 'DED', '2024-08-07', 'poNON1', 2),
('1/SJT/APA/08/2024', 'APA', NULL, 'L 333 YY', 'NON', 'DED', '2024-08-07', 'potax1', 3),
('2/SJK/NON/08/2024', 'NON', NULL, 'L 666 AA', 'NON', 'TOM', '2024-08-08', 'poNON2', 2),
('2/SJT/APA/08/2024', 'APA', NULL, 'L 666 AA', 'NON', 'TOM', '2024-08-08', 'potax2', 3),
('3/SJK/NON/08/2024', 'NON', NULL, 'L 823 CI', 'NON', 'ZEN', '2024-08-09', 'poNON3', 2),
('3/SJT/APA/08/2024', 'APA', NULL, 'L 823 CI', NULL, 'DED', '2024-08-16', 'potax3', 3),
('H810005211', 'APA', '1/LPB/APA/08/2024', 'L 1237 QZ', 'WIM', 'NON', '2024-08-07', '0203/WIM/SC/07/2024', 1),
('H810005212', 'APA', '2/LPB/APA/08/2024', 'L 999 LC', 'WIM', 'NON', '2024-08-08', '0203/WIM/SC/07/2024', 1),
('H810005213', 'APA', '3/LPB/APA/08/2024', 'L 789 AA', 'WIM', 'NON', '2024-08-09', '0203/WIM/SC/07/2024', 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_products`
--

DROP TABLE IF EXISTS `order_products`;
CREATE TABLE `order_products` (
  `nomor_surat_jalan` varchar(80) DEFAULT NULL,
  `moving_no_moving` varchar(80) DEFAULT NULL,
  `repack_no_repack` varchar(80) DEFAULT NULL,
  `productCode` varchar(10) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `UOM` varchar(10) DEFAULT NULL,
  `price_per_UOM` decimal(30,2) DEFAULT NULL,
  `note` varchar(80) DEFAULT NULL,
  `product_status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_products`
--

INSERT INTO `order_products` (`nomor_surat_jalan`, `moving_no_moving`, `repack_no_repack`, `productCode`, `qty`, `UOM`, `price_per_UOM`, `note`, `product_status`) VALUES
('H810005211', '-', '-', 'RR-120-A', 5000, 'tray', 480000.00, '', 'in'),
('H810005211', '-', '-', 'MM-100-A', 3000, 'tray', 580000.00, '', 'in'),
('H810005212', '-', '-', 'RR-120-A', 3000, 'tray', 500000.00, '', 'in'),
('H810005212', '-', '-', 'MM-120-A', 5000, 'tray', 600000.00, '', 'in'),
('H810005213', '-', '-', 'RR-100-A', 3000, 'tray', 485000.00, '', 'in'),
('H810005213', '-', '-', 'MM-100-A', 3000, 'tray', 585000.00, '', 'in'),
('1/SJK/NON/08/2024', '-', '-', 'RR-120-A', 1000, 'tray', 500000.00, '', 'out'),
('1/SJK/NON/08/2024', '-', '-', 'MM-100-A', 1500, 'tray', 605000.00, '', 'out'),
('2/SJK/NON/08/2024', '-', '-', 'RR-120-A', 2000, 'tray', 500000.00, '', 'out'),
('2/SJK/NON/08/2024', '-', '-', 'RR-100-A', 1000, 'tray', 480000.00, '', 'out'),
('3/SJK/NON/08/2024', '-', '-', 'MM-120-A', 1500, 'tray', 615000.00, '', 'out'),
('3/SJK/NON/08/2024', '-', '-', 'MM-100-A', 3000, 'tray', 605000.00, '', 'out'),
('-', '1/SJP/APA/08/2024', '-', 'RR-120-A', 1500, 'tray', 490000.00, '', 'moving'),
('-', '2/SJP/APA/08/2024', '-', 'MM-120-A', 2000, 'tray', 605000.00, '', 'moving'),
('1/SJT/APA/08/2024', '-', '-', 'RR-120-A', 2500, 'tray', 485000.00, '', 'out_tax'),
('1/SJT/APA/08/2024', '-', '-', 'MM-100-A', 3000, 'tray', 585000.00, '', 'out_tax'),
('2/SJT/APA/08/2024', '-', '-', 'RR-120-A', 3000, 'tray', 505000.00, '', 'out_tax'),
('2/SJT/APA/08/2024', '-', '-', 'MM-120-A', 3000, 'tray', 605000.00, '', 'out_tax'),
('1/SJT/NON/08/2024', '-', '-', 'RR-100-A', 3000, 'tray', 0.00, '', 'out_tax'),
('1/SJT/NON/08/2024', '-', '-', 'MM-100-A', 3000, 'tray', 0.00, '', 'out_tax'),
('3/SJT/APA/08/2024', '-', '-', 'RR-100-A', 3000, 'tray', 490000.00, '', 'out_tax'),
('3/SJT/APA/08/2024', '-', '-', 'MM-100-A', 3000, 'tray', 590000.00, '', 'out_tax'),
('-', '1/SJP/RBL/08/2024', '-', 'RR-120-A', 10, 'tray', 0.00, '', 'moving'),
('-', '-', '1/SJR/APA/08/2024', 'RR-120-A', 1000, 'tray', 0.00, '', 'repack_awal'),
('-', '-', '1/SJR/APA/08/2024', 'RR-100-A', 1000, 'tray', 0.00, '', 'repack_akhir');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `nomor_surat_jalan` varchar(80) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_amount` decimal(30,2) DEFAULT NULL,
  `no_moving` varchar(80) DEFAULT NULL,
  `payment_id` char(36) NOT NULL DEFAULT (UUID())
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`nomor_surat_jalan`, `payment_date`, `payment_amount`, `no_moving`, `payment_id`) VALUES
('H810005212', '2024-08-09', 1997000000.00, '-', '1c89817e-5b95-11ef-b86d-5cbaef99b658'),
('H810005211', '2024-08-08', 4595400000.00, '-', '3f4a2f42-5b94-11ef-b86d-5cbaef99b658'),
('1/SJK/NON/08/2024', '2024-08-08', 1400000000.00, '-', '81765f1c-5bbf-11ef-bd84-5cbaef99b658'),
('2/SJK/NON/08/2024', '2024-08-09', 500000000.00, '-', 'aa07f3d3-5bbf-11ef-bd84-5cbaef99b658'),
('-', '2024-08-08', 815850000.00, '1/SJP/APA/08/2024', 'ad4e840d-5bcf-11ef-bd84-5cbaef99b658'),
('-', '2024-08-09', 500000000.00, '2/SJP/APA/08/2024', 'c38d3fc9-5bcf-11ef-bd84-5cbaef99b658'),
('1/SJT/APA/08/2024', '2024-08-08', 3832275000.00, '-', 'e45da993-5c72-11ef-b5fe-5cbaef99b658'),
('2/SJT/APA/08/2024', '2024-08-09', 1696300000.00, '-', 'f4992c30-5c72-11ef-b5fe-5cbaef99b658');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `productCode` varchar(10) NOT NULL,
  `productName` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`productCode`, `productName`) VALUES
('MK-100-BB', 'Mild Kapsul 100 Blueberry'),
('MM-100-A', 'Mild Mono 100 Acetatow'),
('MM-120-A', 'Mild Mono 120 Acetatow'),
('RF-100-A', 'Reguler Falvour 100 Acetatow'),
('RF-120-A', 'Reguler Falvour 120 Acetatow'),
('RR-100-A', 'Reguler Mono 100 Acetatow'),
('RR-120-A', 'Reguler Mono 120 Acetatow');

-- --------------------------------------------------------

--
-- Table structure for table `repacks`
--

DROP TABLE IF EXISTS `repacks`;
CREATE TABLE `repacks` (
  `no_repack` varchar(80) NOT NULL,
  `repack_date` date DEFAULT NULL,
  `storageCode` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repacks`
--

INSERT INTO `repacks` (`no_repack`, `repack_date`, `storageCode`) VALUES
('-', NULL, 'NON'),
('1/SJR/APA/08/2024', '2024-08-07', 'APA');

-- --------------------------------------------------------

--
-- Table structure for table `saldos`
--

DROP TABLE IF EXISTS `saldos`;
CREATE TABLE `saldos` (
  `productCode` varchar(10) NOT NULL,
  `storageCode` varchar(10) NOT NULL,
  `totalQty` int(11) DEFAULT NULL,
  `totalPrice` decimal(30,2) DEFAULT NULL,
  `saldoMonth` int(11) DEFAULT NULL,
  `saldoYear` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saldos`
--

INSERT INTO `saldos` (`productCode`, `storageCode`, `totalQty`, `totalPrice`, `saldoMonth`, `saldoYear`) VALUES
('MM-120-A', 'APA', 0, 0.00, 8, 2024),
('RR-100-A', 'APA', 1000, 485625000.00, 8, 2024),
('MM-100-A', 'APA', 0, 0.00, 8, 2024),
('RR-120-A', 'APA', 0, 0.00, 8, 2024),
('RR-120-A', 'RBL', -10, 0.00, 8, 2024),
('MM-120-A', 'BBB', 2000, 1210000000.00, 8, 2024),
('RR-120-A', 'BBB', 1500, 735000000.00, 8, 2024),
('RR-120-A', 'NON', 4000, 1950000000.00, 8, 2024),
('MM-120-A', 'NON', 3500, 2100000000.00, 8, 2024),
('MM-100-A', 'NON', 1500, 873750000.00, 8, 2024),
('RR-100-A', 'NON', 3000, 1456875000.00, 8, 2024);

-- --------------------------------------------------------

--
-- Table structure for table `storages`
--

DROP TABLE IF EXISTS `storages`;
CREATE TABLE `storages` (
  `storageCode` varchar(10) NOT NULL,
  `storageName` varchar(80) DEFAULT NULL,
  `storageAddress` varchar(120) DEFAULT NULL,
  `storageNPWP` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `storages`
--

INSERT INTO `storages` (`storageCode`, `storageName`, `storageAddress`, `storageNPWP`) VALUES
('APA', 'Agraprana Paramitha Amartya', 'jalan agraprana', '003.111.111.111-111'),
('BBB', 'Berkah Berbagi Berkat', 'jalan Berkah Berbagi', '005.111.111.111-111'),
('CBA', 'Catur Berkat Amartya', 'jalan catur berkat', '002.111.111.111-111'),
('NON', 'none', 'none', '001.111.111.111-111'),
('RBL', 'Rizky Berkah Lumintu', 'jalan Rizky Berkah', '004.111.111.111-111'),
('RQQ', 'Rorqual', 'aa', 'aaaa213');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `userID` char(36) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `userType` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `username`, `password`, `userType`) VALUES
('37d72912-5ad0-11ef-b5d1-5cbaef99b658', 'admin1', '$2y$10$I6HDp20xfQ.eyexX6Xu0XOmiCwmPmVGf7WuNTF6LApGFg0kxVcbIG', 1),
('3de53767-5ad0-11ef-b5d1-5cbaef99b658', 'user1', '$2y$10$5Ymv4R2Qn3Fw/8FKRJxHmu5XAmO2G0mfXTK2naenRcsssZuvPBLVa', 0);

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

DROP TABLE IF EXISTS `vendors`;
CREATE TABLE `vendors` (
  `vendorCode` varchar(10) NOT NULL,
  `vendorName` varchar(100) DEFAULT NULL,
  `vendorAddress` varchar(120) DEFAULT NULL,
  `vendorNPWP` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`vendorCode`, `vendorName`, `vendorAddress`, `vendorNPWP`) VALUES
('ASTRA', 'As astra', 'jln astralll', '100.90.111.111-111'),
('COC', 'Coca', 'jln coca', '100.111.111.199-111'),
('NON', 'none', 'none', '000.111.111.111-111'),
('NVIDIA', 'video graphics', 'jln video', '100.90.111.111-111'),
('SPIRAL', 'spiral sphinx', 'jln assdisekai', '1213.123.1223.12'),
('WIM', 'Wismilak Inti Makmur', 'jln Wismilak Inti', '100.111.111.111-111');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customerCode`),
  ADD UNIQUE KEY `customerCode` (`customerCode`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD KEY `nomor_surat_jalan` (`nomor_surat_jalan`),
  ADD KEY `no_moving` (`no_moving`);

--
-- Indexes for table `movings`
--
ALTER TABLE `movings`
  ADD PRIMARY KEY (`no_moving`),
  ADD UNIQUE KEY `no_moving` (`no_moving`),
  ADD KEY `storageCodeSender` (`storageCodeSender`),
  ADD KEY `storageCodeReceiver` (`storageCodeReceiver`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`nomor_surat_jalan`),
  ADD UNIQUE KEY `nomor_surat_jalan` (`nomor_surat_jalan`),
  ADD KEY `storageCode` (`storageCode`),
  ADD KEY `vendorCode` (`vendorCode`),
  ADD KEY `customerCode` (`customerCode`);

--
-- Indexes for table `order_products`
--
ALTER TABLE `order_products`
  ADD KEY `nomor_surat_jalan` (`nomor_surat_jalan`),
  ADD KEY `moving_no_moving` (`moving_no_moving`),
  ADD KEY `repack_no_repack` (`repack_no_repack`),
  ADD KEY `productCode` (`productCode`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `nomor_surat_jalan` (`nomor_surat_jalan`),
  ADD KEY `no_moving` (`no_moving`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`productCode`),
  ADD UNIQUE KEY `productCode` (`productCode`);

--
-- Indexes for table `repacks`
--
ALTER TABLE `repacks`
  ADD PRIMARY KEY (`no_repack`),
  ADD UNIQUE KEY `no_repack` (`no_repack`),
  ADD KEY `storageCode` (`storageCode`);

--
-- Indexes for table `saldos`
--
ALTER TABLE `saldos`
  ADD KEY `productCode` (`productCode`),
  ADD KEY `storageCode` (`storageCode`);

--
-- Indexes for table `storages`
--
ALTER TABLE `storages`
  ADD PRIMARY KEY (`storageCode`),
  ADD UNIQUE KEY `storageCode` (`storageCode`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`vendorCode`),
  ADD UNIQUE KEY `vendorCode` (`vendorCode`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `movings`
--
ALTER TABLE `movings`
  ADD CONSTRAINT `movings_ibfk_1` FOREIGN KEY (`storageCodeSender`) REFERENCES `storages` (`storageCode`),
  ADD CONSTRAINT `movings_ibfk_2` FOREIGN KEY (`storageCodeReceiver`) REFERENCES `storages` (`storageCode`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`storageCode`) REFERENCES `storages` (`storageCode`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`vendorCode`) REFERENCES `vendors` (`vendorCode`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`customerCode`) REFERENCES `customers` (`customerCode`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
