SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

START TRANSACTION;

-- --------------------------------------------------------
-- Tables
-- --------------------------------------------------------

DROP TABLE IF EXISTS attr;
CREATE TABLE IF NOT EXISTS attr (
    id VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    entity_id VARCHAR(100) NOT NULL,
    sort INTEGER(11) NOT NULL DEFAULT '0',
    type VARCHAR(100) NOT NULL,
    required BOOLEAN NOT NULL DEFAULT '0',
    uniq BOOLEAN NOT NULL DEFAULT '0',
    searchable BOOLEAN NOT NULL DEFAULT '0',
    opt JSON DEFAULT NULL,
    actions JSON DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_attr_name (name),
    KEY idx_attr_entity (entity_id),
    KEY idx_attr_type (type),
    KEY idx_attr_sort (sort),
    KEY idx_attr_required (required),
    KEY idx_attr_uniq (uniq),
    KEY idx_attr_searchable (searchable),
    CONSTRAINT con_attr_entity FOREIGN KEY (entity_id) REFERENCES entity (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS content;
CREATE TABLE IF NOT EXISTS content (
    id INTEGER(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    entity_id VARCHAR(100) NOT NULL,
    active BOOLEAN NOT NULL DEFAULT '0',
    system BOOLEAN NOT NULL DEFAULT '0',
    content TEXT DEFAULT NULL,
    meta JSON DEFAULT NULL,
    search TEXT DEFAULT NULL,
    created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    creator INTEGER(11) DEFAULT NULL,
    modified DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    modifier INTEGER(11) DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_content_name (name),
    KEY idx_content_entity (entity_id),
    KEY idx_content_active (active),
    KEY idx_content_system (system),
    KEY idx_content_created (created),
    KEY idx_content_creator (creator),
    KEY idx_content_modified (modified),
    KEY idx_content_modifier (modifier),
    FULLTEXT idx_content_search (search),
    CONSTRAINT con_content_entity FOREIGN KEY (entity_id) REFERENCES entity (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT con_content_creator FOREIGN KEY (creator) REFERENCES user (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT con_content_modifier FOREIGN KEY (modifier) REFERENCES user (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS eav;
CREATE TABLE IF NOT EXISTS eav (
    content_id INTEGER(11) NOT NULL,
    attr_id VARCHAR(100) NOT NULL,
    value TEXT NOT NULL,
    PRIMARY KEY (content_id, attr_id),
    KEY idx_eav_content (content_id),
    KEY idx_eav_attr (attr_id),
    CONSTRAINT con_eav_content FOREIGN KEY (content_id) REFERENCES content (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT con_eav_attr FOREIGN KEY (attr_id) REFERENCES attr (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS entity;
CREATE TABLE IF NOT EXISTS entity (
    id VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    model VARCHAR(255) NOT NULL,
    actions JSON DEFAULT NULL,
    system BOOLEAN NOT NULL DEFAULT '0',
    PRIMARY KEY (id),
    KEY idx_entity_name (name),
    KEY idx_entity_model (model),
    KEY idx_entity_system (system)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS node;
CREATE TABLE IF NOT EXISTS node (
    id INTEGER(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    target VARCHAR(255) DEFAULT NULL,
    root_id INTEGER(11) NOT NULL,
    lft INTEGER(11) NOT NULL,
    rgt INTEGER(11) NOT NULL,
    parent_id INTEGER(11) DEFAULT NULL,
    level INTEGER(11) NOT NULL,
    position VARCHAR(255) AS (CONCAT(root_id, ':', lft)) STORED NOT NULL,
    PRIMARY KEY (id),
    KEY idx_node_name (name),
    KEY idx_node_target (target),
    KEY idx_node_root (root_id),
    KEY idx_node_lft (lft),
    KEY idx_node_rgt (rgt),
    KEY idx_node_parent (parent_id),
    KEY idx_node_level (level),
    KEY idx_node_position (position),
    KEY idx_node_item (root_id,lft,rgt),
    CONSTRAINT con_node_root FOREIGN KEY (root_id) REFERENCES content (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT con_node_parent FOREIGN KEY (parent_id) REFERENCES node (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS project;
CREATE TABLE IF NOT EXISTS project (
    id INTEGER(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    host VARCHAR(255) DEFAULT NULL,
    active BOOLEAN NOT NULL DEFAULT '0',
    system BOOLEAN NOT NULL DEFAULT '0',
    PRIMARY KEY (id),
    UNIQUE KEY uni_project_host (host),
    KEY idx_project_name (name),
    KEY idx_project_active (active),
    KEY idx_project_system (system)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS rewrite;
CREATE TABLE IF NOT EXISTS rewrite (
    id INTEGER(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    target VARCHAR(255) NOT NULL,
    redirect BOOLEAN NOT NULL DEFAULT '0',
    system BOOLEAN NOT NULL DEFAULT '0',
    PRIMARY KEY (id),
    UNIQUE KEY uni_rewrite_name (name),
    KEY idx_rewrite_target (target),
    KEY idx_rewrite_redirect (redirect),
    KEY idx_rewrite_system (system)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS role;
CREATE TABLE IF NOT EXISTS role (
    id INTEGER(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    privilege JSON NOT NULL,
    active BOOLEAN NOT NULL DEFAULT '0',
    system BOOLEAN NOT NULL DEFAULT '0',
    PRIMARY KEY (id),
    UNIQUE KEY uni_role_name (name),
    KEY idx_role_active (active),
    KEY idx_role_system (system)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS user;
CREATE TABLE IF NOT EXISTS user (
    id INTEGER(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INTEGER(11) NOT NULL,
    active BOOLEAN NOT NULL DEFAULT '0',
    system BOOLEAN NOT NULL DEFAULT '0',
    PRIMARY KEY (id),
    UNIQUE KEY uni_user_username (username),
    KEY idx_user_name (name),
    KEY idx_user_role (role_id),
    KEY idx_user_active (active),
    KEY idx_user_system (system),
    CONSTRAINT con_user_role FOREIGN KEY (role_id) REFERENCES role (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Data
-- --------------------------------------------------------

INSERT INTO content (id, name, entity_id, active, system, content) VALUES
(1, 'Home', 'page', '1', '0', 'Hello World'),
(2, 'Toolbar', 'menu', '1', '1', NULL),
(3, 'Main Navigation', 'menu', '1', '1', NULL);

INSERT INTO entity (id, name, model, actions, system) VALUES
('attr', 'Attribute', 'flat', '["create", "edit", "delete", "index"]', '1'),
('content', 'Content', 'flat', '[]', '1'),
('eav', 'EAV', 'flat', '[]', '1'),
('entity', 'Entity', 'flat', '["create", "edit", "delete", "index"]', '1'),
('project', 'Project', 'flat', '["create", "edit", "delete", "index"]', '1'),
('rewrite', 'Rewrite', 'flat', '["create", "edit", "delete", "index"]', '1'),
('role', 'Role', 'flat', '["create", "edit", "delete", "index"]', '1'),
('user', 'User', 'flat', '["create", "edit", "delete", "index"]', '1'),
('node', 'Menu Node', 'node', '["create", "edit", "delete", "index"]', '1'),
('menu', 'Menu', 'content', '["create", "edit", "delete", "index"]', '1'),
('page', 'Page', 'eav', '["all"]', '1');

INSERT INTO node (id, name, target, root_id, lft, rgt, parent_id, level) VALUES
(1, 'Home', 'page/view/id/1', 2, 1, 2, NULL, 1),
(2, 'Dashboard', 'user/dashboard', 2, 3, 4, NULL, 1),
(3, 'Profile', 'user/profile', 2, 5, 6, NULL, 1),
(4, 'Logout', 'user/logout', 2, 7, 8, NULL, 1),
(5, 'Content', '', 2, 9, 12, NULL, 1),
(6, 'Structure', '', 2, 13, 22, NULL, 1),
(7, 'System', '', 2, 23, 32, NULL, 1),
(8, 'Page', 'page/index', 2, 10, 11, 5, 2),
(9, 'Project', 'project/index', 2, 24, 25, 7, 2),
(10, 'User', 'user/index', 2, 26, 27, 7, 2),
(11, 'Role', 'role/index', 2, 28, 29, 7, 2),
(12, 'Rewrite', 'rewrite/index', 2, 30, 31, 7, 2),
(13, 'Menu', 'menu/index', 2, 14, 15, 6, 2),
(14, 'Menu Node', 'node/index', 2, 16, 17, 6, 2),
(15, 'Entity', 'entity/index', 2, 18, 19, 6, 2),
(16, 'Attribute', 'attr/index', 2, 20, 21, 6, 2);

INSERT INTO project (id, name, host, active, system) VALUES
(0, 'global', NULL, '1', '1');

INSERT INTO rewrite (id, name, target) VALUES
(1, 'http-base', 'page/view/id/1');

INSERT INTO role (id, name, privilege, active, system) VALUES
(0, 'anonymous', '["page.list", "page.view"]', '1', '1'),
(1, 'admin', '["all"]', '1', '1');

INSERT INTO user (id, name, username, password, role_id, active, system) VALUES
(0, 'Anonymous', 'anonymous', '', 0, '1', '1'),
(1, 'Admin', 'admin', '$2y$10$9wnkOfY1qLvz0sRXG5G.d.rf2NhCU8a9m.XrLYIgeQA.SioSWwtsW', 1, '1', '1');

-- --------------------------------------------------------

COMMIT;

SET FOREIGN_KEY_CHECKS=1;
