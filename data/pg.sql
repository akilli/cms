START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Project
-- ---------------------------------------------------------------------------------------------------------------------

CREATE TABLE project (
    id serial PRIMARY KEY,
    uid varchar(20) NOT NULL UNIQUE,
    name varchar(50) NOT NULL,
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
    name varchar(50) NOT NULL,
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
CREATE INDEX idx_role_project_id ON role (project_id);

-- -----------------------------------------------------------

CREATE TABLE account (
    id serial PRIMARY KEY,
    name varchar(50) NOT NULL,
    password varchar(255) NOT NULL,
    role_id integer NOT NULL REFERENCES role ON DELETE RESTRICT ON UPDATE CASCADE,
    active boolean NOT NULL DEFAULT FALSE,
    system boolean NOT NULL DEFAULT FALSE,
    project_id integer NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (project_id, name)
);

CREATE INDEX idx_account_role_id ON account (role_id);
CREATE INDEX idx_account_active ON account (active);
CREATE INDEX idx_account_system ON account (system);
CREATE INDEX idx_account_project_id ON account (project_id);

-- ---------------------------------------------------------------------------------------------------------------------
-- Page
-- ---------------------------------------------------------------------------------------------------------------------

CREATE TABLE page (
    id serial PRIMARY KEY,
    name varchar(100) NOT NULL,
    url varchar(255) NOT NULL,
    active boolean NOT NULL DEFAULT FALSE,
    parent_id integer DEFAULT NULL REFERENCES page ON DELETE CASCADE ON UPDATE CASCADE,
    sort integer NOT NULL DEFAULT 0,
    content text NOT NULL,
    search tsvector NOT NULL,
    project_id integer NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (project_id, url)
);

CREATE INDEX idx_page_name ON page (name);
CREATE INDEX idx_page_url ON page (url);
CREATE INDEX idx_page_active ON page (active);
CREATE INDEX idx_page_parent_id ON page (parent_id);
CREATE INDEX idx_page_sort ON page (sort);
CREATE INDEX idx_page_search ON page USING GIN (search);
CREATE INDEX idx_page_project_id ON page (project_id);

CREATE FUNCTION page_save_before() RETURNS trigger AS
$$
    DECLARE
        _max integer;
    BEGIN
        SELECT COUNT(id) + 1 FROM page INTO _max WHERE project_id = NEW.project_id AND COALESCE(parent_id, 0) = COALESCE(NEW.parent_id, 0);

        IF (TG_OP = 'UPDATE' AND COALESCE(NEW.parent_id, 0) = COALESCE(OLD.parent_id, 0)) THEN
            _max := _max - 1;
        END IF;

        IF (NEW.sort IS NULL OR NEW.sort <= 0 OR NEW.sort > _max) THEN
            NEW.sort = _max;
        END IF;

        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION page_save_after() RETURNS trigger AS
$$
    BEGIN
        IF (TG_OP = 'INSERT' OR COALESCE(NEW.parent_id, 0) != COALESCE(OLD.parent_id, 0) OR NEW.sort != OLD.sort) THEN
            IF (TG_OP = 'UPDATE') THEN
                UPDATE page SET sort = sort - 1 WHERE project_id = OLD.project_id AND id != OLD.id AND COALESCE(parent_id, 0) = COALESCE(OLD.parent_id, 0) AND sort > OLD.sort;
            END IF;

            UPDATE page SET sort = sort + 1 WHERE project_id = NEW.project_id AND id != NEW.id AND COALESCE(parent_id, 0) = COALESCE(NEW.parent_id, 0) AND sort >= NEW.sort;
        END IF;

        REFRESH MATERIALIZED VIEW tree;

        RETURN NULL;
    END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION page_delete() RETURNS trigger AS
$$
    BEGIN
        UPDATE page SET sort = sort - 1 WHERE project_id = OLD.project_id AND COALESCE(parent_id, 0) = COALESCE(OLD.parent_id, 0) AND sort > OLD.sort;
        RETURN NULL;
    END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER page_save_before BEFORE INSERT OR UPDATE ON page FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE page_save_before();
CREATE TRIGGER page_save_after AFTER INSERT OR UPDATE ON page FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE page_save_after();
CREATE TRIGGER page_delete AFTER DELETE ON page FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE page_delete();

-- -----------------------------------------------------------

CREATE MATERIALIZED VIEW tree AS
WITH RECURSIVE t AS (
        SELECT
            id,
            name,
            url,
            parent_id,
            sort,
            project_id,
            TO_JSONB(id) AS path,
            1 AS depth,
            CAST(sort AS text) AS pos,
            LPAD(CAST(sort AS text), 3, '0') AS sortpos
        FROM
            page
        WHERE
            active = TRUE
            AND parent_id IS NULL
    UNION
        SELECT
            p.id,
            p.name,
            p.url,
            p.parent_id,
            p.sort,
            p.project_id,
            t.path || TO_JSONB(p.id) AS path,
            t.depth + 1 AS depth,
            t.pos || '.' || CAST(p.sort AS text) AS pos,
            t.sortpos || '.' || LPAD(CAST(p.sort AS text), 3, '0') AS sortpos
        FROM
            page p
        INNER JOIN
            t
                ON t.id = p.parent_id
        WHERE
            p.project_id = t.project_id
            AND p.active = TRUE
)
SELECT
    id,
    name,
    url,
    parent_id,
    sort,
    project_id,
    path,
    depth,
    pos,
    sortpos
FROM
    t
ORDER BY
    project_id ASC,
    sortpos ASC;

CREATE UNIQUE INDEX uni_tree_id ON tree (id);
CREATE INDEX idx_tree_name ON tree (name);
CREATE INDEX idx_tree_url ON tree (url);
CREATE INDEX idx_tree_parent_id ON tree (parent_id);
CREATE INDEX idx_tree_sort ON tree (sort);
CREATE INDEX idx_tree_project_id ON tree (project_id);
CREATE INDEX idx_tree_path ON tree USING GIN (path);
CREATE INDEX idx_tree_depth ON tree (depth);
CREATE INDEX idx_tree_pos ON tree (pos);
CREATE INDEX idx_tree_sortpos ON tree (sortpos);

-- -----------------------------------------------------------

CREATE TABLE template (
    id serial PRIMARY KEY,
    name varchar(100) NOT NULL,
    image varchar(255) NOT NULL,
    info text NOT NULL,
    content text NOT NULL,
    project_id integer NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX idx_template_name ON template (name);
CREATE INDEX idx_template_project_id ON template (project_id);

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
