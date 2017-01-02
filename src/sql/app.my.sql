SET FOREIGN_KEY_CHECKS=0;
START TRANSACTION;

DROP TABLE IF EXISTS attr;
CREATE TABLE IF NOT EXISTS attr (
    id INTEGER(11) NOT NULL AUTO_INCREMENT,
    entity_id VARCHAR(100) NOT NULL,
    uid VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    sort INTEGER(11) NOT NULL DEFAULT '0',
    type VARCHAR(100) NOT NULL,
    required BOOLEAN NOT NULL DEFAULT '0',
    uniq BOOLEAN NOT NULL DEFAULT '0',
    searchable BOOLEAN NOT NULL DEFAULT '0',
    opt JSON NOT NULL,
    actions JSON NOT NULL,
    project_id VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uni_attr_uid (entity_id, uid),
    KEY idx_attr_entity (entity_id),
    KEY idx_attr_uid (uid),
    KEY idx_attr_name (name),
    KEY idx_attr_type (type),
    KEY idx_attr_sort (sort),
    KEY idx_attr_required (required),
    KEY idx_attr_uniq (uniq),
    KEY idx_attr_searchable (searchable),
    KEY idx_attr_project (project_id),
    CONSTRAINT con_attr_entity FOREIGN KEY (entity_id) REFERENCES entity (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT con_attr_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS content;
CREATE TABLE IF NOT EXISTS content (
    id INTEGER(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    entity_id VARCHAR(100) NOT NULL,
    active BOOLEAN NOT NULL DEFAULT '0',
    content TEXT DEFAULT NULL,
    search TEXT DEFAULT NULL,
    created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    creator INTEGER(11) DEFAULT NULL,
    modified DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    modifier INTEGER(11) DEFAULT NULL,
    project_id VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_content_name (name),
    KEY idx_content_entity (entity_id),
    KEY idx_content_active (active),
    KEY idx_content_created (created),
    KEY idx_content_creator (creator),
    KEY idx_content_modified (modified),
    KEY idx_content_modifier (modifier),
    KEY idx_content_project (project_id),
    FULLTEXT idx_content_search (search),
    CONSTRAINT con_content_entity FOREIGN KEY (entity_id) REFERENCES entity (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT con_content_creator FOREIGN KEY (creator) REFERENCES user (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT con_content_modifier FOREIGN KEY (modifier) REFERENCES user (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT con_content_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS eav;
CREATE TABLE IF NOT EXISTS eav (
    content_id INTEGER(11) NOT NULL,
    attr_id INTEGER(11) NOT NULL,
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
    actions JSON NOT NULL,
    system BOOLEAN NOT NULL DEFAULT '0',
    project_id VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_entity_name (name),
    KEY idx_entity_system (system),
    KEY idx_entity_project (project_id),
    CONSTRAINT con_entity_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS menu;
CREATE TABLE IF NOT EXISTS menu (
    id INTEGER(11) NOT NULL AUTO_INCREMENT,
    uid VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    system BOOLEAN NOT NULL DEFAULT '0',
    project_id VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uni_menu_uid (project_id, uid),
    KEY idx_menu_uid (uid),
    KEY idx_menu_name (name),
    KEY idx_menu_system (system),
    KEY idx_menu_project (project_id),
    CONSTRAINT con_menu_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS node;
CREATE TABLE IF NOT EXISTS node (
    id INTEGER(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    target VARCHAR(255) NOT NULL,
    root_id INTEGER(11) NOT NULL,
    lft INTEGER(11) NOT NULL,
    rgt INTEGER(11) NOT NULL,
    level INTEGER(11) NOT NULL,
    project_id VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_node_name (name),
    KEY idx_node_target (target),
    KEY idx_node_root (root_id),
    KEY idx_node_lft (lft),
    KEY idx_node_rgt (rgt),
    KEY idx_node_level (level),
    KEY idx_node_project (project_id),
    KEY idx_node_item (root_id,lft,rgt),
    CONSTRAINT con_node_root FOREIGN KEY (root_id) REFERENCES menu (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT con_node_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS project;
CREATE TABLE IF NOT EXISTS project (
    id VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    host VARCHAR(255) DEFAULT NULL,
    theme VARCHAR(100) DEFAULT NULL,
    active BOOLEAN NOT NULL DEFAULT '0',
    system BOOLEAN NOT NULL DEFAULT '0',
    PRIMARY KEY (id),
    UNIQUE KEY uni_project_host (host),
    KEY idx_project_name (name),
    KEY idx_project_theme (theme),
    KEY idx_project_active (active),
    KEY idx_project_system (system)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS role;
CREATE TABLE IF NOT EXISTS role (
    id INTEGER(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    privilege JSON NOT NULL,
    active BOOLEAN NOT NULL DEFAULT '0',
    system BOOLEAN NOT NULL DEFAULT '0',
    project_id VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uni_role_name (project_id, name),
    KEY idx_role_name (name),
    KEY idx_role_active (active),
    KEY idx_role_system (system),
    KEY idx_role_project (project_id),
    CONSTRAINT con_role_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS url;
CREATE TABLE IF NOT EXISTS url (
    id INTEGER(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    target VARCHAR(255) NOT NULL,
    redirect BOOLEAN NOT NULL DEFAULT '0',
    system BOOLEAN NOT NULL DEFAULT '0',
    project_id VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uni_url_name (project_id, name),
    KEY idx_url_name (name),
    KEY idx_url_target (target),
    KEY idx_url_redirect (redirect),
    KEY idx_url_system (system),
    KEY idx_url_project (project_id),
    CONSTRAINT con_url_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE ON UPDATE CASCADE
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
    project_id VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uni_user_name (project_id, name),
    UNIQUE KEY uni_user_username (username),
    KEY idx_user_name (name),
    KEY idx_user_role (role_id),
    KEY idx_user_active (active),
    KEY idx_user_system (system),
    KEY idx_user_project (project_id),
    CONSTRAINT con_user_role FOREIGN KEY (role_id) REFERENCES role (id),
    CONSTRAINT con_user_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

COMMIT;
SET FOREIGN_KEY_CHECKS=1;
