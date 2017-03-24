START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Project
-- ---------------------------------------------------------------------------------------------------------------------

CREATE TABLE project (
    id serial PRIMARY KEY,
    uid varchar(100) NOT NULL UNIQUE,
    name varchar(255) NOT NULL,
    active boolean NOT NULL DEFAULT FALSE,
    system boolean NOT NULL DEFAULT FALSE
);

CREATE INDEX idx_project_name ON project (name);
CREATE INDEX idx_project_active ON project (active);
CREATE INDEX idx_project_system ON project (system);

-- ---------------------------------------------------------------------------------------------------------------------
-- Auth
-- ---------------------------------------------------------------------------------------------------------------------

CREATE TABLE role (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL,
    privilege jsonb NOT NULL,
    active boolean NOT NULL DEFAULT FALSE,
    system boolean NOT NULL DEFAULT FALSE,
    project_id integer NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (project_id, name)
);

CREATE INDEX idx_role_name ON role (name);
CREATE INDEX idx_role_privilege ON role USING GIN (privilege);
CREATE INDEX idx_role_active ON role (active);
CREATE INDEX idx_role_system ON role (system);
CREATE INDEX idx_role_project ON role (project_id);

-- -----------------------------------------------------------

CREATE TABLE account (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL,
    password varchar(255) NOT NULL,
    role_id integer NOT NULL REFERENCES role ON DELETE RESTRICT ON UPDATE CASCADE,
    active boolean NOT NULL DEFAULT FALSE,
    system boolean NOT NULL DEFAULT FALSE,
    project_id integer NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (project_id, name)
);

CREATE INDEX idx_account_role ON account (role_id);
CREATE INDEX idx_account_active ON account (active);
CREATE INDEX idx_account_system ON account (system);
CREATE INDEX idx_account_project ON account (project_id);

-- ---------------------------------------------------------------------------------------------------------------------
-- Page
-- ---------------------------------------------------------------------------------------------------------------------

CREATE TABLE page (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL,
    active boolean NOT NULL DEFAULT FALSE,
    content text NOT NULL,
    search tsvector NOT NULL,
    created timestamp NOT NULL DEFAULT current_timestamp,
    creator integer DEFAULT NULL REFERENCES account ON DELETE SET NULL ON UPDATE CASCADE,
    modified timestamp NOT NULL DEFAULT current_timestamp,
    modifier integer DEFAULT NULL REFERENCES account ON DELETE SET NULL ON UPDATE CASCADE,
    project_id integer NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX idx_page_name ON page (name);
CREATE INDEX idx_page_active ON page (active);
CREATE INDEX idx_page_search ON page USING GIN (search);
CREATE INDEX idx_page_created ON page (created);
CREATE INDEX idx_page_creator ON page (creator);
CREATE INDEX idx_page_modified ON page (modified);
CREATE INDEX idx_page_modifier ON page (modifier);
CREATE INDEX idx_page_project ON page (project_id);

-- ---------------------------------------------------------------------------------------------------------------------
-- URL
-- ---------------------------------------------------------------------------------------------------------------------

CREATE TABLE url (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL,
    target varchar(255) NOT NULL,
    system boolean NOT NULL DEFAULT FALSE,
    project_id integer NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (project_id, name)
);

CREATE INDEX idx_url_name ON url (name);
CREATE INDEX idx_url_target ON url (target);
CREATE INDEX idx_url_system ON url (system);
CREATE INDEX idx_url_project ON url (project_id);

-- ---------------------------------------------------------------------------------------------------------------------
-- Data
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO
    project
    (uid, name, active, system)
VALUES
    ('www', 'WWW', TRUE, TRUE);

INSERT INTO
    role
    (name, privilege, active, system, project_id)
VALUES
    ('admin', '["_all_"]', TRUE, TRUE, CURRVAL('project_id_seq'));

INSERT INTO
    account
    (name, password, role_id, active, system, project_id)
VALUES
    ('admin', '$2y$10$FZSRqIGNKq64P3Rz27jlzuKuSZ9Rik9qHnqk5zH2Z7d67.erqaNhy', CURRVAL('role_id_seq'), TRUE, TRUE, CURRVAL('project_id_seq'));

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
