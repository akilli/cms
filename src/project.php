<?php
namespace qnd;

use InvalidArgumentException;

/**
 * Constants
 */
const PROJECT_ID = 'base';
const PROJECT_THEME = 'base';

/**
 * Project
 *
 * @param string $key
 *
 * @return mixed
 */
function project(string $key)
{
    static $data;

    if ($data === null) {
        $data = [];
        $id = (string) session('project');
        $crit = $id ? ['id' => $id] : ['host' => request('host')];
        $crit['active'] = true;
        $data = one('project', $crit) ?: one('project', ['id' => PROJECT_ID]);
        $data['ids'] = array_unique([PROJECT_ID, $data['id']]);
        $data['theme'] = $data['theme'] ?: PROJECT_THEME;
        session('project', $data['id']);
    }

    return $data[$key] ?? null;
}

/**
 * Gets project-specific absolute path to specified subpath in given directory
 *
 * @param string $dir
 * @param string $subpath
 *
 * @return string
 *
 * @throws InvalidArgumentException
 */
function project_path(string $dir, string $subpath = null): string
{
    $data = & registry('project.path');

    if ($data === null) {
        $data = [];
        $id = project('id');
        $data['asset'] = path('asset', $id);
        $data['media'] = path('media', $id);
    }

    if (empty($data[$dir])) {
        throw new InvalidArgumentException(_('Invalid path %s', $dir));
    }

    return rtrim($data[$dir] . '/' . $subpath, '/');
}
