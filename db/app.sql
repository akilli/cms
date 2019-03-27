START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Type
-- ---------------------------------------------------------------------------------------------------------------------

CREATE TYPE status AS ENUM ('draft', 'pending', 'published', 'archived');

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
    ext varchar(10) DEFAULT NULL,
    thumb_url varchar(255) DEFAULT NULL UNIQUE,
    thumb_mime varchar(255) DEFAULT NULL,
    thumb_ext varchar(10) DEFAULT NULL,
    info text NOT NULL
);

CREATE INDEX ON file (name);
CREATE INDEX ON file (entity_id);
CREATE INDEX ON file (mime);
CREATE INDEX ON file (ext);
CREATE INDEX ON file (thumb_mime);
CREATE INDEX ON file (thumb_ext);

--
-- Page
--

CREATE TABLE page (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL,
    entity_id varchar(50) NOT NULL,
    title varchar(255) DEFAULT NULL,
    image int DEFAULT NULL REFERENCES file ON DELETE SET NULL ON UPDATE CASCADE,
    content text NOT NULL DEFAULT '',
    aside text NOT NULL DEFAULT '',
    meta_title varchar(80) NOT NULL DEFAULT '',
    meta_description varchar(300) NOT NULL DEFAULT '',
    date timestamp(0) NOT NULL DEFAULT current_timestamp,
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
    status status NOT NULL,
    timestamp timestamp(0) NOT NULL DEFAULT current_timestamp,
    UNIQUE (parent_id, slug)
);

CREATE INDEX ON page (name);
CREATE INDEX ON page (entity_id);
CREATE INDEX ON page (title);
CREATE INDEX ON page (image);
CREATE INDEX ON page (meta_title);
CREATE INDEX ON page (meta_description);
CREATE INDEX ON page (date);
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
CREATE INDEX ON page (status);
CREATE INDEX ON page (timestamp);

--
-- Version
--

CREATE TABLE version (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL,
    entity_id varchar(50) NOT NULL,
    page_id int NOT NULL REFERENCES page ON DELETE CASCADE ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED,
    title varchar(255) DEFAULT NULL,
    content text NOT NULL,
    aside text NOT NULL,
    account_id int DEFAULT NULL REFERENCES account ON DELETE SET NULL ON UPDATE CASCADE,
    status status NOT NULL,
    timestamp timestamp(0) NOT NULL DEFAULT current_timestamp
);

CREATE INDEX ON version (name);
CREATE INDEX ON version (entity_id);
CREATE INDEX ON version (page_id);
CREATE INDEX ON version (title);
CREATE INDEX ON version (account_id);
CREATE INDEX ON version (status);
CREATE INDEX ON version (timestamp);

--
-- Block
--

CREATE TABLE block (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL,
    entity_id varchar(50) NOT NULL,
    title varchar(255) DEFAULT NULL,
    link varchar(255) DEFAULT NULL,
    media int DEFAULT NULL REFERENCES file ON DELETE SET NULL ON UPDATE CASCADE,
    content text NOT NULL DEFAULT ''
);

CREATE INDEX ON block (name);
CREATE INDEX ON block (entity_id);
CREATE INDEX ON block (title);
CREATE INDEX ON block (link);
CREATE INDEX ON block (media);

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
-- Page Article
--

CREATE VIEW page_article AS
SELECT
    *
FROM
    page
WHERE
    entity_id = 'page_article'
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
-- Materialized View
-- ---------------------------------------------------------------------------------------------------------------------

--
-- File Media
--

CREATE MATERIALIZED VIEW file_media AS
SELECT
    *
FROM
    file
WHERE
    entity_id IN ('file_audio', 'file_iframe', 'file_image', 'file_video');

CREATE UNIQUE INDEX ON file_media (id);
CREATE UNIQUE INDEX ON file_media (url);
CREATE UNIQUE INDEX ON file_media (thumb_url);
CREATE INDEX ON file_media (name);
CREATE INDEX ON file_media (entity_id);
CREATE INDEX ON file_media (mime);
CREATE INDEX ON file_media (ext);
CREATE INDEX ON file_media (thumb_mime);
CREATE INDEX ON file_media (thumb_ext);

--
-- Layout Page
--
CREATE MATERIALIZED VIEW layout_page AS
WITH s AS (
    SELECT
        p.id,
        (SELECT l.page_id FROM layout l INNER JOIN page j ON j.id = l.page_id WHERE l.parent_id = 'sidebar' AND l.page_id = ANY(p.path) ORDER BY j.level DESC LIMIT 1) AS page_id
    FROM
        page p
    ORDER BY
        id ASC
)
SELECT
    l.id,
    l.name,
    l.entity_id,
    l.block_id,
    p.id AS page_id,
    l.parent_id,
    l.sort
FROM
    page p
INNER JOIN
    s
        ON
            s.id = p.id
INNER JOIN
    layout l
        ON
            l.page_id = p.id
            OR l.parent_id = 'sidebar' AND l.page_id = s.page_id
ORDER BY
    id ASC,
    parent_id ASC,
    sort ASC;

CREATE UNIQUE INDEX ON layout_page (page_id, parent_id, name);
CREATE INDEX ON layout_page (name);
CREATE INDEX ON layout_page (entity_id);
CREATE INDEX ON layout_page (block_id);
CREATE INDEX ON layout_page (page_id);
CREATE INDEX ON layout_page (parent_id);
CREATE INDEX ON layout_page (sort);

-- ---------------------------------------------------------------------------------------------------------------------
-- Trigger Function
-- ---------------------------------------------------------------------------------------------------------------------

--
-- Generic trigger for not auto-updatable views
--
-- You could use this generic trigger for entities that extend a base entity and define additional columns in an
-- extension table. The resulting views are usually not auto-updatable. Anyhow you should prefer dedicated triggers
-- over this generic one. It's just too much voodoo going on here.
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
        IF (TG_OP = 'UPDATE' AND (NEW.ext != OLD.ext OR NEW.mime != OLD.mime)) THEN
            RAISE EXCEPTION 'Cannot change filetype anymore';
        END IF;

        IF (NEW.entity_id = 'file_iframe') THEN
            NEW.mime := 'text/html';
        ELSE
            NEW.url := '/file/' || NEW.id || '.' || NEW.ext;
        END IF;

        IF (NEW.thumb_url IS NOT NULL AND NEW.thumb_ext IS NOT NULL) THEN
            NEW.thumb_url := '/file/' || NEW.id || '.thumb.' || NEW.thumb_ext;
        END IF;

        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION file_view() RETURNS trigger AS $$
    BEGIN
        REFRESH MATERIALIZED VIEW CONCURRENTLY file_media;

        RETURN NULL;
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

CREATE FUNCTION page_version_before() RETURNS trigger AS $$
    DECLARE
        _aid int;
        _sta status;
    BEGIN
        -- Actually, archived status should not be allowed for new items (INSERTs) after initial setup of DB
        IF (TG_OP = 'UPDATE' AND NEW.status = 'archived' AND OLD.status != 'published') THEN
            RAISE EXCEPTION 'Can not archive unpublished or already archived page';
        END IF;

        -- Archive published version without change
        IF (TG_OP = 'UPDATE' AND NEW.status = 'archived') THEN
            _aid := NEW.account_id;
            NEW := OLD;
            NEW.account_id := _aid;
            NEW.status := 'archived';
        END IF;

        -- Check parent status
        IF ((TG_OP = 'INSERT' OR coalesce(NEW.parent_id, 0) != coalesce(OLD.parent_id, 0)) AND NEW.parent_id IS NOT NULL) THEN
            SELECT
                status
            FROM
                page
            WHERE
                id = NEW.parent_id
            INTO
                _sta;

            IF (_sta = 'archived') THEN
                RAISE EXCEPTION 'Can not set archived page as parent';
            END IF;

            -- If parent is not published yet, the child pages' status must be draft
            IF (_sta IN ('draft', 'pending')) THEN
                NEW.status := 'draft';
            END IF;
        END IF;

        -- Create new version
        IF (TG_OP = 'INSERT' OR NEW.name != OLD.name OR NEW.entity_id != OLD.entity_id OR NEW.title != OLD.title OR NEW.content != OLD.content OR NEW.aside != OLD.aside OR NEW.account_id != OLD.account_id OR NEW.status != OLD.status) THEN
            IF (TG_OP = 'UPDATE') THEN
                NEW.timestamp := current_timestamp;
            END IF;

            INSERT INTO
                version
                (name, entity_id, page_id, title, content, aside, account_id, status, timestamp)
            VALUES
                (NEW.name, NEW.entity_id, NEW.id, NEW.title, NEW.content, NEW.aside, NEW.account_id, NEW.status, NEW.timestamp);
        END IF;

        -- Don't overwrite published version with a draft
        IF (TG_OP = 'UPDATE' AND NEW.status IN ('draft', 'pending') AND OLD.status = 'published') THEN
            RETURN NULL;
        END IF;

        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION page_version_after() RETURNS trigger AS $$
    DECLARE
        _row RECORD;
    BEGIN
        IF (NEW.status != 'archived') THEN
            RETURN NULL;
        END IF;

        -- Recursively update child pages
        FOR _row IN
            SELECT
                *
            FROM
                page
            WHERE
                id != OLD.id
                AND path @> ARRAY[OLD.id]
                AND status != 'archived'
            ORDER BY
                pos ASC
        LOOP
            -- Delete page if it was never published
            IF (_row.status IN ('draft', 'pending')) THEN
                DELETE FROM
                    page
                WHERE
                    id = _row.id
                    OR path @> ARRAY[_row.id];

                CONTINUE;
            END IF;

            _row.account_id := NEW.account_id;
            _row.status := 'archived';

            -- Create new version
            INSERT INTO
                version
                (name, entity_id, page_id, title, content, aside, account_id, status)
            VALUES
                (_row.name, _row.entity_id, _row.id, _row.title, _row.content, _row.aside, _row.account_id, _row.status);

            -- Update page status
            UPDATE
                page
            SET
                account_id = _row.account_id,
                status = _row.status;
        END LOOP;

        RETURN NULL;
    END;
$$ LANGUAGE plpgsql;

--
-- Version
--

CREATE FUNCTION version_before() RETURNS trigger AS $$
    BEGIN
        IF (TG_OP = 'UPDATE') THEN
            RAISE EXCEPTION 'Update not allowed';
        END IF;

        -- Delete old drafts
        DELETE FROM
            version
        WHERE
            page_id = NEW.page_id
            AND status IN ('draft', 'pending');

        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION version_reset() RETURNS void AS $$
    BEGIN
        TRUNCATE version RESTART IDENTITY;

        INSERT INTO
            version
            (name, entity_id, page_id, title, content, aside, account_id, status, timestamp)
        SELECT
            name,
            entity_id,
            id AS page_id,
            title,
            content,
            aside,
            account_id,
            status,
            timestamp
        FROM
            page
        ORDER BY
            id ASC;
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

CREATE FUNCTION layout_view() RETURNS trigger AS $$
    BEGIN
        REFRESH MATERIALIZED VIEW CONCURRENTLY layout_page;

        RETURN NULL;
    END;
$$ LANGUAGE plpgsql;

-- ---------------------------------------------------------------------------------------------------------------------
-- Trigger
-- ---------------------------------------------------------------------------------------------------------------------

--
-- File
--

CREATE TRIGGER file_save BEFORE INSERT OR UPDATE ON file FOR EACH ROW EXECUTE PROCEDURE file_save();
CREATE TRIGGER file_view AFTER INSERT OR UPDATE OR DELETE ON file FOR EACH STATEMENT EXECUTE PROCEDURE file_view();

--
-- Page
--

CREATE TRIGGER page_before BEFORE INSERT OR UPDATE ON page FOR EACH ROW EXECUTE PROCEDURE page_before();
CREATE TRIGGER page_menu_before BEFORE INSERT OR UPDATE ON page FOR EACH ROW WHEN (pg_trigger_depth() < 1) EXECUTE PROCEDURE page_menu_before();
CREATE TRIGGER page_menu_after AFTER INSERT OR UPDATE OR DELETE ON page FOR EACH ROW WHEN (pg_trigger_depth() < 1) EXECUTE PROCEDURE page_menu_after();
CREATE TRIGGER page_version_before BEFORE INSERT OR UPDATE ON page FOR EACH ROW EXECUTE PROCEDURE page_version_before();
CREATE TRIGGER page_version_after AFTER UPDATE ON page FOR EACH ROW EXECUTE PROCEDURE page_version_after();

--
-- Version
--

CREATE TRIGGER version_before BEFORE INSERT OR UPDATE ON version FOR EACH ROW EXECUTE PROCEDURE version_before();

--
-- Layout
--

CREATE TRIGGER layout_save BEFORE INSERT OR UPDATE ON layout FOR EACH ROW EXECUTE PROCEDURE layout_save();
CREATE TRIGGER layout_view AFTER INSERT OR UPDATE OR DELETE ON layout FOR EACH STATEMENT EXECUTE PROCEDURE layout_view();

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
