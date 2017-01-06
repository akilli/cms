START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Project
-- ---------------------------------------------------------------------------------------------------------------------
CREATE TABLE project (
    id VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    host VARCHAR(255) DEFAULT NULL,
    theme VARCHAR(100) DEFAULT NULL,
    active BOOLEAN NOT NULL DEFAULT FALSE,
    system BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (id)
);

CREATE UNIQUE INDEX uni_project_host ON project (host);
CREATE INDEX idx_project_name ON project (name);
CREATE INDEX idx_project_theme ON project (theme);
CREATE INDEX idx_project_active ON project (active);
CREATE INDEX idx_project_system ON project (system);

-- ---------------------------------------------------------------------------------------------------------------------
-- Auth
-- ---------------------------------------------------------------------------------------------------------------------
CREATE TABLE role (
    id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    privilege JSON NOT NULL,
    active BOOLEAN NOT NULL DEFAULT FALSE,
    system BOOLEAN NOT NULL DEFAULT FALSE,
    project_id VARCHAR(100) NOT NULL,
    PRIMARY KEY (id)
);

CREATE UNIQUE INDEX uni_role_name ON role (project_id, name);
CREATE INDEX idx_role_name ON role (name);
CREATE INDEX idx_role_active ON role (active);
CREATE INDEX idx_role_system ON role (system);
CREATE INDEX idx_role_project ON role (project_id);

ALTER TABLE role
    ADD CONSTRAINT con_role_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE ON UPDATE CASCADE;

-- -----------------------------------------------------------
CREATE TABLE account (
    id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INTEGER NOT NULL,
    active BOOLEAN NOT NULL DEFAULT FALSE,
    system BOOLEAN NOT NULL DEFAULT FALSE,
    project_id VARCHAR(100) NOT NULL,
    PRIMARY KEY (id)
);

CREATE UNIQUE INDEX uni_account_name ON account (project_id, name);
CREATE UNIQUE INDEX uni_account_username ON account (username);
CREATE INDEX idx_account_name ON account (name);
CREATE INDEX idx_account_role ON account (role_id);
CREATE INDEX idx_account_active ON account (active);
CREATE INDEX idx_account_system ON account (system);
CREATE INDEX idx_account_project ON account (project_id);

ALTER TABLE account
    ADD CONSTRAINT con_account_role FOREIGN KEY (role_id) REFERENCES role (id),
    ADD CONSTRAINT con_account_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE ON UPDATE CASCADE;

-- ---------------------------------------------------------------------------------------------------------------------
-- EAV
-- ---------------------------------------------------------------------------------------------------------------------
CREATE TABLE entity (
    id VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    actions JSON NOT NULL,
    system BOOLEAN NOT NULL DEFAULT FALSE,
    project_id VARCHAR(100) NOT NULL,
    PRIMARY KEY (id)
);

CREATE INDEX idx_entity_name ON entity (name);
CREATE INDEX idx_entity_system ON entity (system);
CREATE INDEX idx_entity_project ON entity (project_id);

ALTER TABLE entity
    ADD CONSTRAINT con_entity_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE ON UPDATE CASCADE;

-- -----------------------------------------------------------
CREATE TABLE attr (
    id INTEGER NOT NULL AUTO_INCREMENT,
    entity_id VARCHAR(100) NOT NULL,
    uid VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    sort INTEGER NOT NULL DEFAULT 0,
    type VARCHAR(100) NOT NULL,
    required BOOLEAN NOT NULL DEFAULT FALSE,
    uniq BOOLEAN NOT NULL DEFAULT FALSE,
    searchable BOOLEAN NOT NULL DEFAULT FALSE,
    opt JSON NOT NULL,
    actions JSON NOT NULL,
    project_id VARCHAR(100) NOT NULL,
    PRIMARY KEY (id)
);

CREATE UNIQUE INDEX uni_attr_uid ON attr (entity_id, uid);
CREATE INDEX idx_attr_entity ON attr (entity_id);
CREATE INDEX idx_attr_uid ON attr (uid);
CREATE INDEX idx_attr_name ON attr (name);
CREATE INDEX idx_attr_type ON attr (type);
CREATE INDEX idx_attr_sort ON attr (sort);
CREATE INDEX idx_attr_required ON attr (required);
CREATE INDEX idx_attr_uniq ON attr (uniq);
CREATE INDEX idx_attr_searchable ON attr (searchable);
CREATE INDEX idx_attr_project ON attr (project_id);

ALTER TABLE attr
    ADD CONSTRAINT con_attr_entity FOREIGN KEY (entity_id) REFERENCES entity (id) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT con_attr_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE ON UPDATE CASCADE;

-- -----------------------------------------------------------
CREATE TABLE content (
    id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    entity_id VARCHAR(100) NOT NULL,
    active BOOLEAN NOT NULL DEFAULT FALSE,
    content TEXT NOT NULL,
    search TEXT NOT NULL,
    created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    creator INTEGER DEFAULT NULL,
    modified DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modifier INTEGER DEFAULT NULL,
    project_id VARCHAR(100) NOT NULL,
    PRIMARY KEY (id)
);

CREATE INDEX idx_content_name ON content (name);
CREATE INDEX idx_content_entity ON content (entity_id);
CREATE INDEX idx_content_active ON content (active);
CREATE INDEX idx_content_created ON content (created);
CREATE INDEX idx_content_creator ON content (creator);
CREATE INDEX idx_content_modified ON content (modified);
CREATE INDEX idx_content_modifier ON content (modifier);
CREATE INDEX idx_content_project ON content (project_id);
CREATE INDEX idx_content_search ON content (search);

ALTER TABLE content
    ADD CONSTRAINT con_content_entity FOREIGN KEY (entity_id) REFERENCES entity (id) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT con_content_creator FOREIGN KEY (creator) REFERENCES account (id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT con_content_modifier FOREIGN KEY (modifier) REFERENCES account (id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT con_content_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE ON UPDATE CASCADE;

-- -----------------------------------------------------------
CREATE TABLE val (
    content_id INTEGER NOT NULL,
    attr_id INTEGER NOT NULL,
    value TEXT NOT NULL,
    PRIMARY KEY (content_id, attr_id)
);

CREATE INDEX idx_val_content ON val (content_id);
CREATE INDEX idx_val_attr ON val (attr_id);

ALTER TABLE val
    ADD CONSTRAINT con_val_content FOREIGN KEY (content_id) REFERENCES content (id) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT con_val_attr FOREIGN KEY (attr_id) REFERENCES attr (id) ON DELETE CASCADE ON UPDATE CASCADE;

-- ---------------------------------------------------------------------------------------------------------------------
-- Menu
-- ---------------------------------------------------------------------------------------------------------------------
CREATE TABLE menu (
    id INTEGER NOT NULL AUTO_INCREMENT,
    uid VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    system BOOLEAN NOT NULL DEFAULT FALSE,
    project_id VARCHAR(100) NOT NULL,
    PRIMARY KEY (id)
);

CREATE UNIQUE INDEX uni_menu_uid ON menu (project_id, uid);
CREATE INDEX idx_menu_uid ON menu (uid);
CREATE INDEX idx_menu_name ON menu (name);
CREATE INDEX idx_menu_system ON menu (system);
CREATE INDEX idx_menu_project ON menu (project_id);

ALTER TABLE menu
    ADD CONSTRAINT con_menu_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE ON UPDATE CASCADE;

-- -----------------------------------------------------------
CREATE TABLE node (
    id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    target VARCHAR(255) NOT NULL,
    root_id INTEGER NOT NULL,
    lft INTEGER NOT NULL,
    rgt INTEGER NOT NULL,
    level INTEGER NOT NULL,
    project_id VARCHAR(100) NOT NULL,
    PRIMARY KEY (id)
);

CREATE INDEX idx_node_name ON node (name);
CREATE INDEX idx_node_target ON node (target);
CREATE INDEX idx_node_root ON node (root_id);
CREATE INDEX idx_node_lft ON node (lft);
CREATE INDEX idx_node_rgt ON node (rgt);
CREATE INDEX idx_node_level ON node (level);
CREATE INDEX idx_node_project ON node (project_id);
CREATE INDEX idx_node_item ON node (root_id,lft,rgt);

ALTER TABLE node
    ADD CONSTRAINT con_node_root FOREIGN KEY (root_id) REFERENCES menu (id) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT con_node_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE ON UPDATE CASCADE;

-- ---------------------------------------------------------------------------------------------------------------------
-- URL
-- ---------------------------------------------------------------------------------------------------------------------
CREATE TABLE url (
    id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    target VARCHAR(255) NOT NULL,
    redirect BOOLEAN NOT NULL DEFAULT FALSE,
    system BOOLEAN NOT NULL DEFAULT FALSE,
    project_id VARCHAR(100) NOT NULL,
    PRIMARY KEY (id)
);

CREATE UNIQUE INDEX uni_url_name ON url (project_id, name);
CREATE INDEX idx_url_name ON url (name);
CREATE INDEX idx_url_target ON url (target);
CREATE INDEX idx_url_redirect ON url (redirect);
CREATE INDEX idx_url_system ON url (system);
CREATE INDEX idx_url_project ON url (project_id);

ALTER TABLE url
    ADD CONSTRAINT con_url_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE ON UPDATE CASCADE;

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
