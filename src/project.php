<?php
namespace qnd;

use InvalidArgumentException;

/**
 * Project
 *
 * @param string $key
 *
 * @return mixed
 */
function project(string $key)
{
    $data = & registry('project');

    if ($data === null) {
        $data = [];
        $id = (string) session('project');
        $crit = $id ? ['id' => $id] : ['host' => request('host')];
        $crit['active'] = true;
        $data = one('project', $crit) ?: one('project', ['id' => data('app', 'project')]);
        $data['ids'] = array_unique([data('app', 'project'), $data['id']]);
        $data['theme'] = $data['theme'] ?: data('app', 'theme');
        session('project', $data['id']);
    }

    return $data[$key] ?? null;
}

/**
 * Gets project-specific absolute path to specified subpath in given directory
 *
 * @param string $dir
 * @param string $id
 *
 * @return string
 *
 * @throws InvalidArgumentException
 */
function project_path(string $dir, string $id = ''): string
{
    $data = & registry('project.path');

    if ($data === null) {
        $data = [];
        $asset = path('asset', project('id'));
        $data['cache'] = $asset . '/cache';
        $data['media'] = $asset . '/media';
    }

    if (empty($data[$dir])) {
        throw new InvalidArgumentException(_('Invalid path %s', $dir));
    }

    return rtrim($data[$dir] . '/' . $id, '/');
}
