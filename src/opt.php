<?php
declare(strict_types = 1);

namespace cms;

/**
 * Option
 *
 * @param array $attr
 *
 * @return array
 */
function opt(array $attr): array
{
    if ($attr['backend'] === 'bool') {
        return [_('No'), _('Yes')];
    }

    if ($attr['type'] === 'entity') {
        return opt_entity($attr['opt']);
    }

    if (is_string($attr['opt'])) {
        return $attr['opt']($attr);
    }

    return $attr['opt'];
}

/**
 * Entity options
 *
 * @param string $eId
 *
 * @return array
 */
function opt_entity(string $eId): array
{
    $data = & registry('opt.entity.' . $eId);

    if ($data[$eId] === null) {
        if ($eId === 'page') {
            $data[$eId] = [];

            foreach (all('page', [], ['select' => ['id', 'name', 'pos'], 'order' => ['pos' => 'asc']]) as $item) {
                $data[$eId][$item['id']] = viewer($item['_entity']['attr']['pos'], $item) . ' ' . $item['name'];
            }
        } else {
            $data[$eId] = array_column(all($eId, [], ['select' => ['id', 'name']]), 'name', 'id');
        }
    }

    return $data[$eId];
}

/**
 * Privilege options
 *
 * @return array
 */
function opt_privilege(): array
{
    $data = [];

    foreach (cfg('privilege') as $key => $priv) {
        if (empty($priv['call']) && allowed($key)) {
            $data[$key] = $priv['name'];
        }
    }

    return $data;
}
