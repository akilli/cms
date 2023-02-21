START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Function
-- ---------------------------------------------------------------------------------------------------------------------

CREATE FUNCTION public.app_entity_id(_schema text, _table text) RETURNS text AS $$
BEGIN
    IF (_schema = 'public') THEN
        RETURN _table;
    END IF;

    RETURN _schema || '.' || _table;
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION public.app_is_entity_id(_schema text, _table text) RETURNS boolean AS $$
DECLARE
    _is boolean;
BEGIN
    SELECT
        count(column_name) > 0
    FROM
        information_schema.columns
    WHERE
        table_schema = _schema
        AND table_name = _table
        AND column_name = 'entity_id'
    INTO
        _is;

    RETURN _is;
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION public.app_version_get(_schema text) RETURNS text AS $$
DECLARE
    _version text;
BEGIN
    SELECT obj_description(_schema::regnamespace, 'pg_namespace') INTO _version;
    RETURN _version;
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION public.app_version_set(_schema text, _version text) RETURNS void AS $$
BEGIN
    EXECUTE format('COMMENT ON SCHEMA %I IS %L', _schema, _version);
END;
$$ LANGUAGE plpgsql;

-- ---------------------------------------------------------------------------------------------------------------------
-- Table
-- ---------------------------------------------------------------------------------------------------------------------

--
-- Role
--

CREATE TABLE public.role (
    id serial PRIMARY KEY,
    name varchar(50) NOT NULL UNIQUE,
    privilege text[] NOT NULL DEFAULT '{}',
    created timestamp(0) NOT NULL DEFAULT current_timestamp
);

CREATE INDEX ON public.role (created);

--
-- Account
--

CREATE TABLE public.account (
    id serial PRIMARY KEY,
    name varchar(50) NOT NULL UNIQUE,
    uid varchar(100) NOT NULL UNIQUE,
    url varchar(102) NOT NULL UNIQUE GENERATED ALWAYS AS ('/~' || uid) STORED,
    role_id int NOT NULL REFERENCES public.role ON DELETE RESTRICT ON UPDATE CASCADE,
    username varchar(50) NOT NULL UNIQUE,
    password varchar(255) NOT NULL,
    email varchar(50) DEFAULT null UNIQUE,
    image varchar(255) DEFAULT null UNIQUE,
    active boolean NOT NULL DEFAULT false,
    created timestamp(0) NOT NULL DEFAULT current_timestamp
);

CREATE INDEX ON public.account (role_id);
CREATE INDEX ON public.account (created);

--
-- Menu
--

CREATE TABLE public.menu (
    id serial PRIMARY KEY,
    name varchar(100) NOT NULL,
    url varchar(255) DEFAULT null,
    parent_id int DEFAULT null REFERENCES public.menu ON DELETE CASCADE ON UPDATE CASCADE,
    sort int NOT NULL DEFAULT 0,
    position varchar(255) NOT NULL DEFAULT '',
    level int NOT NULL DEFAULT 0,
    path int[] NOT NULL DEFAULT '{}',
    created timestamp(0) NOT NULL DEFAULT current_timestamp
);

CREATE INDEX ON public.menu (name);
CREATE INDEX ON public.menu (url);
CREATE INDEX ON public.menu (parent_id);
CREATE INDEX ON public.menu (position);
CREATE INDEX ON public.menu (level);
CREATE INDEX ON public.menu (created);

--
-- File
--

CREATE TABLE public.file (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL UNIQUE,
    entity_id varchar(50) NOT NULL,
    mime varchar(255) NOT NULL,
    info text NOT NULL DEFAULT '',
    created timestamp(0) NOT NULL DEFAULT current_timestamp
);

CREATE INDEX ON public.file (entity_id);
CREATE INDEX ON public.file (created);

--
-- Page
--

CREATE TABLE public.page (
    id serial PRIMARY KEY,
    name varchar(100) NOT NULL,
    entity_id varchar(50) NOT NULL,
    url varchar(255) NOT NULL UNIQUE,
    title varchar(100) NOT NULL DEFAULT '',
    meta_title varchar(80) NOT NULL DEFAULT '',
    meta_description varchar(300) NOT NULL DEFAULT '',
    content text NOT NULL DEFAULT '',
    created timestamp(0) NOT NULL DEFAULT current_timestamp
);

CREATE INDEX ON public.page (name);
CREATE INDEX ON public.page (entity_id);
CREATE INDEX ON public.page (created);

--
-- Block
--

CREATE TABLE public.block (
    id serial PRIMARY KEY,
    name varchar(100) NOT NULL,
    entity_id varchar(50) NOT NULL,
    content text NOT NULL DEFAULT '',
    created timestamp(0) NOT NULL DEFAULT current_timestamp
);

CREATE INDEX ON public.block (name);
CREATE INDEX ON public.block (entity_id);
CREATE INDEX ON public.block (created);

--
-- Layout
--

CREATE TABLE public.layout (
    id serial PRIMARY KEY,
    name varchar(100) NOT NULL,
    block_entity_id varchar(50) NOT NULL,
    block_id int NOT NULL REFERENCES public.block ON DELETE CASCADE ON UPDATE CASCADE,
    page_id int NOT NULL REFERENCES public.page ON DELETE CASCADE ON UPDATE CASCADE,
    parent_id varchar(100) NOT NULL,
    sort int NOT NULL DEFAULT 0,
    created timestamp(0) NOT NULL DEFAULT current_timestamp
);

CREATE INDEX ON public.layout (name);
CREATE INDEX ON public.layout (block_entity_id);
CREATE INDEX ON public.layout (block_id);
CREATE INDEX ON public.layout (page_id);
CREATE INDEX ON public.layout (parent_id);
CREATE INDEX ON public.layout (sort);
CREATE INDEX ON public.layout (created);

--
-- URL
--

CREATE TABLE public.url (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL UNIQUE,
    target_entity_id varchar(50) NOT NULL,
    target_id int NOT NULL
);

CREATE UNIQUE INDEX ON public.url (target_entity_id, target_id);

-- ---------------------------------------------------------------------------------------------------------------------
-- View
-- ---------------------------------------------------------------------------------------------------------------------

--
-- Audio
--

CREATE VIEW public.audio AS
SELECT
    *
FROM
    public.file
WHERE
    entity_id = 'audio'
WITH LOCAL CHECK OPTION;

--
-- Document
--

CREATE VIEW public.document AS
SELECT
    *
FROM
    public.file
WHERE
    entity_id = 'document'
WITH LOCAL CHECK OPTION;

--
-- Iframe
--

CREATE VIEW public.iframe AS
SELECT
    *
FROM
    public.file
WHERE
    entity_id = 'iframe'
WITH LOCAL CHECK OPTION;

--
-- Image
--

CREATE VIEW public.image AS
SELECT
    *
FROM
    public.file
WHERE
    entity_id = 'image'
WITH LOCAL CHECK OPTION;

--
-- Video
--

CREATE VIEW public.video AS
SELECT
    *
FROM
    public.file
WHERE
    entity_id = 'video'
WITH LOCAL CHECK OPTION;

--
-- Content Page
--

CREATE VIEW public.contentpage AS
SELECT
    *
FROM
    public.page
WHERE
    entity_id = 'contentpage'
WITH LOCAL CHECK OPTION;

--
-- Content Block
--

CREATE VIEW public.contentblock AS
SELECT
    *
FROM
    public.block
WHERE
    entity_id = 'contentblock'
WITH LOCAL CHECK OPTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Trigger Function
-- ---------------------------------------------------------------------------------------------------------------------

--
-- Generic
--

CREATE FUNCTION public.entity_url_delete() RETURNS trigger AS $$
DECLARE
    _ent text;
BEGIN
    IF (public.app_is_entity_id(TG_TABLE_SCHEMA, TG_TABLE_NAME)) THEN
        _ent := OLD.entity_id;
    ELSE
        _ent := public.app_entity_id(TG_TABLE_SCHEMA, TG_TABLE_NAME);
    END IF;

    DELETE FROM
        public.url
    WHERE
        target_entity_id = _ent
        AND target_id = OLD.id;

    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION public.entity_url_save() RETURNS trigger AS $$
DECLARE
    _cnt int := 0;
    _ent text;
BEGIN
    IF (public.app_is_entity_id(TG_TABLE_SCHEMA, TG_TABLE_NAME)) THEN
        _ent := NEW.entity_id;
    ELSE
        _ent := public.app_entity_id(TG_TABLE_SCHEMA, TG_TABLE_NAME);
    END IF;

    IF (TG_OP = 'UPDATE') THEN
        SELECT
            count(id)
        FROM
            public.url
        WHERE
            name = OLD.url
            AND target_entity_id = _ent
            AND target_id = OLD.id
        INTO
            _cnt;
    END IF;

    IF (TG_OP = 'INSERT' OR _cnt = 0) THEN
        INSERT INTO
            public.url
            (name, target_entity_id, target_id)
        VALUES
            (NEW.url, _ent, NEW.id);
    ELSE
        UPDATE
            public.url
        SET
            name = NEW.url
        WHERE
            name = OLD.url
            AND target_entity_id = _ent
            AND target_id = OLD.id;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION public.entity_placeholder_delete() RETURNS trigger AS $$
DECLARE
    _col text;
    _cols text[];
    _schema text;
    _set text;
    _table text;
    _pat text;
    _tag text;
    _where text;
BEGIN
    IF (array_length(TG_ARGV, 1) < 4) THEN
        RAISE EXCEPTION 'You must pass the tag, table schema, table name and at least one column as arguments';
    END IF;

    _tag := TG_ARGV[0];
    _schema := TG_ARGV[1];
    _table := TG_ARGV[2];
    _cols := TG_ARGV[3:];
    _pat := format('<%1$s id="%2$s-%3$s">([^<]*)</%1$s>', _tag, OLD.entity_id, OLD.id);
    _set := '';
    _where := '';

    FOREACH _col IN ARRAY _cols LOOP
        IF (_set != '') THEN
            _set := _set || ', ';
            _where := _where || ' OR ';
        END IF;

        _set := _set || format(
            '%1$I = regexp_replace(regexp_replace(%1$I, %2$L, %3$L, %4$L), %5$L, %3$L, %4$L)',
            _col,
            _pat,
            '',
            'g',
            '<figure([^>]*)>\s*(<figcaption([^>]*)>([^<]*)</figcaption>)?\s*</figure>'
        );
        _where := _where || format('%I ~ %L', _col, _pat);
    END LOOP;

    EXECUTE format('UPDATE %I.%I SET %s WHERE %s', _schema, _table, _set, _where);

    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

--
-- Menu
--

CREATE FUNCTION public.menu_before() RETURNS trigger AS $$
DECLARE
    _cnt int;
BEGIN
    -- Avoid recursion
    IF (current_setting('app.menu', true) != '1') THEN
        RETURN NEW;
    END IF;

    IF (TG_OP = 'UPDATE'
        AND NEW.parent_id IS NOT NULL
        AND (SELECT path @> ARRAY[OLD.id] FROM public.menu WHERE id = NEW.parent_id)
    ) THEN
        RAISE EXCEPTION 'Recursion error';
    END IF;

    SELECT
        count(id) + 1
    FROM
        public.menu
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

CREATE FUNCTION public.menu_after() RETURNS trigger AS $$
DECLARE
    _pad int := 5;
BEGIN
    -- Avoid recursion
    IF (current_setting('app.menu', true) != '1') THEN
        RETURN null;
    END IF;

    -- Set session variable to handle recursion
    PERFORM set_config('app.menu', '1', true);

    -- Remove from old parent
    IF (TG_OP = 'UPDATE' OR TG_OP = 'DELETE') THEN
        UPDATE
            public.menu
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
            public.menu
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
            m.id,
            (
                SELECT
                   count(id)
                FROM
                   public.menu
                WHERE
                   coalesce(parent_id, 0) = coalesce(m.parent_id, 0)
                   AND (sort < m.sort OR sort = m.sort AND id < m.id)
            ) + 1 AS sort
        FROM
            public.menu m
    ),
    t AS (
        SELECT
            m.id,
            s.sort,
            lpad(cast(s.sort AS text), _pad, '0') AS position,
            1 AS level,
            '{}'::int[] || m.id AS path
        FROM
            public.menu m
        INNER JOIN
            s
                ON s.id = m.id
        WHERE
            m.parent_id IS NULL
        UNION
        SELECT
            m.id,
            s.sort,
            t.position || '.' || lpad(cast(s.sort AS text), _pad, '0') AS position,
            t.level + 1 AS level,
            t.path || m.id AS path
        FROM
            public.menu m
        INNER JOIN
            t
                ON t.id = m.parent_id
        INNER JOIN
            s
                ON s.id = m.id
    )
    UPDATE
        public.menu m
    SET
        sort = t.sort,
        position = t.position,
        level = t.level,
        path = t.path
    FROM
        t
    WHERE
        m.id = t.id
        AND (m.sort != t.sort OR m.position != t.position OR m.level != t.level OR m.path != t.path);

    -- Set session variable to handle recursion
    PERFORM set_config('app.menu', '', true);

    RETURN null;
END;
$$ LANGUAGE plpgsql;

--
-- Page
--

CREATE FUNCTION public.page_menu_delete() RETURNS trigger AS $$
BEGIN
    DELETE FROM public.menu WHERE url = OLD.url;

    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION public.page_menu_update() RETURNS trigger AS $$
BEGIN
    UPDATE public.menu SET url = NEW.url WHERE url = OLD.url;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

--
-- Layout
--

CREATE FUNCTION public.layout_save() RETURNS trigger AS $$
BEGIN
    NEW.block_entity_id := (SELECT entity_id FROM public.block WHERE id = NEW.block_id);

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ---------------------------------------------------------------------------------------------------------------------
-- Trigger
-- ---------------------------------------------------------------------------------------------------------------------

--
-- Account
--

CREATE CONSTRAINT TRIGGER
    account_url_delete
AFTER DELETE ON
    public.account
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW EXECUTE PROCEDURE
    public.entity_url_delete();

CREATE CONSTRAINT TRIGGER
    account_url_insert
AFTER INSERT ON
    public.account
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW EXECUTE PROCEDURE
    public.entity_url_save();

CREATE CONSTRAINT TRIGGER
    account_url_update
AFTER UPDATE OF url ON
    public.account
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW WHEN (NEW.url != OLD.url) EXECUTE PROCEDURE
    public.entity_url_save();

--
-- File
--

CREATE CONSTRAINT TRIGGER
    page_delete_placeholder_file
AFTER DELETE ON
    public.file
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW EXECUTE PROCEDURE
    public.entity_placeholder_delete('app-file', 'public', 'page', 'content');

CREATE CONSTRAINT TRIGGER
    block_delete_placeholder_file
AFTER DELETE ON
    public.file
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW EXECUTE PROCEDURE
    public.entity_placeholder_delete('app-file', 'public', 'block', 'content');

--
-- Menu
--

CREATE TRIGGER
    menu_before
BEFORE INSERT ON
    public.menu
FOR EACH ROW EXECUTE PROCEDURE
    public.menu_before();

CREATE CONSTRAINT TRIGGER
    menu_after
AFTER DELETE OR INSERT ON
    public.menu
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW EXECUTE PROCEDURE
    public.menu_after();

CREATE TRIGGER
    menu_before_update
BEFORE UPDATE OF parent_id, sort ON
    public.menu
FOR EACH ROW WHEN (
    coalesce(NEW.parent_id, 0) != coalesce(OLD.parent_id, 0)
    OR NEW.sort != OLD.sort
) EXECUTE PROCEDURE
    public.menu_before();

CREATE CONSTRAINT TRIGGER
    menu_after_update
AFTER UPDATE OF parent_id, sort ON
    public.menu
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW WHEN (
    coalesce(NEW.parent_id, 0) != coalesce(OLD.parent_id, 0)
    OR NEW.sort != OLD.sort
) EXECUTE PROCEDURE
    public.menu_after();

--
-- Page
--

CREATE CONSTRAINT TRIGGER
    page_menu_delete
AFTER DELETE ON
    public.page
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW EXECUTE PROCEDURE
    public.page_menu_delete();

CREATE CONSTRAINT TRIGGER
    page_menu_update
AFTER UPDATE OF url ON
    public.page
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW WHEN (NEW.url != OLD.url) EXECUTE PROCEDURE
    public.page_menu_update();

CREATE CONSTRAINT TRIGGER
    page_url_delete
AFTER DELETE ON
    public.page
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW EXECUTE PROCEDURE
    public.entity_url_delete();

CREATE CONSTRAINT TRIGGER
    page_url_insert
AFTER INSERT ON
    public.page
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW EXECUTE PROCEDURE
    public.entity_url_save();

CREATE CONSTRAINT TRIGGER
    page_url_update
AFTER UPDATE OF url ON
    public.page
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW WHEN (NEW.url != OLD.url) EXECUTE PROCEDURE
    public.entity_url_save();

--
-- Block
--

CREATE CONSTRAINT TRIGGER
    page_delete_placeholder_block
AFTER DELETE ON
    public.block
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW EXECUTE PROCEDURE
    public.entity_placeholder_delete('app-block', 'public', 'page', 'content');

--
-- Layout
--

CREATE TRIGGER
    layout_save
BEFORE INSERT OR UPDATE ON
    public.layout
FOR EACH ROW EXECUTE PROCEDURE
    public.layout_save();

-- ---------------------------------------------------------------------------------------------------------------------
-- Version
-- ---------------------------------------------------------------------------------------------------------------------

SELECT public.app_version_set('public', 0::text);
SELECT public.app_version_get('public');

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
