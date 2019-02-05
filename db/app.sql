START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Type
-- ---------------------------------------------------------------------------------------------------------------------

CREATE TYPE status AS ENUM ('draft', 'pending', 'published', 'archived');

-- ---------------------------------------------------------------------------------------------------------------------
-- Function
-- ---------------------------------------------------------------------------------------------------------------------

--
-- Generic trigger for not auto-updatable views
--
-- Naming conventions
-- -------------------------------------------------------------------
-- base table/entity name | {base}            | p.e. block
-- view/entity name       | {base}_{name}     | p.e. block_content
-- extension table name   | {base}_{name}_ext | p.e. block_content_ext
--

CREATE FUNCTION entity_save() RETURNS trigger AS $$
    DECLARE
        _attr text;
        _base text;
        _col text := '';
        _data jsonb;
        _ext text;
        _set text := '';
        _sql text := '';
        _val text := '';
    BEGIN
        _base := substring(TG_TABLE_NAME FROM '^([^_]+)_');
        _ext := TG_TABLE_NAME || '_ext';
        NEW.entity_id := TG_TABLE_NAME;
        _data := to_jsonb(NEW);

        -- Base table
        FOR _attr IN
            SELECT
                column_name
            FROM
                information_schema.columns
            WHERE
                table_schema = TG_TABLE_SCHEMA
                AND table_name = _base
                AND column_name != 'id'
            ORDER BY
                ordinal_position ASC
        LOOP
            IF (_col != '') THEN
                _col := _col || ', ';
                _val := _val || ', ';
                _set := _set || ', ';
            END IF;

            _col := _col || quote_ident(_attr);

            IF (jsonb_extract_path_text(_data, _attr) IS NULL) THEN
                _val := _val || 'NULL';
                _set := _set || quote_ident(_attr) || ' = ' || 'NULL';
            ELSE
                _val := _val || quote_literal(jsonb_extract_path_text(_data, _attr));
                _set := _set || quote_ident(_attr) || ' = ' || quote_literal(jsonb_extract_path_text(_data, _attr));
            END IF;
        END LOOP;

        IF (TG_OP = 'UPDATE') THEN
            _sql := format('UPDATE %I SET id = %L, %s WHERE id = %L RETURNING id', _base, NEW.id, _set, OLD.id);
        ELSE
            _sql := format('INSERT INTO %I (%s) VALUES (%s) RETURNING id', _base, _col, _val);
        END IF;

        _sql := 'WITH t AS (' || _sql || ')';

        -- Extension table
        _col := '';
        _val := '';
        _set := '';

        FOR _attr IN
            SELECT
                column_name
            FROM
                information_schema.columns
            WHERE
                table_schema = TG_TABLE_SCHEMA
                AND table_name = _ext
                AND column_name != 'id'
            ORDER BY
                ordinal_position ASC
        LOOP
            IF (_col != '') THEN
                _col := _col || ', ';
                _val := _val || ', ';
                _set := _set || ', ';
            END IF;

            _col := _col || quote_ident(_attr);

            IF (jsonb_extract_path_text(_data, _attr) IS NULL) THEN
                _val := _val || 'NULL';
                _set := _set || quote_ident(_attr) || ' = ' || 'NULL';
            ELSE
                _val := _val || quote_literal(jsonb_extract_path_text(_data, _attr));
                _set := _set || quote_ident(_attr) || ' = ' || quote_literal(jsonb_extract_path_text(_data, _attr));
            END IF;
        END LOOP;

        IF (TG_OP = 'UPDATE' AND (SELECT count(*) FROM block_content_ext WHERE id = OLD.id) > 0) THEN
            _sql := _sql || format(' UPDATE %I e SET %s FROM t WHERE e.id = t.id', _ext, _set);
        ELSE
            _sql := _sql || format(' INSERT INTO %s (id, %s) SELECT id, %s FROM t', _ext, _col, _val);
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

        NEW.url := '/file/' || NEW.id || '.' || NEW.ext;

        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

--
-- Page
--

CREATE FUNCTION page_menu_before() RETURNS trigger AS $$
    DECLARE
        _chk boolean;
        _cnt integer;
        _cur integer;
        _slg text;
    BEGIN
        IF (TG_OP = 'UPDATE' AND NEW.parent_id IS NOT NULL) THEN
            SELECT
                path @> OLD.id::text::jsonb
            FROM
                page
            WHERE
                id = NEW.parent_id
            INTO
                _chk;

            IF (_chk) THEN
                RAISE EXCEPTION 'Recursion error';
            END IF;
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

        IF (TG_OP = 'INSERT' OR NEW.slug != OLD.slug OR coalesce(NEW.parent_id, 0) != coalesce(OLD.parent_id, 0)) THEN
            _slg := NEW.slug;
            _cur := 0;

            LOOP
                IF (_cur > 0) THEN
                    _slg := NEW.slug || '-' || _cur;
                END IF;

                SELECT
                    count(*)
                FROM
                    page
                WHERE
                    coalesce(parent_id, 0) = coalesce(NEW.parent_id, 0)
                    AND slug = _slg
                INTO
                    _cnt;

                EXIT WHEN _cnt = 0;
                _cur := _cur + 1;
            END LOOP;

            NEW.slug := _slg;
        END IF;

        IF (NEW.menu_name = NEW.name) THEN
            NEW.menu_name := NULL;
        END IF;

        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION page_menu_after() RETURNS trigger AS $$
    DECLARE
        _pad integer := 5;
        _ext text := '.html';
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
        WITH RECURSIVE t AS (
            SELECT
                id,
                CASE WHEN slug = 'index' THEN '' ELSE '/' || slug END AS urlkey,
                CASE WHEN slug = 'index' THEN '/' ELSE '/' || slug || _ext END AS url,
                menu,
                '[]'::jsonb || TO_JSONB(id) AS path,
                0 AS level,
                LPAD(cast(sort AS text), _pad, '0') AS pos
            FROM
                page
            WHERE
                parent_id IS NULL
            UNION
            SELECT
                p.id,
                t.urlkey || '/' || p.slug AS urlkey,
                t.urlkey || '/' || p.slug || _ext AS url,
                t.menu AND p.menu AS menu,
                t.path || to_jsonb(p.id) AS path,
                t.level + 1 AS level,
                t.pos || '.' || lpad(cast(p.sort AS text), _pad, '0') AS pos
            FROM
                page p
            INNER JOIN
                t
                    ON t.id = p.parent_id
            )
            UPDATE
                page p
            SET
                url = t.url,
                menu = t.menu,
                path = t.path,
                level = t.level,
                pos = t.pos
            FROM
                t
            WHERE
                p.id = t.id
                AND (p.url != t.url OR p.menu != t.menu OR p.path != t.path OR p.level != t.level OR p.pos != t.pos);

        RETURN NULL;
    END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION page_version_before() RETURNS trigger AS $$
    DECLARE
        _now timestamp := current_timestamp;
        _sta status;
    BEGIN
        -- Actually, archived status should not be allowed for new items (INSERTs) after initial setup of DB
        IF (TG_OP = 'UPDATE' AND NEW.status = 'archived' AND OLD.status != 'published') THEN
            RAISE EXCEPTION 'Can not archive unpublished or already archived page';
        END IF;

        -- Archive published version without change
        IF (TG_OP = 'UPDATE' AND NEW.status = 'archived') THEN
            NEW := OLD;
            NEW.status := 'archived';
        END IF;

        -- Delete old drafts when item is published or archived
        IF (TG_OP = 'UPDATE' AND NEW.status IN ('published', 'archived')) THEN
            DELETE FROM
                version
            WHERE
                page_id = OLD.id
                AND status IN ('draft', 'pending');
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
        IF (TG_OP = 'INSERT' OR NEW.name != OLD.name OR NEW.teaser != OLD.teaser OR NEW.main != OLD.main OR NEW.aside != OLD.aside OR NEW.status != OLD.status) THEN
            IF (TG_OP = 'INSERT') THEN
                _now := NEW.timestamp;
            END IF;

            INSERT INTO
                version
                (name, teaser, main, aside, status, timestamp, page_id)
            VALUES
                (NEW.name, NEW.teaser, NEW.main, NEW.aside, NEW.status, _now, NEW.id);
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
        FOR _row IN SELECT * FROM page WHERE id != OLD.id AND path @> OLD.id::text::jsonb AND status != 'archived' ORDER BY pos ASC LOOP
            -- Delete page if it was never published
            IF (_row.status IN ('draft', 'pending')) THEN
                DELETE FROM
                    page
                WHERE
                    id = _row.id
                    OR path @> _row.id::text::jsonb;

                CONTINUE;
            END IF;

            -- Delete old drafts when item is published
            DELETE FROM
                version
            WHERE
                page_id = _row.id
                AND status IN ('draft', 'pending');

            _row.status := 'archived';

            -- Create new version
            INSERT INTO
                version
                (name, teaser, main, aside, status, page_id)
            VALUES
                (_row.name, _row.teaser, _row.main, _row.aside, _row.status, _row.id);

            -- Update page status
            UPDATE
                page
            SET
                status = _row.status;
        END LOOP;

        RETURN NULL;
    END;
$$ LANGUAGE plpgsql;

--
-- Version
--

CREATE FUNCTION version_protect() RETURNS trigger AS $$
    BEGIN
        RAISE EXCEPTION 'Update not allowed';
        RETURN NULL;
    END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION version_reset() RETURNS void AS $$
    BEGIN
        TRUNCATE version RESTART IDENTITY;

        INSERT INTO
            version
            (name, teaser, main, aside, status, timestamp, page_id)
        SELECT
            name,
            teaser,
            main,
            aside,
            status,
            timestamp,
            id AS page_id
        FROM
            page
        ORDER BY
            id ASC;
    END;
$$ LANGUAGE plpgsql;

-- ---------------------------------------------------------------------------------------------------------------------
-- Table
-- ---------------------------------------------------------------------------------------------------------------------

--
-- Role
--

CREATE TABLE role (
    id serial PRIMARY KEY,
    name varchar(50) NOT NULL UNIQUE,
    priv jsonb NOT NULL
);

CREATE INDEX ON role USING GIN (priv);

--
-- Account
--

CREATE TABLE account (
    id serial PRIMARY KEY,
    name varchar(50) NOT NULL UNIQUE,
    password varchar(255) NOT NULL,
    role_id integer NOT NULL REFERENCES role ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE INDEX ON account (role_id);

--
-- File
--

CREATE TABLE file (
    id serial PRIMARY KEY,
    name varchar(100) NOT NULL,
    url varchar(255) NOT NULL UNIQUE,
    ext varchar(10) NOT NULL,
    mime varchar(255) NOT NULL,
    info text NOT NULL,
    entity_id varchar(50) NOT NULL
);

CREATE INDEX ON file (name);
CREATE INDEX ON file (ext);
CREATE INDEX ON file (mime);
CREATE INDEX ON file (entity_id);

CREATE TRIGGER file_save BEFORE INSERT OR UPDATE ON file FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE file_save();

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
-- Page
--

CREATE TABLE page (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL,
    image integer DEFAULT NULL REFERENCES file ON DELETE SET NULL ON UPDATE CASCADE,
    teaser text NOT NULL DEFAULT '',
    main text NOT NULL DEFAULT '',
    aside text NOT NULL DEFAULT '',
    meta_title varchar(80) NOT NULL DEFAULT '',
    meta_description varchar(300) NOT NULL DEFAULT '',
    slug varchar(75) NOT NULL,
    url varchar(400) UNIQUE DEFAULT NULL,
    disabled boolean NOT NULL DEFAULT FALSE,
    menu boolean NOT NULL DEFAULT FALSE,
    menu_name varchar(255) DEFAULT NULL,
    parent_id integer DEFAULT NULL REFERENCES page ON DELETE CASCADE ON UPDATE CASCADE,
    sort integer NOT NULL DEFAULT 0,
    pos varchar(255) NOT NULL DEFAULT '',
    level integer NOT NULL DEFAULT 0,
    path jsonb NOT NULL DEFAULT '[]',
    status status NOT NULL,
    timestamp timestamp NOT NULL DEFAULT current_timestamp,
    date timestamp NOT NULL DEFAULT current_timestamp,
    entity_id varchar(50) NOT NULL,
    UNIQUE (parent_id, slug)
);

CREATE INDEX ON page (name);
CREATE INDEX ON page (image);
CREATE INDEX ON page (meta_title);
CREATE INDEX ON page (meta_description);
CREATE INDEX ON page (slug);
CREATE INDEX ON page (url);
CREATE INDEX ON page (disabled);
CREATE INDEX ON page (menu);
CREATE INDEX ON page (menu_name);
CREATE INDEX ON page (parent_id);
CREATE INDEX ON page (sort);
CREATE INDEX ON page (pos);
CREATE INDEX ON page (level);
CREATE INDEX ON page USING GIN (path);
CREATE INDEX ON page (status);
CREATE INDEX ON page (timestamp);
CREATE INDEX ON page (date);
CREATE INDEX ON page (entity_id);

CREATE TRIGGER page_menu_before BEFORE INSERT OR UPDATE ON page FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE page_menu_before();
CREATE TRIGGER page_menu_after AFTER INSERT OR UPDATE OR DELETE ON page FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE page_menu_after();
CREATE TRIGGER page_version_before BEFORE INSERT OR UPDATE ON page FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE page_version_before();
CREATE TRIGGER page_version_after AFTER UPDATE ON page FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE page_version_after();

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
-- Version
--

CREATE TABLE version (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL,
    teaser text NOT NULL,
    main text NOT NULL,
    aside text NOT NULL,
    status status NOT NULL,
    timestamp timestamp NOT NULL DEFAULT current_timestamp,
    page_id integer NOT NULL REFERENCES page ON DELETE CASCADE ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED
);

CREATE INDEX ON version (name);
CREATE INDEX ON version (status);
CREATE INDEX ON version (timestamp);
CREATE INDEX ON version (page_id);

CREATE TRIGGER version_protect BEFORE UPDATE ON version FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE version_protect();

--
-- Block
--

CREATE TABLE block (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL,
    entity_id varchar(50) NOT NULL
);

CREATE INDEX ON block (name);
CREATE INDEX ON block (entity_id);

--
-- Block Content
--

CREATE TABLE block_content_ext (
    id integer NOT NULL PRIMARY KEY REFERENCES block ON DELETE CASCADE ON UPDATE CASCADE,
    content text NOT NULL DEFAULT ''
);

CREATE VIEW block_content AS
SELECT
    *
FROM
    block
LEFT JOIN
    block_content_ext
        USING (id)
WHERE
    entity_id = 'block_content';

CREATE TRIGGER entity_save INSTEAD OF INSERT OR UPDATE ON block_content FOR EACH ROW EXECUTE PROCEDURE entity_save();
CREATE TRIGGER entity_delete INSTEAD OF DELETE ON block_content FOR EACH ROW EXECUTE PROCEDURE entity_delete();

--
-- Layout
--

CREATE TABLE layout (
    id serial PRIMARY KEY,
    name varchar(100) NOT NULL,
    block_id integer NOT NULL REFERENCES block ON DELETE CASCADE ON UPDATE CASCADE,
    page_id integer NOT NULL REFERENCES page ON DELETE CASCADE ON UPDATE CASCADE,
    parent_id varchar(100) NOT NULL,
    sort integer NOT NULL DEFAULT 0,
    UNIQUE (page_id, name)
);

CREATE INDEX ON layout (name);
CREATE INDEX ON layout (block_id);
CREATE INDEX ON layout (page_id);
CREATE INDEX ON layout (parent_id);
CREATE INDEX ON layout (sort);

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
