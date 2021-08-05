<?php
declare(strict_types=1);

namespace cfg;

use app;
use arr;
use file;
use DomainException;

/**
 * Backs up configuration
 */
function backup(): void
{
    file_put_contents(APP['path']['tmp'] . '/cfg.php', "<?php\nreturn " . var_export(preload(), true) . ';');
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

        if (is_dir($appPath) || !is_file($appPath . '.php') && is_dir($extPath)) {
            $data = all($appPath);
            $ext = all($extPath);
        } else {
            $data = one($appPath . '.php');
            $ext = one($extPath . '.php');
        }

        $cfg = match ($id) {
            'attr', 'backend' => $data + $ext,
            'block' => block($data, $ext),
            'db', 'i18n' => arr\extend($data, $ext),
            'entity' => entity($data, $ext),
            'event' => event($data, $ext),
            'frontend', 'opt', 'validator', 'viewer' => call($data, $ext),
            'layout' => layout($data, $ext),
            'privilege' => privilege($data, $ext),
            'toolbar' => toolbar($data, $ext),
            default => array_replace($data, $ext),
        };
    }

    return $cfg;
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
        $data[$id] = ['call' => $item['call'] ?? null];

        if (!is_callable($item['call'])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }
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

    foreach ($data as $id => $type) {
        $data[$id] = arr\replace(APP['cfg']['block'], $type);

        if (!is_callable($type['call'])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }
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
    $dbCfg = load('db');
    $attrCfg = load('attr');
    $backendCfg = load('backend');
    $frontendCfg = load('frontend');
    $validatorCfg = load('validator');
    $viewerCfg = load('viewer');
    $optCfg = load('opt');
    uasort($data, fn(array $a, array $b): int => ($a['parent_id'] ?? null) <=> ($b['parent_id'] ?? null));

    foreach ($data as $entityId => $entity) {
        $entity = arr\replace(APP['cfg']['entity'], $entity, ['id' => $entityId]);
        $parent = $data[$entity['parent_id']] ?? null;

        if (!$entity['name']
            || !$entity['db']
            || !$entity['type'] && !($entity['type'] = $dbCfg[$entity['db']]['type'] ?? null)
            || $entity['parent_id'] && (!$parent || $parent['parent_id'])
            || !$entity['parent_id'] && !arr\has($entity['attr'], ['id', 'name'], true)
            || $entity['unique'] !== array_filter($entity['unique'], 'is_array')
        ) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }

        if ($entity['parent_id']) {
            $entity['unique'] = array_merge($parent['unique'], $entity['unique']);
            $entity['attr'] = arr\extend($parent['attr'], $entity['attr']);
        }

        $data[$entityId] = $entity;
    }

    foreach ($data as $entityId => $entity) {
        foreach ($entity['attr'] as $attrId => $attr) {
            if (empty($attr['name'])
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
                || $attr['min'] > 0 && $attr['max'] > 0 && $attr['min'] > $attr['max']
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

    foreach ($data as $id => $event) {
        asort($data[$id], SORT_NUMERIC);

        foreach (array_keys($event) as $call) {
            if (!is_callable($call)) {
                throw new DomainException(app\i18n('Invalid configuration'));
            }
        }
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

    $entities = load('entity');
    $noViewTypes = ['json', 'multientity', 'multiint', 'multitext', 'password', 'serial'];

    foreach ($entities as $entity) {
        $parent = $entities[$entity['parent_id']] ?? null;

        // Do not generate layout for child entities unless they have custom attributes in order to use inherited config
        if ($entity['parent_id'] && array_keys($entity['attr']) === array_keys($parent['attr'])) {
            continue;
        }

        foreach (['edit', 'index', 'view'] as $action) {
            $id = app\id('html', $entity['id'], $action);

            if (!in_array($action, $entity['action']) || !empty($data[$id])) {
                continue;
            }

            $cfg = [];

            foreach ($entity['attr'] as $attrId => $attr) {
                if ($attr['type'] === 'entitychild') {
                    continue;
                } elseif ($action === 'edit' && !$attr['auto']) {
                    $cfg['attr_id'][] = $attrId;
                } elseif ($action === 'edit' || in_array($attr['type'], $noViewTypes)) {
                    continue;
                } elseif ($action === 'view') {
                    $cfg['attr_id'][] = $attrId;
                } elseif ($action === 'index' && !$attr['nullable']) {
                    $cfg['attr_id'][] = $attrId;

                    if ($attr['opt'] || in_array($attr['backend'], ['bool', 'datetime', 'date', 'time'])) {
                        $cfg['filter'][] = $attrId;
                    } elseif($attr['backend'] === 'varchar') {
                        $cfg['search'][] = $attrId;
                    }
                }
            }

            if ($cfg) {
                $data[$id][$action]['cfg'] = $cfg;
            }
        }
    }

    return $data;
}

/**
 * Loads privilege configuration
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
        $data[$id] = arr\replace(APP['cfg']['privilege'], $item);
    }

    return $data;
}

/**
 * Loads toolbar configuration
 *
 * @throws DomainException
 */
function toolbar(array $data, array $ext): array
{
    $data = arr\extend($data, $ext);
    $entities = load('entity');
    $generated = [];

    foreach ($entities as $entity) {
        if (in_array('index', $entity['action'])) {
            if ($entity['parent_id']) {
                $generated[$entity['parent_id']] = ['name' => $entities[$entity['parent_id']]['name']];
            }

            $generated[$entity['id']] = [
                'name' => $entity['name'],
                'privilege' => app\id($entity['id'], 'index'),
                'url' => '/' . $entity['id'] . '/index',
                'parent_id' => $entity['parent_id'],
            ];
        }
    }

    foreach ($data as $id => $item) {
        if (!empty($item['name'])) {
            $data[$id]['name'] = app\i18n($item['name']);
        }
    }

    $data = arr\order(arr\extend($generated, $data), ['parent_id' => 'asc', 'sort' => 'asc', 'name' => 'asc']);
    $parentId = null;
    $sort = 0;

    foreach ($data as $id => $item) {
        $item = arr\replace(APP['cfg']['toolbar'], $item, ['id' => $id]);

        if (!$item['name'] || $item['parent_id'] && empty($data[$item['parent_id']])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }

        if ($parentId !== $item['parent_id']) {
            $sort = 0;
        }

        $item['sort'] = ++$sort;
        $parentPosition = $item['parent_id'] ? $data[$item['parent_id']]['position'] . '.' : '';
        $item['position'] = sprintf('%s%05d', $parentPosition, $item['sort']);
        $item['level'] = $item['parent_id'] ? $data[$item['parent_id']]['level'] + 1 : 1;
        $data[$id] = $item;
        $parentId = $item['parent_id'];
    }

    return arr\order($data, ['position' => 'asc']);
}
