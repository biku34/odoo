-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 12, 2025 at 12:29 PM
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
-- Database: `odoo`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action_type`, `reference_id`, `message`, `created_at`) VALUES
(1, 1, 'uploaded_item', 1, 'You uploaded Floral Maxi Dress.', '2025-07-12 03:49:41'),
(2, 1, 'uploaded_item', 2, 'You uploaded High Waist Jeans.', '2025-07-12 03:49:41'),
(3, 1, 'requested_swap', 1, 'Swap request initiated for Leather Jacket.', '2025-07-12 03:49:41');

-- --------------------------------------------------------

--
-- Table structure for table `admin_actions`
--

CREATE TABLE `admin_actions` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_actions`
--

INSERT INTO `admin_actions` (`id`, `admin_id`, `item_id`, `action`, `reason`, `created_at`) VALUES
(1, 3, 4, 'approved', 'Verified ethnic wear', '2025-07-12 03:49:41');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `size` varchar(20) DEFAULT NULL,
  `condition` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `user_id`, `title`, `description`, `category`, `type`, `size`, `condition`, `status`, `created_at`) VALUES
(1, 1, 'Floral Maxi Dress', 'Perfect for brunch!', 'Dresses', 'Women', 'M', 'Like New', 'available', '2025-07-12 03:49:41'),
(2, 1, 'High Waist Jeans', 'Classic and comfy.', 'Pants', 'Women', 'L', 'Used', 'available', '2025-07-12 03:49:41'),
(3, 2, 'Leather Jacket', 'Warm and stylish', 'Jackets', 'Men', 'L', 'Gently Used', 'available', '2025-07-12 03:49:41'),
(4, 2, 'Kurta Set', 'Great for festivals!', 'Ethnic Wear', 'Unisex', 'XL', 'Like New', 'swapped', '2025-07-12 03:49:41'),
(5, 1, 'New Jacket', 'Super Cool Jacket', 'Jackets', 'Unisex', 'L', 'Gently Used', 'available', '2025-07-12 04:59:46'),
(9, 1, 'New Shirt (2 days old )', '2 days old shirt, need to replace for perfect fit', 'T-Shirts', 'Unisex', 'L', 'Gently Used', 'available', '2025-07-12 09:14:28'),
(10, 1, 'New Shirt', '30 days old shirt', 'T-Shirts', 'Women', 'M', 'Gently Used', 'available', '2025-07-12 09:58:09');

-- --------------------------------------------------------

--
-- Table structure for table `item_images`
--

CREATE TABLE `item_images` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `image_url` text NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_images`
--

INSERT INTO `item_images` (`id`, `item_id`, `image_url`, `is_primary`) VALUES
(1, 1, 'https://images.cbazaar.com/images/black-faux-georgette-chaniya-choli-with-artful-embroidery-kutch-work-ghswe14-u.jpg', 1),
(2, 2, 'https://images.cbazaar.com/images/black-faux-georgette-chaniya-choli-with-artful-embroidery-kutch-work-ghswe14-u.jpg', 1),
(3, 3, 'https://images.cbazaar.com/images/black-faux-georgette-chaniya-choli-with-artful-embroidery-kutch-work-ghswe14-u.jpg', 1),
(4, 4, 'https://images.cbazaar.com/images/black-faux-georgette-chaniya-choli-with-artful-embroidery-kutch-work-ghswe14-u.jpg', 1),
(5, 5, 'uploads/6871ebc26e448.jpg', 1),
(13, 9, 'https://via.placeholder.com/150?text=Preview+1', 1),
(14, 9, 'https://via.placeholder.com/150?text=Preview+2', 0),
(15, 9, 'https://via.placeholder.com/150?text=Preview+3', 0),
(16, 10, 'https://via.placeholder.com/150?text=Preview+1', 1),
(17, 10, 'https://via.placeholder.com/150?text=Preview+2', 0),
(18, 10, 'https://via.placeholder.com/150?text=Preview+3', 0);

-- --------------------------------------------------------

--
-- Table structure for table `item_tags`
--

CREATE TABLE `item_tags` (
  `item_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_tags`
--

INSERT INTO `item_tags` (`item_id`, `tag_id`) VALUES
(1, 1),
(1, 4),
(2, 2),
(2, 6),
(3, 5),
(4, 3),
(5, 6),
(9, 6),
(9, 8),
(10, 6);

-- --------------------------------------------------------

--
-- Table structure for table `redemptions`
--

CREATE TABLE `redemptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `points_used` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `redemptions`
--

INSERT INTO `redemptions` (`id`, `user_id`, `item_id`, `points_used`, `status`, `created_at`) VALUES
(1, 1, 3, 80, 'approved', '2025-07-12 03:49:41');

-- --------------------------------------------------------

--
-- Table structure for table `swaps`
--

CREATE TABLE `swaps` (
  `id` int(11) NOT NULL,
  `requester_id` int(11) DEFAULT NULL,
  `responder_id` int(11) DEFAULT NULL,
  `requester_item_id` int(11) DEFAULT NULL,
  `responder_item_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `swaps`
--

INSERT INTO `swaps` (`id`, `requester_id`, `responder_id`, `requester_item_id`, `responder_item_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 4, 'completed', '2025-07-12 03:49:41', '2025-07-12 03:49:41');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `name`) VALUES
(6, 'Casual'),
(8, 'cotton'),
(3, 'Ethnic'),
(5, 'Formal'),
(1, 'Summer'),
(4, 'Trendy'),
(2, 'Winter');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` text NOT NULL,
  `profile_image` text DEFAULT NULL,
  `points_balance` int(11) DEFAULT 0,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `profile_image`, `points_balance`, `is_admin`, `created_at`, `updated_at`) VALUES
(1, 'Anaya Kapoor', 'anaya@example.com', 'hash1', 'https://randomuser.me/api/portraits/women/1.jpg', 120, 0, '2025-07-12 03:49:41', '2025-07-12 03:49:41'),
(2, 'Rishi Sharma', 'rishi@example.com', 'hash2', 'https://randomuser.me/api/portraits/men/2.jpg', 80, 0, '2025-07-12 03:49:41', '2025-07-12 03:49:41'),
(3, 'Admin User', 'admin@rewear.com', 'hashadmin', 'https://randomuser.me/api/portraits/men/3.jpg', 500, 1, '2025-07-12 03:49:41', '2025-07-12 03:49:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admin_actions`
--
ALTER TABLE `admin_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `item_images`
--
ALTER TABLE `item_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `item_tags`
--
ALTER TABLE `item_tags`
  ADD PRIMARY KEY (`item_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `redemptions`
--
ALTER TABLE `redemptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `swaps`
--
ALTER TABLE `swaps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requester_id` (`requester_id`),
  ADD KEY `responder_id` (`responder_id`),
  ADD KEY `requester_item_id` (`requester_item_id`),
  ADD KEY `responder_item_id` (`responder_item_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admin_actions`
--
ALTER TABLE `admin_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `item_images`
--
ALTER TABLE `item_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `redemptions`
--
ALTER TABLE `redemptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `swaps`
--
ALTER TABLE `swaps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `admin_actions`
--
ALTER TABLE `admin_actions`
  ADD CONSTRAINT `admin_actions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `admin_actions_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `item_images`
--
ALTER TABLE `item_images`
  ADD CONSTRAINT `item_images_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `item_tags`
--
ALTER TABLE `item_tags`
  ADD CONSTRAINT `item_tags_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `item_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `redemptions`
--
ALTER TABLE `redemptions`
  ADD CONSTRAINT `redemptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `redemptions_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Constraints for table `swaps`
--
ALTER TABLE `swaps`
  ADD CONSTRAINT `swaps_ibfk_1` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `swaps_ibfk_2` FOREIGN KEY (`responder_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `swaps_ibfk_3` FOREIGN KEY (`requester_item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `swaps_ibfk_4` FOREIGN KEY (`responder_item_id`) REFERENCES `items` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
