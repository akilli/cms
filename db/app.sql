START TRANSACTION;

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
-- File
--

CREATE TABLE public.file (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL UNIQUE,
    entity_id varchar(50) NOT NULL,
    mime varchar(255) NOT NULL,
    thumb varchar(255) DEFAULT null UNIQUE,
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
    title varchar(100) NOT NULL DEFAULT '',
    content text NOT NULL DEFAULT '',
    aside text NOT NULL DEFAULT '',
    meta_title varchar(80) NOT NULL DEFAULT '',
    meta_description varchar(300) NOT NULL DEFAULT '',
    slug varchar(75) NOT NULL,
    url varchar(400) UNIQUE DEFAULT null,
    disabled boolean NOT NULL DEFAULT false,
    breadcrumb boolean NOT NULL DEFAULT false,
    menu boolean NOT NULL DEFAULT false,
    parent_id int DEFAULT null REFERENCES public.page ON DELETE CASCADE ON UPDATE CASCADE,
    sort int NOT NULL DEFAULT 0,
    position varchar(255) NOT NULL DEFAULT '',
    level int NOT NULL DEFAULT 0,
    path int[] NOT NULL DEFAULT '{}',
    account_id int DEFAULT null REFERENCES public.account ON DELETE SET null ON UPDATE CASCADE,
    created timestamp(0) NOT NULL DEFAULT current_timestamp,
    UNIQUE (parent_id, slug)
);

CREATE INDEX ON public.page (name);
CREATE INDEX ON public.page (entity_id);
CREATE INDEX ON public.page (slug);
CREATE INDEX ON public.page (parent_id);
CREATE INDEX ON public.page (position);
CREATE INDEX ON public.page (level);
CREATE INDEX ON public.page (account_id);
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
    entity_id varchar(50) NOT NULL,
    block_id int NOT NULL REFERENCES public.block ON DELETE CASCADE ON UPDATE CASCADE,
    page_id int NOT NULL REFERENCES public.page ON DELETE CASCADE ON UPDATE CASCADE,
    parent_id varchar(100) NOT NULL,
    sort int NOT NULL DEFAULT 0,
    created timestamp(0) NOT NULL DEFAULT current_timestamp
);

CREATE INDEX ON public.layout (name);
CREATE INDEX ON public.layout (entity_id);
CREATE INDEX ON public.layout (block_id);
CREATE INDEX ON public.layout (page_id);
CREATE INDEX ON public.layout (parent_id);
CREATE INDEX ON public.layout (sort);
CREATE INDEX ON public.layout (created);

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
-- Generic trigger function for not automatically updatable views
--
-- You could use this generic trigger function for entities that extend a base entity and define additional columns in
-- an extension table as the resulting views are usually not automatically updatable. Anyhow you should prefer dedicated
-- trigger functions over this generic one. It's just too much voodoo going on here.
--
-- Example
-- -------
--
-- Variables:
--
-- $base_schema = base table schema
-- $base_name = base table name
-- $ext_schema = extension table schema
-- $base_schema = extension table name
-- $entity_schema = entity table schema
-- $entity_name = entity table name
-- $entity = [$entity_schema.]$entity_table ($entity_schema might be omitted for $entity_schema = public)
--
-- !!! Please replace the variables in the following example with the real table schemas and names !!!
--
-- CREATE TABLE $ext_schema.$ext_name (
--     id int PRIMARY KEY REFERENCES $base_schema.$base_name ON DELETE CASCADE ON UPDATE CASCADE,
--     additional_column varchar(255) NOT NULL
-- );
--
-- CREATE VIEW $entity_schema.$entity_name AS
-- SELECT *
-- FROM $base_schema.$base_name
-- LEFT JOIN $ext_schema.$ext_name USING (id)
-- WHERE entity_id = '$entity';
--
-- CREATE TRIGGER entity_save
-- INSTEAD OF INSERT OR UPDATE ON $entity_schema.$entity_name
-- FOR EACH ROW EXECUTE PROCEDURE public.entity_save('$base_schema', '$base_name', '$ext_schema', '$ext_name');
--
-- CREATE TRIGGER entity_delete
-- INSTEAD OF DELETE ON $entity_schema.$entity_table
-- FOR EACH ROW EXECUTE PROCEDURE public.entity_delete('$base_schema', '$base_name');
--

CREATE FUNCTION public.entity_save() RETURNS trigger AS $$
    DECLARE
        _attr RECORD;
        _base_name text;
        _base_schema text;
        _cnt int;
        _col text := '';
        _ext_name text;
        _ext_schema text;
        _new jsonb;
        _newVal text;
        _old jsonb;
        _oldVal text;
        _set text := '';
        _sql text := '';
        _val text := '';
    BEGIN
        IF (array_length(TG_ARGV, 1) < 4) THEN
            RAISE EXCEPTION 'You must pass base and extension table as the first two arguments with CREATE TRIGGER';
        END IF;

        _base_schema := TG_ARGV[0];
        _base_name := TG_ARGV[1];
        _ext_schema := TG_ARGV[2];
        _ext_name := TG_ARGV[3];

        IF (TG_TABLE_SCHEMA = 'public') THEN
            NEW.entity_id := TG_TABLE_NAME;
        ELSE
            NEW.entity_id := TG_TABLE_SCHEMA || '.' || TG_TABLE_NAME;
        END IF;

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
                table_schema = _base_schema
                AND table_name = _base_name
                AND column_name != 'id'
            ORDER BY
                ordinal_position ASC
        LOOP
            _newVal := jsonb_extract_path_text(_new, _attr.name);

            IF (TG_OP = 'UPDATE') THEN
                _oldVal := jsonb_extract_path_text(_old, _attr.name);
            ELSE
                _oldVal := null;
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
            _sql := format('UPDATE %I.%I SET id = %L, %s WHERE id = %L', _base_schema, _base_name, NEW.id, _set, OLD.id);
        ELSE
            _sql := format('INSERT INTO %I.%I (%s) VALUES (%s)', _base_schema, _base_name, _col, _val);
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
                table_schema = _ext_schema
                AND table_name = _ext_name
                AND column_name != 'id'
            ORDER BY
                ordinal_position ASC
        LOOP
            _newVal := jsonb_extract_path_text(_new, _attr.name);

            IF (TG_OP = 'UPDATE') THEN
                _oldVal := jsonb_extract_path_text(_old, _attr.name);
            ELSE
                _oldVal := null;
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
                EXECUTE format('SELECT count(id) FROM %I.%I WHERE id = %L', _ext_schema, _ext_name, OLD.id) INTO _cnt;
            ELSE
                _cnt := 0;
            END IF;

            IF (TG_OP = 'UPDATE' AND _cnt > 0) THEN
                _sql := _sql || format(' UPDATE %I.%I e SET %s FROM t WHERE e.id = t.id', _ext_schema, _ext_name, _set);
            ELSE
                _sql := _sql || format(' INSERT INTO %I.%I (id, %s) SELECT id, %s FROM t', _ext_schema, _ext_name, _col, _val);
            END IF;
        ELSIF (_col != '' OR _val != '' OR _set != '') THEN
            RAISE EXCEPTION 'An error occurred with values _col(%), _val(%) and _set(%)', _col, _val, _set;
        END IF;

        EXECUTE _sql;

        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION public.entity_delete() RETURNS trigger AS $$
    BEGIN
        IF (array_length(TG_ARGV, 1) < 2) THEN
            RAISE EXCEPTION 'You must pass the base table schema and name as the first two arguments with CREATE TRIGGER';
        END IF;

        EXECUTE format('DELETE FROM %I.%I WHERE id = %s', TG_ARGV[0], TG_ARGV[1], OLD.id);

        RETURN OLD;
    END;
$$ LANGUAGE plpgsql;

--
-- Page
--

CREATE FUNCTION public.page_menu_before() RETURNS trigger AS $$
    DECLARE
        _cnt int;
    BEGIN
        -- Avoid recursion
        IF (current_setting('app.page_menu', true) != '1') THEN
            RETURN NEW;
        END IF;

        IF (TG_OP = 'UPDATE'
            AND NEW.parent_id IS NOT NULL
            AND (SELECT path @> ARRAY[OLD.id] FROM public.page WHERE id = NEW.parent_id)
        ) THEN
            RAISE EXCEPTION 'Recursion error';
        END IF;

        SELECT
            count(id) + 1
        FROM
            public.page
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

CREATE FUNCTION public.page_menu_after() RETURNS trigger AS $$
    DECLARE
        _ext text := '.html';
        _index text := 'index';
        _pad int := 10;
    BEGIN
        -- Avoid recursion
        IF (current_setting('app.page_menu', true) != '1') THEN
            RETURN null;
        END IF;

        -- Set session variable to handle recursion
        PERFORM set_config('app.page_menu', '1', true);

        -- Remove from old parent
        IF (TG_OP = 'UPDATE' OR TG_OP = 'DELETE') THEN
            UPDATE
                public.page
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
                public.page
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
                       count(id)
                    FROM
                       public.page
                    WHERE
                       coalesce(parent_id, 0) = coalesce(p.parent_id, 0)
                       AND (sort < p.sort OR sort = p.sort AND id < p.id)
                ) + 1 AS sort
            FROM
                public.page p
        ),
        t AS (
            SELECT
                p.id,
                CASE WHEN p.slug = _index THEN '' ELSE '/' || p.slug END AS urlkey,
                CASE WHEN p.slug = _index THEN '/' ELSE '/' || p.slug || _ext END AS url,
                p.menu,
                s.sort,
                LPAD(cast(s.sort AS text), _pad, '0') AS position,
                0 AS level,
                '{}'::int[] || p.id AS path
            FROM
                public.page p
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
                public.page p
            INNER JOIN
                t
                    ON t.id = p.parent_id
            INNER JOIN
                s
                    ON s.id = p.id
        )
        UPDATE
            public.page p
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
            AND (
                p.url != t.url
                OR p.menu != t.menu
                OR p.sort != t.sort
                OR p.position != t.position
                OR p.level != t.level
                OR p.path != t.path
            );

        -- Set session variable to handle recursion
        PERFORM set_config('app.page_menu', '', true);

        RETURN null;
    END;
$$ LANGUAGE plpgsql;

--
-- Layout
--

CREATE FUNCTION public.layout_save() RETURNS trigger AS $$
    BEGIN
        NEW.entity_id := (SELECT entity_id FROM public.block WHERE id = NEW.block_id);

        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

-- ---------------------------------------------------------------------------------------------------------------------
-- Trigger
-- ---------------------------------------------------------------------------------------------------------------------

--
-- Page
--

CREATE TRIGGER
    page_menu_before
BEFORE INSERT ON
    public.page
FOR EACH ROW EXECUTE PROCEDURE
    public.page_menu_before();

CREATE CONSTRAINT TRIGGER
    page_menu_after
AFTER DELETE OR INSERT ON
    public.page
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW EXECUTE PROCEDURE
    public.page_menu_after();

CREATE TRIGGER
    page_menu_before_update
BEFORE UPDATE OF parent_id, sort ON
    public.page
FOR EACH ROW WHEN (
    coalesce(NEW.parent_id, 0) != coalesce(OLD.parent_id, 0)
    OR NEW.sort != OLD.sort
) EXECUTE PROCEDURE
    public.page_menu_before();

CREATE CONSTRAINT TRIGGER
    page_menu_after_update
AFTER UPDATE OF parent_id, sort, slug, menu ON
    public.page
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW WHEN (
    coalesce(NEW.parent_id, 0) != coalesce(OLD.parent_id, 0)
    OR NEW.sort != OLD.sort
    OR NEW.slug != OLD.slug
    OR NEW.menu != OLD.menu
) EXECUTE PROCEDURE
    public.page_menu_after();

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

COMMIT;
