<?php
namespace qnd;

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
            $data = one('project', ['id' => 0, 'active' => true]);
        }

        if ($id <= 0 || !$data) {
            session('project', null, true);
        }
    }

    if ($key === null) {
        return $data;
    }

    return $data[$key] ?? null;
}
