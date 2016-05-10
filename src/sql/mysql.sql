SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

START TRANSACTION;

-- --------------------------------------------------------
-- Tables
-- --------------------------------------------------------

DROP TABLE IF EXISTS `attribute`;
CREATE TABLE IF NOT EXISTS `attribute` (
    `id` VARCHAR(100) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `type` VARCHAR(100) NOT NULL,
    `options_entity` VARCHAR(100) DEFAULT NULL,
    `options_callback` VARCHAR(255) DEFAULT NULL,
    `options` TEXT DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_attribute_name` (`name`),
    KEY `idx_attribute_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `content`;
CREATE TABLE IF NOT EXISTS `content` (
    `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `entity_id` VARCHAR(100) NOT NULL,
    `active` BOOLEAN NOT NULL DEFAULT '0',
    `system` BOOLEAN NOT NULL DEFAULT '0',
    `meta` TEXT DEFAULT NULL,
    `search` TEXT DEFAULT NULL,
    `created` DATETIME NOT NULL,
    `creator` INTEGER(11) DEFAULT NULL,
    `modified` DATETIME NOT NULL,
    `modifier` INTEGER(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_content_name` (`name`),
    KEY `idx_content_entity` (`entity_id`),
    KEY `idx_content_active` (`active`),
    KEY `idx_content_system` (`system`),
    KEY `idx_content_created` (`created`),
    KEY `idx_content_creator` (`creator`),
    KEY `idx_content_modified` (`modified`),
    KEY `idx_content_modifier` (`modifier`),
    FULLTEXT `idx_content_search` (`search`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `eav`;
CREATE TABLE IF NOT EXISTS `eav` (
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
    PRIMARY KEY (`id`),
    UNIQUE KEY `uni_eav_value` (`attribute_id`, `content_id`),
    KEY `idx_eav_entity` (`entity_id`),
    KEY `idx_eav_attribute` (`attribute_id`),
    KEY `idx_eav_content` (`content_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `entity`;
CREATE TABLE IF NOT EXISTS `entity` (
    `id` VARCHAR(100) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `actions` TEXT DEFAULT NULL,
    `toolbar` VARCHAR(255) NOT NULL,
    `sort_order` INTEGER(11) NOT NULL DEFAULT '0',
    `system` BOOLEAN NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `idx_entity_name` (`name`),
    KEY `idx_entity_toolbar` (`toolbar`),
    KEY `idx_entity_sort` (`sort_order`),
    KEY `idx_entity_system` (`system`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `meta`;
CREATE TABLE IF NOT EXISTS `meta` (
    `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `entity_id` VARCHAR(100) NOT NULL,
    `attribute_id` VARCHAR(100) NOT NULL,
    `sort_order` INTEGER(11) NOT NULL DEFAULT '0',
    `required` BOOLEAN NOT NULL DEFAULT '0',
    `unambiguous` BOOLEAN NOT NULL DEFAULT '0',
    `searchable` BOOLEAN NOT NULL DEFAULT '0',
    `actions` TEXT DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uni_meta_attribute` (`entity_id`,`attribute_id`),
    KEY `idx_meta_entity` (`entity_id`),
    KEY `idx_meta_attribute` (`attribute_id`),
    KEY `idx_meta_sort` (`sort_order`),
    KEY `idx_meta_required` (`required`),
    KEY `idx_meta_unambiguous` (`unambiguous`),
    KEY `idx_meta_searchable` (`searchable`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `project`;
CREATE TABLE IF NOT EXISTS `project` (
    `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `host` VARCHAR(255) DEFAULT NULL,
    `active` BOOLEAN NOT NULL DEFAULT '0',
    `system` BOOLEAN NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uni_project_host` (`host`),
    KEY `idx_project_name` (`name`),
    KEY `idx_project_active` (`active`),
    KEY `idx_project_system` (`system`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `rewrite`;
CREATE TABLE IF NOT EXISTS `rewrite` (
    `id` VARCHAR(255) NOT NULL,
    `target` VARCHAR(255) NOT NULL,
    `redirect` BOOLEAN NOT NULL DEFAULT '0',
    `system` BOOLEAN NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `idx_rewrite_target` (`target`),
    KEY `idx_rewrite_redirect` (`redirect`),
    KEY `idx_rewrite_system` (`system`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `role`;
CREATE TABLE IF NOT EXISTS `role` (
    `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `privilege` TEXT DEFAULT NULL,
    `active` BOOLEAN NOT NULL DEFAULT '0',
    `system` BOOLEAN NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uni_role_name` (`name`),
    KEY `idx_role_active` (`active`),
    KEY `idx_role_system` (`system`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `tree`;
CREATE TABLE IF NOT EXISTS `tree` (
    `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `target` VARCHAR(255) NOT NULL,
    `root_id` INTEGER(11) NOT NULL,
    `lft` INTEGER(11) NOT NULL,
    `rgt` INTEGER(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_tree_name` (`name`),
    KEY `idx_tree_target` (`target`),
    KEY `idx_tree_root` (`root_id`),
    KEY `idx_tree_lft` (`lft`),
    KEY `idx_tree_rgt` (`rgt`),
    KEY `idx_tree_item` (`root_id`,`lft`,`rgt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `tree_root`;
CREATE TABLE IF NOT EXISTS `tree_root` (
    `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `system` BOOLEAN NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `idx_tree_root_name` (`name`),
    KEY `idx_tree_root_system` (`system`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
    `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `username` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role_id` INTEGER(11) NOT NULL,
    `active` BOOLEAN NOT NULL DEFAULT '0',
    `system` BOOLEAN NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uni_user_username` (`username`),
    KEY `idx_user_name` (`name`),
    KEY `idx_user_role` (`role_id`),
    KEY `idx_user_active` (`active`),
    KEY `idx_user_system` (`system`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Constraints
-- --------------------------------------------------------

ALTER TABLE `content`
    ADD CONSTRAINT `con_content_entity` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `con_content_creator` FOREIGN KEY (`creator`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `con_content_modifier` FOREIGN KEY (`modifier`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `eav`
    ADD CONSTRAINT `con_eav_entity` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `con_eav_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `con_eav_content` FOREIGN KEY (`content_id`) REFERENCES `content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `meta`
    ADD CONSTRAINT `con_meta_entity` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `con_meta_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `tree`
    ADD CONSTRAINT `con_tree_root` FOREIGN KEY (`root_id`) REFERENCES `tree_root` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `user`
    ADD CONSTRAINT `con_user_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`);

-- --------------------------------------------------------
-- Data
-- --------------------------------------------------------

INSERT INTO `project` (`id`, `name`, `host`, `active`, `system`) VALUES
(0, 'global', NULL, '1', '1');

INSERT INTO `role` (`id`, `name`, `privilege`, `active`, `system`) VALUES
(0, 'anonymous', NULL, '1', '1'),
(1, 'admin', '["all"]', '1', '1');

INSERT INTO `user` (`id`, `name`, `username`, `password`, `role_id`, `active`, `system`) VALUES
(0, 'anonymous', '', '', 0, '1', '1'),
(1, 'admin', 'admin', '$2y$10$9wnkOfY1qLvz0sRXG5G.d.rf2NhCU8a9m.XrLYIgeQA.SioSWwtsW', 1, '1', '1');

-- --------------------------------------------------------

COMMIT;

SET FOREIGN_KEY_CHECKS=1;
