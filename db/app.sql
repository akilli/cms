START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Account
-- ---------------------------------------------------------------------------------------------------------------------

CREATE TABLE role (
    id serial PRIMARY KEY,
    name varchar(50) NOT NULL UNIQUE,
    priv jsonb NOT NULL
);

CREATE INDEX ON role USING GIN (priv);

CREATE TABLE account (
    id serial PRIMARY KEY,
    name varchar(50) NOT NULL UNIQUE,
    password varchar(255) NOT NULL,
    role integer NOT NULL REFERENCES role ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE INDEX ON account (role);

-- ---------------------------------------------------------------------------------------------------------------------
-- URL
-- ---------------------------------------------------------------------------------------------------------------------

CREATE TABLE url (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL UNIQUE,
    target varchar(255) NOT NULL,
    redirect integer DEFAULT NULL
);

CREATE INDEX ON url (target);
CREATE INDEX ON url (redirect);

-- ---------------------------------------------------------------------------------------------------------------------
-- Asset
-- ---------------------------------------------------------------------------------------------------------------------

CREATE FUNCTION asset_save() RETURNS trigger AS $$
    BEGIN
        IF (TG_OP = 'UPDATE' AND NEW.type != OLD.type) THEN
            RAISE EXCEPTION 'Cannot change filetype anymore';
        END IF;

        NEW.name := '/asset/' || NEW.id || '.' || NEW.type;

        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE TABLE asset (
    id serial PRIMARY KEY,
    name varchar(50) NOT NULL UNIQUE,
    type varchar(5) NOT NULL,
    info text NOT NULL,
    ent varchar(50) NOT NULL CHECK (ent != '')
);

CREATE INDEX ON asset (type);
CREATE INDEX ON asset (ent);

CREATE TRIGGER asset_save BEFORE INSERT OR UPDATE ON asset FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE asset_save();

-- ---------------------------------------------------------------------------------------------------------------------
-- Page
-- ---------------------------------------------------------------------------------------------------------------------

-- Type
CREATE TYPE status AS ENUM ('draft', 'pending', 'published', 'archived');

-- Trigger
CREATE FUNCTION page_menu_before() RETURNS trigger AS $$
    DECLARE
        _chk boolean;
        _cnt integer;
        _cur integer;
        _slg text;
    BEGIN
        IF (TG_OP = 'UPDATE' AND NEW.parent IS NOT NULL) THEN
            SELECT
                path @> OLD.id::text::jsonb
            FROM
                page
            WHERE
                id = NEW.parent
            INTO
                _chk;

            IF (_chk) THEN
                RAISE EXCEPTION 'Recursion error';
            END IF;
        END IF;

        SELECT
            COUNT(id) + 1
        FROM
            page
        WHERE
            COALESCE(parent, 0) = COALESCE(NEW.parent, 0)
        INTO
            _cnt;

        IF (TG_OP = 'UPDATE' AND COALESCE(NEW.parent, 0) = COALESCE(OLD.parent, 0)) THEN
            _cnt := _cnt - 1;
        END IF;

        IF (NEW.sort IS NULL OR NEW.sort <= 0 OR NEW.sort > _cnt) THEN
            NEW.sort := _cnt;
        END IF;

        IF (TG_OP = 'INSERT' OR NEW.slug != OLD.slug OR COALESCE(NEW.parent, 0) != COALESCE(OLD.parent, 0)) THEN
            _slg := NEW.slug;
            _cur := 0;

            LOOP
                IF (_cur > 0) THEN
                    _slg := NEW.slug || '-' || _cur;
                END IF;

                SELECT
                    COUNT(id)
                FROM
                    page
                WHERE
                    COALESCE(parent, 0) = COALESCE(NEW.parent, 0)
                    AND slug = _slg
                INTO
                    _cnt;

                EXIT WHEN _cnt = 0;
                _cur := _cur + 1;
            END LOOP;

            NEW.slug := _slg;
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
        IF (TG_OP = 'UPDATE' AND NEW.slug = OLD.slug AND NEW.menu = OLD.menu AND COALESCE(NEW.parent, 0) = COALESCE(OLD.parent, 0) AND NEW.sort = OLD.sort) THEN
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
                AND COALESCE(parent, 0) = COALESCE(OLD.parent, 0)
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
                AND COALESCE(parent, 0) = COALESCE(NEW.parent, 0)
                AND sort >= NEW.sort;
        END IF;

        -- Update positions
        WITH RECURSIVE t AS (
            SELECT
                id,
                '/' || slug AS urlkey,
                '/' || slug || _ext AS url,
                menu,
                '[]'::jsonb || TO_JSONB(id) AS path,
                1 AS level,
                LPAD(CAST(sort AS text), _pad, '0') AS pos
            FROM
                page
            WHERE
                parent IS NULL
            UNION
            SELECT
                p.id,
                t.urlkey || '/' || p.slug AS urlkey,
                t.urlkey || '/' || p.slug || _ext AS url,
                t.menu AND p.menu AS menu,
                t.path || TO_JSONB(p.id) AS path,
                t.level + 1 AS level,
                t.pos || '.' || LPAD(CAST(p.sort AS text), _pad, '0') AS pos
            FROM
                page p
            INNER JOIN
                t
                    ON t.id = p.parent
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

CREATE FUNCTION page_version() RETURNS trigger AS $$
    DECLARE
        _row RECORD;
    BEGIN
        IF (TG_OP = 'UPDATE' AND NEW.status = 'archived') THEN
            -- Actually, archived status should not be allowed for new items (INSERTs) after initial setup of DB
            IF (OLD.status != 'published') THEN
                RAISE EXCEPTION 'Can not archive unpublished or already archived item';
            END IF;

            -- Archive published version without change
            NEW := OLD;
            NEW.status := 'archived';

            -- Delete old drafts when item is archived
            DELETE FROM
                version
            WHERE
                page = OLD.id
                AND status IN ('draft', 'pending');

            -- Recursively update child pages
            FOR _row IN SELECT * FROM page WHERE pos LIKE OLD.pos || '/%' AND status != 'archived' ORDER BY level DESC LOOP
                IF (_row.status IN ('draft', 'pending')) THEN
                    DELETE FROM
                        page
                    WHERE
                        id = _row.id
                        OR pos LIKE _row.pos || '/%';

                    CONTINUE;
                END IF;

                DELETE FROM
                    version
                WHERE
                    page = _row.id
                    AND status IN ('draft', 'pending');

                _row.status := 'archived';
                _row.date := current_timestamp;

                INSERT INTO
                    version
                    (name, teaser, body, status, date, page)
                VALUES
                    (_row.name, _row.teaser, _row.body, _row.status, _row.date, _row.id);

                UPDATE
                    page
                SET
                    status = _row.status,
                    date = _row.date;
            END LOOP;
        ELSIF (TG_OP = 'UPDATE' AND NEW.status = 'published') THEN
            -- Delete old drafts when item is published
            DELETE FROM
                version
            WHERE
                page = OLD.id
                AND status IN ('draft', 'pending');
        END IF;

        IF (TG_OP = 'INSERT' OR NEW.name != OLD.name OR NEW.teaser != OLD.teaser OR NEW.body != OLD.body OR NEW.status != OLD.status) THEN
            IF (TG_OP = 'UPDATE' OR NEW.date IS NULL) THEN
                NEW.date := current_timestamp;
            END IF;

            INSERT INTO
                version
                (name, teaser, body, status, date, page)
            VALUES
                (NEW.name, NEW.teaser, NEW.body, NEW.status, NEW.date, NEW.id);
        ELSE
            NEW.date := OLD.date;
        END IF;

        -- Don't overwrite published version with a draft
        IF (TG_OP = 'UPDATE' AND NEW.status IN ('draft', 'pending') AND OLD.status = 'published') THEN
            RETURN NULL;
        END IF;

        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION version_protect() RETURNS trigger AS $$
    BEGIN
        RAISE EXCEPTION 'Update not allowed';
        RETURN NULL;
    END;
$$ LANGUAGE plpgsql;

-- Table
CREATE TABLE page (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL,
    image integer DEFAULT NULL REFERENCES asset ON DELETE SET NULL ON UPDATE CASCADE,
    teaser text NOT NULL DEFAULT '',
    body text NOT NULL DEFAULT '',
    meta varchar(300) NOT NULL DEFAULT '',
    slug varchar(50) NOT NULL,
    url varchar(255) UNIQUE DEFAULT NULL,
    menu boolean NOT NULL DEFAULT FALSE,
    parent integer DEFAULT NULL REFERENCES page ON DELETE CASCADE ON UPDATE CASCADE,
    sort integer NOT NULL DEFAULT 0,
    pos varchar(255) NOT NULL DEFAULT '',
    level integer NOT NULL DEFAULT 0,
    path jsonb NOT NULL DEFAULT '[]',
    status status NOT NULL,
    date timestamp NOT NULL DEFAULT current_timestamp,
    ent varchar(50) NOT NULL CHECK (ent != ''),
    UNIQUE (parent, slug)
);

CREATE INDEX ON page (name);
CREATE INDEX ON page (image);
CREATE INDEX ON page (meta);
CREATE INDEX ON page (slug);
CREATE INDEX ON page (url);
CREATE INDEX ON page (menu);
CREATE INDEX ON page (parent);
CREATE INDEX ON page (sort);
CREATE INDEX ON page (pos);
CREATE INDEX ON page (level);
CREATE INDEX ON page USING GIN (path);
CREATE INDEX ON page (status);
CREATE INDEX ON page (date);
CREATE INDEX ON page (ent);

CREATE TRIGGER page_menu_before BEFORE INSERT OR UPDATE ON page FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE page_menu_before();
CREATE TRIGGER page_menu_after AFTER INSERT OR UPDATE OR DELETE ON page FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE page_menu_after();
CREATE TRIGGER page_version BEFORE INSERT OR UPDATE ON page FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE page_version();

CREATE TABLE version (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL,
    teaser text NOT NULL,
    body text NOT NULL,
    status status NOT NULL,
    date timestamp NOT NULL,
    page integer NOT NULL REFERENCES page ON DELETE CASCADE ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED
);

CREATE INDEX ON version (name);
CREATE INDEX ON version (status);
CREATE INDEX ON version (date);
CREATE INDEX ON version (page);

CREATE TRIGGER version_protect BEFORE UPDATE ON version FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE version_protect();

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
