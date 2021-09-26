START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
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
-- $ext_name = extension table name
-- $entity_schema = entity table schema
-- $entity_name = entity table name
-- $entity = [$entity_schema.]$entity_name ($entity_schema might be omitted for $entity_schema = public)
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
-- WHERE $base_schema.entity_id = '$entity';
--
-- CREATE TRIGGER entity_save
-- INSTEAD OF INSERT OR UPDATE ON $entity_schema.$entity_name
-- FOR EACH ROW EXECUTE PROCEDURE public.entity_save('$base_schema', '$base_name', '$ext_schema', '$ext_name');
--
-- CREATE TRIGGER entity_delete
-- INSTEAD OF DELETE ON $entity_schema.$entity_table
-- FOR EACH ROW EXECUTE PROCEDURE public.entity_delete('$base_schema', '$base_name');
-- ---------------------------------------------------------------------------------------------------------------------

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

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
