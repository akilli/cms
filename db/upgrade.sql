START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Account
-- ---------------------------------------------------------------------------------------------------------------------

ALTER TABLE account RENAME COLUMN role TO role_id;

-- ---------------------------------------------------------------------------------------------------------------------
-- File
-- ---------------------------------------------------------------------------------------------------------------------

ALTER TABLE file RENAME COLUMN ent TO entity;

-- ---------------------------------------------------------------------------------------------------------------------
-- Page
-- ---------------------------------------------------------------------------------------------------------------------

ALTER TABLE page ADD COLUMN meta_title varchar(80) NOT NULL DEFAULT '';
ALTER TABLE page RENAME COLUMN meta TO meta_description;
ALTER TABLE page RENAME COLUMN ent TO entity;

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
