START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Converts given text to UID
--
-- This optional PostgreSQL function mimics the PHP function str\uid() and could be useful during initial migration.
-- ---------------------------------------------------------------------------------------------------------------------

CREATE FUNCTION public.app_uid(_val text) RETURNS text AS $$
BEGIN
    _val := lower(_val);
    _val := replace(_val, '₀', '0');
    _val := replace(_val, '₁', '1');
    _val := replace(_val, '₂', '2');
    _val := replace(_val, '₃', '3');
    _val := replace(_val, '₄', '4');
    _val := replace(_val, '₅', '5');
    _val := replace(_val, '₆', '6');
    _val := replace(_val, '₇', '7');
    _val := replace(_val, '₈', '8');
    _val := replace(_val, '₉', '9');
    _val := replace(_val, '⁰', '0');
    _val := replace(_val, '¹', '1');
    _val := replace(_val, '²', '2');
    _val := replace(_val, '³', '3');
    _val := replace(_val, '⁴', '4');
    _val := replace(_val, '⁵', '5');
    _val := replace(_val, '⁶', '6');
    _val := replace(_val, '⁷', '7');
    _val := replace(_val, '⁸', '8');
    _val := replace(_val, '⁹', '9');
    _val := replace(_val, 'à', 'a');
    _val := replace(_val, 'á', 'a');
    _val := replace(_val, 'â', 'a');
    _val := replace(_val, 'ã', 'a');
    _val := replace(_val, 'å', 'a');
    _val := replace(_val, 'ǻ', 'a');
    _val := replace(_val, 'ă', 'a');
    _val := replace(_val, 'ǎ', 'a');
    _val := replace(_val, 'ª', 'a');
    _val := replace(_val, 'æ', 'ae');
    _val := replace(_val, 'ǽ', 'ae');
    _val := replace(_val, 'ä', 'ae');
    _val := replace(_val, 'ĉ', 'c');
    _val := replace(_val, 'ċ', 'c');
    _val := replace(_val, 'ð', 'dj');
    _val := replace(_val, 'đ', 'd');
    _val := replace(_val, 'è', 'e');
    _val := replace(_val, 'é', 'e');
    _val := replace(_val, 'ê', 'e');
    _val := replace(_val, 'ë', 'e');
    _val := replace(_val, 'ĕ', 'e');
    _val := replace(_val, 'ė', 'e');
    _val := replace(_val, 'ƒ', 'f');
    _val := replace(_val, 'ĝ', 'g');
    _val := replace(_val, 'ġ', 'g');
    _val := replace(_val, 'ĥ', 'h');
    _val := replace(_val, 'ħ', 'h');
    _val := replace(_val, 'ì', 'i');
    _val := replace(_val, 'í', 'i');
    _val := replace(_val, 'î', 'i');
    _val := replace(_val, 'ï', 'i');
    _val := replace(_val, 'ĩ', 'i');
    _val := replace(_val, 'ĭ', 'i');
    _val := replace(_val, 'ǐ', 'i');
    _val := replace(_val, 'į', 'i');
    _val := replace(_val, 'ĳ', 'ij');
    _val := replace(_val, 'ĵ', 'j');
    _val := replace(_val, 'ĺ', 'l');
    _val := replace(_val, 'ľ', 'l');
    _val := replace(_val, 'ŀ', 'l');
    _val := replace(_val, 'ñ', 'n');
    _val := replace(_val, 'ŉ', 'n');
    _val := replace(_val, 'ò', 'o');
    _val := replace(_val, 'ô', 'o');
    _val := replace(_val, 'õ', 'o');
    _val := replace(_val, 'ō', 'o');
    _val := replace(_val, 'ŏ', 'o');
    _val := replace(_val, 'ǒ', 'o');
    _val := replace(_val, 'ő', 'o');
    _val := replace(_val, 'ơ', 'o');
    _val := replace(_val, 'ø', 'o');
    _val := replace(_val, 'ǿ', 'o');
    _val := replace(_val, 'º', 'o');
    _val := replace(_val, 'œ', 'oe');
    _val := replace(_val, 'ö', 'oe');
    _val := replace(_val, 'ŕ', 'r');
    _val := replace(_val, 'ŗ', 'r');
    _val := replace(_val, 'ŝ', 's');
    _val := replace(_val, 'ș', 's');
    _val := replace(_val, 'ſ', 's');
    _val := replace(_val, 'ß', 'ss');
    _val := replace(_val, 'ţ', 't');
    _val := replace(_val, 'ț', 't');
    _val := replace(_val, 'ŧ', 't');
    _val := replace(_val, 'þ', 'th');
    _val := replace(_val, 'ù', 'u');
    _val := replace(_val, 'ú', 'u');
    _val := replace(_val, 'û', 'u');
    _val := replace(_val, 'ũ', 'u');
    _val := replace(_val, 'ŭ', 'u');
    _val := replace(_val, 'ű', 'u');
    _val := replace(_val, 'ų', 'u');
    _val := replace(_val, 'ư', 'u');
    _val := replace(_val, 'ǔ', 'u');
    _val := replace(_val, 'ǖ', 'u');
    _val := replace(_val, 'ǘ', 'u');
    _val := replace(_val, 'ǚ', 'u');
    _val := replace(_val, 'ǜ', 'u');
    _val := replace(_val, 'ü', 'ue');
    _val := replace(_val, 'ŵ', 'w');
    _val := replace(_val, 'ý', 'y');
    _val := replace(_val, 'ÿ', 'y');
    _val := replace(_val, 'ŷ', 'y');
    _val := regexp_replace(_val, '[^a-z0-9-]+', '-', 'g');

    RETURN trim(BOTH '-' FROM _val);
END;
$$ LANGUAGE plpgsql;

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
