START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Project
-- ---------------------------------------------------------------------------------------------------------------------
CREATE TABLE project (
    id VARCHAR(100) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    host VARCHAR(255) DEFAULT NULL UNIQUE,
    theme VARCHAR(100) DEFAULT NULL,
    active BOOLEAN NOT NULL DEFAULT FALSE,
    system BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE INDEX idx_project_name ON project (name);
CREATE INDEX idx_project_theme ON project (theme);
CREATE INDEX idx_project_active ON project (active);
CREATE INDEX idx_project_system ON project (system);

-- ---------------------------------------------------------------------------------------------------------------------
-- Auth
-- ---------------------------------------------------------------------------------------------------------------------
CREATE TABLE role (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    privilege JSON NOT NULL,
    active BOOLEAN NOT NULL DEFAULT FALSE,
    system BOOLEAN NOT NULL DEFAULT FALSE,
    project_id VARCHAR(100) NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (project_id, name)
);

CREATE INDEX idx_role_name ON role (name);
CREATE INDEX idx_role_active ON role (active);
CREATE INDEX idx_role_system ON role (system);
CREATE INDEX idx_role_project ON role (project_id);

-- -----------------------------------------------------------
CREATE TABLE account (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INTEGER NOT NULL REFERENCES role ON DELETE RESTRICT ON UPDATE CASCADE,
    active BOOLEAN NOT NULL DEFAULT FALSE,
    system BOOLEAN NOT NULL DEFAULT FALSE,
    project_id VARCHAR(100) NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (project_id, name)
);

CREATE INDEX idx_account_name ON account (name);
CREATE INDEX idx_account_role ON account (role_id);
CREATE INDEX idx_account_active ON account (active);
CREATE INDEX idx_account_system ON account (system);
CREATE INDEX idx_account_project ON account (project_id);

-- ---------------------------------------------------------------------------------------------------------------------
-- EAV
-- ---------------------------------------------------------------------------------------------------------------------
CREATE TABLE entity (
    id VARCHAR(100) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    actions JSON NOT NULL,
    system BOOLEAN NOT NULL DEFAULT FALSE,
    project_id VARCHAR(100) NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX idx_entity_name ON entity (name);
CREATE INDEX idx_entity_system ON entity (system);
CREATE INDEX idx_entity_project ON entity (project_id);

-- -----------------------------------------------------------
CREATE TABLE attr (
    id SERIAL PRIMARY KEY,
    entity_id VARCHAR(100) NOT NULL REFERENCES entity ON DELETE CASCADE ON UPDATE CASCADE,
    uid VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    sort INTEGER NOT NULL DEFAULT 0,
    type VARCHAR(100) NOT NULL,
    required BOOLEAN NOT NULL DEFAULT FALSE,
    uniq BOOLEAN NOT NULL DEFAULT FALSE,
    searchable BOOLEAN NOT NULL DEFAULT FALSE,
    opt JSON NOT NULL,
    actions JSON NOT NULL,
    project_id VARCHAR(100) NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (entity_id, uid)
);

CREATE INDEX idx_attr_entity ON attr (entity_id);
CREATE INDEX idx_attr_uid ON attr (uid);
CREATE INDEX idx_attr_name ON attr (name);
CREATE INDEX idx_attr_type ON attr (type);
CREATE INDEX idx_attr_sort ON attr (sort);
CREATE INDEX idx_attr_required ON attr (required);
CREATE INDEX idx_attr_uniq ON attr (uniq);
CREATE INDEX idx_attr_searchable ON attr (searchable);
CREATE INDEX idx_attr_project ON attr (project_id);

-- -----------------------------------------------------------
CREATE TABLE content (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    entity_id VARCHAR(100) NOT NULL REFERENCES entity ON DELETE CASCADE ON UPDATE CASCADE,
    active BOOLEAN NOT NULL DEFAULT FALSE,
    content TEXT NOT NULL,
    search TEXT NOT NULL,
    created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    creator INTEGER DEFAULT NULL REFERENCES account ON DELETE SET NULL ON UPDATE CASCADE,
    modified TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modifier INTEGER DEFAULT NULL REFERENCES account ON DELETE SET NULL ON UPDATE CASCADE,
    project_id VARCHAR(100) NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE
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

-- -----------------------------------------------------------
CREATE TABLE eav (
    content_id INTEGER NOT NULL REFERENCES content ON DELETE CASCADE ON UPDATE CASCADE,
    attr_id INTEGER NOT NULL REFERENCES attr ON DELETE CASCADE ON UPDATE CASCADE,
    value TEXT NOT NULL,
    PRIMARY KEY (content_id, attr_id)
);

CREATE INDEX idx_eav_content ON eav (content_id);
CREATE INDEX idx_eav_attr ON eav (attr_id);

-- ---------------------------------------------------------------------------------------------------------------------
-- Menu
-- ---------------------------------------------------------------------------------------------------------------------
CREATE TABLE menu (
    id SERIAL PRIMARY KEY,
    uid VARCHAR(100),
    name VARCHAR(255) NOT NULL,
    system BOOLEAN NOT NULL DEFAULT FALSE,
    project_id VARCHAR(100) NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (project_id, uid)
);

CREATE INDEX idx_menu_uid ON menu (uid);
CREATE INDEX idx_menu_name ON menu (name);
CREATE INDEX idx_menu_system ON menu (system);
CREATE INDEX idx_menu_project ON menu (project_id);

-- -----------------------------------------------------------
CREATE TABLE node (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    target VARCHAR(255) NOT NULL,
    root_id INTEGER NOT NULL REFERENCES menu ON DELETE CASCADE ON UPDATE CASCADE,
    lft INTEGER NOT NULL,
    rgt INTEGER NOT NULL,
    level INTEGER NOT NULL,
    project_id VARCHAR(100) NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX idx_node_name ON node (name);
CREATE INDEX idx_node_target ON node (target);
CREATE INDEX idx_node_root ON node (root_id);
CREATE INDEX idx_node_lft ON node (lft);
CREATE INDEX idx_node_rgt ON node (rgt);
CREATE INDEX idx_node_level ON node (level);
CREATE INDEX idx_node_project ON node (project_id);
CREATE INDEX idx_node_item ON node (root_id,lft,rgt);

-- ---------------------------------------------------------------------------------------------------------------------
-- URL
-- ---------------------------------------------------------------------------------------------------------------------
CREATE TABLE url (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    target VARCHAR(255) NOT NULL,
    redirect BOOLEAN NOT NULL DEFAULT FALSE,
    system BOOLEAN NOT NULL DEFAULT FALSE,
    project_id VARCHAR(100) NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (project_id, name)
);

CREATE INDEX idx_url_name ON url (name);
CREATE INDEX idx_url_target ON url (target);
CREATE INDEX idx_url_redirect ON url (redirect);
CREATE INDEX idx_url_system ON url (system);
CREATE INDEX idx_url_project ON url (project_id);

-- ---------------------------------------------------------------------------------------------------------------------
-- Data
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO
    project
    (id, name, host, active, system)
VALUES
    ('base', 'BASE', NULL, TRUE, TRUE);

INSERT INTO
    role
    (id, name, privilege, active, system, project_id)
VALUES
    (1, 'admin', '["_all_"]', TRUE, TRUE, 'base');

INSERT INTO
    account
    (name, username, password, role_id, active, system, project_id)
VALUES
    ('Admin', 'admin', '$2y$10$9wnkOfY1qLvz0sRXG5G.d.rf2NhCU8a9m.XrLYIgeQA.SioSWwtsW', 1, TRUE, TRUE, 'base');

INSERT INTO
    entity
    (id, name, actions, system, project_id)
VALUES
    ('page', 'Page', '["admin", "create", "delete", "edit", "index", "view"]', TRUE, 'base');

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
