-- Store Order & Inventory Database Dump
-- Host: 127.0.0.1    Database: store_order_inventory

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `tax_percentage` decimal(5,2) NOT NULL,
  `stock_quantity` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_code_unique` (`code`),
  KEY `products_stock_quantity_index` (`stock_quantity`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `name`, `code`, `price`, `tax_percentage`, `stock_quantity`, `created_at`, `updated_at`) VALUES
('25', 'Book', 'PRD-34621', '10.00', '18.00', '8', '2026-10-30 06:36:04', '2026-10-30 06:44:57'),
('26', 'Pen', 'PRD-46993', '10.00', '4.00', '9', '2026-10-30 06:41:48', '2026-10-30 06:42:53'),
('27', 'Wireless Optical Mouse', 'PRD-MOU-001', '599.00', '18.00', '25', '2026-10-30 07:04:52', '2026-10-30 07:04:52'),
('28', 'Mechanical Gaming Keyboard', 'PRD-KEY-002', '2499.00', '18.00', '12', '2026-10-30 07:04:52', '2026-10-30 07:04:52'),
('29', 'USB-C Fast Charging Cable (1m)', 'PRD-CAB-003', '299.00', '18.00', '45', '2026-10-30 07:04:52', '2026-10-30 07:04:52'),
('30', 'Stainless Steel Water Bottle (750ml)', 'PRD-BOT-004', '450.00', '12.00', '3', '2026-10-30 07:04:52', '2026-10-30 07:04:52'),
('31', 'Hardbound Ruled Notebook (A5)', 'PRD-NOT-005', '180.00', '5.00', '50', '2026-10-30 07:04:52', '2026-10-30 07:04:52'),
('32', 'Ergonomic Aluminum Laptop Stand', 'PRD-STA-006', '1299.00', '18.00', '2', '2026-10-30 07:04:52', '2026-10-30 07:04:52'),
('33', 'Organic Darjeeling Green Tea (100g)', 'PRD-TEA-007', '320.00', '5.00', '15', '2026-10-30 07:04:52', '2026-10-30 07:04:52'),
('34', 'Portable Bluetooth Speaker (5W)', 'PRD-SPK-008', '1499.00', '18.00', '4', '2026-10-30 07:04:52', '2026-10-30 07:04:52'),
('35', 'Noise Cancelling Wireless Earbuds', 'PRD-EAR-009', '2999.00', '18.00', '8', '2026-10-30 07:04:52', '2026-10-30 07:04:52'),
('36', 'Ceramic Coffee Mug (350ml)', 'PRD-MUG-010', '220.00', '12.00', '1', '2026-10-30 07:04:52', '2026-10-30 07:04:52');

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customers` (`id`, `name`, `email`, `created_at`, `updated_at`) VALUES
('2', 'Customer B', 'customerB@example.com', '2026-10-30 06:28:17', '2026-10-30 06:28:17'),
('13', 'Rohit Patil', 'rohit@example.com', '2026-10-30 06:42:53', '2026-10-30 06:42:53'),
('14', 'Aarav Sharma', 'aarav.sharma@example.com', '2026-10-30 07:04:52', '2026-10-30 07:04:52'),
('15', 'Priya Nair', 'priya.nair@example.com', '2026-10-30 07:04:52', '2026-10-30 07:04:52'),
('16', 'Ananya Verma', 'ananya.verma@example.com', '2026-10-30 07:04:52', '2026-10-30 07:04:52');

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `tax_amount` decimal(12,2) NOT NULL,
  `grand_total` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_customer_id_foreign` (`customer_id`),
  CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `orders` (`id`, `customer_id`, `subtotal`, `tax_amount`, `grand_total`, `created_at`, `updated_at`) VALUES
('9', '13', '10.00', '0.40', '10.40', '2026-10-30 06:42:53', '2026-10-30 06:42:53'),
('10', '13', '20.00', '3.60', '23.60', '2026-10-30 06:44:57', '2026-10-30 06:44:57');

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `tax_percentage` decimal(5,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `tax_amount` decimal(12,2) NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`, `tax_percentage`, `subtotal`, `tax_amount`, `total`, `created_at`, `updated_at`) VALUES
('10', '9', '26', '1', '10.00', '4.00', '10.00', '0.40', '10.40', '2026-10-30 06:42:53', '2026-10-30 06:42:53'),
('11', '10', '25', '2', '10.00', '18.00', '20.00', '3.60', '23.60', '2026-10-30 06:44:57', '2026-10-30 06:44:57');

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
('1', 'default', '{\"uuid\":\"b582ca26-9655-4725-a99b-770482fe4e0a\",\"displayName\":\"App\\\\Jobs\\\\SendOrderConfirmationJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendOrderConfirmationJob\",\"command\":\"O:33:\\\"App\\\\Jobs\\\\SendOrderConfirmationJob\\\":1:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:2;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\"}}', '0', NULL, '1793341697', '1793341697'),
('2', 'default', '{\"uuid\":\"402b23d1-5b9c-4171-8ba2-5c86c122ea86\",\"displayName\":\"App\\\\Jobs\\\\SendOrderConfirmationJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendOrderConfirmationJob\",\"command\":\"O:33:\\\"App\\\\Jobs\\\\SendOrderConfirmationJob\\\":1:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:9;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\"}}', '0', NULL, '1793342573', '1793342573'),
('3', 'default', '{\"uuid\":\"4fa16c37-a9fd-4fee-a7ea-9110bd0dcc08\",\"displayName\":\"App\\\\Jobs\\\\SendOrderConfirmationJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendOrderConfirmationJob\",\"command\":\"O:33:\\\"App\\\\Jobs\\\\SendOrderConfirmationJob\\\":1:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:10;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\"}}', '0', NULL, '1793342697', '1793342697');

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
('1', '2014_10_12_000000_create_users_table', '1'),
('2', '2014_10_12_100000_create_password_reset_tokens_table', '1'),
('3', '2019_08_19_000000_create_failed_jobs_table', '1'),
('4', '2019_12_14_000001_create_personal_access_tokens_table', '1'),
('5', '2026_01_01_000001_create_products_table', '1'),
('6', '2026_01_01_000002_create_customers_table', '1'),
('7', '2026_01_01_000003_create_orders_table', '1'),
('8', '2026_01_01_000004_create_order_items_table', '1'),
('9', '2026_10_29_134515_create_jobs_table', '1');

SET FOREIGN_KEY_CHECKS=1;
