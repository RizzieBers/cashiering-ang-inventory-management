-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 15, 2025 at 05:12 AM
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
-- Database: `web`
--

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`product_id`, `product_name`, `product_price`, `stock`, `category`, `product_image`, `created_at`, `updated_at`) VALUES
(1, 'Iced Cappuccino Latte', 145.00, 5, 'Protein Iced Coffee', 'a1.jpg', '2025-03-15 04:04:32', '2025-03-15 04:04:32'),
(2, 'Iced Chocolate Latte', 230.00, 5, 'Protein Iced Coffee', 'a2.jpg', '2025-03-15 04:04:47', '2025-03-15 04:04:47'),
(3, 'Iced Matcha Latte', 165.00, 5, 'Protein Iced Coffee', 'a3.jpg', '2025-03-15 04:05:01', '2025-03-15 04:05:01'),
(4, 'Iced Salted Caramel Latte', 145.00, 5, 'Protein Iced Coffee', 'a4.jpg', '2025-03-15 04:05:21', '2025-03-15 04:05:21'),
(5, 'Iced Strawberry Latte', 235.00, 5, 'Protein Iced Coffee', 'a5.jpg', '2025-03-15 04:05:42', '2025-03-15 04:05:42'),
(6, 'Avocado Graham Delight', 190.00, 5, 'Protein Shake', 'b1.jpg', '2025-03-15 04:06:09', '2025-03-15 04:06:09'),
(7, 'Dragon Fruit Delight', 190.00, 5, 'Protein Shake', 'b3.jpg', '2025-03-15 04:06:23', '2025-03-15 04:06:23'),
(8, 'Dulce de Leche', 150.00, 5, 'Protein Shake', 'b4.jpg', '2025-03-15 04:06:42', '2025-03-15 04:06:42'),
(9, 'Dutch Choco', 150.00, 7, 'Protein Shake', 'b5.jpg', '2025-03-15 04:06:58', '2025-03-15 04:06:58'),
(10, 'Lotus Biscoff Protein Shake', 200.00, 7, 'Protein Shake', 'b6.jpg', '2025-03-15 04:07:13', '2025-03-15 04:07:13'),
(11, 'Mango Cookie Delight', 190.00, 5, 'Protein Shake', 'b7.jpg', '2025-03-15 04:07:31', '2025-03-15 04:07:31'),
(12, 'Wild Berry Classic', 150.00, 7, 'Protein Shake', 'b8.jpg', '2025-03-15 04:07:49', '2025-03-15 04:07:49'),
(13, 'Wild Berry Delight', 190.00, 7, 'Protein Shake', 'b9.jpg', '2025-03-15 04:08:08', '2025-03-15 04:08:08'),
(14, 'Blueberry Lemonade', 95.00, 6, 'Refreshing Lemonade', 'c1.jpg', '2025-03-15 04:08:28', '2025-03-15 04:08:28'),
(15, 'Lemonade Delight', 95.00, 7, 'Refreshing Lemonade', 'c2.jpg', '2025-03-15 04:08:46', '2025-03-15 04:08:46'),
(16, 'Mango Lemonade', 85.00, 6, 'Refreshing Lemonade', 'c3.jpg', '2025-03-15 04:09:03', '2025-03-15 04:09:03'),
(17, 'Strawberry Lemonade', 95.00, 7, 'Refreshing Lemonade', 'c4.jpg', '2025-03-15 04:09:25', '2025-03-15 04:09:25'),
(18, 'Orange+Strawberry Lemonade', 95.00, 7, 'Refreshing Lemonade', 'c5.jpg', '2025-03-15 04:09:42', '2025-03-15 04:09:42'),
(19, 'Classic Lemonade', 95.00, 8, 'Refreshing Lemonade', 'c6.jpg', '2025-03-15 04:10:14', '2025-03-15 04:10:14');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `order_type` varchar(50) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` date NOT NULL,
  `order_time` time NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `customer_name`, `order_type`, `payment_method`, `total_amount`, `order_date`, `order_time`, `contact_number`, `status`) VALUES
(1, 1, 'RIZIEL', 'Dine-in', 'Cash', 840.00, '0000-00-00', '05:11:33', 'Not Provided', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `item_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `product_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_name`, `item_price`, `quantity`, `subtotal`, `product_image`) VALUES
(1, 1, 'Iced Matcha Latte', 165.00, 1, 165.00, 'uploads/a3.jpg'),
(2, 1, 'Iced Salted Caramel Latte', 145.00, 1, 145.00, 'uploads/a4.jpg'),
(3, 1, 'Avocado Graham Delight', 190.00, 1, 190.00, 'uploads/b1.jpg'),
(4, 1, 'Dragon Fruit Delight', 190.00, 1, 190.00, 'uploads/b3.jpg'),
(5, 1, 'Dulce de Leche', 150.00, 1, 150.00, 'uploads/b4.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `phone`, `password`, `created_at`) VALUES
(1, 'RIZIEL', '9754685620', '$2y$10$CWRTgjRXosxm1evdMiQddeiZi3fX9FaLHAKwQRomrvzR.ddkDKBwW', '2025-03-15 04:00:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
