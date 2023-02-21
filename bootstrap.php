<?php
declare(strict_types=1);

/**
 * Application constants
 */
require_once __DIR__ . '/const.php';

if (file_exists(APP['path']['ext'] . '/const.php')) {
    require_once APP['path']['ext'] . '/const.php';
}

/**
 * Restore pregenerated configuration
 */
cfg\restore();

/**
 * I18n
 */
setlocale(LC_ALL, APP['locale']);
