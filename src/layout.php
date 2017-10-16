<?php
declare(strict_types = 1);

namespace layout;

use app;
use InvalidArgumentException;

/**
 * Get or add layout section
 *
 * @throws InvalidArgumentException
 */
function data(string $id = null, array $§ = null): ?array
{
    $data = & app\data('layout');

    if ($data === null) {
        $data = app\cfg('layout');
    }

    // Get whole layout
    if ($id === null) {
        return $data;
    }

    // Get layout section
    if ($§ === null) {
        return $data[$id] ?? null;
    }

    // Add new or update existing section
    $data[$id] = array_replace_recursive($data[$id] ?? SECTION, $§, ['id' => $id]);

    if (empty($data[$id]['section'])) {
        throw new InvalidArgumentException(app\i18n('No or invalid section for ID %s', $id));
    }

    return $data[$id];
}

/**
 * Set section variables
 */
function vars(string $id, array $vars): void
{
    data($id, ['vars' => $vars]);
}
