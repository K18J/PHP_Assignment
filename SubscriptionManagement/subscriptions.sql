-- Subscription plans table
CREATE TABLE IF NOT EXISTS `subscription_plans` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `billing_cycle_days` INT NOT NULL DEFAULT 30,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Subscriptions table
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `customer_id` INT NOT NULL,
  `plan_id` INT NOT NULL,
  `status` ENUM('trial','active','suspended','cancelled','expired') NOT NULL DEFAULT 'trial',
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `auto_renew` TINYINT(1) NOT NULL DEFAULT 1,
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_subscriptions_customer_id` (`customer_id`),
  KEY `idx_subscriptions_plan_id` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
