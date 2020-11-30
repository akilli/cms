START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Role
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO
    role
    (id, name, privilege)
VALUES
    (1, 'admin', '{"_all_"}');

SELECT setval('role_id_seq', (SELECT max(id) FROM role));

-- ---------------------------------------------------------------------------------------------------------------------
-- Account
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO
    account
    (id, name, role_id, username, password)
VALUES
    (1, 'Admin', 1, 'admin', '$2y$10$FZSRqIGNKq64P3Rz27jlzuKuSZ9Rik9qHnqk5zH2Z7d67.erqaNhy');

SELECT setval('account_id_seq', (SELECT max(id) FROM account));

-- ---------------------------------------------------------------------------------------------------------------------
-- Page
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO
    contentpage
    (id, name, entity_id, slug, menu)
VALUES
    (1, 'Homepage', 'contentpage', 'index', TRUE);

SELECT setval('page_id_seq', (SELECT max(id) FROM page));

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
