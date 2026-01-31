-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 11, 2025 at 07:19 PM
-- Server version: 5.7.44
-- PHP Version: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `m4d4pkhj4s0n_wk`
--
CREATE DATABASE IF NOT EXISTS `m4d4pkhj4s0n_wk` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `m4d4pkhj4s0n_wk`;

-- --------------------------------------------------------

--
-- Table structure for table `wk_address`
--

CREATE TABLE `wk_address` (
  `addr_id` int(11) NOT NULL,
  `addr_relative_path` varchar(50) DEFAULT NULL,
  `addr_bnbsmartchain_address` varchar(255) DEFAULT NULL,
  `addr_ethereum_address` varchar(255) DEFAULT NULL,
  `addr_polygon_address` varchar(255) DEFAULT NULL,
  `addr_link` text,
  `user_id` int(11) DEFAULT NULL,
  `addr_cdate` datetime DEFAULT NULL,
  `addr_mdate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `wk_deposit`
--

CREATE TABLE `wk_deposit` (
  `bcd_id` int(11) NOT NULL,
  `bcd_chain` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `bcd_sender` varchar(255) DEFAULT NULL,
  `bcd_receiver` varchar(255) DEFAULT NULL,
  `bcd_amount` decimal(36,18) DEFAULT NULL,
  `bcd_position` int(11) NOT NULL DEFAULT '0',
  `bcd_txhash` varchar(255) DEFAULT NULL,
  `bcd_cdate` datetime DEFAULT NULL,
  `bcd_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `wk_query_log`
--

CREATE TABLE `wk_query_log` (
  `log_id` bigint(20) NOT NULL,
  `log_type` varchar(50) DEFAULT NULL,
  `log_response` text,
  `log_cdate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `wk_user`
--

CREATE TABLE `wk_user` (
  `user_id` int(11) NOT NULL,
  `user_username` varchar(50) DEFAULT NULL,
  `user_password` varchar(100) DEFAULT NULL,
  `user_usdt_balance` decimal(18,8) DEFAULT '0.00000000',
  `user_busd_balance` decimal(18,8) DEFAULT '0.00000000',
  `user_usdc_balance` decimal(18,8) DEFAULT '0.00000000',
  `user_cdate` datetime DEFAULT NULL,
  `user_mdate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `wk_user`
--

INSERT INTO `wk_user` (`user_id`, `user_username`, `user_password`, `user_usdt_balance`, `user_busd_balance`, `user_usdc_balance`, `user_cdate`, `user_mdate`) VALUES
(1, 'muttu', '432553f43c23a9853', 1.00000000, 0.00000000, 0.50100000, '2025-12-05 16:55:45', '2025-12-05 18:11:06'),
(2, 'ali', '428937234790790a79d898e8987f', 1.00000000, 0.00000000, 0.50100000, '2025-12-05 16:55:45', '2025-12-05 18:11:06'),
(3, 'xiaoming', '4323790790a79d898e8987f', 1.00000000, 0.00000000, 0.00000000, '2025-12-05 16:55:45', '2025-12-05 18:11:06'),
(4, 'mingtian', '432553f43c23a9853', 0.00000000, 0.00000000, 0.00000000, '2025-12-05 16:55:45', '2025-12-05 16:55:45'),
(5, 'ismail', '432553f43c23a9853', 0.00000000, 0.00000000, 0.00000000, '2025-12-05 16:55:45', '2025-12-05 16:55:45'),
(6, 'peter', '432553f43c23a9853', 0.00000000, 0.00000000, 0.00000000, '2025-12-05 16:55:45', '2025-12-05 16:55:45'),
(7, 'johnson', '432553f43c23a9853', 0.00000000, 0.00000000, 0.00000000, '2025-12-05 16:55:45', '2025-12-05 16:55:45'),
(8, 'meyen', '432553f43c23a9853', 0.00000000, 0.00000000, 0.00000000, '2025-12-05 16:55:45', '2025-12-05 16:55:45'),
(9, 'socka', '432553f43c23a9853', 0.00000000, 0.00000000, 0.00000000, '2025-12-05 16:55:45', '2025-12-05 16:55:45'),
(10, 'lindu', '432553f43c23a9853', 0.00000000, 0.00000000, 0.00000000, '2025-12-05 16:55:45', '2025-12-05 16:55:45');

-- --------------------------------------------------------

--
-- Table structure for table `wk_withdrawal`
--

CREATE TABLE `wk_withdrawal` (
  `mw_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `mw_recipient` varchar(255) DEFAULT NULL,
  `mw_amount` decimal(18,8) DEFAULT NULL,
  `mw_chain` varchar(50) NOT NULL,
  `mw_coin_symbol` varchar(10) DEFAULT NULL,
  `mw_api_response` text,
  `mw_status` varchar(20) DEFAULT NULL,
  `mw_position` int(10) NOT NULL DEFAULT '0',
  `mw_txhash` varchar(255) DEFAULT NULL,
  `mw_cdate` datetime DEFAULT NULL,
  `mw_mdate` datetime DEFAULT NULL,
  `mw_pddate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wk_address`
--
ALTER TABLE `wk_address`
  ADD PRIMARY KEY (`addr_id`),
  ADD UNIQUE KEY `addr_relative_path` (`addr_relative_path`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `addr_bnbsmartchain_address` (`addr_bnbsmartchain_address`),
  ADD UNIQUE KEY `addr_ethereum_address` (`addr_ethereum_address`),
  ADD UNIQUE KEY `addr_polygon_address` (`addr_polygon_address`);

--
-- Indexes for table `wk_deposit`
--
ALTER TABLE `wk_deposit`
  ADD PRIMARY KEY (`bcd_id`),
  ADD UNIQUE KEY `bcd_chain` (`bcd_chain`,`bcd_position`,`bcd_txhash`);

--
-- Indexes for table `wk_query_log`
--
ALTER TABLE `wk_query_log`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `wk_user`
--
ALTER TABLE `wk_user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_username` (`user_username`);

--
-- Indexes for table `wk_withdrawal`
--
ALTER TABLE `wk_withdrawal`
  ADD PRIMARY KEY (`mw_id`),
  ADD UNIQUE KEY `mw_chain` (`mw_chain`,`mw_position`,`mw_txhash`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wk_address`
--
ALTER TABLE `wk_address`
  MODIFY `addr_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wk_deposit`
--
ALTER TABLE `wk_deposit`
  MODIFY `bcd_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wk_user`
--
ALTER TABLE `wk_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `wk_withdrawal`
--
ALTER TABLE `wk_withdrawal`
  MODIFY `mw_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
