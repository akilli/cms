START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Rename old schema to old
-- ---------------------------------------------------------------------------------------------------------------------

ALTER SCHEMA public RENAME TO old;
CREATE SCHEMA public;

-- ---------------------------------------------------------------------------------------------------------------------
-- Execute app.sql from cms
-- ---------------------------------------------------------------------------------------------------------------------

-- Content of app.sql without first (START TRANSACTION;) and last (COMMIT;) line

-- ---------------------------------------------------------------------------------------------------------------------
-- Role
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO
    role
    (
        id,
        name,
        priv
    )
SELECT
    id,
    name,
    priv
FROM
    old.role
ORDER BY
    id ASC;

-- ---------------------------------------------------------------------------------------------------------------------
-- Account
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO
    account
    (
        id,
        name,
        password,
        role_id
    )
SELECT
    id,
    name,
    password,
    role_id
FROM
    old.account
ORDER BY
    id ASC;

-- ---------------------------------------------------------------------------------------------------------------------
-- File
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO
    file
    (
        id,
        name,
        info,
        entity
    )
SELECT
    id,
    name,
    info,
    entity
FROM
    old.file
ORDER BY
    id ASC;

-- ---------------------------------------------------------------------------------------------------------------------
-- Page
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO
    page
    (
        id,
        name,
        image,
        teaser,
        main,
        aside,
        sidebar,
        meta_title,
        meta_description,
        layout,
        slug,
        disabled,
        menu,
        menu_name,
        parent_id,
        sort,
        status,
        timestamp,
        date,
        entity
    )
SELECT
    id,
    name,
    image,
    teaser,
    main,
    aside,
    sidebar,
    meta_title,
    meta_description,
    layout,
    slug,
    disabled,
    menu,
    menu_name,
    parent_id,
    sort,
    status,
    timestamp,
    date,
    entity
FROM
    old.page
ORDER BY
    id ASC;

-- ---------------------------------------------------------------------------------------------------------------------
-- Drop old schema
-- ---------------------------------------------------------------------------------------------------------------------

DROP SCHEMA old CASCADE;

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
