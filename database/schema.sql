-- ============================================================================
-- The Giving Grid - Database Schema (MVG)
-- ============================================================================
-- 
-- Minimum Viable Grid schema for MySQL 8.0+
-- Run this script to create all tables for the initial deployment.
--
-- Usage:
--   mysql -u your_user -p your_database < database/schema.sql
--
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ────────────────────────────────────────────────────────────────────────────
-- Users
-- ────────────────────────────────────────────────────────────────────────────

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `display_name` VARCHAR(100) NOT NULL,
    `county` VARCHAR(100) DEFAULT NULL,
    `role` ENUM('individual', 'org_member', 'admin') NOT NULL DEFAULT 'individual',
    `org_id` INT UNSIGNED DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_users_email` (`email`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_org` (`org_id`),
    KEY `idx_users_county` (`county`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- Organizations
-- ────────────────────────────────────────────────────────────────────────────

DROP TABLE IF EXISTS `organizations`;
CREATE TABLE `organizations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `mission` TEXT DEFAULT NULL,
    `county_served` VARCHAR(100) NOT NULL,
    `contact_email` VARCHAR(255) NOT NULL,
    `contact_phone` VARCHAR(20) DEFAULT NULL,
    `website` VARCHAR(255) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
    `verified_at` TIMESTAMP NULL DEFAULT NULL,
    `verified_by` INT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_orgs_county` (`county_served`),
    KEY `idx_orgs_verified` (`is_verified`),
    KEY `idx_orgs_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key for users.org_id after organizations table exists
ALTER TABLE `users`
    ADD CONSTRAINT `fk_users_org` 
    FOREIGN KEY (`org_id`) REFERENCES `organizations` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- ────────────────────────────────────────────────────────────────────────────
-- Causes (Tags for Discovery)
-- ────────────────────────────────────────────────────────────────────────────

DROP TABLE IF EXISTS `causes`;
CREATE TABLE `causes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_causes_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- Listings (Needs, Offers, Volunteer Opportunities)
-- ────────────────────────────────────────────────────────────────────────────

DROP TABLE IF EXISTS `listings`;
CREATE TABLE `listings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type` ENUM('need', 'offer', 'volunteer') NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `quantity` VARCHAR(100) DEFAULT NULL,
    `county` VARCHAR(100) NOT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `urgency` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
    `status` ENUM('open', 'in_progress', 'fulfilled', 'closed') NOT NULL DEFAULT 'open',
    `logistics` ENUM('pickup', 'delivery', 'either', 'na') NOT NULL DEFAULT 'na',
    `contact_method` VARCHAR(255) DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `org_id` INT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `fulfilled_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_listings_type` (`type`),
    KEY `idx_listings_status` (`status`),
    KEY `idx_listings_county` (`county`),
    KEY `idx_listings_category` (`category`),
    KEY `idx_listings_urgency` (`urgency`),
    KEY `idx_listings_user` (`user_id`),
    KEY `idx_listings_org` (`org_id`),
    KEY `idx_listings_created` (`created_at`),
    -- Composite index for common browse query
    KEY `idx_listings_browse` (`status`, `type`, `county`, `urgency`),
    CONSTRAINT `fk_listings_user` 
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_listings_org` 
        FOREIGN KEY (`org_id`) REFERENCES `organizations` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- Listing Causes (Pivot Table)
-- ────────────────────────────────────────────────────────────────────────────

DROP TABLE IF EXISTS `listing_causes`;
CREATE TABLE `listing_causes` (
    `listing_id` INT UNSIGNED NOT NULL,
    `cause_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`listing_id`, `cause_id`),
    KEY `idx_lc_cause` (`cause_id`),
    CONSTRAINT `fk_lc_listing` 
        FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_lc_cause` 
        FOREIGN KEY (`cause_id`) REFERENCES `causes` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- Responses (Coordination / "I Can Help")
-- ────────────────────────────────────────────────────────────────────────────

DROP TABLE IF EXISTS `responses`;
CREATE TABLE `responses` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `listing_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('pending', 'accepted', 'declined', 'completed') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_responses_listing` (`listing_id`),
    KEY `idx_responses_user` (`user_id`),
    KEY `idx_responses_status` (`status`),
    CONSTRAINT `fk_responses_listing` 
        FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_responses_user` 
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- Response Messages (Thread within a Response)
-- ────────────────────────────────────────────────────────────────────────────

DROP TABLE IF EXISTS `response_messages`;
CREATE TABLE `response_messages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `response_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `message` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_rm_response` (`response_id`),
    KEY `idx_rm_user` (`user_id`),
    CONSTRAINT `fk_rm_response` 
        FOREIGN KEY (`response_id`) REFERENCES `responses` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_rm_user` 
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- Reports (Trust & Safety)
-- ────────────────────────────────────────────────────────────────────────────

DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reporter_id` INT UNSIGNED DEFAULT NULL,
    `reporter_email` VARCHAR(255) DEFAULT NULL,
    `listing_id` INT UNSIGNED DEFAULT NULL,
    `reported_user_id` INT UNSIGNED DEFAULT NULL,
    `reported_org_id` INT UNSIGNED DEFAULT NULL,
    `reason` VARCHAR(100) NOT NULL,
    `details` TEXT DEFAULT NULL,
    `status` ENUM('open', 'reviewing', 'resolved', 'dismissed') NOT NULL DEFAULT 'open',
    `resolved_by` INT UNSIGNED DEFAULT NULL,
    `resolution_notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_reports_status` (`status`),
    KEY `idx_reports_listing` (`listing_id`),
    KEY `idx_reports_reporter` (`reporter_id`),
    KEY `idx_reports_user` (`reported_user_id`),
    KEY `idx_reports_org` (`reported_org_id`),
    CONSTRAINT `fk_reports_reporter` 
        FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_reports_listing` 
        FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_reports_user` 
        FOREIGN KEY (`reported_user_id`) REFERENCES `users` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_reports_org` 
        FOREIGN KEY (`reported_org_id`) REFERENCES `organizations` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- Password Reset Tokens
-- ────────────────────────────────────────────────────────────────────────────

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `used_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pr_token` (`token`),
    KEY `idx_pr_user` (`user_id`),
    CONSTRAINT `fk_pr_user` 
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- End of Schema
-- ============================================================================
