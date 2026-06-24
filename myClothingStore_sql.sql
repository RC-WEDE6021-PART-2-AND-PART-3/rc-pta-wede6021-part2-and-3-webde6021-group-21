-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2026 at 12:59 AM
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
-- Database: `clothingstore`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblclothes`
--

CREATE TABLE `tblclothes` (
  `clothes_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(100) DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblclothes`
--

INSERT INTO `tblclothes` (`clothes_id`, `name`, `description`, `price`, `image`, `seller_id`) VALUES
(1, 'Vintage Jacket', 'Good condition jacket', 250.00, 'jacket.jpg', 2),
(2, 'Nike Hoodie', 'Original hoodie', 300.00, 'hoodie.jpg', 2),
(3, 'Blue Jeans', 'Stylish denim jeans', 200.00, 'jeans.jpg', 2),
(4, 'Summer Dress', 'Light and comfortable', 180.00, 'dress.jpg', 2),
(5, 'Leather Boots', 'Durable boots', 400.00, 'boots.jpg', 2),
(6, 'White T-Shirt', 'Simple cotton shirt', 120.00, 'tshirt.jpg', 2),
(7, 'Black Skirt', 'Trendy black skirt', 150.00, 'skirt.jpg', 2),
(8, 'Denim Shorts', 'Comfortable shorts', 130.00, 'shorts.jpg', 2),
(9, 'Formal Shirt', 'Office wear shirt', 220.00, 'formalshirt.jpg', 2),
(10, 'Hooded Jacket', 'Warm winter jacket', 350.00, 'hoodedjacket.jpg', 2);

-- --------------------------------------------------------

--
-- Table structure for table `tblmessage`
--

CREATE TABLE `tblmessage` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblmessage`
--

INSERT INTO `tblmessage` (`message_id`, `sender_id`, `receiver_id`, `message`) VALUES
(1, 1, 2, 'Hi, is this available?'),
(2, 2, 1, 'Yes, it is still available');

-- --------------------------------------------------------

--
-- Table structure for table `tblorder`
--

CREATE TABLE `tblorder` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `clothes_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbluser`
--

CREATE TABLE `tbluser` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbluser`
--

INSERT INTO `tbluser` (`user_id`, `name`, `email`, `username`, `password`, `role`, `status`) VALUES
(1, 'Fhulu mudau', 'fhulufhelomm@gmail.com', 'fhulu', '25d55ad283aa400af464c76d713c07ad', 'buyer', 'verified'),
(2, 'Ravhura Sedzani', 'ravhurasedzani@gmail.com', 'Sedzi', '25d55ad283aa400af464c76d713c07ad', 'seller', 'verified'),
(3, 'Mulweli M', 'mulwelimm@gmail.com', 'Mulweli', '25d55ad283aa400af464c76d713c07ad', 'admin', 'verified');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblclothes`
--
ALTER TABLE `tblclothes`
  ADD PRIMARY KEY (`clothes_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `tblmessage`
--
ALTER TABLE `tblmessage`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `tblorder`
--
ALTER TABLE `tblorder`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `clothes_id` (`clothes_id`);

--
-- Indexes for table `tbluser`
--
ALTER TABLE `tbluser`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblclothes`
--
ALTER TABLE `tblclothes`
  MODIFY `clothes_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tblmessage`
--
ALTER TABLE `tblmessage`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tblorder`
--
ALTER TABLE `tblorder`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbluser`
--
ALTER TABLE `tbluser`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblclothes`
--
ALTER TABLE `tblclothes`
  ADD CONSTRAINT `tblclothes_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `tbluser` (`user_id`);

--
-- Constraints for table `tblmessage`
--
ALTER TABLE `tblmessage`
  ADD CONSTRAINT `tblmessage_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `tbluser` (`user_id`),
  ADD CONSTRAINT `tblmessage_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `tbluser` (`user_id`);

--
-- Constraints for table `tblorder`
--
ALTER TABLE `tblorder`
  ADD CONSTRAINT `tblorder_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbluser` (`user_id`),
  ADD CONSTRAINT `tblorder_ibfk_2` FOREIGN KEY (`clothes_id`) REFERENCES `tblclothes` (`clothes_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
