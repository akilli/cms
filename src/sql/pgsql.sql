BEGIN TRANSACTION;

-- --------------------------------------------------------

--
-- Structure for table "account"
--

DROP TABLE IF EXISTS "account";
CREATE TABLE "account" (
    "id" SERIAL,
    "name" VARCHAR(255) NOT NULL,
    "password" VARCHAR(255) NOT NULL,
    "role_id" INTEGER NOT NULL,
    "is_active" BOOLEAN NOT NULL DEFAULT '0',
    "is_system" BOOLEAN NOT NULL DEFAULT '0',
    "created" TIMESTAMP NOT NULL,
    "creator" INTEGER DEFAULT NULL,
    "modified" TIMESTAMP NOT NULL,
    "modifier" INTEGER DEFAULT NULL,
    PRIMARY KEY ("id"),
    UNIQUE ("name")
);

--
-- Indexes for table "account"
--

CREATE INDEX "idx_account_role" ON "account" ("role_id");
CREATE INDEX "idx_account_active" ON "account" ("is_active");
CREATE INDEX "idx_account_system" ON "account" ("is_system");
CREATE INDEX "idx_account_created" ON "account" ("created");
CREATE INDEX "idx_account_creator" ON "account" ("creator");
CREATE INDEX "idx_account_modified" ON "account" ("modified");
CREATE INDEX "idx_account_modifier" ON "account" ("modifier");

--
-- Data for table "account"
--

INSERT INTO "account" ("id", "name", "password", "role_id", "is_active", "is_system", "created", "creator", "modified", "modifier") VALUES
(0, 'Anonymous', '', 0, '1', '1', NOW(), NULL, NOW(), NULL),
(1, 'admin', '$2y$10$9wnkOfY1qLvz0sRXG5G.d.rf2NhCU8a9m.XrLYIgeQA.SioSWwtsW', 1, '1', '1', NOW(), NULL, NOW(), NULL);

-- --------------------------------------------------------

--
-- Structure for table "attribute"
--

DROP TABLE IF EXISTS "attribute";
CREATE TABLE "attribute" (
    "id" VARCHAR(100) NOT NULL,
    "name" VARCHAR(255) NOT NULL,
    "type" VARCHAR(100) NOT NULL,
    "description" TEXT NOT NULL,
    "foreign_entity_id" VARCHAR(100) DEFAULT NULL,
    "options_callback" VARCHAR(255) DEFAULT NULL,
    "options" TEXT,
    "created" TIMESTAMP NOT NULL,
    "creator" INTEGER DEFAULT NULL,
    "modified" TIMESTAMP NOT NULL,
    "modifier" INTEGER DEFAULT NULL,
    PRIMARY KEY ("id")
);
--
-- Indexes for table "attribute"
--

CREATE INDEX "idx_attribute_name" ON "attribute" ("name");
CREATE INDEX "idx_attribute_type" ON "attribute" ("type");
CREATE INDEX "idx_attribute_foreign" ON "attribute" ("foreign_entity_id");
CREATE INDEX "idx_attribute_created" ON "attribute" ("created");
CREATE INDEX "idx_attribute_creator" ON "attribute" ("creator");
CREATE INDEX "idx_attribute_modified" ON "attribute" ("modified");
CREATE INDEX "idx_attribute_modifier" ON "attribute" ("modifier");

-- --------------------------------------------------------

--
-- Structure for table "content"
--

DROP TABLE IF EXISTS "content";
CREATE TABLE "content" (
    "id" SERIAL,
    "name" VARCHAR(255) NOT NULL,
    "entity_id" VARCHAR(100) NOT NULL,
    "is_active" BOOLEAN NOT NULL DEFAULT '0',
    "meta" TEXT,
    "created" TIMESTAMP NOT NULL,
    "creator" INTEGER DEFAULT NULL,
    "modified" TIMESTAMP NOT NULL,
    "modifier" INTEGER DEFAULT NULL,
    PRIMARY KEY ("id")
);
--
-- Indexes for table "content"
--

CREATE INDEX "idx_content_name" ON "content" ("name");
CREATE INDEX "idx_content_entity" ON "content" ("entity_id");
CREATE INDEX "idx_content_active" ON "content" ("is_active");
CREATE INDEX "idx_content_created" ON "content" ("created");
CREATE INDEX "idx_content_creator" ON "content" ("creator");
CREATE INDEX "idx_content_modified" ON "content" ("modified");
CREATE INDEX "idx_content_modifier" ON "content" ("modifier");

-- --------------------------------------------------------

--
-- Structure for table "eav"
--

DROP TABLE IF EXISTS "eav";
CREATE TABLE "eav" (
    "id" SERIAL,
    "entity_id" VARCHAR(100) NOT NULL,
    "attribute_id" VARCHAR(100) NOT NULL,
    "content_id" INTEGER NOT NULL,
    "value_bool" BOOLEAN DEFAULT NULL,
    "value_datetime" TIMESTAMP DEFAULT NULL,
    "value_decimal" DECIMAL(12,4) DEFAULT NULL,
    "value_int" INTEGER DEFAULT NULL,
    "value_text" TEXT,
    "value_varchar" VARCHAR(255) DEFAULT NULL,
    "created" TIMESTAMP NOT NULL,
    "creator" INTEGER DEFAULT NULL,
    "modified" TIMESTAMP NOT NULL,
    "modifier" INTEGER DEFAULT NULL,
    PRIMARY KEY ("id"),
    UNIQUE ("attribute_id", "content_id")
);
--
-- Indexes for table "eav"
--

CREATE INDEX "idx_eav_entity" ON "eav" ("entity_id");
CREATE INDEX "idx_eav_attribute" ON "eav" ("attribute_id");
CREATE INDEX "idx_eav_content" ON "eav" ("content_id");
CREATE INDEX "idx_eav_created" ON "eav" ("created");
CREATE INDEX "idx_eav_creator" ON "eav" ("creator");
CREATE INDEX "idx_eav_modified" ON "eav" ("modified");
CREATE INDEX "idx_eav_modifier" ON "eav" ("modifier");

-- --------------------------------------------------------

--
-- Structure for table "entity"
--

DROP TABLE IF EXISTS "entity";
CREATE TABLE "entity" (
    "id" VARCHAR(100) NOT NULL,
    "name" VARCHAR(255) NOT NULL,
    "description" TEXT NOT NULL,
    "actions" TEXT,
    "toolbar" VARCHAR(255) NOT NULL,
    "sort_order" INTEGER NOT NULL DEFAULT '0',
    "is_system" BOOLEAN NOT NULL DEFAULT '0',
    "created" TIMESTAMP NOT NULL,
    "creator" INTEGER DEFAULT NULL,
    "modified" TIMESTAMP NOT NULL,
    "modifier" INTEGER DEFAULT NULL,
    PRIMARY KEY ("id")
);
--
-- Indexes for table "entity"
--

CREATE INDEX "idx_entity_name" ON "entity" ("name");
CREATE INDEX "idx_entity_toolbar" ON "entity" ("toolbar");
CREATE INDEX "idx_entity_sort" ON "entity" ("sort_order");
CREATE INDEX "idx_entity_system" ON "entity" ("is_system");
CREATE INDEX "idx_entity_created" ON "entity" ("created");
CREATE INDEX "idx_entity_creator" ON "entity" ("creator");
CREATE INDEX "idx_entity_modified" ON "entity" ("modified");
CREATE INDEX "idx_entity_modifier" ON "entity" ("modifier");

-- --------------------------------------------------------

--
-- Structure for table "menu"
--

DROP TABLE IF EXISTS "menu";
CREATE TABLE "menu" (
    "id" SERIAL,
    "name" VARCHAR(255) NOT NULL,
    "target" VARCHAR(255) NOT NULL,
    "root_id" VARCHAR(100) NOT NULL,
    "lft" INTEGER NOT NULL,
    "rgt" INTEGER NOT NULL,
    "is_system" BOOLEAN NOT NULL DEFAULT '0',
    "created" TIMESTAMP NOT NULL,
    "creator" INTEGER DEFAULT NULL,
    "modified" TIMESTAMP NOT NULL,
    "modifier" INTEGER DEFAULT NULL,
    PRIMARY KEY ("id")
);
--
-- Indexes for table "menu"
--

CREATE INDEX "idx_menu_name" ON "menu" ("name");
CREATE INDEX "idx_menu_target" ON "menu" ("target");
CREATE INDEX "idx_menu_root" ON "menu" ("root_id");
CREATE INDEX "idx_menu_lft" ON "menu" ("lft");
CREATE INDEX "idx_menu_rgt" ON "menu" ("rgt");
CREATE INDEX "idx_menu_item" ON "menu" ("root_id", "lft", "rgt");
CREATE INDEX "idx_menu_system" ON "menu" ("is_system");
CREATE INDEX "idx_menu_created" ON "menu" ("created");
CREATE INDEX "idx_menu_creator" ON "menu" ("creator");
CREATE INDEX "idx_menu_modified" ON "menu" ("modified");
CREATE INDEX "idx_menu_modifier" ON "menu" ("modifier");

-- --------------------------------------------------------

--
-- Structure for table "menu_root"
--

DROP TABLE IF EXISTS "menu_root";
CREATE TABLE "menu_root" (
    "id" VARCHAR(100) NOT NULL,
    "name" VARCHAR(100) NOT NULL,
    "is_system" BOOLEAN NOT NULL DEFAULT '0',
    "created" TIMESTAMP NOT NULL,
    "creator" INTEGER DEFAULT NULL,
    "modified" TIMESTAMP NOT NULL,
    "modifier" INTEGER DEFAULT NULL,
    PRIMARY KEY ("id")
);
--
-- Indexes for table "menu_root"
--

CREATE INDEX "idx_menu_root_name" ON "menu_root" ("name");
CREATE INDEX "idx_menu_root_system" ON "menu_root" ("is_system");
CREATE INDEX "idx_menu_root_created" ON "menu_root" ("created");
CREATE INDEX "idx_menu_root_creator" ON "menu_root" ("creator");
CREATE INDEX "idx_menu_root_modified" ON "menu_root" ("modified");
CREATE INDEX "idx_menu_root_modifier" ON "menu_root" ("modifier");

-- --------------------------------------------------------

--
-- Structure for table "metadata"
--

DROP TABLE IF EXISTS "metadata";
CREATE TABLE "metadata" (
    "id" SERIAL,
    "entity_id" VARCHAR(100) NOT NULL,
    "attribute_id" VARCHAR(100) NOT NULL,
    "sort_order" INTEGER NOT NULL DEFAULT '0',
    "actions" TEXT,
    "is_required" BOOLEAN NOT NULL DEFAULT '0',
    "is_unique" BOOLEAN NOT NULL DEFAULT '0',
    "created" TIMESTAMP NOT NULL,
    "creator" INTEGER DEFAULT NULL,
    "modified" TIMESTAMP NOT NULL,
    "modifier" INTEGER DEFAULT NULL,
    PRIMARY KEY ("id"),
    UNIQUE ("entity_id", "attribute_id")
);
--
-- Indexes for table "metadata"
--

CREATE INDEX "idx_metadata_entity" ON "metadata" ("entity_id");
CREATE INDEX "idx_metadata_attribute" ON "metadata" ("attribute_id");
CREATE INDEX "idx_metadata_sort" ON "metadata" ("sort_order");
CREATE INDEX "idx_metadata_required" ON "metadata" ("is_required");
CREATE INDEX "idx_metadata_unique" ON "metadata" ("is_unique");
CREATE INDEX "idx_metadata_created" ON "metadata" ("created");
CREATE INDEX "idx_metadata_creator" ON "metadata" ("creator");
CREATE INDEX "idx_metadata_modified" ON "metadata" ("modified");
CREATE INDEX "idx_metadata_modifier" ON "metadata" ("modifier");

-- --------------------------------------------------------

--
-- Structure for table "rewrite"
--

DROP TABLE IF EXISTS "rewrite";
CREATE TABLE "rewrite" (
    "id" VARCHAR(255) NOT NULL,
    "target" VARCHAR(255) NOT NULL,
    "is_redirect" BOOLEAN NOT NULL DEFAULT '0',
    "is_system" BOOLEAN NOT NULL DEFAULT '0',
    "created" TIMESTAMP NOT NULL,
    "creator" INTEGER DEFAULT NULL,
    "modified" TIMESTAMP NOT NULL,
    "modifier" INTEGER DEFAULT NULL,
    PRIMARY KEY ("id")
);
--
-- Indexes for table "rewrite"
--

CREATE INDEX "idx_rewrite_target" ON "rewrite" ("target");
CREATE INDEX "idx_rewrite_redirect" ON "rewrite" ("is_redirect");
CREATE INDEX "idx_rewrite_system" ON "rewrite" ("is_system");
CREATE INDEX "idx_rewrite_created" ON "rewrite" ("created");
CREATE INDEX "idx_rewrite_creator" ON "rewrite" ("creator");
CREATE INDEX "idx_rewrite_modified" ON "rewrite" ("modified");
CREATE INDEX "idx_rewrite_modifier" ON "rewrite" ("modifier");

-- --------------------------------------------------------

--
-- Structure for table "role"
--

DROP TABLE IF EXISTS "role";
CREATE TABLE "role" (
    "id" SERIAL,
    "name" VARCHAR(255) NOT NULL,
    "privilege" TEXT,
    "is_active" BOOLEAN NOT NULL DEFAULT '0',
    "is_system" BOOLEAN NOT NULL DEFAULT '0',
    "created" TIMESTAMP NOT NULL,
    "creator" INTEGER DEFAULT NULL,
    "modified" TIMESTAMP NOT NULL,
    "modifier" INTEGER DEFAULT NULL,
    PRIMARY KEY ("id"),
    UNIQUE ("name")
);
--
-- Indexes for table "role"
--

CREATE INDEX "idx_role_active" ON "role" ("is_active");
CREATE INDEX "idx_role_system" ON "role" ("is_system");
CREATE INDEX "idx_role_created" ON "role" ("created");
CREATE INDEX "idx_role_creator" ON "role" ("creator");
CREATE INDEX "idx_role_modified" ON "role" ("modified");
CREATE INDEX "idx_role_modifier" ON "role" ("modifier");

--
-- Data for table "role"
--

INSERT INTO "role" ("id", "name", "privilege", "is_active", "is_system", "created", "creator", "modified", "modifier") VALUES
(0, 'Anonymous', NULL, '1', '1', NOW(), NULL, NOW(), NULL),
(1, 'Administrator', '["all"]', '1', '1', NOW(), NULL, NOW(), NULL);

-- --------------------------------------------------------

--
-- Structure for table "search"
--

DROP TABLE IF EXISTS "search";
CREATE TABLE "search" (
    "id" SERIAL,
    "entity_id" VARCHAR(100) NOT NULL,
    "content_id" VARCHAR(255) NOT NULL,
    "content" TEXT NOT NULL,
    "created" TIMESTAMP NOT NULL,
    "creator" INTEGER DEFAULT NULL,
    "modified" TIMESTAMP NOT NULL,
    "modifier" INTEGER DEFAULT NULL,
    PRIMARY KEY ("id"),
    UNIQUE ("entity_id", "content_id")
);
--
-- Indexes for table "search"
--

CREATE INDEX "idx_search_entity" ON "search" ("entity_id");
CREATE INDEX "idx_search_content" ON "search" ("content_id");
CREATE INDEX "idx_search_created" ON "search" ("created");
CREATE INDEX "idx_search_creator" ON "search" ("creator");
CREATE INDEX "idx_search_modified" ON "search" ("modified");
CREATE INDEX "idx_search_modifier" ON "search" ("modifier");

-- --------------------------------------------------------

--
-- Constraints for table "account"
--
ALTER TABLE "account"
    ADD CONSTRAINT "con_account_role" FOREIGN KEY ("role_id") REFERENCES "role" ("id"),
    ADD CONSTRAINT "con_account_creator" FOREIGN KEY ("creator") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT "con_account_modifier" FOREIGN KEY ("modifier") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table "attribute"
--
ALTER TABLE "attribute"
    ADD CONSTRAINT "con_attribute_creator" FOREIGN KEY ("creator") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT "con_attribute_modifier" FOREIGN KEY ("modifier") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table "content"
--
ALTER TABLE "content"
    ADD CONSTRAINT "con_content_entity" FOREIGN KEY ("entity_id") REFERENCES "entity" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT "con_content_creator" FOREIGN KEY ("creator") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT "con_content_modifier" FOREIGN KEY ("modifier") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table "eav"
--
ALTER TABLE "eav"
    ADD CONSTRAINT "con_eav_entity" FOREIGN KEY ("entity_id") REFERENCES "entity" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT "con_eav_attribute" FOREIGN KEY ("attribute_id") REFERENCES "attribute" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT "con_eav_content" FOREIGN KEY ("content_id") REFERENCES "content" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT "con_eav_creator" FOREIGN KEY ("creator") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT "con_eav_modifier" FOREIGN KEY ("modifier") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table "entity"
--
ALTER TABLE "entity"
    ADD CONSTRAINT "con_entity_creator" FOREIGN KEY ("creator") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT "con_entity_modifier" FOREIGN KEY ("modifier") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table "menu"
--
ALTER TABLE "menu"
    ADD CONSTRAINT "con_menu_root" FOREIGN KEY ("root_id") REFERENCES "menu_root" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT "con_menu_creator" FOREIGN KEY ("creator") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT "con_menu_modifier" FOREIGN KEY ("modifier") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table "menu_root"
--
ALTER TABLE "menu_root"
    ADD CONSTRAINT "con_menu_root_creator" FOREIGN KEY ("creator") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT "con_menu_root_modifier" FOREIGN KEY ("modifier") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table "metadata"
--
ALTER TABLE "metadata"
    ADD CONSTRAINT "con_metadata_entity" FOREIGN KEY ("entity_id") REFERENCES "entity" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT "con_metadata_attribute" FOREIGN KEY ("attribute_id") REFERENCES "attribute" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT "con_metadata_creator" FOREIGN KEY ("creator") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT "con_metadata_modifier" FOREIGN KEY ("modifier") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table "rewrite"
--
ALTER TABLE "rewrite"
    ADD CONSTRAINT "con_rewrite_creator" FOREIGN KEY ("creator") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT "con_rewrite_modifier" FOREIGN KEY ("modifier") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table "role"
--
ALTER TABLE "role"
    ADD CONSTRAINT "con_role_creator" FOREIGN KEY ("creator") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT "con_role_modifier" FOREIGN KEY ("modifier") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table "search"
--
ALTER TABLE "search"
    ADD CONSTRAINT "con_search_creator" FOREIGN KEY ("creator") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT "con_search_modifier" FOREIGN KEY ("modifier") REFERENCES "account" ("id") ON DELETE SET NULL ON UPDATE CASCADE;

-- --------------------------------------------------------

COMMIT;
