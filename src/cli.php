<?php
declare(strict_types=1);

namespace cli;

use file;
use pdo;

function app_init(array $data): array
{
    $path = APP['path']['app.db'] . '/init';
    $db = pdo\db('app');

    foreach (file\scan($path) as $file) {
        if (str_ends_with($file, '.sql')) {
            $db->exec(file_get_contents($path . '/' . $file));
        }
    }

    return $data;
}

function app_upgrade(array $data): array
{
    $path = APP['path']['app.db'] . '/upgrade';
    $version = pdo\version();

    if (!file_exists($path) || APP['app.schema'] <= $version) {
        return $data;
    }

    $db = pdo\db('app');
    $files = file\scan($path);
    sort($files, SORT_NUMERIC);

    foreach ($files as $file) {
        if (preg_match('#^([0-9]+)\.sql$#', $file, $match) &&
            (int)$match[1] > $version &&
            (int)$match[1] <= APP['app.schema']
        ) {
            $db->exec(file_get_contents($path . '/' . $file));
        }
    }

    return $data;
}
