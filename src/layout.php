<?php
declare(strict_types = 1);

namespace layout;

use function account\allowed;
use function app\i18n;
use app;
use InvalidArgumentException;
use const section\SECTION;

/**
 * Layout section
 */
function §(string $id): string
{
    if (!($§ = data($id)) || !$§['active'] || $§['priv'] && !allowed($§['priv'])) {
        return '';
    }

    $§ = app\event('section.' . $§['section'], $§);
    $§ = app\event('layout.section.' . $id, $§);

    return ('section\\' . $§['section'])($§);
}

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
        throw new InvalidArgumentException(i18n('No or invalid section for ID %s', $id));
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
