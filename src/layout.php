<?php
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
    $section = & layout($id);

    if (!$section
        || !$section['active']
        || !$section['type']
        || ($callback = fqn('section_' . $section['type'])) && !is_callable($callback)
        || $section['privilege'] && !allowed($section['privilege'])
    ) {
        return '';
    }

    event(['section.type.' . $section['type'], 'section.' . $id], $section);

    return $callback($section);
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
    $section = & layout($id);

    foreach ($vars as $var => $value) {
        $section['vars'][$var] = $value;
    }
}

/**
 * Layout
 *
 * @param string $id
 * @param array $section
 *
 * @return mixed
 */
function & layout(string $id, array $section = null)
{
    $data = & registry('layout');

    if ($data === null) {
        $data = [];
    }

    if ($section !== null || !array_key_exists($id, $data)) {
        $data[$id] = $section;
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

        if (admin()) {
            $data[] = 'role-admin';
        }

        if (request('base') === request('url')) {
            $data[] = 'http-base';
        }

        $meta = data('meta', request('entity'));

        if ($meta && meta_action(request('action'), $meta)) {
            $data[] = 'action-' . request('action');
        }

        $data[] = request('entity');
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
        foreach (data_filter($layout, ['handle' => $handle]) as $section) {
            layout_add($section);
        }
    }
}

/**
 * Add section to layout
 *
 * @param array $section
 *
 * @return void
 *
 * @throws InvalidArgumentException
 */
function layout_add(array $section)
{
    if (empty($section['id'])) {
        throw new InvalidArgumentException('No section ID given');
    }

    $data = & layout($section['id']);

    // New section
    if ($data === null) {
        $data = data('skeleton', 'section');

        if (empty($section['type']) || !is_callable(fqn('section_' . $section['type']))) {
            throw new InvalidArgumentException('No or invalid section type given for section with ID ' . $section['id']);
        }
    }

    if ($section['id'] === 'root') {
        $section['parent'] = '';
    } elseif (!empty($section['parent']) && $section['parent'] !== $data['parent']) {
        layout_parent($section, $data['parent']);
    }

    // Add or update section
    $data = array_replace($data, $section);
}

/**
 * Sets or updates parent section
 *
 * @param array $section
 * @param string $oldId
 *
 * @return void
 */
function layout_parent(array $section, string $oldId)
{
    $oldParent = layout($oldId);
    $parent = layout($section['parent']);

    // Remove section from old parent section if it exists
    if ($oldParent) {
        $oldParent = & layout($oldId);
        unset($oldParent['children'][$section['id']]);
    }

    // Add section to new parent section if it exists
    if ($parent) {
        $parent = & layout($section['parent']);
        $parent['children'][$section['id']] = intval($section['sort'] ?? 0);
    }
}
