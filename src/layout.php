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
function section(string $id): string
{
    if (!($§ = layout($id)) || !$§['active'] || $§['privilege'] && !allowed($§['privilege'])) {
        return '';
    }

    $§ = event('section.' . $§['section'], $§);
    $§ = event('layout.section.' . $id, $§);

    return $§['call']($§);
}

/**
 * Layout
 *
 * @param string $id
 * @param array $§
 *
 * @return array|null
 */
function layout(string $id, array $§ = null): ?array
{
    $data = & registry('layout');

    if ($data === null) {
        $data = [];
    }

    if ($§ !== null) {
        $data[$id] = $§;
    }

    return $data[$id] ?? null;
}

/**
 * Load layout by handles
 *
 * @return void
 */
function layout_load(): void
{
    $layout = data('layout');

    foreach (layout_handles() as $handle) {
        if (!empty($layout[$handle])) {
            foreach ($layout[$handle] as $id => $§) {
                $§['id'] = $id;
                layout_add($§);
            }
        }
    }
}

/**
 * Add section to layout
 *
 * @param array $§
 *
 * @return void
 *
 * @throws InvalidArgumentException
 */
function layout_add(array $§): void
{
    if (empty($§['id'])) {
        throw new InvalidArgumentException(_('No section ID given'));
    }

    $data = layout($§['id']);

    // New section
    if ($data === null && (empty($§['section']) || !($data = data('section', $§['section'])))) {
        throw new InvalidArgumentException(_('No or invalid section for ID %s', $§['id']));
    }

    if ($§['id'] === 'root') {
        $§['parent'] = '';
    } elseif (!empty($§['parent']) && $§['parent'] !== $data['parent']) {
        layout_parent($§, $data['parent']);
    }

    // Add or update section
    layout($§['id'], array_replace($data, $§));
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
    $§ = layout($id);
    $§['vars'] = array_replace($§['vars'] ?? [], $vars);
    layout($id, $§);
}

/**
 * Layout handles
 *
 * @return array
 */
function layout_handles(): array
{
    $data = [ALL];
    $data[] = 'account-' . (account_user() ? 'user' : 'guest');

    if ($entity = data('entity', request('entity'))) {
        $act = request('action');

        if (in_array($act, $entity['actions'])) {
            $data[] = 'action-' . $act;
        }

        $data[] = 'entity-' . $entity['id'];
        $data[] = $entity['id'] . '/' . $act;
    }

    return $data;
}

/**
 * Sets or updates parent section
 *
 * @param array $§
 * @param string $oldId
 *
 * @return void
 */
function layout_parent(array $§, string $oldId): void
{
    // Remove section from old parent section if it exists
    if ($oldParent = layout($oldId)) {
        unset($oldParent['children'][$§['id']]);
        layout($oldId, $oldParent);
    }

    // Add section to new parent section if it exists
    if ($parent = layout($§['parent'])) {
        $parent['children'][$§['id']] = $§['sort'] ?? 0;
        layout($§['parent'], $parent);
    }
}
