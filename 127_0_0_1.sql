-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 04, 2026 at 03:41 PM
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
-- Database: `inventory`
--
CREATE DATABASE IF NOT EXISTS `inventory` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `inventory`;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `branch_id` int(11) NOT NULL,
  `branch_name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`branch_id`, `branch_name`, `address`) VALUES
(1, 'Sylhet', 'North East Bangladesh'),
(4, 'Head Quater', 'Mirpur 10, Dhaka, Bangladesh'),
(6, 'Rangpur', 'Rangpur, Bangladesh'),
(8, 'Chittagang', 'Faruk Chembar, Chittagang');

-- --------------------------------------------------------

--
-- Table structure for table `cash_deposits`
--

CREATE TABLE `cash_deposits` (
  `deposit_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `deposit_date` date DEFAULT NULL,
  `slip_photo` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cash_deposits`
--

INSERT INTO `cash_deposits` (`deposit_id`, `branch_id`, `amount`, `deposit_date`, `slip_photo`, `remarks`, `created_at`) VALUES
(5, 1, 214970, '2026-02-02', '1770036710_slipdeposit.png', '214,970/= DEPOSIT TO DBBL', '2026-02-02 12:51:50');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `cat_id` int(11) NOT NULL,
  `cat_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`cat_id`, `cat_name`) VALUES
(12, 'Phone Accesories'),
(15, 'Computer Accessories'),
(16, 'Pen Drive'),
(18, 'Mother Board'),
(19, 'Printer'),
(20, 'Networking Products ');

-- --------------------------------------------------------

--
-- Table structure for table `corporate_customer`
--

CREATE TABLE `corporate_customer` (
  `corporate_id` int(11) NOT NULL,
  `corporate_name` varchar(255) DEFAULT NULL,
  `corporate_number` varchar(50) DEFAULT NULL,
  `corporate_address` text DEFAULT NULL,
  `corporate_code` varchar(50) DEFAULT NULL,
  `corporate_email` varchar(100) DEFAULT NULL,
  `accounts_approvel_status` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `corporate_customer`
--

INSERT INTO `corporate_customer` (`corporate_id`, `corporate_name`, `corporate_number`, `corporate_address`, `corporate_code`, `corporate_email`, `accounts_approvel_status`) VALUES
(1, 'LGED Bangladesh Sylhet', '017897512655', '52, Nobab Road, Sylhet, Bangladeesh', 'LGEDSYLHET3326', 'lged@gmail.com', 1),
(2, 'Bangladesh Bank Sylhet Section', '01745879623', '36, Taltola, Telihour, Sylhet, Bangladesh', 'BDBANKSYL8795', 'bdbanksyl@gmail.com', 1),
(3, 'Sylhet Railway Station', '0156975354', 'Railway Station, Sylhet', 'RSS2365', 'rss@gmail.com', 1),
(4, 'BRAC - Sylhet', '013649751364', 'Temuki, Tuker Bazar, Sylhet', 'bracsyl2698', 'bracsyl@gmail.com', 1),
(5, 'Brac Rangpur', '013547950658', 'Rangpur, Rangpur', 'BRACRANG0365', 'bracrang@gmail.com', 1),
(6, 'North East University Bangladesh', '0178065982', 'DDDDDDD', 'NEUB001', 'neub@gmail.com', 0);

-- --------------------------------------------------------

--
-- Table structure for table `corporate_quotation`
--

CREATE TABLE `corporate_quotation` (
  `corporate_quotation_id` int(11) NOT NULL,
  `corporate_quotation_invoice_id` varchar(50) DEFAULT NULL,
  `corporate_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_stock_id` int(11) DEFAULT NULL,
  `corporate_code` varchar(50) DEFAULT NULL,
  `branch_id` int(11) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `buy_price` decimal(10,2) DEFAULT NULL,
  `offer_price` decimal(10,2) DEFAULT NULL,
  `manager_approvel_status` int(11) DEFAULT 0,
  `bill_status` int(11) NOT NULL DEFAULT 0,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `corporate_quotation`
--

INSERT INTO `corporate_quotation` (`corporate_quotation_id`, `corporate_quotation_invoice_id`, `corporate_id`, `product_id`, `product_stock_id`, `corporate_code`, `branch_id`, `product_name`, `qty`, `buy_price`, `offer_price`, `manager_approvel_status`, `bill_status`, `remarks`, `created_at`) VALUES
(30, 'CQ-20260202182916', 2, 44, 57, 'BDBANKSYL8795', 1, ' Gigabyte B760 GAMING X WIFI6E GEN5 LGA1700 DDR5 ATX Motherboard-cover-thumbnailGigabyte B760 GAMING X WIFI6E GEN5 LGA1700 DDR5 ATX Motherboard-gallery-1Gigabyte B760 GAMING X WIFI6E GEN5 LGA1700 DDR5 ATX Motherboard-gallery-2Gigabyte B760 GAMING X WIFI6E', 5, 25500.00, 25500.00, 1, 1, 'AAAAAAAAAAA', '2026-02-02 17:29:16'),
(31, 'CQ-20260202182916', 2, 42, 59, 'BDBANKSYL8795', 1, 'Earphone 3.6mm Jack', 10, 280.00, 280.00, 1, 1, 'AAAAAAAAAAA', '2026-02-02 17:29:16'),
(32, 'CQ-20260202182916', 2, 43, 60, 'BDBANKSYL8795', 1, 'Epson EcoTank L6370 (A4) Wi-Fi Duplex All-in-One 15W Ink Tank Printer', 5, 42000.00, 42000.00, 1, 1, 'AAAAAAAAAAA', '2026-02-02 17:29:16'),
(33, 'CQ-20260202182916', 2, 40, 61, 'BDBANKSYL8795', 1, 'PNY 256GB NVME CS1031 SOLID STATE DRIVE ', 5, 3800.00, 3800.00, 1, 1, 'AAAAAAAAAAA', '2026-02-02 17:29:16'),
(36, 'CQ-20260202183859', 1, 40, 61, 'LGEDSYLHET3326', 1, 'PNY 256GB NVME CS1031 SOLID STATE DRIVE ', 5, 3800.00, 3900.00, 0, 0, 'DDD', '2026-02-02 17:38:59'),
(37, 'CQ-20260202183859', 1, 39, 64, 'LGEDSYLHET3326', 1, 'TwinMOS X3 64GB USB 3.0 Pen Drive', 2, 580.00, 600.00, 0, 0, 'DDD', '2026-02-02 17:38:59');

-- --------------------------------------------------------

--
-- Table structure for table `corporate_sales`
--

CREATE TABLE `corporate_sales` (
  `corporate_sales_id` int(11) NOT NULL,
  `corporate_sales_invoice_id` varchar(50) DEFAULT NULL,
  `corporate_quotation_id` int(11) DEFAULT NULL,
  `corporate_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_stock_id` int(11) DEFAULT NULL,
  `corporate_code` varchar(50) DEFAULT NULL,
  `product_code` varchar(50) DEFAULT NULL,
  `corporate_name` varchar(150) DEFAULT NULL,
  `product_name` varchar(150) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `buy_price` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `bill_collection_date` date DEFAULT NULL,
  `bill_collection_status` int(11) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `corporate_sales`
--

INSERT INTO `corporate_sales` (`corporate_sales_id`, `corporate_sales_invoice_id`, `corporate_quotation_id`, `corporate_id`, `product_id`, `product_stock_id`, `corporate_code`, `product_code`, `corporate_name`, `product_name`, `branch_id`, `qty`, `buy_price`, `selling_price`, `delivery_date`, `bill_collection_date`, `bill_collection_status`, `remarks`, `created_at`) VALUES
(16, 'CS-20260202183606', 30, 2, 44, 57, 'BDBANKSYL8795', 'B760GIG', 'Bangladesh Bank Sylhet Section', ' Gigabyte B760 GAMING X WIFI6E GEN5 LGA1700 DDR5 ATX Motherboard-cover-thumbnailGigabyte B760 GAMING X WIFI6E GEN5 LGA1700 DDR5 ATX Motherboard-galler', 1, 5, 25500.00, 25500.00, '2026-02-03', '2026-02-03', 0, 'DONE', '2026-02-02 17:36:06'),
(17, 'CS-20260202183606', 31, 2, 42, 59, 'BDBANKSYL8795', '0403054', 'Bangladesh Bank Sylhet Section', 'Earphone 3.6mm Jack', 1, 10, 280.00, 280.00, '2026-02-03', '2026-02-03', 0, 'DONE', '2026-02-02 17:36:06'),
(18, 'CS-20260202183606', 32, 2, 43, 60, 'BDBANKSYL8795', 'L6370A4', 'Bangladesh Bank Sylhet Section', 'Epson EcoTank L6370 (A4) Wi-Fi Duplex All-in-One 15W Ink Tank Printer', 1, 5, 42000.00, 42000.00, '2026-02-03', '2026-02-03', 0, 'DONE', '2026-02-02 17:36:06'),
(19, 'CS-20260202183606', 33, 2, 40, 61, 'BDBANKSYL8795', 'M280CS1031', 'Bangladesh Bank Sylhet Section', 'PNY 256GB NVME CS1031 SOLID STATE DRIVE ', 1, 5, 3800.00, 3800.00, '2026-02-03', '2026-02-03', 0, 'DONE', '2026-02-02 17:36:06');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `cus_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `customer_code` int(11) NOT NULL,
  `ledger` int(11) NOT NULL,
  `due_amount` int(11) NOT NULL,
  `address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`cus_id`, `name`, `email`, `phone`, `customer_code`, `ledger`, `due_amount`, `address`) VALUES
(4, 'Kamal', 'kamal@gmail.com', '01756669784', 115523, 47000, 3000, 'Kamal Bazar, Sylhet'),
(5, 'Tomal', 'tomal@gmail.com', '01346971132', 115526, 35000, 0, 'Tomal Bazar, sylhet'),
(6, 'Rafi', 'rafi@gmail.com', '0124875666', 222669, 22642, 7358, 'Boroikandi 1no, Sylhet'),
(9, 'Emon', 'emon@gmail.com', '01640027997', 332589, 25000, 0, 'Temuki, Tuker Bazar, Sylhet, Bangladesh'),
(10, 'Tanim', 'uftanim2@gmail.com', '01756569753', 369852, 20000, 0, 'Pirpur, Tuker Bazar Sylhet'),
(12, 'Computer City ', 'demo@gmail.com', '0168793120', 230126, 5000, 0, 'Zindabazar, Sylhet, Bangladesh');

-- --------------------------------------------------------

--
-- Table structure for table `direct_sales`
--

CREATE TABLE `direct_sales` (
  `sale_id` int(11) NOT NULL,
  `invoice_no` varchar(255) DEFAULT NULL,
  `cus_id` int(11) DEFAULT NULL,
  `customer_code` int(11) DEFAULT NULL,
  `product_stock_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `product_code` varchar(255) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `buy_price` int(11) NOT NULL,
  `sell_price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `due_amount` decimal(10,2) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `discount_percent` decimal(5,2) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `direct_sales`
--

INSERT INTO `direct_sales` (`sale_id`, `invoice_no`, `cus_id`, `customer_code`, `product_stock_id`, `product_name`, `product_code`, `qty`, `buy_price`, `sell_price`, `total_price`, `paid_amount`, `due_amount`, `branch_id`, `status`, `created_at`, `discount_percent`, `remarks`) VALUES
(47, 'INV_20260202_124637_37', 4, 115523, 57, ' Gigabyte B760 GAMING X WIFI6E GEN5 LGA1700 DDR5 ATX Motherboard-cover-thumbnailGigabyte B760 GAMING X WIFI6E GEN5 LGA1700 DDR5 ATX Motherboard-gallery-1Gigabyte B760 GAMING X WIFI6E GEN5 LGA1700 DDR5 ATX Motherboard-gallery-2Gigabyte B760 GAMING X WIFI6E', 'B760GIG', 5, 25500, 26000.00, 130000.00, 216070.00, 3000.00, 1, 1, '2026-02-02 11:46:37', 5.00, 'DIRECT SALES TO TANIM'),
(48, 'INV_20260202_124637_37', 4, 115523, 60, 'Epson EcoTank L6370 (A4) Wi-Fi Duplex All-in-One 15W Ink Tank Printer', 'L6370A4', 2, 42000, 44000.00, 88000.00, 216070.00, 3000.00, 1, 1, '2026-02-02 11:46:37', 5.00, 'DIRECT SALES TO TANIM'),
(49, 'INV_20260202_124637_37', 4, 115523, 61, 'PNY 256GB NVME CS1031 SOLID STATE DRIVE ', 'M280CS1031', 3, 3800, 4200.00, 12600.00, 216070.00, 3000.00, 1, 1, '2026-02-02 11:46:37', 5.00, 'DIRECT SALES TO TANIM'),
(50, 'INV_20260202_135254_97', 10, 369852, 61, 'PNY 256GB NVME CS1031 SOLID STATE DRIVE ', 'M280CS1031', 2, 3800, 4200.00, 8400.00, 7560.00, 0.00, 1, 1, '2026-02-02 12:52:54', 10.00, 'DIRECT SALES TO CUSTOMER'),
(61, 'INV_20260202_144236_50', 12, 230126, 60, 'Epson EcoTank L6370 (A4) Wi-Fi Duplex All-in-One 15W Ink Tank Printer', 'L6370A4', 1, 42000, 44000.00, 44000.00, 44000.00, 0.00, 1, 1, '2026-02-02 13:42:36', 0.00, ''),
(62, 'INV_20260202_184221_99', 6, 222669, 58, 'Corsair VENGEANCE RGB 16GB DDR5 6000MHz CL36 Desktop RAM', 'CMH16GX5M1E6000Z36', 5, 28000, 30000.00, 150000.00, 140000.00, 6858.00, 1, 0, '2026-02-02 17:42:21', 3.00, 'DDDD'),
(63, 'INV_20260202_184221_99', 6, 222669, 64, 'TwinMOS X3 64GB USB 3.0 Pen Drive', '26779', 2, 580, 700.00, 1400.00, 140000.00, 6858.00, 1, 0, '2026-02-02 17:42:21', 3.00, 'DDDD'),
(64, 'INV_20260204_153650_85', 6, 222669, 59, 'Earphone 3.6mm Jack', '0403054', 2, 280, 500.00, 1000.00, 500.00, 500.00, 1, 0, '2026-02-04 14:36:50', 0.00, 'fad');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `expense_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `expense_date` date NOT NULL,
  `expense_category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `vendor_name` varchar(150) DEFAULT NULL,
  `invoice_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`expense_id`, `branch_id`, `expense_date`, `expense_category`, `description`, `amount`, `vendor_name`, `invoice_pic`) VALUES
(6, 1, '2026-01-25', 'Courier Charge', 'FDALDKJFLA SDFAS', 100.00, 'SUNDORBAN COURIER', 'EXP_1769339256_6485.webp'),
(7, 1, '2026-02-02', 'Electricity Bill', 'Feb-26', 10000.00, 'Bangladesh Electiricity Commision', 'INV_1770053167_Basic_Ui__28186_29.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `ex_category`
--

CREATE TABLE `ex_category` (
  `exc_id` int(11) NOT NULL,
  `e_cat_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ex_category`
--

INSERT INTO `ex_category` (`exc_id`, `e_cat_name`) VALUES
(2, 'House Rent'),
(3, 'Sneaks Bill'),
(4, 'Electricity Bill'),
(5, 'Courier Charge'),
(8, 'Convence Bill');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `cat_id` int(11) DEFAULT NULL,
  `photo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `name`, `code`, `cat_id`, `photo`) VALUES
(39, 'TwinMOS X3 64GB USB 3.0 Pen Drive', '26779', 16, 'x3-ultra-01-500x500.webp'),
(40, 'PNY 256GB NVME CS1031 SOLID STATE DRIVE ', 'M280CS1031', 15, 'cs1031-256gb-500x500.jpg'),
(41, 'Type B Data Cable', '05604998', 12, '1755439092_charger.JPG'),
(42, 'Earphone 3.6mm Jack', '0403054', 12, '1755439116_earp.JPG'),
(43, 'Epson EcoTank L6370 (A4) Wi-Fi Duplex All-in-One 15W Ink Tank Printer', 'L6370A4', 19, '1769864591_Screenshot 2026-01-31 190214.png'),
(44, ' Gigabyte B760 GAMING X WIFI6E GEN5 LGA1700 DDR5 ATX Motherboard-cover-thumbnailGigabyte B760 GAMING X WIFI6E GEN5 LGA1700 DDR5 ATX Motherboard-gallery-1Gigabyte B760 GAMING X WIFI6E GEN5 LGA1700 DDR5 ATX Motherboard-gallery-2Gigabyte B760 GAMING X WIFI6E', 'B760GIG', 18, 'Screenshot 2026-01-31 185434.png'),
(45, 'Corsair VENGEANCE RGB 16GB DDR5 6000MHz CL36 Desktop RAM', 'CMH16GX5M1E6000Z36', 15, 'Screenshot 2026-01-31 185632.png');

-- --------------------------------------------------------

--
-- Table structure for table `product_stock`
--

CREATE TABLE `product_stock` (
  `product_stock_id` int(11) NOT NULL,
  `stock_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `cat_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `lot_no` varchar(50) DEFAULT NULL,
  `buy_price` decimal(10,2) DEFAULT NULL,
  `sell_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_stock`
--

INSERT INTO `product_stock` (`product_stock_id`, `stock_id`, `product_id`, `supplier_id`, `cat_id`, `branch_id`, `qty`, `lot_no`, `buy_price`, `sell_price`, `created_at`) VALUES
(50, 66, 42, 9, 12, 4, 70, 'LOT_20260131_U1AHLP', 280.00, 500.00, '2026-01-31 12:36:26'),
(51, 67, 40, 9, 15, 4, 50, 'LOT_20260131_U1AHLP', 3800.00, 4200.00, '2026-01-31 12:36:26'),
(52, 68, 44, 11, 18, 4, 50, 'LOT_20260131_08NG6Z', 25500.00, 26000.00, '2026-01-31 13:10:57'),
(53, 72, 39, 11, 16, 4, 0, 'LOT_20260131_M39JS6', 580.00, 650.00, '2026-01-31 13:12:04'),
(54, 70, 43, 12, 19, 4, 30, 'LOT_20260131_H0XSZQ', 42000.00, 44000.00, '2026-01-31 13:34:18'),
(55, 71, 45, 12, 15, 4, 90, 'LOT_20260131_H0XSZQ', 28000.00, 29500.00, '2026-01-31 13:34:18'),
(57, 68, 44, 11, 18, 1, 20, 'LOT_20260131_08NG6Z', 25500.00, 26000.00, '2026-02-02 11:34:05'),
(58, 71, 45, 12, 15, 1, 30, 'LOT_20260131_H0XSZQ', 28000.00, 29500.00, '2026-02-02 11:34:05'),
(59, 66, 42, 9, 12, 1, 20, 'LOT_20260131_U1AHLP', 280.00, 500.00, '2026-02-02 11:34:05'),
(60, 70, 43, 12, 19, 1, 12, 'LOT_20260131_H0XSZQ', 42000.00, 44000.00, '2026-02-02 11:34:06'),
(61, 67, 40, 9, 15, 1, 20, 'LOT_20260131_U1AHLP', 3800.00, 4200.00, '2026-02-02 11:34:06'),
(63, 74, 39, 11, 16, 4, 10, 'LOT_20260202_42QDJO', 580.00, 650.00, '2026-02-02 11:37:44'),
(64, 74, 39, 11, 16, 1, 10, 'LOT_20260202_42QDJO', 580.00, 650.00, '2026-02-02 11:38:33'),
(65, 75, 41, 10, 12, 4, 200, 'LOT_20260202_KQ7BZF', 180.00, 250.00, '2026-02-04 13:58:28'),
(66, 76, 42, 10, 12, 4, 200, 'LOT_20260202_KQ7BZF', 280.00, 500.00, '2026-02-04 14:00:59'),
(67, 80, 42, 9, 12, 4, 10, 'LOT_20260204_9L6Q83', 250.00, 300.00, '2026-02-04 14:03:23');

-- --------------------------------------------------------

--
-- Table structure for table `product_transfer`
--

CREATE TABLE `product_transfer` (
  `id` int(11) NOT NULL,
  `transfer_id` varchar(50) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_stock_id` int(11) DEFAULT NULL,
  `from_branch` int(11) DEFAULT NULL,
  `to_branch` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `lot_no` varchar(100) DEFAULT NULL,
  `buy_price` decimal(10,2) DEFAULT NULL,
  `sell_price` decimal(10,2) DEFAULT NULL,
  `stock_manager_approval_status` int(11) NOT NULL DEFAULT 0,
  `transfer_status` int(11) NOT NULL DEFAULT 0,
  `branch_to_branch_status` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_transfer`
--

INSERT INTO `product_transfer` (`id`, `transfer_id`, `product_id`, `product_stock_id`, `from_branch`, `to_branch`, `qty`, `lot_no`, `buy_price`, `sell_price`, `stock_manager_approval_status`, `transfer_status`, `branch_to_branch_status`, `created_at`) VALUES
(77, 'REQ_20260202_122140_STAQ', 44, 52, 1, 4, 30, 'LOT_20260131_08NG6Z', 25500.00, 26000.00, 1, 2, 1, '2026-02-02 17:22:47'),
(78, 'REQ_20260202_122140_STAQ', 45, 55, 1, 4, 30, 'LOT_20260131_H0XSZQ', 28000.00, 29500.00, 1, 2, 1, '2026-02-02 17:22:47'),
(79, 'REQ_20260202_122140_STAQ', 42, 50, 1, 4, 30, 'LOT_20260131_U1AHLP', 280.00, 500.00, 1, 2, 1, '2026-02-02 17:22:47'),
(80, 'REQ_20260202_122140_STAQ', 43, 54, 1, 4, 20, 'LOT_20260131_H0XSZQ', 42000.00, 44000.00, 1, 2, 1, '2026-02-02 17:22:47'),
(81, 'REQ_20260202_122140_STAQ', 40, 51, 1, 4, 30, 'LOT_20260131_U1AHLP', 3800.00, 4200.00, 1, 2, 1, '2026-02-02 17:22:47'),
(82, 'REQ_20260202_122140_STAQ', 39, NULL, 1, 4, 10, 'LOT_20260131_DXBEN7', 580.00, 0.00, 1, 2, 1, '2026-02-02 17:22:47'),
(83, 'TRF_20260202_123818_FDY6', 39, 63, 4, 1, 10, 'LOT_20260202_42QDJO', 580.00, 650.00, 1, 2, 0, '2026-02-02 17:38:33'),
(84, 'REQ_20260202_184312_UFL2', 45, 55, 1, 4, 10, 'LOT_20260131_H0XSZQ', 28000.00, 29500.00, 1, 1, 1, '2026-02-02 23:43:29'),
(85, 'REQ_20260202_184312_UFL2', 42, 50, 1, 4, 10, 'LOT_20260131_U1AHLP', 280.00, 500.00, 1, 1, 1, '2026-02-02 23:43:29'),
(86, 'REQ_20260202_184356_PUAZ', 45, 55, 1, 4, 5, 'LOT_20260131_H0XSZQ', 28000.00, 29500.00, 0, 0, 1, '2026-02-02 23:44:08'),
(87, 'REQ_20260202_184502_TAEX', 43, 60, 6, 1, 3, 'LOT_20260131_H0XSZQ', 42000.00, 44000.00, 1, 0, 1, '2026-02-02 23:45:21'),
(88, 'REQ_20260202_190353_0PW2', 42, 50, 6, 4, 5, 'LOT_20260131_U1AHLP', 280.00, 500.00, 0, 0, 1, '2026-02-03 00:04:06'),
(89, 'REQ_20260202_190409_GHEY', 45, 55, 6, 4, 5, 'LOT_20260131_H0XSZQ', 28000.00, 29500.00, 1, 0, 1, '2026-02-03 00:04:25');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order`
--

CREATE TABLE `purchase_order` (
  `stock_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `lot_no` varchar(255) NOT NULL,
  `buy_price` int(11) NOT NULL,
  `sell_price` int(11) NOT NULL,
  `paid_amount` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `invoice_no` varchar(255) NOT NULL,
  `adjusted_with` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cat_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_order`
--

INSERT INTO `purchase_order` (`stock_id`, `product_id`, `supplier_id`, `qty`, `lot_no`, `buy_price`, `sell_price`, `paid_amount`, `status`, `invoice_no`, `adjusted_with`, `created_at`, `cat_id`) VALUES
(66, 42, 9, 100, 'LOT_20260131_U1AHLP', 280, 500, 28000, 1, 'INV20260131-8RT0GO', NULL, '2026-01-31 07:36:05', NULL),
(67, 40, 9, 80, 'LOT_20260131_U1AHLP', 3800, 4200, 304000, 1, 'INV20260131-8RT0GO', NULL, '2026-01-31 07:36:05', NULL),
(68, 44, 11, 80, 'LOT_20260131_08NG6Z', 25500, 26000, 2040000, 1, 'INV20260131-WG8BM3', NULL, '2026-01-31 08:01:09', NULL),
(69, 39, 11, 200, 'LOT_20260131_08NG6Z', 580, 650, 0, 2, 'INV20260131-WG8BM3', NULL, '2026-01-31 08:01:09', NULL),
(70, 43, 12, 50, 'LOT_20260131_H0XSZQ', 42000, 44000, 2100000, 1, 'INV20260131-ELYCI2', NULL, '2026-01-31 08:04:35', NULL),
(71, 45, 12, 120, 'LOT_20260131_H0XSZQ', 28000, 29500, 3360000, 1, 'INV20260131-ELYCI2', NULL, '2026-01-31 08:04:35', NULL),
(72, 39, 11, 20, 'LOT_20260131_M39JS6', 580, 650, 11600, 1, 'INV20260131-L3T9XI', NULL, '2026-01-31 08:11:47', NULL),
(73, 39, 11, 20, 'LOT_20260131_DXBEN7', 580, 0, 8000, 1, 'INV20260131-X0C4Y6', 'SF_20260131_25876B', '2026-01-31 08:33:55', NULL),
(74, 39, 11, 20, 'LOT_20260202_42QDJO', 580, 650, 8000, 1, 'INV20260202-F91X62', NULL, '2026-02-02 06:37:27', NULL),
(75, 41, 10, 200, 'LOT_20260202_KQ7BZF', 180, 250, 30000, 1, 'INV20260202-E9LU2B', NULL, '2026-02-02 13:03:13', NULL),
(76, 42, 10, 200, 'LOT_20260202_KQ7BZF', 280, 500, 56000, 1, 'INV20260202-E9LU2B', NULL, '2026-02-02 13:03:13', NULL),
(77, 39, 11, 20, 'LOT_20260204_X5VSP0', 580, 680, 0, 2, 'INV20260204-LR06ST', 'SF_20260131_25876B', '2026-02-04 08:30:13', NULL),
(80, 42, 9, 10, 'LOT_20260204_9L6Q83', 250, 300, 2500, 1, 'INV20260204-V2C7Q4', NULL, '2026-02-04 09:03:01', NULL),
(81, 41, 9, 10, 'LOT_20260204_9L6Q83', 350, 400, 3500, 0, 'INV20260204-V2C7Q4', NULL, '2026-02-04 09:03:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_return`
--

CREATE TABLE `purchase_return` (
  `id` int(11) NOT NULL,
  `lot_no` varchar(100) DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `buy_price` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `return_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_return`
--

INSERT INTO `purchase_return` (`id`, `lot_no`, `invoice_no`, `product_id`, `supplier_id`, `qty`, `buy_price`, `total`, `reason`, `return_date`) VALUES
(7, 'LOT_20260131_08NG6Z', 'INV20260131-WG8BM3', 39, 11, 200, 580.00, 116000.00, 'NO NEED', '2026-01-31 19:06:36');

-- --------------------------------------------------------

--
-- Table structure for table `stock_faulty_items`
--

CREATE TABLE `stock_faulty_items` (
  `id` int(11) NOT NULL,
  `stock_faulty_id` varchar(50) DEFAULT NULL,
  `product_stock_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `cat_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `lot_no` varchar(50) DEFAULT NULL,
  `faulty_photo` varchar(255) NOT NULL,
  `buy_price` decimal(10,2) DEFAULT NULL,
  `adjustable_amount` decimal(10,2) DEFAULT NULL,
  `loss_amount` int(11) NOT NULL,
  `remarks` varchar(500) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_faulty_items`
--

INSERT INTO `stock_faulty_items` (`id`, `stock_faulty_id`, `product_stock_id`, `product_id`, `supplier_id`, `cat_id`, `qty`, `lot_no`, `faulty_photo`, `buy_price`, `adjustable_amount`, `loss_amount`, `remarks`, `created_at`, `status`) VALUES
(13, 'SF_20260131_25876B', 53, 39, 11, 16, 20, 'LOT_20260131_M39JS6', 'FLT_1769866370_0.jpg', 580.00, 0.00, 3600, 'broken', '2026-01-31 19:32:50', 1);

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id` int(11) NOT NULL,
  `sup_name` varchar(255) NOT NULL,
  `sup_add` varchar(255) NOT NULL,
  `sup_phone` varchar(255) NOT NULL,
  `sup_email` varchar(255) NOT NULL,
  `sup_photo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id`, `sup_name`, `sup_add`, `sup_phone`, `sup_email`, `sup_photo`) VALUES
(4, 'Supplier1', 'Addd', '01235656544', 'uftanim2@gmail.com', 'tamim.jpg'),
(6, 'Rafi Ashraf', 'Boroikandi, Sylhet', '01736682957', 'mdrafiasraf2016@gmail.com', 'rafi.enc'),
(9, 'Samsung', 'Mirpur, Dhaka, Bangladesh', '01754568418', 'samsung@gmail.com', 'avatar.jpg'),
(10, 'Honda', 'Gulshan-2, Dhaka, Bangladesh', '01313004506', 'honda@gmail.com', 'avatar.jpg'),
(11, 'Smrat Technologies', 'Mirpur Sheurapara', '01756564956', 'smart@gmail.com', 'avatar.jpg'),
(12, 'Computer Source 1', 'Dhanmondi 32, Dhaka, Bangladesh 250', '01378956442', 'cmpsource@gmail.com', 'Basic_Ui__28186_29.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `sup_invoice`
--

CREATE TABLE `sup_invoice` (
  `supplier_invoice_id` int(11) NOT NULL,
  `invoice_no` varchar(255) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `supp_id` int(11) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `due_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qty` int(11) NOT NULL DEFAULT 1,
  `code` varchar(100) NOT NULL,
  `adjusted_with` varchar(255) DEFAULT NULL,
  `datee` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sup_invoice`
--

INSERT INTO `sup_invoice` (`supplier_invoice_id`, `invoice_no`, `product_id`, `product_name`, `supp_id`, `paid_amount`, `due_amount`, `qty`, `code`, `adjusted_with`, `datee`) VALUES
(61, 'INV20260131-8RT0GO', 42, 'Earphone 3.6mm Jack', 9, 28000.00, 0.00, 100, '0403054', NULL, '2026-01-31'),
(62, 'INV20260131-8RT0GO', 40, 'PNY 256GB NVME CS1031 SOLID STATE DRIVE ', 9, 304000.00, 0.00, 80, 'M280CS1031', NULL, '2026-01-31'),
(63, 'INV20260131-WG8BM3', 44, ' Gigabyte B760 GAMING X WIFI6E GEN5 LGA1700 DDR5 ATX Motherboard-cover-thumbnailGigabyte B760 GAMING X WIFI6E GEN5 LGA1700 DDR5 ATX Motherboard-gallery-1Gigabyte B760 GAMING X WIFI6E GEN5 LGA1700 DDR5 ATX Motherboard-gallery-2Gigabyte B760 GAMING X WIFI6E', 11, 2040000.00, 0.00, 80, 'B760GIG', NULL, '2026-01-31'),
(64, 'INV20260131-WG8BM3', 39, 'TwinMOS X3 64GB USB 3.0 Pen Drive', 11, 116000.00, 0.00, 200, '26779', NULL, '2026-01-31'),
(65, 'INV20260131-ELYCI2', 43, 'Epson EcoTank L6370 (A4) Wi-Fi Duplex All-in-One 15W Ink Tank Printer', 12, 2100000.00, 0.00, 50, 'L6370A4', NULL, '2026-01-31'),
(66, 'INV20260131-ELYCI2', 45, 'Corsair VENGEANCE RGB 16GB DDR5 6000MHz CL36 Desktop RAM', 12, 3360000.00, 0.00, 120, 'CMH16GX5M1E6000Z36', NULL, '2026-01-31'),
(67, 'INV20260131-L3T9XI', 39, 'TwinMOS X3 64GB USB 3.0 Pen Drive', 11, 11600.00, 0.00, 20, '26779', NULL, '2026-01-31'),
(68, 'INV20260131-X0C4Y6', 39, 'TwinMOS X3 64GB USB 3.0 Pen Drive', 11, 8000.00, 3600.00, 20, '26779', 'SF_20260131_25876B', '2026-01-31'),
(69, 'INV20260202-F91X62', 39, 'TwinMOS X3 64GB USB 3.0 Pen Drive', 11, 8000.00, 3600.00, 20, '26779', NULL, '2026-02-02'),
(72, 'INV20260204-LR06ST', 39, 'TwinMOS X3 64GB USB 3.0 Pen Drive', 11, 8001.00, 3599.00, 20, '26779', 'SF_20260131_25876B', '2026-02-04'),
(75, 'INV20260204-V2C7Q4', 42, 'Earphone 3.6mm Jack', 9, 2500.00, 0.00, 10, '0403054', NULL, '2026-02-04'),
(76, 'INV20260204-V2C7Q4', 41, 'Type B Data Cable', 9, 3500.00, 0.00, 10, '05604998', NULL, '2026-02-04');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `s_name` varchar(255) NOT NULL,
  `f_name` varchar(255) NOT NULL,
  `m_name` varchar(255) NOT NULL,
  `dod` varchar(255) NOT NULL,
  `blood` varchar(255) NOT NULL,
  `gender` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `per_careof` varchar(255) NOT NULL,
  `per_village` varchar(255) NOT NULL,
  `pdivi` varchar(255) NOT NULL,
  `pdist` varchar(255) NOT NULL,
  `p_posto` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `nid` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `branch_id` int(11) DEFAULT NULL,
  `user_type` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `s_name`, `f_name`, `m_name`, `dod`, `blood`, `gender`, `phone`, `per_careof`, `per_village`, `pdivi`, `pdist`, `p_posto`, `image`, `nid`, `email`, `password`, `status`, `branch_id`, `user_type`) VALUES
(2, 'Omor Faruque Tanim', 'Abdul Hannan', 'Mrs Nazma Begum', '2024-09-03', 'B+', 'Male', '01756569753', 'Father', 'Pirpur', 'Sylhet', 'Sylhet', 'Tuker bazar', 'WhatsApp Image 2024-04-27 at 3.08.45 PM.jpeg', 'nid.JPG', 'uftanim2@gmail.com', 'tanim123', 1, 1, 4),
(6, 'Abu Sufian Emon', 'DDDD', 'MMMMM', '2005-05-05', 'B+', 'Male', '01640027997', 'Father', 'Najregau', 'Sylhet', 'Sylhet', 'Tuker bazar', 'emon.jpg', 'nid.JPG', 'sufianemon2024@gmail.com', 'emon123', 1, 4, 2),
(8, 'Branch Manager', 'DDJDK', 'MMMMM', '2025-08-01', 'AB+', 'Male', '01754678718', 'FDAS', 'DFAD', 'DFADa', 'DFAD', 'ADFd', 'Basic_Ui__28186_29.jpg', 'nid.JPG', 'osiloscoop@gmail.com', '123456', 1, 1, 3),
(9, 'Mashiath Chowdhury', 'Mr Kamal', 'KKKKKKK', '2000-10-21', 'B+', 'Female', '01796592345', 'Father', 'FenchuGanj Sylhet', 'Sylhet', 'Sylhet', 'Sadar', 'Screenshot 2026-01-03 193132.png', 'Screenshot 2026-01-04 144647.png', 'mashi@gmail.com', 'mashi123', 1, 4, 1),
(10, 'Manager Rangpur', 'DDD', 'MMMM', '2026-01-02', 'A+', 'Male', '015454678974', 'fdsas', 'sdafds', 'dsfas', 'dafs', 'dasf', 'Screenshot 2026-01-03 183148.png', 'Screenshot 2026-01-04 144808.png', 'rangpur@gmail.com', 'rangpur123', 1, 6, 3),
(11, 'Rangpur Executive', 'DDD', 'DDD', '2025-12-04', 'AB+', 'Male', '015454678974', 'Father', 'Rangpur', 'Rangpur', 'Rangpur', 'Rangpur', 'Screenshot 2026-01-18 172037.png', 'Screenshot 2026-01-03 193132.png', 'omor_tanim@gbpl.com.bd', '654321', 1, 6, 4),
(13, 'Omo Fa Tanim', 'Abdul Hannan', 'Nazma Begum', '2009-10-02', 'B+', 'Male', '01606995141', 'Father', 'Pirpur', 'Sylhet', 'Sylhet', 'Tuker bazar', 'WhatsApp Image 2024-04-27 at 3.08.45 PM.jpeg', 'nid.JPG', 'oftanim1001@gmail.com', 'acc123', 1, 4, 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`branch_id`);

--
-- Indexes for table `cash_deposits`
--
ALTER TABLE `cash_deposits`
  ADD PRIMARY KEY (`deposit_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`cat_id`);

--
-- Indexes for table `corporate_customer`
--
ALTER TABLE `corporate_customer`
  ADD PRIMARY KEY (`corporate_id`),
  ADD UNIQUE KEY `corporate_code` (`corporate_code`);

--
-- Indexes for table `corporate_quotation`
--
ALTER TABLE `corporate_quotation`
  ADD PRIMARY KEY (`corporate_quotation_id`),
  ADD KEY `corporate_id` (`corporate_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `product_stock_id` (`product_stock_id`),
  ADD KEY `fk_quotation_branch` (`branch_id`);

--
-- Indexes for table `corporate_sales`
--
ALTER TABLE `corporate_sales`
  ADD PRIMARY KEY (`corporate_sales_id`),
  ADD KEY `corporate_quotation_id` (`corporate_quotation_id`),
  ADD KEY `corporate_id` (`corporate_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `product_stock_id` (`product_stock_id`),
  ADD KEY `branch_corporates_sales` (`branch_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`cus_id`);

--
-- Indexes for table `direct_sales`
--
ALTER TABLE `direct_sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `fk_sales_customer` (`cus_id`),
  ADD KEY `fk_sales_product_stock` (`product_stock_id`),
  ADD KEY `fk_sales_branch` (`branch_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`expense_id`),
  ADD KEY `fk_expense_branch` (`branch_id`);

--
-- Indexes for table `ex_category`
--
ALTER TABLE `ex_category`
  ADD PRIMARY KEY (`exc_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cat_id` (`cat_id`);
ALTER TABLE `product` ADD FULLTEXT KEY `code_2` (`code`);

--
-- Indexes for table `product_stock`
--
ALTER TABLE `product_stock`
  ADD PRIMARY KEY (`product_stock_id`),
  ADD KEY `fk_stock_purchase_order` (`stock_id`),
  ADD KEY `fk_stock_product` (`product_id`),
  ADD KEY `fk_stock_supplier` (`supplier_id`),
  ADD KEY `fk_stock_category` (`cat_id`),
  ADD KEY `fk_product_stock_branch` (`branch_id`);

--
-- Indexes for table `product_transfer`
--
ALTER TABLE `product_transfer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transfer_id` (`transfer_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `from_branch` (`from_branch`),
  ADD KEY `to_branch` (`to_branch`),
  ADD KEY `fk_product_transfer_stock` (`product_stock_id`);

--
-- Indexes for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD PRIMARY KEY (`stock_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_supplier` (`supplier_id`),
  ADD KEY `fk_purchase_category` (`cat_id`);

--
-- Indexes for table `purchase_return`
--
ALTER TABLE `purchase_return`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `stock_faulty_items`
--
ALTER TABLE `stock_faulty_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_stock_id` (`product_stock_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `cat_id` (`cat_id`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sup_invoice`
--
ALTER TABLE `sup_invoice`
  ADD PRIMARY KEY (`supplier_invoice_id`),
  ADD KEY `fk_product` (`product_id`),
  ADD KEY `fk_supplier` (`supp_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_branch` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `branch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cash_deposits`
--
ALTER TABLE `cash_deposits`
  MODIFY `deposit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `corporate_customer`
--
ALTER TABLE `corporate_customer`
  MODIFY `corporate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `corporate_quotation`
--
ALTER TABLE `corporate_quotation`
  MODIFY `corporate_quotation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `corporate_sales`
--
ALTER TABLE `corporate_sales`
  MODIFY `corporate_sales_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `cus_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `direct_sales`
--
ALTER TABLE `direct_sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ex_category`
--
ALTER TABLE `ex_category`
  MODIFY `exc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `product_stock`
--
ALTER TABLE `product_stock`
  MODIFY `product_stock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `product_transfer`
--
ALTER TABLE `product_transfer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `purchase_order`
--
ALTER TABLE `purchase_order`
  MODIFY `stock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `purchase_return`
--
ALTER TABLE `purchase_return`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `stock_faulty_items`
--
ALTER TABLE `stock_faulty_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sup_invoice`
--
ALTER TABLE `sup_invoice`
  MODIFY `supplier_invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `corporate_quotation`
--
ALTER TABLE `corporate_quotation`
  ADD CONSTRAINT `corporate_quotation_ibfk_1` FOREIGN KEY (`corporate_id`) REFERENCES `corporate_customer` (`corporate_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `corporate_quotation_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `corporate_quotation_ibfk_3` FOREIGN KEY (`product_stock_id`) REFERENCES `product_stock` (`product_stock_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_quotation_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `corporate_sales`
--
ALTER TABLE `corporate_sales`
  ADD CONSTRAINT `branch_corporates_sales` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `corporate_sales_ibfk_1` FOREIGN KEY (`corporate_quotation_id`) REFERENCES `corporate_quotation` (`corporate_quotation_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `corporate_sales_ibfk_2` FOREIGN KEY (`corporate_id`) REFERENCES `corporate_customer` (`corporate_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `corporate_sales_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `corporate_sales_ibfk_4` FOREIGN KEY (`product_stock_id`) REFERENCES `product_stock` (`product_stock_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `direct_sales`
--
ALTER TABLE `direct_sales`
  ADD CONSTRAINT `fk_sales_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sales_customer` FOREIGN KEY (`cus_id`) REFERENCES `customer` (`cus_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sales_product_stock` FOREIGN KEY (`product_stock_id`) REFERENCES `product_stock` (`product_stock_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `fk_expense_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_2` FOREIGN KEY (`cat_id`) REFERENCES `category` (`cat_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_stock`
--
ALTER TABLE `product_stock`
  ADD CONSTRAINT `fk_product_stock_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_stock_category` FOREIGN KEY (`cat_id`) REFERENCES `category` (`cat_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_stock_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_stock_purchase_order` FOREIGN KEY (`stock_id`) REFERENCES `purchase_order` (`stock_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_stock_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_transfer`
--
ALTER TABLE `product_transfer`
  ADD CONSTRAINT `fk_product_transfer_stock` FOREIGN KEY (`product_stock_id`) REFERENCES `product_stock` (`product_stock_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_transfer_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_transfer_ibfk_2` FOREIGN KEY (`from_branch`) REFERENCES `branches` (`branch_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_transfer_ibfk_3` FOREIGN KEY (`to_branch`) REFERENCES `branches` (`branch_id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD CONSTRAINT `fk_purchase_category` FOREIGN KEY (`cat_id`) REFERENCES `category` (`cat_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `purchase_return`
--
ALTER TABLE `purchase_return`
  ADD CONSTRAINT `purchase_return_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_return_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stock_faulty_items`
--
ALTER TABLE `stock_faulty_items`
  ADD CONSTRAINT `stock_faulty_items_ibfk_1` FOREIGN KEY (`product_stock_id`) REFERENCES `product_stock` (`product_stock_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_faulty_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_faulty_items_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_faulty_items_ibfk_4` FOREIGN KEY (`cat_id`) REFERENCES `category` (`cat_id`) ON DELETE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
