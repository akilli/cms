<?php
namespace qnd;

use InvalidArgumentException;

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
        $data = one('project', $crit) ?: one('project', ['id' => PROJECT_DEFAULT]);
        session('project', $data['id']);
    }

    if ($key === null) {
        return $data;
    }

    return $data[$key] ?? null;
}

/**
 * Current and default project Ids
 *
 * @return array
 */
function project_all(): array
{
    static $data;

    if ($data === null) {
        $data = array_unique([PROJECT_DEFAULT, project('id')]);
    }

    return $data;
}

/**
 * Gets project-specific absolute path to specified subpath in given directory
 *
 * @param string $dir
 * @param string $subpath
 *
 * @return string[]|string
 *
 * @throws InvalidArgumentException
 */
function project_path(string $dir = null, string $subpath = null)
{
    $data = & registry('project.path');

    if ($data === null) {
        $data = [];
        $id = project('id');
        $data['asset'] = path('asset', $id);
        $data['log'] = path('log', $id);
        $data['media'] = path('media', $id);
        $data['tmp'] = path('tmp', $id);
    }

    if ($dir === null) {
        return $data;
    }

    if (empty($data[$dir])) {
        throw new InvalidArgumentException(_('Invalid path %s', $dir));
    }

    return rtrim($data[$dir] . '/' . $subpath, '/');
}
