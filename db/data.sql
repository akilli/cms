START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Data
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO
    role
    (id, name, priv)
VALUES
    (1, 'admin', '["_all_"]');

SELECT SETVAL('role_id_seq', 1);

INSERT INTO
    account
    (name, password, role)
VALUES
    ('admin', '$2y$10$FZSRqIGNKq64P3Rz27jlzuKuSZ9Rik9qHnqk5zH2Z7d67.erqaNhy', 1);

INSERT INTO
    url
    (name, target)
VALUES
    ('/', '/app/home');

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
