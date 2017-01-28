START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Project
-- ---------------------------------------------------------------------------------------------------------------------

CREATE TABLE project (
    id serial PRIMARY KEY,
    uid varchar(100) UNIQUE,
    name varchar(255) NOT NULL,
    host varchar(255) NOT NULL UNIQUE,
    theme varchar(100) NOT NULL,
    active boolean NOT NULL DEFAULT FALSE,
    system boolean NOT NULL DEFAULT FALSE
);

CREATE INDEX idx_project_name ON project (name);
CREATE INDEX idx_project_theme ON project (theme);
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
    name varchar(255) NOT NULL UNIQUE,
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
-- EAV
-- ---------------------------------------------------------------------------------------------------------------------

CREATE TABLE entity (
    id serial PRIMARY KEY,
    uid varchar(100) UNIQUE,
    name varchar(255) NOT NULL,
    actions jsonb NOT NULL,
    system boolean NOT NULL DEFAULT FALSE,
    project_id integer NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX idx_entity_name ON entity (name);
CREATE INDEX idx_entity_actions ON entity USING GIN (actions);
CREATE INDEX idx_entity_system ON entity (system);
CREATE INDEX idx_entity_project ON entity (project_id);

CREATE FUNCTION entity_update_after() RETURNS trigger AS
$$
    DECLARE
        _old varchar(255);
        _new varchar(255);
    BEGIN
        _old := '^/' || OLD.uid || '/';
        _new := '/' || NEW.uid || '/';

        DELETE FROM url WHERE target ~ _old AND id NOT IN (SELECT DISTINCT id FROM url u INNER JOIN jsonb_array_elements_text(NEW.actions) a ON u.target ~ (_old || a::varchar));

        IF (OLD.uid != NEW.uid) THEN
            UPDATE url SET target = regexp_replace(target, _old, _new) WHERE target ~ _old;
        END IF;

        RETURN NULL;
    END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION entity_delete_after() RETURNS trigger AS
$$
    DECLARE
        _old varchar(255);
    BEGIN
        _old := '^/' || OLD.uid || '/';

        DELETE FROM url WHERE target ~ _old;

        RETURN NULL;
    END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER entity_update_after AFTER UPDATE ON entity FOR EACH ROW WHEN (OLD.actions != NEW.actions OR OLD.uid != NEW.uid) EXECUTE PROCEDURE entity_update_after();
CREATE TRIGGER entity_delete_after AFTER DELETE ON entity FOR EACH ROW EXECUTE PROCEDURE entity_delete_after();

-- -----------------------------------------------------------

CREATE TABLE attr (
    id serial PRIMARY KEY,
    entity_id integer NOT NULL REFERENCES entity ON DELETE CASCADE ON UPDATE CASCADE,
    uid varchar(100) NOT NULL,
    name varchar(255) NOT NULL,
    sort integer NOT NULL DEFAULT 0,
    type varchar(100) NOT NULL,
    required boolean NOT NULL DEFAULT FALSE,
    uniq boolean NOT NULL DEFAULT FALSE,
    searchable boolean NOT NULL DEFAULT FALSE,
    opt jsonb NOT NULL,
    actions jsonb NOT NULL,
    project_id integer NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (entity_id, uid)
);

CREATE INDEX idx_attr_entity ON attr (entity_id);
CREATE INDEX idx_attr_uid ON attr (uid);
CREATE INDEX idx_attr_name ON attr (name);
CREATE INDEX idx_attr_sort ON attr (sort);
CREATE INDEX idx_attr_type ON attr (type);
CREATE INDEX idx_attr_required ON attr (required);
CREATE INDEX idx_attr_uniq ON attr (uniq);
CREATE INDEX idx_attr_searchable ON attr (searchable);
CREATE INDEX idx_attr_opt ON attr USING GIN (opt);
CREATE INDEX idx_attr_actions ON attr USING GIN (actions);
CREATE INDEX idx_attr_project ON attr (project_id);

-- -----------------------------------------------------------

CREATE TABLE content (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL,
    entity_id integer NOT NULL REFERENCES entity ON DELETE CASCADE ON UPDATE CASCADE,
    active boolean NOT NULL DEFAULT FALSE,
    content text NOT NULL,
    search tsvector NOT NULL,
    created timestamp NOT NULL DEFAULT current_timestamp,
    creator integer DEFAULT NULL REFERENCES account ON DELETE SET NULL ON UPDATE CASCADE,
    modified timestamp NOT NULL DEFAULT current_timestamp,
    modifier integer DEFAULT NULL REFERENCES account ON DELETE SET NULL ON UPDATE CASCADE,
    project_id integer NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX idx_content_name ON content (name);
CREATE INDEX idx_content_entity ON content (entity_id);
CREATE INDEX idx_content_active ON content (active);
CREATE INDEX idx_content_search ON content USING GIN (search);
CREATE INDEX idx_content_created ON content (created);
CREATE INDEX idx_content_creator ON content (creator);
CREATE INDEX idx_content_modified ON content (modified);
CREATE INDEX idx_content_modifier ON content (modifier);
CREATE INDEX idx_content_project ON content (project_id);

CREATE FUNCTION content_update_before() RETURNS trigger AS
$$
    BEGIN
        NEW.modified := current_timestamp;
        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER content_update_before BEFORE UPDATE ON content FOR EACH ROW EXECUTE PROCEDURE content_update_before();

-- -----------------------------------------------------------

CREATE TABLE eav (
    content_id integer NOT NULL REFERENCES content ON DELETE CASCADE ON UPDATE CASCADE,
    attr_id integer NOT NULL REFERENCES attr ON DELETE CASCADE ON UPDATE CASCADE,
    value text NOT NULL,
    PRIMARY KEY (content_id, attr_id)
);

CREATE INDEX idx_eav_content ON eav (content_id);
CREATE INDEX idx_eav_attr ON eav (attr_id);

-- ---------------------------------------------------------------------------------------------------------------------
-- Menu
-- ---------------------------------------------------------------------------------------------------------------------

CREATE TABLE menu (
    id serial PRIMARY KEY,
    uid varchar(100),
    name varchar(255) NOT NULL,
    project_id integer NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (project_id, uid)
);

CREATE INDEX idx_menu_uid ON menu (uid);
CREATE INDEX idx_menu_name ON menu (name);
CREATE INDEX idx_menu_project ON menu (project_id);

-- -----------------------------------------------------------

CREATE TABLE node (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL,
    target varchar(255) NOT NULL,
    root_id integer NOT NULL REFERENCES menu ON DELETE CASCADE ON UPDATE CASCADE,
    lft integer NOT NULL,
    rgt integer NOT NULL,
    parent_id integer DEFAULT NULL REFERENCES node ON DELETE SET NULL ON UPDATE CASCADE,
    level integer NOT NULL,
    project_id integer NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX idx_node_name ON node (name);
CREATE INDEX idx_node_target ON node (target);
CREATE INDEX idx_node_root ON node (root_id);
CREATE INDEX idx_node_lft ON node (lft);
CREATE INDEX idx_node_rgt ON node (rgt);
CREATE INDEX idx_node_parent ON node (parent_id);
CREATE INDEX idx_node_level ON node (level);
CREATE INDEX idx_node_project ON node (project_id);

CREATE FUNCTION node_save_before() RETURNS trigger AS
$$
    DECLARE
        _baseId integer;
        _baseLevel integer;
        _baseLft integer;
        _baseParentId integer;
        _baseRgt integer;
        _lft integer;
        _range integer;
    BEGIN
        -- Validate
        IF (NEW.root_id IS NULL) THEN
            RAISE EXCEPTION 'Missing root';
        END IF;

        -- Set tree attributes
        IF (NEW.lft = 0 OR NEW.lft IS NULL) THEN
            SELECT COALESCE(MAX(rgt), 0) + 1 INTO _lft FROM node WHERE root_id = NEW.root_id;
            NEW.parent_id := NULL;
            NEW.level := 1;
        ELSE
            _baseLft := ABS(NEW.lft);
            SELECT id, rgt, parent_id, level INTO _baseId, _baseRgt, _baseParentId, _baseLevel FROM node WHERE root_id = NEW.root_id AND lft = _baseLft;

            IF (TG_OP = 'UPDATE' AND OLD.root_id = NEW.root_id AND OLD.lft = _baseLft) THEN
                NEW.lft := OLD.lft;
                NEW.rgt := OLD.rgt;
                NEW.parent_id := OLD.parent_id;
                NEW.level := OLD.level;

                RETURN NEW;
            END IF;

            IF (TG_OP = 'UPDATE' AND OLD.root_id = NEW.root_id AND OLD.lft < _baseLft AND OLD.rgt > _baseRgt) THEN
                RAISE EXCEPTION 'Node can not be child of itself';
            END IF;

            IF (NEW.lft > 0) THEN
                _lft := _baseRgt;
                NEW.parent_id := _baseId;
                NEW.level := _baseLevel + 1;
            ELSE
                _lft := _baseLft;
                NEW.parent_id := _baseParentId;
                NEW.level := _baseLevel;
            END IF;
        END IF;

        IF (TG_OP = 'UPDATE') THEN
            _range := OLD.rgt - OLD.lft + 1;

            IF (NEW.root_id = OLD.root_id AND _lft > OLD.lft) THEN
                _lft := _lft - _range;
            END IF;
        ELSE
            _range := 2;
        END IF;

        NEW.lft = -1 * _lft;
        NEW.rgt := -1 * (_lft + _range - 1);

        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION node_save_after() RETURNS trigger AS
$$
    DECLARE
        _diff integer;
        _lft integer;
        _rgt integer;
        _range integer;
    BEGIN
        -- No change in postion
        IF (TG_OP = 'UPDATE' AND NEW.root_id = OLD.root_id AND NEW.lft = OLD.lft) THEN
            RETURN NULL;
        END IF;

        -- Vars
        _lft := -1 * NEW.lft;
        _rgt := -1 * NEW.rgt;
        _range := _rgt - _lft + 1;

        -- Move from old tree
        IF (TG_OP = 'UPDATE') THEN
            _diff := _lft - OLD.lft;
            UPDATE node SET root_id = NEW.root_id, lft = -1 * (lft + _diff), rgt = -1 * (rgt + _diff), level = level + NEW.level - OLD.level WHERE root_id = OLD.root_id AND lft BETWEEN OLD.lft AND OLD.rgt;
            UPDATE node SET lft = lft - _range WHERE root_id = OLD.root_id AND lft > OLD.rgt;
            UPDATE node SET rgt = rgt - _range WHERE root_id = OLD.root_id AND rgt > OLD.rgt;
        END IF;

        -- Add to new tree
        UPDATE node SET lft = lft + _range WHERE root_id = NEW.root_id AND lft >= _lft;
        UPDATE node SET rgt = rgt + _range WHERE root_id = NEW.root_id AND rgt >= _lft;
        UPDATE node SET lft = -1 * lft, rgt = -1 * rgt WHERE root_id = NEW.root_id AND lft < 0;

        RETURN NULL;
    END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION node_delete_after() RETURNS trigger AS
$$
    DECLARE
        _range integer;
    BEGIN
        -- Delete affected nodes
        DELETE FROM node WHERE root_id = OLD.root_id AND lft BETWEEN OLD.lft AND OLD.rgt;
        -- Close gap in old tree
        _range := OLD.rgt - OLD.lft + 1;
        UPDATE node SET lft = lft - _range WHERE root_id = OLD.root_id AND lft > OLD.rgt;
        UPDATE node SET rgt = rgt - _range WHERE root_id = OLD.root_id AND rgt > OLD.rgt;

        RETURN NULL;
    END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER node_save_before BEFORE INSERT OR UPDATE ON node FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE node_save_before();
CREATE TRIGGER node_save_after AFTER INSERT OR UPDATE ON node FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE node_save_after();
CREATE TRIGGER node_delete_after AFTER DELETE ON node FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE node_delete_after();

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
    (uid, name, host, theme, active, system)
VALUES
    ('base', 'BASE', '', 'base', TRUE, TRUE);

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

INSERT INTO
    entity
    (uid, name, actions, system, project_id)
VALUES
    ('page', 'Page', '["admin", "delete", "edit", "index", "view"]', TRUE, CURRVAL('project_id_seq'));

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
