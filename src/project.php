<?php
declare(strict_types = 1);

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
        $base = one('project', ['uid' => data('app', 'project')]);
        $id = (int) session('project');
        // @todo Use request()
        $uid = strstr($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'], '.', true);
        $crit = $id ? ['id' => $id] : ['uid' => $uid];
        $crit['active'] = true;
        $data = one('project', $crit) ?: $base;
        $data['ids'] = array_unique([$base['id'], $data['id']]);
        $data['theme'] = data('app', 'theme');
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
