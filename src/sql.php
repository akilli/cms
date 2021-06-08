<?php
declare(strict_types=1);

namespace sql;

use app;
use DomainException;

/**
 * INSERT part
 */
function insert(string $table): string
{
    return 'INSERT INTO ' . $table;
}

/**
 * VALUES part
 */
function values(array $cols): string
{
    return ' (' . implode(', ', array_keys($cols)) . ') VALUES (' . implode(', ', $cols) . ')';
}

/**
 * UPDATE part
 */
function update(string $table): string
{
    return 'UPDATE ' . $table;
}

/**
 * SET part
 */
function set(array $cols): string
{
    $sql = '';

    foreach ($cols as $col => $val) {
        $sql .= ($sql ? ', ' : '') . $col . ' = ' . $val;
    }

    return ' SET ' . $sql;
}

/**
 * DELETE part
 */
function delete(string $table): string
{
    return 'DELETE FROM ' . $table;
}

/**
 * SELECT part
 */
function select(array $sel): string
{
    $sql = '';

    foreach ($sel as $as => $col) {
        $sql .= ($sql ? ', ' : '') . $col . ($as && is_string($as) ? ' AS ' . $as : '');
    }

    return $sql ? ' SELECT ' . $sql : '';
}

/**
 * FROM part
 */
function from(string $table, string $as = null): string
{
    return ' FROM ' . $table . ($as ? ' AS ' . $as : '');
}

/**
 * WHERE part
 */
function where(array $cols): string
{
    return $cols ? ' WHERE ' . implode(' AND ', $cols) : '';
}

/**
 * JOIN part
 *
 * @throws DomainException
 */
function join(string $type, string $table, string $as = null, array $cols = []): string
{
    if (empty(APP['join'][$type]) || !$table) {
        throw new DomainException(app\i18n('Invalid JOIN'));
    }

    $on = $cols ? ' ON ' . implode(' AND ', $cols) : '';

    return APP['join'][$type] . ' JOIN ' . $table . ($as ? ' AS ' . $as : '') . $on;
}

/**
 * GROUP BY part
 */
function group(array $cols): string
{
    return $cols ? ' GROUP BY ' . implode(', ', $cols) : '';
}

/**
 * ORDER BY part
 */
function order(array $order): string
{
    $sql = '';

    foreach ($order as $attrId => $dir) {
        $sql .= ($sql ? ', ' : '') . $attrId . ($dir === 'desc' ? ' DESC NULLS LAST' : ' ASC NULLS FIRST');
    }

    return $sql ? ' ORDER BY ' . $sql : '';
}

/**
 * LIMIT part
 */
function limit(int $limit, int $offset = 0): string
{
    return $limit > 0 ? ' LIMIT ' . $limit . ' OFFSET ' . max(0, $offset) : '';
}

/**
 * WITH part
 */
function with(array $with, bool $recursive = false): string
{
    $sql = '';

    foreach ($with as $name => $part) {
        $sql .= ($sql ? ', ' : ' ') . $name . ' AS (' . $part . ')';
    }

    return 'WITH ' . ($recursive ? 'RECURSIVE ' : '') . $sql;
}

/**
 * RETURNING part
 */
function returning(array $cols): string
{
    return $cols ? ' RETURNING ' . implode(', ', $cols) : '';
}

/**
 * UNION part
 */
function union(): string
{
    RETURN ' UNION ';
}
