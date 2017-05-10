START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Project
-- ---------------------------------------------------------------------------------------------------------------------

CREATE TABLE project (
    id serial PRIMARY KEY,
    uid varchar(20) NOT NULL UNIQUE,
    name varchar(50) NOT NULL,
    exported date DEFAULT NULL,
    active boolean NOT NULL DEFAULT FALSE,
    system boolean NOT NULL DEFAULT FALSE
);

CREATE INDEX ON project (name);
CREATE INDEX ON project (exported);
CREATE INDEX ON project (active);
CREATE INDEX ON project (system);

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

CREATE INDEX ON role (name);
CREATE INDEX ON role USING GIN (privilege);
CREATE INDEX ON role (active);
CREATE INDEX ON role (system);
CREATE INDEX ON role (project_id);

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

CREATE INDEX ON account (role_id);
CREATE INDEX ON account (active);
CREATE INDEX ON account (system);
CREATE INDEX ON account (project_id);

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
    path jsonb NOT NULL DEFAULT '[]',
    depth integer NOT NULL DEFAULT 0,
    pos varchar(255) NOT NULL DEFAULT '',
    project_id integer NOT NULL REFERENCES project ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (project_id, url)
);

CREATE INDEX ON page (name);
CREATE INDEX ON page (url);
CREATE INDEX ON page (active);
CREATE INDEX ON page (parent_id);
CREATE INDEX ON page (sort);
CREATE INDEX ON page USING GIN (search);
CREATE INDEX ON page USING GIN (path);
CREATE INDEX ON page (depth);
CREATE INDEX ON page (pos);
CREATE INDEX ON page (project_id);

CREATE FUNCTION page_before() RETURNS trigger AS
$$
    DECLARE
        _max integer;
    BEGIN
        SELECT
            COUNT(id) + 1
        FROM
            page
        INTO
            _max
        WHERE
            project_id = NEW.project_id
            AND COALESCE(parent_id, 0) = COALESCE(NEW.parent_id, 0);

        IF (TG_OP = 'UPDATE' AND COALESCE(NEW.parent_id, 0) = COALESCE(OLD.parent_id, 0)) THEN
            _max := _max - 1;
        END IF;

        IF (NEW.sort IS NULL OR NEW.sort <= 0 OR NEW.sort > _max) THEN
            NEW.sort = _max;
        END IF;

        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION page_after() RETURNS trigger AS
$$
    DECLARE
        _pId integer;
    BEGIN
        -- Position did not change on update
        IF (TG_OP = 'UPDATE' AND COALESCE(NEW.parent_id, 0) = COALESCE(OLD.parent_id, 0) AND NEW.sort = OLD.sort) THEN
            RETURN NULL;
        END IF;

        -- Remove from old parent
        IF (TG_OP = 'UPDATE' OR TG_OP = 'DELETE') THEN
            UPDATE
                page
            SET
                sort = sort - 1
            WHERE
                project_id = OLD.project_id
                AND id != OLD.id
                AND COALESCE(parent_id, 0) = COALESCE(OLD.parent_id, 0)
                AND sort > OLD.sort;
        END IF;

        -- Add to new parent
        IF (TG_OP = 'INSERT' OR TG_OP = 'UPDATE') THEN
            UPDATE
                page
            SET
                sort = sort + 1
            WHERE
                project_id = NEW.project_id
                AND id != NEW.id
                AND COALESCE(parent_id, 0) = COALESCE(NEW.parent_id, 0)
                AND sort >= NEW.sort;
        END IF;

        -- Update positions in project
        IF (TG_OP = 'UPDATE' OR TG_OP = 'DELETE') THEN
            _pId := OLD.project_id;
        ELSE
            _pId := NEW.project_id;
        END IF;

        WITH RECURSIVE t AS (
                SELECT
                    id,
                    '[]'::jsonb || TO_JSONB(id) AS path,
                    1 AS depth,
                    LPAD(CAST(sort AS text), 3, '0') AS pos
                FROM
                    page
                WHERE
                    project_id = _pId
                    AND parent_id IS NULL
            UNION
                SELECT
                    p.id,
                    t.path || TO_JSONB(p.id) AS path,
                    t.depth + 1 AS depth,
                    t.pos || '.' || LPAD(CAST(p.sort AS text), 3, '0') AS pos
                FROM
                    page p
                INNER JOIN
                    t
                        ON t.id = p.parent_id
                WHERE
                    p.project_id = _pId
        )
        UPDATE
            page p
        SET
            path = t.path,
            depth = t.depth,
            pos = t.pos
        FROM
            t
        WHERE
            p.id = t.id
            AND (p.path != t.path OR p.depth != t.depth OR p.pos != t.pos);

        RETURN NULL;
    END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER
    page_before
BEFORE INSERT OR UPDATE ON
    page
FOR EACH ROW WHEN
    (pg_trigger_depth() = 0)
EXECUTE PROCEDURE
    page_before();

CREATE TRIGGER
    page_after
AFTER INSERT OR UPDATE OR DELETE ON
    page
FOR EACH ROW WHEN
    (pg_trigger_depth() = 0)
EXECUTE PROCEDURE
    page_after();

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
