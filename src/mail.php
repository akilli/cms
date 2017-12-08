<?php
declare(strict_types = 1);

namespace mail;

/**
 * Load entity
 */
function load(array $ent, array $crit = [], array $opt = []): array
{
    return $opt['mode'] === 'size' ? [0] : [];
}

/**
 * Save entity
 */
function save(array $data): array
{
    return $data;
}

/**
 * Delete entity
 */
function delete(array $data): void
{
}
