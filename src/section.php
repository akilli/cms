<?php
namespace qnd;

/**
 * Template section
 *
 * @param array $§
 *
 * @return string
 */
function section_template(array & $§): string
{
    return render($§);
}

/**
 * Container section
 *
 * @param array $§
 *
 * @return string
 */
function section_container(array & $§): string
{
    $html = '';

    if (!empty($§['children']) && is_array($§['children'])) {
        asort($§['children'], SORT_NUMERIC);

        foreach (array_keys($§['children']) as $id) {
            $html .= §($id);
        }
    }

    return $html;
}

/**
 * Entity section
 *
 * @param array $§
 *
 * @return string
 */
function section_entity(array & $§): string
{
    if (empty($§['vars']['entity']) || !($entity = data('entity', $§['vars']['entity']))) {
        return '';
    }

    $§['vars'] = array_replace(['crit' => [], 'opts' => []], $§['vars']);
    $§['vars']['data'] = all($§['vars']['entity'], $§['vars']['crit'], $§['vars']['opts']);
    $§['vars']['title'] = _($entity['name']);

    return render($§);
}

/**
 * Pager section
 *
 * @param array $§
 *
 * @return string
 */
function section_pager(array & $§): string
{
    $§['vars'] += ['size' => 0, 'limit' => 0, 'links' => [], 'params' => []];

    if ($§['vars']['size'] < 1 || $§['vars']['limit'] < 1) {
        return '';
    }

    $§['vars']['pages'] = (int) ceil($§['vars']['size'] / $§['vars']['limit']);
    $§['vars']['page'] = max($§['vars']['params']['page'] ?? 0, 1);
    $§['vars']['offset'] = ($§['vars']['page'] - 1) * $§['vars']['limit'];
    unset($§['vars']['params']['page']);
    $c = max(0, config('entity.pager'));
    $min = max(1, min($§['vars']['page'] - intdiv($c, 2), $§['vars']['pages'] - $c + 1));
    $max = min($min + $c - 1, $§['vars']['pages']);

    if ($§['vars']['page'] >= 2) {
        $p = $§['vars']['page'] === 2 ? $§['vars']['params'] : ['page' => $§['vars']['page'] - 1] + $§['vars']['params'];
        $§['vars']['links'][] = ['name' => _('Previous'), 'params' => $p];
    }

    for ($i = $min; $min < $max && $i <= $max; $i++) {
        $p = $i > 1 ? ['page' => $i] + $§['vars']['params'] : $§['vars']['params'];
        $§['vars']['links'][] = ['name' => $i, 'params' => $p, 'active' => $i === $§['vars']['page']];
    }

    if ($§['vars']['page'] < $§['vars']['pages']) {
        $p = ['page' => $§['vars']['page'] + 1] + $§['vars']['params'];
        $§['vars']['links'][] = ['name' => _('Next'), 'params' => $p];
    }

    return render($§);
}

/**
 * Message section
 *
 * @param array $§
 *
 * @return string
 */
function section_message(array & $§): string
{
    if (!$§['vars']['data'] = session('message')) {
        return '';
    }

    session('message', null, true);

    return render($§);
}

/**
 * Node section
 *
 * @param array $§
 *
 * @return string
 */
function section_node(array & $§): string
{
    if (empty($§['vars']['crit'])
        || !($menu = one('menu', $§['vars']['crit']))
        || !($data = all('node', ['root_id' => $menu['id'], 'project_id' => $menu['project_id']]))
    ) {
        return '';
    }

    $data = array_filter(
        $data,
        function ($item) use ($data) {
            if (strpos($item['target'], 'http') === 0) {
                return true;
            }

            if ($item['target'] !== '#') {
                return allowed(privilege_url($item['target']));
            }

            foreach ($data as $i) {
                if ($i['lft'] > $item['lft']
                    && $i['rgt'] < $item['rgt']
                    && $i['target'] !== '#'
                    && allowed(privilege_url($i['target']))
                ) {
                    return true;
                }
            }

            return false;
        }
    );
    $count = count($data);
    $level = 0;
    $i = 0;
    $html = '';

    foreach ($data as $item) {
        $attrs = [];
        $class = '';

        if (!empty($§['vars']['translate'])) {
            $item['name'] = _($item['name']);
        }

        if ($item['target'] === request('path')) {
            $attrs['class'] = 'active';
            $class .= ' class="active"';
        }

        if ($item['level'] > $level) {
             $html .= '<ul><li' . $class . '>';
        } elseif ($item['level'] < $level) {
             $html .= '</li>' . str_repeat('</ul></li>', $level - $item['level']) . '<li' . $class . '>';
        } else {
             $html .= '</li><li' . $class . '>';
        }

        if ($item['target'] !== '#') {
            $attrs['href'] = url($item['target']);
            $html .= html_tag('a', $attrs, $item['name']);
        } else {
            $html .= html_tag('span', [], $item['name']);
        }

        $html .= ++$i === $count ? str_repeat('</li></ul>', $item['level']) : '';
        $level = $item['level'];
    }

    return $html;
}
