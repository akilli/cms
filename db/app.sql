START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Table
-- ---------------------------------------------------------------------------------------------------------------------

--
-- Role
--

CREATE TABLE role (
    id serial PRIMARY KEY,
    name varchar(50) NOT NULL UNIQUE,
    privilege text[] NOT NULL DEFAULT '{}'
);

CREATE INDEX ON role USING GIN (privilege);

--
-- Account
--

CREATE TABLE account (
    id serial PRIMARY KEY,
    name varchar(50) NOT NULL UNIQUE,
    role_id int NOT NULL REFERENCES role ON DELETE RESTRICT ON UPDATE CASCADE,
    username varchar(50) NOT NULL UNIQUE,
    password varchar(255) NOT NULL,
    email varchar(50) DEFAULT NULL UNIQUE
);

CREATE INDEX ON account (role_id);

--
-- File
--

CREATE TABLE file (
    id serial PRIMARY KEY,
    name varchar(100) NOT NULL,
    entity_id varchar(50) NOT NULL,
    url varchar(255) NOT NULL UNIQUE,
    mime varchar(255) NOT NULL,
    thumb varchar(255) DEFAULT NULL UNIQUE,
    info text DEFAULT NULL
);

CREATE INDEX ON file (name);
CREATE INDEX ON file (entity_id);
CREATE INDEX ON file (mime);

--
-- Page
--

CREATE TABLE page (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL,
    entity_id varchar(50) NOT NULL,
    title varchar(255) DEFAULT NULL,
    content text DEFAULT NULL,
    aside text DEFAULT NULL,
    meta_title varchar(80) DEFAULT NULL,
    meta_description varchar(300) DEFAULT NULL,
    slug varchar(75) NOT NULL,
    url varchar(400) UNIQUE DEFAULT NULL,
    disabled boolean NOT NULL DEFAULT FALSE,
    menu boolean NOT NULL DEFAULT FALSE,
    parent_id int DEFAULT NULL REFERENCES page ON DELETE CASCADE ON UPDATE CASCADE,
    sort int NOT NULL DEFAULT 0,
    position varchar(255) NOT NULL DEFAULT '',
    level int NOT NULL DEFAULT 0,
    path int[] NOT NULL DEFAULT '{}',
    account_id int DEFAULT NULL REFERENCES account ON DELETE SET NULL ON UPDATE CASCADE,
    timestamp timestamp(0) NOT NULL DEFAULT current_timestamp,
    UNIQUE (parent_id, slug)
);

CREATE INDEX ON page (name);
CREATE INDEX ON page (entity_id);
CREATE INDEX ON page (title);
CREATE INDEX ON page (meta_title);
CREATE INDEX ON page (meta_description);
CREATE INDEX ON page (slug);
CREATE INDEX ON page (url);
CREATE INDEX ON page (disabled);
CREATE INDEX ON page (menu);
CREATE INDEX ON page (parent_id);
CREATE INDEX ON page (sort);
CREATE INDEX ON page (position);
CREATE INDEX ON page (level);
CREATE INDEX ON page USING GIN (path);
CREATE INDEX ON page (account_id);
CREATE INDEX ON page (timestamp);

--
-- Block
--

CREATE TABLE block (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL,
    entity_id varchar(50) NOT NULL,
    title varchar(255) DEFAULT NULL,
    link varchar(255) DEFAULT NULL,
    file_id int DEFAULT NULL REFERENCES file ON DELETE SET NULL ON UPDATE CASCADE,
    content text DEFAULT NULL
);

CREATE INDEX ON block (name);
CREATE INDEX ON block (entity_id);
CREATE INDEX ON block (title);
CREATE INDEX ON block (link);
CREATE INDEX ON block (file_id);

--
-- Layout
--

CREATE TABLE layout (
    id serial PRIMARY KEY,
    name varchar(100) NOT NULL,
    entity_id varchar(50) NOT NULL,
    block_id int NOT NULL REFERENCES block ON DELETE CASCADE ON UPDATE CASCADE,
    page_id int NOT NULL REFERENCES page ON DELETE CASCADE ON UPDATE CASCADE,
    parent_id varchar(100) NOT NULL,
    sort int NOT NULL DEFAULT 0,
    UNIQUE (page_id, parent_id, name)
);

CREATE INDEX ON layout (name);
CREATE INDEX ON layout (entity_id);
CREATE INDEX ON layout (block_id);
CREATE INDEX ON layout (page_id);
CREATE INDEX ON layout (parent_id);
CREATE INDEX ON layout (sort);

-- ---------------------------------------------------------------------------------------------------------------------
-- View
-- ---------------------------------------------------------------------------------------------------------------------

--
-- Audio
--

CREATE VIEW audio AS
SELECT
    *
FROM
    file
WHERE
    entity_id = 'audio'
WITH LOCAL CHECK OPTION;

--
-- Document
--

CREATE VIEW document AS
SELECT
    *
FROM
    file
WHERE
    entity_id = 'document'
WITH LOCAL CHECK OPTION;

--
-- Iframe
--

CREATE VIEW iframe AS
SELECT
    *
FROM
    file
WHERE
    entity_id = 'iframe'
WITH LOCAL CHECK OPTION;

--
-- File Image
--

CREATE VIEW image AS
SELECT
    *
FROM
    file
WHERE
    entity_id = 'image'
WITH LOCAL CHECK OPTION;

--
-- File Video
--

CREATE VIEW video AS
SELECT
    *
FROM
    file
WHERE
    entity_id = 'video'
WITH LOCAL CHECK OPTION;

--
-- Content Page
--

CREATE VIEW contentpage AS
SELECT
    *
FROM
    page
WHERE
    entity_id = 'contentpage'
WITH LOCAL CHECK OPTION;

--
-- Content Block
--

CREATE VIEW contentblock AS
SELECT
    *
FROM
    block
WHERE
    entity_id = 'contentblock'
WITH LOCAL CHECK OPTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Trigger Function
-- ---------------------------------------------------------------------------------------------------------------------

--
-- File
--

CREATE FUNCTION file_save() RETURNS trigger AS $$
    BEGIN
        IF (TG_OP = 'UPDATE' AND NEW.url != OLD.url) THEN
            RAISE EXCEPTION 'URL must not change';
        ELSIF (TG_OP = 'UPDATE' AND NEW.mime != OLD.mime) THEN
            RAISE EXCEPTION 'MIME-Type must not change';
        END IF;

        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

--
-- Page
--

CREATE FUNCTION page_before() RETURNS trigger AS $$
    BEGIN
        IF (NEW.title = NEW.name) THEN
            NEW.title := NULL;
        END IF;

        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION page_menu_before() RETURNS trigger AS $$
    DECLARE
        _cnt int;
    BEGIN
        IF (TG_OP = 'UPDATE' AND NEW.parent_id IS NOT NULL AND (SELECT path @> ARRAY[OLD.id] FROM page WHERE id = NEW.parent_id)) THEN
            RAISE EXCEPTION 'Recursion error';
        END IF;

        SELECT
            count(*) + 1
        FROM
            page
        WHERE
            coalesce(parent_id, 0) = coalesce(NEW.parent_id, 0)
        INTO
            _cnt;

        IF (TG_OP = 'UPDATE' AND coalesce(NEW.parent_id, 0) = coalesce(OLD.parent_id, 0)) THEN
            _cnt := _cnt - 1;
        END IF;

        IF (NEW.sort IS NULL OR NEW.sort <= 0 OR NEW.sort > _cnt) THEN
            NEW.sort := _cnt;
        END IF;

        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION page_menu_after() RETURNS trigger AS $$
    DECLARE
        _ext text := '.html';
        _pad int := 10;
    BEGIN
        -- No relevant changes
        IF (TG_OP = 'UPDATE' AND NEW.slug = OLD.slug AND NEW.menu = OLD.menu AND coalesce(NEW.parent_id, 0) = coalesce(OLD.parent_id, 0) AND NEW.sort = OLD.sort) THEN
            RETURN NULL;
        END IF;

        -- Remove from old parent
        IF (TG_OP = 'UPDATE' OR TG_OP = 'DELETE') THEN
            UPDATE
                page
            SET
                sort = sort - 1
            WHERE
                id != OLD.id
                AND coalesce(parent_id, 0) = coalesce(OLD.parent_id, 0)
                AND sort > OLD.sort;
        END IF;

        -- Add to new parent
        IF (TG_OP = 'INSERT' OR TG_OP = 'UPDATE') THEN
            UPDATE
                page
            SET
                sort = sort + 1
            WHERE
                id != NEW.id
                AND coalesce(parent_id, 0) = coalesce(NEW.parent_id, 0)
                AND sort >= NEW.sort;
        END IF;

        -- Update positions
        WITH RECURSIVE
        s AS (
            SELECT
                p.id,
                (
                    SELECT
                       count(*)
                    FROM
                       page
                    WHERE
                       coalesce(parent_id, 0) = coalesce(p.parent_id, 0)
                       AND (sort < p.sort OR sort = p.sort AND id < p.id)
                ) + 1 AS sort
            FROM
                page p
        ),
        t AS (
            SELECT
                p.id,
                CASE WHEN p.slug = 'index' THEN '' ELSE '/' || p.slug END AS urlkey,
                CASE WHEN p.slug = 'index' THEN '/' ELSE '/' || p.slug || _ext END AS url,
                p.menu,
                s.sort,
                LPAD(cast(s.sort AS text), _pad, '0') AS position,
                0 AS level,
                '{}'::int[] || p.id AS path
            FROM
                page p
            INNER JOIN
                s
                    ON s.id = p.id
            WHERE
                p.parent_id IS NULL
            UNION
            SELECT
                p.id,
                t.urlkey || '/' || p.slug AS urlkey,
                t.urlkey || '/' || p.slug || _ext AS url,
                t.menu AND p.menu AS menu,
                s.sort,
                t.position || '.' || lpad(cast(s.sort AS text), _pad, '0') AS position,
                t.level + 1 AS level,
                t.path || p.id AS path
            FROM
                page p
            INNER JOIN
                t
                    ON t.id = p.parent_id
            INNER JOIN
                s
                    ON s.id = p.id
        )
        UPDATE
            page p
        SET
            url = t.url,
            menu = t.menu,
            sort = t.sort,
            position = t.position,
            level = t.level,
            path = t.path
        FROM
            t
        WHERE
            p.id = t.id
            AND (p.url != t.url OR p.menu != t.menu OR p.sort != t.sort OR p.position != t.position OR p.level != t.level OR p.path != t.path);

        RETURN NULL;
    END;
$$ LANGUAGE plpgsql;

--
-- Layout
--

CREATE FUNCTION layout_save() RETURNS trigger AS $$
    BEGIN
        NEW.entity_id := (SELECT entity_id FROM block WHERE id = NEW.block_id);

        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

-- ---------------------------------------------------------------------------------------------------------------------
-- Trigger
-- ---------------------------------------------------------------------------------------------------------------------

--
-- File
--

CREATE TRIGGER file_save BEFORE INSERT OR UPDATE ON file FOR EACH ROW EXECUTE PROCEDURE file_save();

--
-- Page
--

CREATE TRIGGER page_before BEFORE INSERT OR UPDATE ON page FOR EACH ROW EXECUTE PROCEDURE page_before();
CREATE TRIGGER page_menu_before BEFORE INSERT OR UPDATE ON page FOR EACH ROW WHEN (pg_trigger_depth() < 1) EXECUTE PROCEDURE page_menu_before();
CREATE TRIGGER page_menu_after AFTER INSERT OR UPDATE OR DELETE ON page FOR EACH ROW WHEN (pg_trigger_depth() < 1) EXECUTE PROCEDURE page_menu_after();

--
-- Layout
--

CREATE TRIGGER layout_save BEFORE INSERT OR UPDATE ON layout FOR EACH ROW EXECUTE PROCEDURE layout_save();

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
