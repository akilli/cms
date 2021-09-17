START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Role
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO
    public.role
    (id, name, privilege)
VALUES
    (1, 'admin', '{"_all_"}');

SELECT setval('public.role_id_seq', (SELECT max(id) FROM public.role));

-- ---------------------------------------------------------------------------------------------------------------------
-- Account
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO
    public.account
    (id, name, role_id, username, password, active)
VALUES
    (1, 'admin', 1, 'admin', '$2y$10$FZSRqIGNKq64P3Rz27jlzuKuSZ9Rik9qHnqk5zH2Z7d67.erqaNhy', true);

SELECT setval('public.account_id_seq', (SELECT max(id) FROM public.account));

-- ---------------------------------------------------------------------------------------------------------------------
-- Page
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO
    public.contentpage
    (id, name, entity_id, url, menu)
VALUES
    (1, 'Homepage', 'contentpage', '/', true);

SELECT setval('public.page_id_seq', (SELECT max(id) FROM public.page));

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
