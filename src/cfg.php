<?php
declare(strict_types=1);

namespace cfg;

use app;
use arr;
use DomainException;
use file;
use ReflectionException;
use ReflectionFunction;

/**
 * Backs up configuration
 */
function backup(): void
{
    file_put_contents(
        APP['path']['tmp'] . '/cfg.php',
        "<?php\nreturn " . var_export(preload(), true) . ';'
    );
}

/**
 * Restores configuration
 */
function restore(): void
{
    $cfg = &app\registry('cfg');
    $cfg = one(APP['path']['tmp'] . '/cfg.php');
}

/**
 * Preloads all configuration data
 */
function preload(): array
{
    $data = ['i18n' => load('i18n')];

    foreach (array_filter([APP['path']['app.cfg'], APP['path']['ext.cfg']], 'is_dir') as $path) {
        foreach (file\scan($path) as $name) {
            $id = basename($name, '.php');
            $file = $path . '/' . $name;

            if (!is_array($data[$id] ?? null) && (is_dir($file) || is_file($file) && $id !== $name)) {
                $data[$id] = load($id);
            }
        }
    }

    return $data;
}

/**
 * Loads data from file
 */
function one(string $file): array
{
    return is_file($file) && ($data = include $file) && is_array($data) ? $data : [];
}

/**
 * Loads data from all files in given directory
 */
function all(string $path): array
{
    $data = [];

    foreach (glob($path . '/*.php') as $file) {
        $data[basename($file, '.php')] = one($file);
    }

    return $data;
}

/**
 * Loads configuration data with or without overwrites
 */
function load(string $id): array
{
    if (($cfg = &app\registry('cfg')[$id]) === null) {
        $cfg = [];
        $appPath = APP['path']['app.cfg'] . '/' . $id;
        $extPath = APP['path']['ext.cfg'] . '/' . $id;
        $data = is_dir($appPath) ? all($appPath) : one($appPath . '.php');
        $ext = is_dir($extPath) ? all($extPath) : one($extPath . '.php');
        $cfg = match ($id) {
            'api' => api($data, $ext),
            'attr', 'backend' => $data + $ext,
            'block' => block($data, $ext),
            'db' => db($data, $ext),
            'entity' => entity($data, $ext),
            'event' => event($data, $ext),
            'frontend', 'opt', 'validator', 'viewer' => call($data, $ext),
            'i18n' => arr\extend($data, $ext),
            'layout' => layout($data, $ext),
            'menu' => menu($data, $ext),
            'privilege' => privilege($data, $ext),
            default => array_replace($data, $ext),
        };
    }

    return $cfg;
}

/**
 * Exports first class callable syntax to string, i. e. namespace\function(...) => 'namespace\function'
 *
 * @throws DomainException
 */
function export(callable $call): string
{
    try {
        return (new ReflectionFunction($call))->getName();
    } catch (ReflectionException) {
        throw new DomainException(app\i18n('Invalid configuration'));
    }
}

/**
 * Loads configuration that consists only of a callback function
 *
 * @throws DomainException
 */
function call(array $data, array $ext): array
{
    $data += $ext;

    foreach ($data as $id => $item) {
        $data[$id] = ['id' => $id, 'call' => export($item['call'])];
    }

    return $data;
}

/**
 * Loads entity API configuration
 *
 * @throws DomainException
 */
function api(array $data, array $ext): array
{
    $data += $ext;

    foreach ($data as $id => $item) {
        $item = arr\replace(APP['cfg']['api'], $item, ['id' => $id]);

        foreach (array_keys(APP['cfg']['api']) as $key) {
            is_callable($item[$key]) || throw new DomainException(app\i18n('Invalid configuration'));
            $item[$key] = export($item[$key]);
        }

        $data[$id] = $item;
    }

    return $data;
}

/**
 * Loads block configuration
 *
 * @throws DomainException
 */
function block(array $data, array $ext): array
{
    $data += $ext;

    foreach ($data as $id => $item) {
        $data[$id] = arr\replace(APP['cfg']['block'], $item, ['id' => $id, 'call' => export($item['call'])]);
    }

    return $data;
}

/**
 * Loads DB configuration
 *
 * @throws DomainException
 */
function db(array $data, array $ext): array
{
    $data = arr\extend($data, $ext);

    foreach ($data as $id => $item) {
        $item = arr\replace(APP['cfg']['db'], $item, ['id' => $id]);

        if (!$item['dsn']) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }

        $data[$id] = $item;
    }

    return $data;
}

/**
 * Loads entity configurations
 *
 * @throws DomainException
 */
function entity(array $data, array $ext): array
{
    $data += $ext;
    $apiCfg = load('api');
    $dbCfg = load('db');
    $attrCfg = load('attr');
    $backendCfg = load('backend');
    $frontendCfg = load('frontend');
    $validatorCfg = load('validator');
    $viewerCfg = load('viewer');
    $optCfg = load('opt');

    foreach ($data as $entityId => $entity) {
        $entity = arr\replace(APP['cfg']['entity'], $entity, ['id' => $entityId]);

        if (!$entityId
            || !preg_match('#^[a-z][a-z_\.]+$#', $entityId)
            || !$entity['name']
            || !$entity['db']
            || empty($dbCfg[$entity['db']])
            || !$entity['api']
            || empty($apiCfg[$entity['api']])
            || empty($entity['attr']['id'])
            || $entity['unique'] !== array_filter($entity['unique'], fn(array $keys) => arr\has($entity['attr'], $keys))
            || preg_grep('#^[a-z]+$#', $entity['action'], PREG_GREP_INVERT)
        ) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }

        foreach ($entity['attr'] as $attrId => $attr) {
            if (!preg_match('#^[a-z][\w]*$#', $attrId)
                || empty($attr['name'])
                || empty($attr['type'])
                || empty($attrCfg[$attr['type']])
                || in_array($attr['type'], ['entity', 'multientity']) && empty($attr['ref'])
                || !empty($attr['ref']) && empty($data[$attr['ref']])
            ) {
                throw new DomainException(app\i18n('Invalid configuration'));
            }

            $a = ['id' => $attrId, 'name' => app\i18n($attr['name'])];
            $attr = arr\replace(APP['cfg']['attr'], $attrCfg[$attr['type']], $attr, $a);

            if (!array_key_exists($attr['backend'], $backendCfg)
                || !$attr['frontend']
                || empty($frontendCfg[$attr['frontend']])
                || $attr['filter'] !== null && empty($frontendCfg[$attr['filter']])
                || $attr['validator'] !== null && empty($validatorCfg[$attr['validator']])
                || $attr['viewer'] !== null && empty($viewerCfg[$attr['viewer']])
                || $attr['opt'] !== null && empty($optCfg[$attr['opt']])
                || $attr['min'] !== null && $attr['max'] !== null && $attr['min'] > $attr['max']
            ) {
                throw new DomainException(app\i18n('Invalid configuration'));
            }

            $attr['frontend'] = $frontendCfg[$attr['frontend']]['call'];
            $attr['filter'] = $attr['filter'] ? $frontendCfg[$attr['filter']]['call'] : $attr['frontend'];
            $attr['validator'] = $attr['validator'] ? $validatorCfg[$attr['validator']]['call'] : null;
            $attr['viewer'] = $attr['viewer'] ? $viewerCfg[$attr['viewer']]['call'] : null;
            $attr['opt'] = $attr['opt'] ? $optCfg[$attr['opt']]['call'] : null;
            $entity['attr'][$attrId] = $attr;
        }

        $entity['name'] = app\i18n($entity['name']);
        $data[$entityId] = $entity;
    }

    return $data;
}

/**
 * Loads event configuration
 */
function event(array $data, array $ext): array
{
    $data = arr\extend($data, $ext);

    foreach ($data as $key => $event) {
        foreach ($event as $id => $item) {
            $event[$id] = arr\replace(APP['cfg']['event'], $item, ['id' => $id, 'call' => export($item['call'])]);
        }

        $data[$key] = arr\order($event, ['sort' => 'asc']);
    }

    return $data;
}

/**
 * Load layout configuration
 */
function layout(array $data, array $ext): array
{
    foreach ($ext as $key => $cfg) {
        foreach ($cfg as $id => $block) {
            $data[$key][$id] = empty($data[$key][$id]) ? $block : arr\extend($data[$key][$id], $block);
        }
    }

    return $data;
}

/**
 * Loads menu configuration
 *
 * @throws DomainException
 */
function menu(array $data, array $ext): array
{
    $data = arr\extend($data, $ext);

    foreach ($data as $menuId => $menu) {
        $menu = arr\order($menu, ['parent_id' => 'asc', 'sort' => 'asc', 'name' => 'asc']);
        $parentId = null;
        $sort = 0;

        foreach ($menu as $id => $item) {
            $item = arr\replace(APP['cfg']['menu'], $item, ['id' => $id]);

            if (!$item['name'] || $item['parent_id'] && empty($menu[$item['parent_id']])) {
                throw new DomainException(app\i18n('Invalid configuration'));
            }

            if ($item['url'] && preg_match('#^/([a-z][a-z_\.]+):([a-z]+)(?:|\:([^/\:\.]+))$#', $item['url'], $match)) {
                $item['privilege'] = app\id($match[1], $match[2]);
            }

            $item['name'] = app\i18n($item['name']);
            $sort = $parentId === $item['parent_id'] ? $sort : 0;
            $item['sort'] = ++$sort;

            if ($item['parent_id']) {
                $item['position'] = sprintf('%s.%03d', $menu[$item['parent_id']]['position'], $item['sort']);
                $item['level'] = $menu[$item['parent_id']]['level'] + 1;
                $item['path'] = [...$menu[$item['parent_id']]['path'], $id];
            } else {
                $item['position'] = sprintf('%03d', $item['sort']);
                $item['level'] = 1;
                $item['path'] = [$id];
            }

            $parentId = $item['parent_id'];
            $menu[$id] = $item;
        }

        $data[$menuId] = arr\order($menu, ['position' => 'asc']);
    }

    return $data;
}

/**
 * Loads privilege configuration
 *
 * @throws DomainException
 */
function privilege(array $data, array $ext): array
{
    $data = arr\extend($data, $ext);
    $generated = [];

    foreach (load('entity') as $entity) {
        foreach ($entity['action'] as $action) {
            $generated[app\id($entity['id'], $action)] = [
                'name' => $entity['name'] . ' ' . app\i18n(ucfirst($action)),
                'use' => $action === 'view' ? '_public_' : null,
            ];
        }
    }

    foreach ($data as $id => $item) {
        if (!empty($item['name'])) {
            $data[$id]['name'] = app\i18n($item['name']);
        }
    }

    $data = arr\extend($generated, $data);

    foreach ($data as $id => $item) {
        $item = arr\replace(APP['cfg']['privilege'], $item, ['id' => $id]);

        if ($item['use'] && empty($data[$item['use']])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }

        $data[$id] = $item;
    }

    return $data;
}
