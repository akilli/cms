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
    mime varchar(255) NOT NULL,
    info text NOT NULL DEFAULT '',
    created timestamp(0) NOT NULL DEFAULT current_timestamp
);

CREATE INDEX ON public.file (created);

--
-- Page
--

CREATE TABLE public.page (
    id serial PRIMARY KEY,
    name varchar(100) NOT NULL,
    url varchar(255) NOT NULL UNIQUE,
    title varchar(100) NOT NULL DEFAULT '',
    meta_title varchar(80) NOT NULL DEFAULT '',
    meta_description varchar(300) NOT NULL DEFAULT '',
    content text NOT NULL DEFAULT '',
    created timestamp(0) NOT NULL DEFAULT current_timestamp
);

CREATE INDEX ON public.page (name);
CREATE INDEX ON public.page (created);

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
-- Trigger Function
-- ---------------------------------------------------------------------------------------------------------------------

--
-- Generic
--

CREATE FUNCTION public.entity_url_delete() RETURNS trigger AS $$
DECLARE
    _ent text;
BEGIN
    _ent := public.app_entity_id(TG_TABLE_SCHEMA, TG_TABLE_NAME);
    DELETE FROM public.url WHERE (target_entity_id, target_id) = (_ent, OLD.id);

    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION public.entity_url_save() RETURNS trigger AS $$
DECLARE
    _cnt int := 0;
    _ent text;
BEGIN
    _ent := public.app_entity_id(TG_TABLE_SCHEMA, TG_TABLE_NAME);

    IF (TG_OP = 'UPDATE') THEN
        SELECT count(id)
        FROM public.url
        WHERE (target_entity_id, target_id, name) = (_ent, OLD.id, OLD.url)
        INTO _cnt;
    END IF;

    IF (TG_OP = 'INSERT' OR _cnt = 0) THEN
        INSERT INTO public.url (name, target_entity_id, target_id)
        VALUES (NEW.url, _ent, NEW.id);
    ELSE
        UPDATE public.url
        SET name = NEW.url
        WHERE (target_entity_id, target_id, name) = (_ent, OLD.id, OLD.url);
    END IF;

    RETURN NEW;
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

    SELECT count(id) + 1
    FROM public.menu
    WHERE coalesce(parent_id, 0) = coalesce(NEW.parent_id, 0)
    INTO _cnt;

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
        UPDATE public.menu
        SET sort = sort - 1
        WHERE id != OLD.id AND coalesce(parent_id, 0) = coalesce(OLD.parent_id, 0) AND sort > OLD.sort;
    END IF;

    -- Add to new parent
    IF (TG_OP = 'INSERT' OR TG_OP = 'UPDATE') THEN
        UPDATE public.menu
        SET sort = sort + 1
        WHERE id != NEW.id AND coalesce(parent_id, 0) = coalesce(NEW.parent_id, 0) AND sort >= NEW.sort;
    END IF;

    -- Update positions
    WITH RECURSIVE
    s AS (
        SELECT
            m.id,
            (
                SELECT count(id)
                FROM public.menu
                WHERE coalesce(parent_id, 0) = coalesce(m.parent_id, 0) AND (sort < m.sort OR sort = m.sort AND id < m.id)
            ) + 1 AS sort
        FROM public.menu m
    ),
    t AS (
        SELECT
            m.id,
            s.sort,
            lpad(cast(s.sort AS text), _pad, '0') AS position,
            1 AS level,
            '{}'::int[] || m.id AS path
        FROM public.menu m
        INNER JOIN s ON s.id = m.id
        WHERE m.parent_id IS NULL
        UNION
        SELECT
            m.id,
            s.sort,
            t.position || '.' || lpad(cast(s.sort AS text), _pad, '0') AS position,
            t.level + 1 AS level,
            t.path || m.id AS path
        FROM public.menu m
        INNER JOIN t ON t.id = m.parent_id
        INNER JOIN s ON s.id = m.id
    )
    UPDATE public.menu m
    SET sort = t.sort, position = t.position, level = t.level, path = t.path
    FROM t
    WHERE m.id = t.id AND (m.sort != t.sort OR m.position != t.position OR m.level != t.level OR m.path != t.path);

    -- Set session variable to handle recursion
    PERFORM set_config('app.menu', '', true);

    RETURN null;
END;
$$ LANGUAGE plpgsql;

--
-- URL
--

CREATE FUNCTION public.url_menu_delete() RETURNS trigger AS $$
BEGIN
    DELETE FROM public.menu WHERE url = OLD.name;
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION public.url_menu_update() RETURNS trigger AS $$
BEGIN
    UPDATE public.menu SET url = NEW.name WHERE url = OLD.name;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ---------------------------------------------------------------------------------------------------------------------
-- Trigger
-- ---------------------------------------------------------------------------------------------------------------------

--
-- Account
--

CREATE CONSTRAINT TRIGGER account_url_delete AFTER DELETE ON public.account DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW EXECUTE PROCEDURE public.entity_url_delete();

CREATE CONSTRAINT TRIGGER account_url_insert AFTER INSERT ON public.account DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW EXECUTE PROCEDURE public.entity_url_save();

CREATE CONSTRAINT TRIGGER account_url_update AFTER UPDATE OF url ON public.account DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW WHEN (NEW.url != OLD.url) EXECUTE PROCEDURE public.entity_url_save();

--
-- Menu
--

CREATE TRIGGER menu_before BEFORE INSERT ON public.menu
FOR EACH ROW EXECUTE PROCEDURE public.menu_before();

CREATE CONSTRAINT TRIGGER menu_after AFTER DELETE OR INSERT ON public.menu DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW EXECUTE PROCEDURE public.menu_after();

CREATE TRIGGER menu_before_update BEFORE UPDATE OF parent_id, sort ON public.menu
FOR EACH ROW WHEN (coalesce(NEW.parent_id, 0) != coalesce(OLD.parent_id, 0) OR NEW.sort != OLD.sort) EXECUTE PROCEDURE public.menu_before();

CREATE CONSTRAINT TRIGGER menu_after_update AFTER UPDATE OF parent_id, sort ON public.menu DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW WHEN (coalesce(NEW.parent_id, 0) != coalesce(OLD.parent_id, 0) OR NEW.sort != OLD.sort) EXECUTE PROCEDURE public.menu_after();

--
-- Page
--

CREATE CONSTRAINT TRIGGER page_url_delete AFTER DELETE ON public.page DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW EXECUTE PROCEDURE public.entity_url_delete();

CREATE CONSTRAINT TRIGGER page_url_insert AFTER INSERT ON public.page DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW EXECUTE PROCEDURE public.entity_url_save();

CREATE CONSTRAINT TRIGGER page_url_update AFTER UPDATE OF url ON public.page DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW WHEN (NEW.url != OLD.url) EXECUTE PROCEDURE public.entity_url_save();

--
-- URL
--

CREATE CONSTRAINT TRIGGER url_menu_delete AFTER DELETE ON public.url DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW EXECUTE PROCEDURE public.url_menu_delete();

CREATE CONSTRAINT TRIGGER url_menu_update AFTER UPDATE OF name ON public.url DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW WHEN (NEW.name != OLD.name) EXECUTE PROCEDURE public.url_menu_update();

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
