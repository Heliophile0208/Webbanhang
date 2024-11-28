-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost
-- Thời gian đã tạo: Th10 26, 2024 lúc 06:48 PM
-- Phiên bản máy phục vụ: 5.7.34
-- Phiên bản PHP: 8.2.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `shop_db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `group` enum('Mùa','Thường Ngày','Bán Chạy','Ngày Lễ') COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `group`) VALUES
(1, 'Tiệc Trang Trọng', 'Các loại đầm thời trang cho nữ, từ đầm dạ hội đến đầm công sở.', 'Ngày Lễ'),
(2, 'Áo', 'Áo phông, áo sơ mi, áo len và nhiều kiểu áo khác.', 'Thường Ngày'),
(3, 'Quần', 'Quần jeans, quần âu, quần short, và quần legging.', 'Thường Ngày'),
(4, 'Giày', 'Giày cao gót, giày thể thao, và giày bệt thời trang.', 'Thường Ngày'),
(5, 'Phụ kiện', 'Các loại phụ kiện thời trang như túi xách, trang sức và khăn choàng.', 'Thường Ngày'),
(9, 'Thời Trang Dạo Phố', 'Trang phục thoải mái cho những buổi đi chơi', 'Thường Ngày'),
(8, 'Thời Trang Công Sở', 'Thời trang chuyên nghiệp cho công việc', 'Thường Ngày'),
(10, 'Thời Trang Mùa Hè', 'Sản phẩm phù hợp cho mùa hè nóng bức', 'Mùa'),
(11, 'Thời Trang Mùa Đông', 'Sản phẩm giữ ấm cho mùa đông', 'Mùa');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `customer_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `address_line1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address_line2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `postal_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `customers`
--

INSERT INTO `customers` (`customer_id`, `user_id`, `customer_name`, `customer_email`, `phone_number`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `country`, `created_at`, `updated_at`) VALUES
(1, 2, 'Administrator', 'lethikimngan20803@gmail.com', '0928338155', 'TP HCM', '', 'TPHCM', 'TPHCM', '700000', 'Việt Nam', '2024-11-21 02:07:37', '2024-11-22 17:54:02'),
(2, 6, 'Lê Thị Kim Ngân', 'lethikimngan20803@gmail.com', '0928338155', '227 đường Phạm Văn Diêu, khu phố 3, phường Tân Hạnh, Thành phố Biên Hoà, tỉnh Đồng Nai', '0928338155', 'TP Biên Hoà', 'Đồng Nai', '810900', 'Việt Nam', '2024-11-21 09:57:41', '2024-11-22 07:34:26');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','canceled') COLLATE utf8_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total`, `status`, `created_at`) VALUES
(43, 3, 250000.00, 'pending', '2024-11-26 00:00:31'),
(45, 2, 900000.00, 'pending', '2024-11-26 16:28:18');

--
-- Bẫy `orders`
--
DELIMITER $$
CREATE TRIGGER `before_order_delete` BEFORE DELETE ON `orders` FOR EACH ROW BEGIN
    DELETE FROM order_items WHERE order_id = OLD.id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `size_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `size_id`) VALUES
(103, 43, 1, 1, 250000.00, 1),
(105, 45, 13, 2, 400000.00, 1),
(106, 45, 1, 2, 250000.00, 5);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT '0',
  `image_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `image_url`, `created_at`) VALUES
(1, 'Quần jeans ống rộng', 'Quần jeans phong cách ống rộng', 250000.00, 20, 'images/quanjeansongrong.jpeg', '2024-10-30 15:19:28'),
(2, 'Quần yếm hai dây', 'Quần yếm hai dây thời trang', 200000.00, 15, 'images/quanyemhaiday.jpeg', '2024-10-30 15:19:28'),
(3, 'Túi Celine Triomphe Canvas', 'Túi xách thời trang cao cấp', 3000000.00, 5, 'images/tuicelinetriomphecanvas.jpeg', '2024-10-30 15:19:28'),
(4, 'Đầm dạ hội phong cách châu Âu', 'Đầm dạ hội thời trang châu Âu', 1200000.00, 8, 'images/damdahoiphongcachchauau.jpeg', '2024-10-30 15:19:28'),
(5, 'Áo thun kèm nón', 'Áo thun tiện dụng kèm nón', 180000.00, 30, 'images/aothunkemnon.jpeg', '2024-10-30 15:19:28'),
(6, 'Vest dáng suông', 'Vest công sở dáng suông', 500000.00, 12, 'images/vestdangsuong.jpeg', '2024-10-30 15:19:28'),
(7, 'Set đồ thể thao kèm áo khoác ngoài', 'Bộ đồ thể thao năng động', 350000.00, 25, 'images/setdothethaokemaokhoacngoai.jpeg', '2024-10-30 15:19:28'),
(8, 'Đồ thể thao nữ', 'Đồ thể thao phong cách nữ', 220000.00, 20, 'images/dothethaonu.jpeg', '2024-10-30 15:19:28'),
(9, 'Váy dạ hội trẻ vai', 'Váy dạ hội kiểu cách trẻ trung', 1500000.00, 10, 'images/vaydahoitrevai.jpeg', '2024-10-30 15:19:28'),
(10, 'Đầm dạ hội xẻ', 'Đầm dạ hội phong cách xẻ', 1100000.00, 10, 'images/damdahoixe.jpeg', '2024-10-30 15:19:28'),
(11, 'Vest nữ công sở', 'Vest nữ dành cho công sở', 600000.00, 15, 'images/vestnucongso.jpeg', '2024-10-30 15:19:28'),
(12, 'God\'s Favorite Crop Top', 'Crop top in hình God\'s Favorite', 120000.00, 40, 'images/godsfavorite_croptop.jpeg', '2024-10-30 15:19:28'),
(13, 'Denim Jacket', 'Áo khoác denim thời trang', 400000.00, 18, 'images/denimjacket.jpeg', '2024-10-30 15:19:28'),
(14, 'Set áo gile vest', 'Set áo gile kết hợp vest', 450000.00, 10, 'images/setaogile_vest.jpeg', '2024-10-30 15:19:28'),
(15, 'Váy cúp ngực phồng', 'Váy thời trang cúp ngực phồng', 750000.00, 12, 'images/vaycupngucphong.jpeg', '2024-10-30 15:19:28'),
(16, 'Sweater crop top', 'Áo sweater kiểu crop top', 200000.00, 22, 'images/sweater_croptop.jpeg', '2024-10-30 15:19:28'),
(17, 'Áo khoác da crop top', 'Áo khoác da kiểu crop top', 550000.00, 7, 'images/Aokhoacda_croptop.jpeg', '2024-10-30 15:19:28'),
(18, 'Sơ mi cavat', 'Áo sơ mi phong cách với cavat', 300000.00, 18, 'images/somi_cavat.jpeg', '2024-10-30 15:19:28'),
(19, 'Áo Len dài tay ', 'Áo Len dài tay, cổ rộng, tròn', 100000.00, 20, 'images/beckyaomuadong.jpg', '2024-11-21 11:40:04'),
(20, 'Giày Sneakers Converse', 'Những đôi giày Converse với thân giày được làm từ chất liệu vải Canvas', 300000.00, 20, 'images/sneakers.jpg', '2024-11-22 09:44:26');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_categories`
--

CREATE TABLE `product_categories` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `product_categories`
--

INSERT INTO `product_categories` (`product_id`, `category_id`) VALUES
(1, 3),
(1, 10),
(2, 3),
(2, 9),
(3, 5),
(3, 10),
(4, 1),
(4, 10),
(5, 2),
(5, 9),
(6, 2),
(6, 8),
(7, 9),
(7, 10),
(8, 9),
(8, 10),
(9, 1),
(9, 10),
(10, 1),
(10, 9),
(11, 8),
(12, 10),
(13, 2),
(13, 10),
(14, 8),
(15, 1),
(15, 10),
(16, 10),
(17, 2),
(17, 9),
(18, 2),
(19, 2),
(19, 11),
(20, 4);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_sizes`
--

CREATE TABLE `product_sizes` (
  `product_id` int(11) NOT NULL,
  `size_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `product_sizes`
--

INSERT INTO `product_sizes` (`product_id`, `size_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(4, 1),
(4, 2),
(4, 3),
(4, 4),
(4, 5),
(5, 1),
(5, 2),
(5, 3),
(5, 4),
(5, 5),
(6, 1),
(6, 2),
(6, 3),
(6, 4),
(6, 5),
(7, 1),
(7, 2),
(7, 3),
(7, 4),
(7, 5),
(8, 1),
(8, 2),
(8, 3),
(8, 4),
(8, 5),
(9, 1),
(9, 2),
(9, 3),
(9, 4),
(9, 5),
(10, 1),
(10, 2),
(10, 3),
(10, 4),
(10, 5),
(11, 1),
(11, 2),
(11, 3),
(11, 4),
(11, 5),
(12, 1),
(12, 2),
(12, 3),
(12, 4),
(12, 5),
(13, 1),
(13, 2),
(13, 3),
(13, 4),
(13, 5),
(14, 1),
(14, 2),
(14, 3),
(14, 4),
(14, 5),
(15, 1),
(15, 2),
(15, 3),
(15, 4),
(15, 5),
(16, 1),
(16, 2),
(16, 3),
(16, 4),
(16, 5),
(17, 1),
(17, 2),
(17, 3),
(17, 4),
(17, 5),
(18, 1),
(18, 2),
(18, 3),
(18, 4),
(18, 5),
(19, 1),
(19, 2),
(19, 3),
(19, 4),
(19, 5),
(20, 6),
(20, 7),
(20, 8),
(20, 9),
(20, 10);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(6, 1, 3, 5, 'Đẹp', '2024-11-26 00:00:42'),
(7, 1, 2, 5, 'Đẹp', '2024-11-26 16:28:36');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `size`
--

CREATE TABLE `size` (
  `id` int(11) NOT NULL,
  `size` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `category` enum('clothes','shoes','accessories') COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `size`
--

INSERT INTO `size` (`id`, `size`, `category`) VALUES
(1, 'XXL', 'clothes'),
(2, 'S', 'clothes'),
(3, 'M', 'clothes'),
(4, 'L', 'clothes'),
(5, 'XL', 'clothes'),
(6, '36', 'shoes'),
(7, '37', 'shoes'),
(8, '38', 'shoes'),
(9, '39', 'shoes'),
(10, '40', 'shoes');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8_unicode_ci DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(2, 'admin', '$2y$10$xVyNJcWp7xhX9y1V9MEc6eyKEZRjeJqRdraoEwl.rmC9CjRsrkNjy', 'admin', '2024-11-20 07:21:15'),
(3, 'ngan', '$2y$10$SeHNSdU6jJeigtPfiAl1F.z7jj/lCvcWdGC6R1h640HzRtylWaDy6', 'admin', '2024-11-20 07:27:21'),
(4, 'user01', '$2y$10$j.Zsk9zIaViTKGWpFnMJz.qerjixIP3TT/hsXCmUfGq8F5O8xZ8MS', 'user', '2024-11-20 07:30:54'),
(14, 'user010', '$2y$10$W4nIoGmaaNoh7KOOU2SOeO9qyA2c.4qSwG1bq/ONrFJVDe1elOdQq', 'user', '2024-11-26 11:37:09');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `fk_size_id` (`size_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`product_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`product_id`,`size_id`),
  ADD KEY `size_id` (`size_id`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `size`
--
ALTER TABLE `size`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `size`
--
ALTER TABLE `size`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
