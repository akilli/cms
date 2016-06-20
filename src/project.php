<?php
namespace qnd;

/**
 * Constants
 */
const PROJECT_GLOBAL = 0;

/**
 * Project
 *
 * @param string $key
 *
 * @return mixed
 */
function project(string $key = null)
{
    static $data;

    if ($data === null) {
        $data = [];
        $id = session('project');
        $crit = $id === null ? ['host' => request('host')] : ['id' => $id];
        $crit['active'] = true;
        $data = one('project', $crit);

        if (!$data) {
            $data = one('project', ['id' => PROJECT_GLOBAL, 'active' => true]);
        }

        if ($id < 0 || !$data) {
            session('project', null, true);
        }
    }

    if ($key === null) {
        return $data;
    }

    return $data[$key] ?? null;
}
