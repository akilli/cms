<?php
namespace qnd;

/**
 * Constants
 */
const PROJECT_DEFAULT = 1;

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
        $id = (int) session('project');
        $crit = $id ? ['id' => $id] : ['host' => request('host')];
        $crit['active'] = true;
        $data = one('project', $crit);

        if (!$data) {
            $data = one('project', ['id' => PROJECT_DEFAULT, 'active' => true]);
        }

        if (!$id || !$data) {
            session('project', null, true);
        }
    }

    if ($key === null) {
        return $data;
    }

    return $data[$key] ?? null;
}
