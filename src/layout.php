<?php
declare(strict_types = 1);

namespace cms;

use InvalidArgumentException;

/**
 * Layout section
 *
 * @param string $id
 *
 * @return string
 */
function §(string $id): string
{
    if (!($§ = layout($id)) || !$§['active'] || $§['privilege'] && !allowed($§['privilege'])) {
        return '';
    }

    $§ = event('section.' . $§['section'], $§);
    $§ = event('layout.section.' . $id, $§);

    return $§['call']($§);
}

/**
 * Get or add layout section
 *
 * @param string $id
 * @param array $§
 *
 * @return array|null
 *
 * @throws InvalidArgumentException
 */
function layout(string $id = null, array $§ = null): ?array
{
    $data = & registry('layout');

    if ($data === null) {
        $data = cfg('layout');
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
    if (empty($data[$id]) && (empty($§['section']) || !($data[$id] = cfg('section', $§['section'])))) {
        throw new InvalidArgumentException(_('No or invalid section for ID %s', $id));
    }

    $data[$id] = array_replace_recursive($data[$id], $§, ['id' => $id]);

    return $data[$id];
}

/**
 * Set section variables
 *
 * @param string $id
 * @param array $vars
 *
 * @return void
 */
function layout_vars(string $id, array $vars): void
{
    layout($id, ['vars' => $vars]);
}
