<?php
declare(strict_types=1);

namespace sql;

function insert(string $table): string
{
    return 'INSERT INTO ' . $table;
}

function values(array $cols): string
{
    return ' (' . implode(', ', array_keys($cols)) . ') VALUES (' . implode(', ', $cols) . ')';
}

function update(string $table): string
{
    return 'UPDATE ' . $table;
}

function set(array $cols): string
{
    $sql = '';

    foreach ($cols as $col => $val) {
        $sql .= ($sql ? ', ' : '') . $col . ' = ' . $val;
    }

    return ' SET ' . $sql;
}

function delete(string $table): string
{
    return 'DELETE FROM ' . $table;
}

function select(array $sel): string
{
    $sql = '';

    foreach ($sel as $as => $col) {
        $sql .= ($sql ? ', ' : '') . $col . ($as && is_string($as) ? ' AS ' . $as : '');
    }

    return $sql ? ' SELECT ' . $sql : '';
}

function from(string $table, string $as = null): string
{
    return ' FROM ' . $table . ($as ? ' AS ' . $as : '');
}

function where(array $cols): string
{
    return $cols ? ' WHERE ' . implode(' AND ', $cols) : '';
}

function natural_join(string $table, string $as = null, array $cols = []): string
{
    $on = $cols ? ' ON ' . implode(' AND ', $cols) : '';

    return ' NATURAL JOIN ' . $table . ($as ? ' AS ' . $as : '') . $on;
}

function inner_join(string $table, string $as = null, array $cols = []): string
{
    $on = $cols ? ' ON ' . implode(' AND ', $cols) : '';

    return ' INNER JOIN ' . $table . ($as ? ' AS ' . $as : '') . $on;
}

function left_join(string $table, string $as = null, array $cols = []): string
{
    $on = $cols ? ' ON ' . implode(' AND ', $cols) : '';

    return ' LEFT JOIN ' . $table . ($as ? ' AS ' . $as : '') . $on;
}

function right_join(string $table, string $as = null, array $cols = []): string
{
    $on = $cols ? ' ON ' . implode(' AND ', $cols) : '';

    return ' RIGHT JOIN ' . $table . ($as ? ' AS ' . $as : '') . $on;
}

function full_join(string $table, string $as = null, array $cols = []): string
{
    $on = $cols ? ' ON ' . implode(' AND ', $cols) : '';

    return ' FULL JOIN ' . $table . ($as ? ' AS ' . $as : '') . $on;
}

function cross_join(string $table, string $as = null): string
{
    return ' CROSS JOIN ' . $table . ($as ? ' AS ' . $as : '');
}

function group(array $cols): string
{
    return $cols ? ' GROUP BY ' . implode(', ', $cols) : '';
}

function order(array $order): string
{
    $sql = '';

    foreach ($order as $col => $dir) {
        $sql .= ($sql ? ', ' : '') . $col . ($dir === 'desc' ? ' DESC NULLS LAST' : ' ASC NULLS FIRST');
    }

    return $sql ? ' ORDER BY ' . $sql : '';
}

function limit(int $limit, int $offset = 0): string
{
    return $limit > 0 ? ' LIMIT ' . $limit . ' OFFSET ' . max(0, $offset) : '';
}

function with(array $with, bool $recursive = false): string
{
    $sql = '';

    foreach ($with as $name => $part) {
        $sql .= ($sql ? ', ' : ' ') . $name . ' AS (' . $part . ')';
    }

    return 'WITH ' . ($recursive ? 'RECURSIVE ' : '') . $sql;
}

function returning(array $cols): string
{
    return $cols ? ' RETURNING ' . implode(', ', $cols) : '';
}

function union(): string
{
    return ' UNION ';
}
