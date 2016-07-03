<?php
namespace qnd;

use InvalidArgumentException;

/**
 * Render section
 *
 * @param string $id
 * @param string $as
 *
 * @return string
 */
function §(string $id, string $as = null): string
{
    $§ = & layout($id);

    if (!$§ || !$§['active'] || $§['privilege'] && !allowed($§['privilege'])) {
        return '';
    }

    if (!isset($§['html'])) {
        $§['as'] = $as ?? $id;
        event(['section.type.' . $§['type'], 'section.' . $id], $§);
        $callback = fqn('section_' . $§['type']);
        $§['html'] = $callback($§);
        $§['as'] = null;
    }

    return $§['html'];
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
function vars(string $id, array $vars)
{
    $§ = & layout($id);

    foreach ($vars as $var => $value) {
        $§['vars'][$var] = $value;
    }
}

/**
 * Layout
 *
 * @param string $id
 * @param array $§
 *
 * @return mixed
 */
function & layout(string $id, array $§ = null)
{
    $data = & registry('layout');

    if ($data === null) {
        $data = [];
    }

    if ($§ !== null || !array_key_exists($id, $data)) {
        $data[$id] = $§;
    }

    return $data[$id];
}

/**
 * Layout handles
 *
 * @return array
 */
function layout_handles(): array
{
    static $data;

    if ($data === null) {
        $data = ['layout-base'];
        $data[] = 'user-' . (registered() ? 'registered' : 'anonymous');
        $entity = data('entity', request('entity'));

        if ($entity && in_array(request('action'), $entity['actions'])) {
            $data[] = 'action-' . request('action');
        }

        if ($entity) {
            $data[] = request('entity');
        }

        $data[] = request('id');
    }

    return $data;
}

/**
 * Load layout by handles
 *
 * @return void
 */
function layout_load()
{
    $layout = data('layout');

    foreach (layout_handles() as $handle) {
        foreach (data_filter($layout, ['handle' => $handle]) as $§) {
            layout_add($§);
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
function layout_add(array $§)
{
    if (empty($§['id'])) {
        throw new InvalidArgumentException(_('No section Id given'));
    }

    $data = & layout($§['id']);

    // New section
    if ($data === null) {
        $data = data('skeleton', 'section');

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
    $data = array_replace($data, $§);
}

/**
 * Sets or updates parent section
 *
 * @param array $§
 * @param string $oldId
 *
 * @return void
 */
function layout_parent(array $§, string $oldId)
{
    $oldParent = layout($oldId);
    $parent = layout($§['parent']);

    // Remove section from old parent section if it exists
    if ($oldParent) {
        $oldParent = & layout($oldId);
        unset($oldParent['children'][$§['id']]);
    }

    // Add section to new parent section if it exists
    if ($parent) {
        $parent = & layout($§['parent']);
        $parent['children'][$§['id']] = intval($§['sort'] ?? 0);
    }
}
