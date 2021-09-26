START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Converts given text to UID
--
-- This optional PostgreSQL function mimics the PHP function str\uid() and could be useful during initial migration.
-- ---------------------------------------------------------------------------------------------------------------------

CREATE FUNCTION public.app_uid(val text) RETURNS text AS $$
    BEGIN
        val := lower(val);
        val := replace(val, '₀', '0');
        val := replace(val, '₁', '1');
        val := replace(val, '₂', '2');
        val := replace(val, '₃', '3');
        val := replace(val, '₄', '4');
        val := replace(val, '₅', '5');
        val := replace(val, '₆', '6');
        val := replace(val, '₇', '7');
        val := replace(val, '₈', '8');
        val := replace(val, '₉', '9');
        val := replace(val, '⁰', '0');
        val := replace(val, '¹', '1');
        val := replace(val, '²', '2');
        val := replace(val, '³', '3');
        val := replace(val, '⁴', '4');
        val := replace(val, '⁵', '5');
        val := replace(val, '⁶', '6');
        val := replace(val, '⁷', '7');
        val := replace(val, '⁸', '8');
        val := replace(val, '⁹', '9');
        val := replace(val, 'à', 'a');
        val := replace(val, 'á', 'a');
        val := replace(val, 'â', 'a');
        val := replace(val, 'ã', 'a');
        val := replace(val, 'å', 'a');
        val := replace(val, 'ǻ', 'a');
        val := replace(val, 'ă', 'a');
        val := replace(val, 'ǎ', 'a');
        val := replace(val, 'ª', 'a');
        val := replace(val, 'æ', 'ae');
        val := replace(val, 'ǽ', 'ae');
        val := replace(val, 'ä', 'ae');
        val := replace(val, 'ĉ', 'c');
        val := replace(val, 'ċ', 'c');
        val := replace(val, 'ð', 'dj');
        val := replace(val, 'đ', 'd');
        val := replace(val, 'è', 'e');
        val := replace(val, 'é', 'e');
        val := replace(val, 'ê', 'e');
        val := replace(val, 'ë', 'e');
        val := replace(val, 'ĕ', 'e');
        val := replace(val, 'ė', 'e');
        val := replace(val, 'ƒ', 'f');
        val := replace(val, 'ĝ', 'g');
        val := replace(val, 'ġ', 'g');
        val := replace(val, 'ĥ', 'h');
        val := replace(val, 'ħ', 'h');
        val := replace(val, 'ì', 'i');
        val := replace(val, 'í', 'i');
        val := replace(val, 'î', 'i');
        val := replace(val, 'ï', 'i');
        val := replace(val, 'ĩ', 'i');
        val := replace(val, 'ĭ', 'i');
        val := replace(val, 'ǐ', 'i');
        val := replace(val, 'į', 'i');
        val := replace(val, 'ĳ', 'ij');
        val := replace(val, 'ĵ', 'j');
        val := replace(val, 'ĺ', 'l');
        val := replace(val, 'ľ', 'l');
        val := replace(val, 'ŀ', 'l');
        val := replace(val, 'ñ', 'n');
        val := replace(val, 'ŉ', 'n');
        val := replace(val, 'ò', 'o');
        val := replace(val, 'ô', 'o');
        val := replace(val, 'õ', 'o');
        val := replace(val, 'ō', 'o');
        val := replace(val, 'ŏ', 'o');
        val := replace(val, 'ǒ', 'o');
        val := replace(val, 'ő', 'o');
        val := replace(val, 'ơ', 'o');
        val := replace(val, 'ø', 'o');
        val := replace(val, 'ǿ', 'o');
        val := replace(val, 'º', 'o');
        val := replace(val, 'œ', 'oe');
        val := replace(val, 'ö', 'oe');
        val := replace(val, 'ŕ', 'r');
        val := replace(val, 'ŗ', 'r');
        val := replace(val, 'ŝ', 's');
        val := replace(val, 'ș', 's');
        val := replace(val, 'ſ', 's');
        val := replace(val, 'ß', 'ss');
        val := replace(val, 'ţ', 't');
        val := replace(val, 'ț', 't');
        val := replace(val, 'ŧ', 't');
        val := replace(val, 'þ', 'th');
        val := replace(val, 'ù', 'u');
        val := replace(val, 'ú', 'u');
        val := replace(val, 'û', 'u');
        val := replace(val, 'ũ', 'u');
        val := replace(val, 'ŭ', 'u');
        val := replace(val, 'ű', 'u');
        val := replace(val, 'ų', 'u');
        val := replace(val, 'ư', 'u');
        val := replace(val, 'ǔ', 'u');
        val := replace(val, 'ǖ', 'u');
        val := replace(val, 'ǘ', 'u');
        val := replace(val, 'ǚ', 'u');
        val := replace(val, 'ǜ', 'u');
        val := replace(val, 'ü', 'ue');
        val := replace(val, 'ŵ', 'w');
        val := replace(val, 'ý', 'y');
        val := replace(val, 'ÿ', 'y');
        val := replace(val, 'ŷ', 'y');
        val := regexp_replace(val, '[^a-z0-9-]+', '-', 'g');

        RETURN trim(BOTH '-' FROM val);
    END;
$$ LANGUAGE plpgsql;

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
