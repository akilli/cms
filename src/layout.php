<?php
declare(strict_types = 1);

namespace qnd;

use InvalidArgumentException;

/**
 * Render section
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

    $§ = event('section.type.' . $§['type'], $§);
    $§ = event('section.' . $id, $§);
    $call = fqn('section_' . $§['type']);

    return $call($§);
}

/**
 * Render template
 *
 * @param array $§
 *
 * @return string
 */
function render(array $§): string
{
    $§ = function ($key) use ($§) {
        if ($key === '§') {
            return $§;
        }

        return $§['vars'][$key] ?? null;
    };
    ob_start();
    include path('template', $§('§')['template']);

    return ob_get_clean();
}

/**
 * Set section variables
 *
 * @param string $id
 * @param array $vars
 *
 * @return void
 */
function vars(string $id, array $vars): void
{
    $§ = layout($id);
    $§['vars'] = array_replace($§['vars'] ?? [], $vars);
    layout($id, $§);
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
 * Layout handles
 *
 * @return array
 */
function layout_handles(): array
{
    $data = ['_base_'];
    $data[] = 'account-' . (registered() ? 'registered' : 'unregistered');

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
        throw new InvalidArgumentException(_('No section Id given'));
    }

    $data = layout($§['id']);

    // New section
    if ($data === null) {
        $data = data('default', 'section');

        if (empty($§['type']) || !is_callable(fqn('section_' . $§['type']))) {
            throw new InvalidArgumentException(_('No or invalid type given for section with Id %s', $§['id']));
        }
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
