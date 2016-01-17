SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

START TRANSACTION;

-- --------------------------------------------------------

--
-- Structure for table `account`
--

DROP TABLE IF EXISTS `account`;
CREATE TABLE IF NOT EXISTS `account` (
    `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role_id` INTEGER(11) NOT NULL,
    `is_active` BOOLEAN NOT NULL DEFAULT '0',
    `is_system` BOOLEAN NOT NULL DEFAULT '0',
    `created` DATETIME NOT NULL,
    `creator` INTEGER(11) DEFAULT NULL,
    `modified` DATETIME NOT NULL,
    `modifier` INTEGER(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uni_account_name` (`name`),
    KEY `idx_account_role` (`role_id`),
    KEY `idx_account_active` (`is_active`),
    KEY `idx_account_system` (`is_system`),
    KEY `idx_account_created` (`created`),
    KEY `idx_account_creator` (`creator`),
    KEY `idx_account_modified` (`modified`),
    KEY `idx_account_modifier` (`modifier`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Data for table `account`
--

INSERT INTO `account` (`id`, `name`, `password`, `role_id`, `is_active`, `is_system`, `created`, `creator`, `modified`, `modifier`) VALUES
(0, 'Anonymous', '', 0, '1', '1', NOW(), NULL, NOW(), NULL),
(1, 'admin', '$2y$10$9wnkOfY1qLvz0sRXG5G.d.rf2NhCU8a9m.XrLYIgeQA.SioSWwtsW', 1, '1', '1', NOW(), NULL, NOW(), NULL);

-- --------------------------------------------------------

--
-- Structure for table `attribute`
--

DROP TABLE IF EXISTS `attribute`;
CREATE TABLE IF NOT EXISTS `attribute` (
    `id` VARCHAR(100) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `type` VARCHAR(100) NOT NULL,
    `description` TEXT NOT NULL,
    `foreign_entity_id` VARCHAR(100) DEFAULT NULL,
    `options_callback` VARCHAR(255) DEFAULT NULL,
    `options` TEXT DEFAULT NULL,
    `created` DATETIME NOT NULL,
    `creator` INTEGER(11) DEFAULT NULL,
    `modified` DATETIME NOT NULL,
    `modifier` INTEGER(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_attribute_name` (`name`),
    KEY `idx_attribute_type` (`type`),
    KEY `idx_attribute_foreign` (`foreign_entity_id`),
    KEY `idx_attribute_created` (`created`),
    KEY `idx_attribute_creator` (`creator`),
    KEY `idx_attribute_modified` (`modified`),
    KEY `idx_attribute_modifier` (`modifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure for table `eav_content`
--

DROP TABLE IF EXISTS `eav_content`;
CREATE TABLE IF NOT EXISTS `eav_content` (
    `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `entity_id` VARCHAR(100) NOT NULL,
    `is_active` BOOLEAN NOT NULL DEFAULT '0',
    `meta` TEXT DEFAULT NULL,
    `created` DATETIME NOT NULL,
    `creator` INTEGER(11) DEFAULT NULL,
    `modified` DATETIME NOT NULL,
    `modifier` INTEGER(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_eav_content_name` (`name`),
    KEY `idx_eav_content_entity` (`entity_id`),
    KEY `idx_eav_content_active` (`is_active`),
    KEY `idx_eav_content_created` (`created`),
    KEY `idx_eav_content_creator` (`creator`),
    KEY `idx_eav_content_modified` (`modified`),
    KEY `idx_eav_content_modifier` (`modifier`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure for table `eav_value`
--

DROP TABLE IF EXISTS `eav_value`;
CREATE TABLE IF NOT EXISTS `eav_value` (
    `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `entity_id` VARCHAR(100) NOT NULL,
    `attribute_id` VARCHAR(100) NOT NULL,
    `content_id` INTEGER(11) NOT NULL,
    `value_bool` BOOLEAN DEFAULT NULL,
    `value_datetime` DATETIME DEFAULT NULL,
    `value_decimal` DECIMAL(12,4) DEFAULT NULL,
    `value_int` INTEGER(11) DEFAULT NULL,
    `value_text` TEXT DEFAULT NULL,
    `value_varchar` VARCHAR(255) DEFAULT NULL,
    `created` DATETIME NOT NULL,
    `creator` INTEGER(11) DEFAULT NULL,
    `modified` DATETIME NOT NULL,
    `modifier` INTEGER(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uni_eav_value_value` (`attribute_id`, `content_id`),
    KEY `idx_eav_value_entity` (`entity_id`),
    KEY `idx_eav_value_attribute` (`attribute_id`),
    KEY `idx_eav_value_content` (`content_id`),
    KEY `idx_eav_value_created` (`created`),
    KEY `idx_eav_value_creator` (`creator`),
    KEY `idx_eav_value_modified` (`modified`),
    KEY `idx_eav_value_modifier` (`modifier`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure for table `entity`
--

DROP TABLE IF EXISTS `entity`;
CREATE TABLE IF NOT EXISTS `entity` (
    `id` VARCHAR(100) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `actions` TEXT DEFAULT NULL,
    `toolbar` VARCHAR(255) NOT NULL,
    `sort_order` INTEGER(11) NOT NULL DEFAULT '0',
    `is_system` BOOLEAN NOT NULL DEFAULT '0',
    `created` DATETIME NOT NULL,
    `creator` INTEGER(11) DEFAULT NULL,
    `modified` DATETIME NOT NULL,
    `modifier` INTEGER(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_entity_name` (`name`),
    KEY `idx_entity_toolbar` (`toolbar`),
    KEY `idx_entity_sort` (`sort_order`),
    KEY `idx_entity_system` (`is_system`),
    KEY `idx_entity_created` (`created`),
    KEY `idx_entity_creator` (`creator`),
    KEY `idx_entity_modified` (`modified`),
    KEY `idx_entity_modifier` (`modifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure for table `menu_item`
--

DROP TABLE IF EXISTS `menu_item`;
CREATE TABLE IF NOT EXISTS `menu_item` (
    `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `target` VARCHAR(255) NOT NULL,
    `root_id` VARCHAR(100) NOT NULL,
    `lft` INTEGER(11) NOT NULL,
    `rgt` INTEGER(11) NOT NULL,
    `is_system` BOOLEAN NOT NULL DEFAULT '0',
    `created` DATETIME NOT NULL,
    `creator` INTEGER(11) DEFAULT NULL,
    `modified` DATETIME NOT NULL,
    `modifier` INTEGER(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_menu_item_name` (`name`),
    KEY `idx_menu_item_target` (`target`),
    KEY `idx_menu_item_root` (`root_id`),
    KEY `idx_menu_item_lft` (`lft`),
    KEY `idx_menu_item_rgt` (`rgt`),
    KEY `idx_menu_item_menu` (`root_id`,`lft`,`rgt`),
    KEY `idx_menu_item_system` (`is_system`),
    KEY `idx_menu_item_created` (`created`),
    KEY `idx_menu_item_creator` (`creator`),
    KEY `idx_menu_item_modified` (`modified`),
    KEY `idx_menu_item_modifier` (`modifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure for table `menu_root`
--

DROP TABLE IF EXISTS `menu_root`;
CREATE TABLE IF NOT EXISTS `menu_root` (
    `id` VARCHAR(100) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `is_system` BOOLEAN NOT NULL DEFAULT '0',
    `created` DATETIME NOT NULL,
    `creator` INTEGER(11) DEFAULT NULL,
    `modified` DATETIME NOT NULL,
    `modifier` INTEGER(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_menu_root_name` (`name`),
    KEY `idx_menu_root_system` (`is_system`),
    KEY `idx_menu_root_created` (`created`),
    KEY `idx_menu_root_creator` (`creator`),
    KEY `idx_menu_root_modified` (`modified`),
    KEY `idx_menu_root_modifier` (`modifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure for table `metadata`
--

DROP TABLE IF EXISTS `metadata`;
CREATE TABLE IF NOT EXISTS `metadata` (
    `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `entity_id` VARCHAR(100) NOT NULL,
    `attribute_id` VARCHAR(100) NOT NULL,
    `sort_order` INTEGER(11) NOT NULL DEFAULT '0',
    `actions` TEXT DEFAULT NULL,
    `is_required` BOOLEAN NOT NULL DEFAULT '0',
    `is_unique` BOOLEAN NOT NULL DEFAULT '0',
    `created` DATETIME NOT NULL,
    `creator` INTEGER(11) DEFAULT NULL,
    `modified` DATETIME NOT NULL,
    `modifier` INTEGER(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uni_metadata_attribute` (`entity_id`,`attribute_id`),
    KEY `idx_metadata_entity` (`entity_id`),
    KEY `idx_metadata_attribute` (`attribute_id`),
    KEY `idx_metadata_sort` (`sort_order`),
    KEY `idx_metadata_required` (`is_required`),
    KEY `idx_metadata_unique` (`is_unique`),
    KEY `idx_metadata_created` (`created`),
    KEY `idx_metadata_creator` (`creator`),
    KEY `idx_metadata_modified` (`modified`),
    KEY `idx_metadata_modifier` (`modifier`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure for table `rewrite`
--

DROP TABLE IF EXISTS `rewrite`;
CREATE TABLE IF NOT EXISTS `rewrite` (
    `id` VARCHAR(255) NOT NULL,
    `target` VARCHAR(255) NOT NULL,
    `is_redirect` BOOLEAN NOT NULL DEFAULT '0',
    `is_system` BOOLEAN NOT NULL DEFAULT '0',
    `created` DATETIME NOT NULL,
    `creator` INTEGER(11) DEFAULT NULL,
    `modified` DATETIME NOT NULL,
    `modifier` INTEGER(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_rewrite_target` (`target`),
    KEY `idx_rewrite_redirect` (`is_redirect`),
    KEY `idx_rewrite_system` (`is_system`),
    KEY `idx_rewrite_created` (`created`),
    KEY `idx_rewrite_creator` (`creator`),
    KEY `idx_rewrite_modified` (`modified`),
    KEY `idx_rewrite_modifier` (`modifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure for table `role`
--

DROP TABLE IF EXISTS `role`;
CREATE TABLE IF NOT EXISTS `role` (
    `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `privilege` TEXT DEFAULT NULL,
    `is_active` BOOLEAN NOT NULL DEFAULT '0',
    `is_system` BOOLEAN NOT NULL DEFAULT '0',
    `created` DATETIME NOT NULL,
    `creator` INTEGER(11) DEFAULT NULL,
    `modified` DATETIME NOT NULL,
    `modifier` INTEGER(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uni_role_name` (`name`),
    KEY `idx_role_active` (`is_active`),
    KEY `idx_role_system` (`is_system`),
    KEY `idx_role_created` (`created`),
    KEY `idx_role_creator` (`creator`),
    KEY `idx_role_modified` (`modified`),
    KEY `idx_role_modifier` (`modifier`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Data for table `role`
--

INSERT INTO `role` (`id`, `name`, `privilege`, `is_active`, `is_system`, `created`, `creator`, `modified`, `modifier`) VALUES
(0, 'Anonymous', NULL, '1', '1', NOW(), NULL, NOW(), NULL),
(1, 'Administrator', '["all"]', '1', '1', NOW(), NULL, NOW(), NULL);

-- --------------------------------------------------------

--
-- Structure for table `search`
--

DROP TABLE IF EXISTS `search`;
CREATE TABLE IF NOT EXISTS `search` (
    `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `entity_id` VARCHAR(100) NOT NULL,
    `content_id` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `created` DATETIME NOT NULL,
    `creator` INTEGER(11) DEFAULT NULL,
    `modified` DATETIME NOT NULL,
    `modifier` INTEGER(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uni_search_content` (`entity_id`,`content_id`),
    KEY `idx_search_entity` (`entity_id`),
    KEY `idx_search_content` (`content_id`),
    KEY `idx_search_created` (`created`),
    KEY `idx_search_creator` (`creator`),
    KEY `idx_search_modified` (`modified`),
    KEY `idx_search_modifier` (`modifier`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Constraints for table `account`
--
ALTER TABLE `account`
    ADD CONSTRAINT `con_account_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`),
    ADD CONSTRAINT `con_account_creator` FOREIGN KEY (`creator`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `con_account_modifier` FOREIGN KEY (`modifier`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `attribute`
--
ALTER TABLE `attribute`
    ADD CONSTRAINT `con_attribute_creator` FOREIGN KEY (`creator`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `con_attribute_modifier` FOREIGN KEY (`modifier`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `eav_content`
--
ALTER TABLE `eav_content`
    ADD CONSTRAINT `con_eav_content_entity` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `con_eav_content_creator` FOREIGN KEY (`creator`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `con_eav_content_modifier` FOREIGN KEY (`modifier`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `eav_value`
--
ALTER TABLE `eav_value`
    ADD CONSTRAINT `con_eav_value_entity` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `con_eav_value_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `con_eav_value_content` FOREIGN KEY (`content_id`) REFERENCES `eav_content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `con_eav_value_creator` FOREIGN KEY (`creator`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `con_eav_value_modifier` FOREIGN KEY (`modifier`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `entity`
--
ALTER TABLE `entity`
    ADD CONSTRAINT `con_entity_creator` FOREIGN KEY (`creator`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `con_entity_modifier` FOREIGN KEY (`modifier`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `menu_item`
--
ALTER TABLE `menu_item`
    ADD CONSTRAINT `con_menu_item_root` FOREIGN KEY (`root_id`) REFERENCES `menu_root` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `con_menu_item_creator` FOREIGN KEY (`creator`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `con_menu_item_modifier` FOREIGN KEY (`modifier`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `menu_root`
--
ALTER TABLE `menu_root`
    ADD CONSTRAINT `con_menu_root_creator` FOREIGN KEY (`creator`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `con_menu_root_modifier` FOREIGN KEY (`modifier`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `metadata`
--
ALTER TABLE `metadata`
    ADD CONSTRAINT `con_metadata_entity` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `con_metadata_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `con_metadata_creator` FOREIGN KEY (`creator`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `con_metadata_modifier` FOREIGN KEY (`modifier`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `rewrite`
--
ALTER TABLE `rewrite`
    ADD CONSTRAINT `con_rewrite_creator` FOREIGN KEY (`creator`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `con_rewrite_modifier` FOREIGN KEY (`modifier`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `role`
--
ALTER TABLE `role`
    ADD CONSTRAINT `con_role_creator` FOREIGN KEY (`creator`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `con_role_modifier` FOREIGN KEY (`modifier`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `search`
--
ALTER TABLE `search`
    ADD CONSTRAINT `con_search_creator` FOREIGN KEY (`creator`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `con_search_modifier` FOREIGN KEY (`modifier`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- --------------------------------------------------------

COMMIT;
SET FOREIGN_KEY_CHECKS=1;
