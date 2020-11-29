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
    priv text[] NOT NULL DEFAULT '{}'
);

CREATE INDEX ON role USING GIN (priv);

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
    pos varchar(255) NOT NULL DEFAULT '',
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
CREATE INDEX ON page (pos);
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
-- File Audio
--

CREATE VIEW file_audio AS
SELECT
    *
FROM
    file
WHERE
    entity_id = 'file_audio'
WITH LOCAL CHECK OPTION;

--
-- File Doc
--

CREATE VIEW file_doc AS
SELECT
    *
FROM
    file
WHERE
    entity_id = 'file_doc'
WITH LOCAL CHECK OPTION;

--
-- File Iframe
--

CREATE VIEW file_iframe AS
SELECT
    *
FROM
    file
WHERE
    entity_id = 'file_iframe'
WITH LOCAL CHECK OPTION;

--
-- File Image
--

CREATE VIEW file_image AS
SELECT
    *
FROM
    file
WHERE
    entity_id = 'file_image'
WITH LOCAL CHECK OPTION;

--
-- File Video
--

CREATE VIEW file_video AS
SELECT
    *
FROM
    file
WHERE
    entity_id = 'file_video'
WITH LOCAL CHECK OPTION;

--
-- Page Content
--

CREATE VIEW page_content AS
SELECT
    *
FROM
    page
WHERE
    entity_id = 'page_content'
WITH LOCAL CHECK OPTION;

--
-- Block Content
--

CREATE VIEW block_content AS
SELECT
    *
FROM
    block
WHERE
    entity_id = 'block_content'
WITH LOCAL CHECK OPTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Trigger Function
-- ---------------------------------------------------------------------------------------------------------------------

--
-- Generic trigger function for not auto-updatable views
--
-- You could use this generic trigger function for entities that extend a base entity and define additional columns in
-- an extension table. The resulting views are usually not auto-updatable. Anyhow you should prefer dedicated trigger
-- functions over this generic one. It's just too much voodoo going on here.
--
-- Naming conventions
-- -------------------------------------------------------------------
-- base table/entity name | {base}            | p.e. block
-- view/entity name       | {base}_{name}     | p.e. block_example
-- extension table name   | {base}_{name}_ext | p.e. block_example_ext
--
-- Usage
-- -----
-- CREATE VIEW block_example AS
-- SELECT
--     *
-- FROM
--     block
-- LEFT JOIN
--     block_example_ext
--         USING (id)
-- WHERE
--     entity_id = 'block_example';
--
-- CREATE TRIGGER entity_save INSTEAD OF INSERT OR UPDATE ON block_example FOR EACH ROW EXECUTE PROCEDURE entity_save();
-- CREATE TRIGGER entity_delete INSTEAD OF DELETE ON block_example FOR EACH ROW EXECUTE PROCEDURE entity_delete();
--

CREATE FUNCTION entity_save() RETURNS trigger AS $$
    DECLARE
        _attr RECORD;
        _base text;
        _cnt int;
        _col text := '';
        _ext text;
        _new jsonb;
        _newVal text;
        _old jsonb;
        _oldVal text;
        _set text := '';
        _sql text := '';
        _val text := '';
    BEGIN
        _base := substring(TG_TABLE_NAME FROM '^([^_]+)_');
        _ext := TG_TABLE_NAME || '_ext';
        NEW.entity_id := TG_TABLE_NAME;
        _new := to_jsonb(NEW);

        IF (TG_OP = 'UPDATE') THEN
            _old := to_jsonb(OLD);
        END IF;

        -- Base table
        FOR _attr IN
            SELECT
                column_name AS name,
                lower(data_type) AS type
            FROM
                information_schema.columns
            WHERE
                table_schema = TG_TABLE_SCHEMA
                AND table_name = _base
                AND column_name != 'id'
            ORDER BY
                ordinal_position ASC
        LOOP
            _newVal := jsonb_extract_path_text(_new, _attr.name);

            IF (TG_OP = 'UPDATE') THEN
                _oldVal := jsonb_extract_path_text(_old, _attr.name);
            ELSE
                _oldVal := NULL;
            END IF;

            IF (_newVal IS NULL AND _oldVal IS NULL) THEN
                CONTINUE;
            ELSIF (_col != '') THEN
                _col := _col || ', ';
                _val := _val || ', ';
                _set := _set || ', ';
            END IF;

            IF (_attr.type = 'array' AND _newVal IS NOT NULL) THEN
                _newVal := regexp_replace(regexp_replace(_newVal, '^\[', '{'), '\]$', '}');
            END IF;

            _col := _col || format('%I', _attr.name);
            _val := _val || format('%L', _newVal);
            _set := _set || format('%I = %L', _attr.name, _newVal);
        END LOOP;

        IF (TG_OP = 'UPDATE') THEN
            _sql := format('UPDATE %I SET id = %L, %s WHERE id = %L', _base, NEW.id, _set, OLD.id);
        ELSE
            _sql := format('INSERT INTO %I (%s) VALUES (%s)', _base, _col, _val);
        END IF;

        -- Extension table
        _col := '';
        _val := '';
        _set := '';

        FOR _attr IN
            SELECT
                column_name AS name,
                lower(data_type) AS type
            FROM
                information_schema.columns
            WHERE
                table_schema = TG_TABLE_SCHEMA
                AND table_name = _ext
                AND column_name != 'id'
            ORDER BY
                ordinal_position ASC
        LOOP
            _newVal := jsonb_extract_path_text(_new, _attr.name);

            IF (TG_OP = 'UPDATE') THEN
                _oldVal := jsonb_extract_path_text(_old, _attr.name);
            ELSE
                _oldVal := NULL;
            END IF;

            IF (_newVal IS NULL AND _oldVal IS NULL) THEN
                CONTINUE;
            ELSIF (_col != '') THEN
                _col := _col || ', ';
                _val := _val || ', ';
                _set := _set || ', ';
            END IF;

            IF (_attr.type = 'array' AND _newVal IS NOT NULL) THEN
                _newVal := regexp_replace(regexp_replace(_newVal, '^\[', '{'), '\]$', '}');
            END IF;

            _col := _col || format('%I', _attr.name);
            _val := _val || format('%L', _newVal);
            _set := _set || format('%I = %L', _attr.name, _newVal);
        END LOOP;

        IF (_col != '' AND _val != '' AND _set != '') THEN
            _sql := 'WITH t AS (' || _sql || ' RETURNING id)';

            IF (TG_OP = 'UPDATE') THEN
                EXECUTE format('SELECT count(*) FROM %I WHERE id = %L', _ext, OLD.id) INTO _cnt;
            ELSE
                _cnt := 0;
            END IF;

            IF (TG_OP = 'UPDATE' AND _cnt > 0) THEN
                _sql := _sql || format(' UPDATE %I e SET %s FROM t WHERE e.id = t.id', _ext, _set);
            ELSE
                _sql := _sql || format(' INSERT INTO %s (id, %s) SELECT id, %s FROM t', _ext, _col, _val);
            END IF;
        ELSIF (_col != '' OR _val != '' OR _set != '') THEN
            RAISE EXCEPTION 'An error occurred with values _col(%), _val(%) and _set(%)', _col, _val, _set;
        END IF;

        EXECUTE _sql;

        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION entity_delete() RETURNS trigger AS $$
    BEGIN
        EXECUTE format('DELETE FROM %I WHERE id = %s', substring(TG_TABLE_NAME FROM '^([^_]+)_'), OLD.id);
        RETURN OLD;
    END;
$$ LANGUAGE plpgsql;

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
        _pad int := 5;
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
                (SELECT count(*) FROM page WHERE coalesce(parent_id, 0) = coalesce(p.parent_id, 0) AND (sort < p.sort OR sort = p.sort AND id < p.id)) + 1 AS sort
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
                LPAD(cast(s.sort AS text), _pad, '0') AS pos,
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
                t.pos || '.' || lpad(cast(s.sort AS text), _pad, '0') AS pos,
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
            pos = t.pos,
            level = t.level,
            path = t.path
        FROM
            t
        WHERE
            p.id = t.id
            AND (p.url != t.url OR p.menu != t.menu OR p.sort != t.sort OR p.pos != t.pos OR p.level != t.level OR p.path != t.path);

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
