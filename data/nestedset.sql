START TRANSACTION;

CREATE FUNCTION node_save_before() RETURNS trigger AS
$$
    DECLARE
        _baseLevel integer;
        _baseLft integer;
        _baseRgt integer;
        _lft integer;
        _range integer;
    BEGIN
        -- Validate postion
        IF (NEW.pos !~ '^([0-9]+):([0-9]+)$') THEN
            RAISE EXCEPTION 'Invalid position: %', NEW.pos;
        END IF;

        NEW.root_id := CAST(split_part(NEW.pos, ':', 1) AS integer);
        _baseLft := CAST(split_part(NEW.pos, ':', 2) AS integer);

        IF (_baseLft < 0) THEN
            RAISE EXCEPTION 'Invalid position: %', NEW.pos;
        END IF;

        -- Set tree attributes
        SELECT rgt, level INTO _baseRgt, _baseLevel FROM node WHERE root_id = NEW.root_id AND lft = _baseLft;

        IF (_baseLft = 0) THEN
            SELECT COALESCE(MAX(rgt), 0) + 1 INTO _lft FROM node WHERE root_id = NEW.root_id;
            NEW.level := 1;
        ELSEIF (TG_OP = 'UPDATE' AND OLD.root_id = NEW.root_id AND OLD.lft < _baseLft AND OLD.rgt > _baseRgt) THEN
            RAISE EXCEPTION 'Node can not be child of itself';
        ELSEIF (NEW.mode = 'child') THEN
            _lft := _baseRgt;
            NEW.level := _baseLevel + 1;
        ELSEIF (NEW.mode = 'before') THEN
            _lft := _baseLft;
            NEW.level := _baseLevel;
        ELSE
            _lft := _baseRgt + 1;
            NEW.level := _baseLevel;
        END IF;

        IF (TG_OP = 'UPDATE') THEN
            _range := OLD.rgt - OLD.lft + 1;

            IF (NEW.root_id = OLD.root_id AND _lft > OLD.lft) THEN
                _lft := _lft - _range;
            END IF;
        ELSE
            _range := 2;
        END IF;

        IF (TG_OP = 'UPDATE' AND NEW.root_id = OLD.root_id AND _lft = OLD.lft) THEN
            NEW.lft := OLD.lft;
            NEW.rgt := OLD.rgt;
        ELSE
            NEW.lft = -1 * _lft;
            NEW.rgt := -1 * (_lft + _range - 1);
        END IF;

        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER node_save_before BEFORE INSERT OR UPDATE ON node FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE node_save_before();

-- -----------------------------------------------------------

CREATE FUNCTION node_save_after() RETURNS trigger AS
$$
    DECLARE
        _diff integer;
        _lft integer;
        _rgt integer;
        _range integer;
    BEGIN
        -- No change in postion
        IF (TG_OP = 'UPDATE' AND NEW.root_id = OLD.root_id AND NEW.lft = OLD.lft) THEN
            RETURN NULL;
        END IF;

        -- Vars
        _lft := -1 * NEW.lft;
        _rgt := -1 * NEW.rgt;
        _range := _rgt - _lft + 1;

        -- Move from old tree
        IF (TG_OP = 'UPDATE') THEN
            _diff := _lft - OLD.lft;
            UPDATE node SET root_id = NEW.root_id, lft = -1 * (lft + _diff), rgt = -1 * (rgt + _diff) WHERE root_id = OLD.root_id AND lft BETWEEN OLD.lft AND OLD.rgt;
            UPDATE node SET lft = lft - _range WHERE root_id = OLD.root_id AND lft > OLD.rgt;
            UPDATE node SET rgt = rgt - _range WHERE root_id = OLD.root_id AND rgt > OLD.rgt;
        END IF;

        -- Add to new tree
        UPDATE node SET lft = lft + _range WHERE root_id = NEW.root_id AND lft >= _lft;
        UPDATE node SET rgt = rgt + _range WHERE root_id = NEW.root_id AND rgt >= _lft;

        IF (TG_OP = 'UPDATE') THEN
            _diff := NEW.level - OLD.level;
        ELSE
            _diff := 0;
        END IF;

        UPDATE node SET lft = -1 * lft, rgt = -1 * rgt, level = level + _diff WHERE root_id = NEW.root_id AND lft < 0;

        RETURN NULL;
    END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER node_save_after AFTER INSERT OR UPDATE ON node FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE node_save_after();

-- -----------------------------------------------------------

CREATE FUNCTION node_delete_after() RETURNS trigger AS
$$
    DECLARE
        _range integer;
    BEGIN
        -- Delete affected nodes
        DELETE FROM node WHERE root_id = OLD.root_id AND lft BETWEEN OLD.lft AND OLD.rgt;
        -- Close gap in old tree
        _range := OLD.rgt - OLD.lft + 1;
        UPDATE node SET lft = lft - _range WHERE root_id = OLD.root_id AND lft > OLD.rgt;
        UPDATE node SET rgt = rgt - _range WHERE root_id = OLD.root_id AND rgt > OLD.rgt;

        RETURN NULL;
    END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER node_delete_after AFTER DELETE ON node FOR EACH ROW WHEN (pg_trigger_depth() = 0) EXECUTE PROCEDURE node_delete_after();

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
