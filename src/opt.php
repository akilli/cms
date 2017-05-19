<?php
declare(strict_types = 1);

namespace qnd;

/**
 * Entity options
 *
 * @param array $attr
 *
 * @return array
 */
function opt_entity(array $attr): array
{
    $eId = $attr['opt'][0];
    $data = & registry('opt.entity.' . $eId);

    if ($data[$eId] === null) {
        if ($eId === 'page') {
            $data[$eId] = [];

            foreach (all('page', [], ['select' => ['id', 'name', 'pos'], 'order' => ['pos' => 'asc']]) as $item) {
                $a = array_replace($item['_entity']['attr']['pos'], ['context' => 'view', 'actions' => ['view']]);
                $data[$eId][$item['id']] = viewer($a, $item) . ' ' . $item['name'];
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

    foreach (data('privilege') as $key => $priv) {
        if (empty($priv['call'])) {
            $data[$key] = $priv['name'];
        }
    }

    return $data;
}
