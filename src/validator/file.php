<?php
declare(strict_types=1);

namespace validator;

use app;
use DomainException;

/**
 * File
 *
 * @throws DomainException
 */
function file(string $val, array $attr): string
{
    $mime = app\data('request', 'file')[$attr['id']]['type'] ?? null;

    if ($val && (!$mime || !in_array($mime, $attr['accept']))) {
        throw new DomainException(app\i18n('Invalid file type'));
    }

    return urlpath($val);
}
