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
    uid varchar(50) NOT NULL UNIQUE,
    url varchar(52) NOT NULL GENERATED ALWAYS AS ('/~' || uid) STORED,
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
    url varchar(400) DEFAULT null,
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
    url varchar(400) NOT NULL UNIQUE,
    title varchar(100) NOT NULL DEFAULT '',
    content text NOT NULL DEFAULT '',
    aside text NOT NULL DEFAULT '',
    meta_title varchar(80) NOT NULL DEFAULT '',
    meta_description varchar(300) NOT NULL DEFAULT '',
    created timestamp(0) NOT NULL DEFAULT current_timestamp
);

CREATE INDEX ON public.page (name);
CREATE INDEX ON public.page (entity_id);
CREATE INDEX ON public.page (url);
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
-- Page
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
        _pad int := 10;
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
