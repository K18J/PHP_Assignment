-- Complete setup for comments feature with sample data.
-- Run this in phpMyAdmin (MySQL/MariaDB) after selecting the target database.
-- Assumes database "commentmanagementsystem" already exists.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- Drop existing tables if you need a clean slate (optional; comment out if not desired)
-- DROP TABLE IF EXISTS `comments`;
-- DROP TABLE IF EXISTS `pages`;
-- DROP TABLE IF EXISTS `users`;

-- Users table (minimal fields for demo + admin flag)
CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100) DEFAULT NULL,
  `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pages table (minimal fields to satisfy Comment->Page relation)
CREATE TABLE IF NOT EXISTS `pages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `content` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments table with FKs to users and pages, plus self-referencing parent_id
CREATE TABLE IF NOT EXISTS `comments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `content` TEXT NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
  `parent_id` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comments_page_id_index` (`page_id`),
  KEY `comments_status_index` (`status`),
  KEY `comments_parent_id_index` (`parent_id`),
  CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `comments_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed users (password is bcrypt for the literal "password")
INSERT INTO `users` (`name`, `email`, `password`, `is_admin`, `created_at`, `updated_at`) VALUES
('Admin User', 'admin@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/atf4F5uiC2u2.', 1, NOW(), NOW()),
('Regular User', 'user@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/atf4F5uiC2u2.', 0, NOW(), NOW());

-- Seed pages
INSERT INTO `pages` (`title`, `slug`, `content`, `created_at`, `updated_at`) VALUES
('Getting Started', 'getting-started', 'Welcome to the CMS.', NOW(), NOW()),
('FAQ', 'faq', 'Frequently asked questions.', NOW(), NOW());

-- Seed comments (nested example: comment id 2 replies to id 1)
INSERT INTO `comments` (`page_id`, `user_id`, `content`, `status`, `parent_id`, `created_at`, `updated_at`) VALUES
(1, 2, 'Great intro page!', 'approved', NULL, NOW(), NOW()),
(1, 1, 'Thanks for the feedback!', 'approved', 1, NOW(), NOW()),
(2, 2, 'I have a question about billing.', 'pending', NULL, NOW(), NOW());


