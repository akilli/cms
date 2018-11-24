<?php
declare(strict_types = 1);

namespace arr;

use app;
use DomainException;

/**
 * Filter data by given criteria
 *
 * @throws DomainException
 */
function crit(array $data, array $crit): array
{
    foreach ($data as $id => $item) {
        foreach ($crit as $part) {
            $part = is_array($part[0]) ? $part : [$part];

            foreach ($part as $c) {
                if (!array_key_exists($c[0], $item)) {
                    unset($data[$id]);
                    break 2;
                }

                $a = $item[$c[0]];
                $val = $c[1] ?? null;
                $op = $c[2] ?? APP['crit']['='];
                $isCol = !empty($c[3]);

                if (empty(APP['crit'][$op]) || is_array($val) && !$val) {
                    throw new DomainException(app\i18n('Invalid criteria'));
                }

                switch ($op) {
                    case APP['crit']['=']:
                        $call = function ($a, $v): bool {
                            return $a === $v;
                        };
                        break;
                    case APP['crit']['!=']:
                        $call = function ($a, $v): bool {
                            return $a !== $v;
                        };
                        break;
                    case APP['crit']['>']:
                        $call = function ($a, $v): bool {
                            return $a > $v;
                        };
                        break;
                    case APP['crit']['>=']:
                        $call = function ($a, $v): bool {
                            return $a >= $v;
                        };
                        break;
                    case APP['crit']['<']:
                        $call = function ($a, $v): bool {
                            return $a < $v;
                        };
                        break;
                    case APP['crit']['<=']:
                        $call = function ($a, $v): bool {
                            return $a <= $v;
                        };
                        break;
                    case APP['crit']['~']:
                        $call = function ($a, $v): bool {
                            return stripos($a, $v) !== false;
                        };
                        break;
                    case APP['crit']['!~']:
                        $call = function ($a, $v): bool {
                            return stripos($a, $v) === false;
                        };
                        break;
                    case APP['crit']['~^']:
                        $call = function ($a, $v): bool {
                            return stripos($a, $v) === 0;
                        };
                        break;
                    case APP['crit']['!~^']:
                        $call = function ($a, $v): bool {
                            return stripos($a, $v) !== 0;
                        };
                        break;
                    case APP['crit']['~$']:
                        $call = function ($a, $v): bool {
                            return mb_strtolower(substr($a, -mb_strlen($v))) === mb_strtolower($v);
                        };
                        break;
                    case APP['crit']['!~$']:
                        $call = function ($a, $v): bool {
                            return mb_strtolower(substr($a, -mb_strlen($v))) !== mb_strtolower($v);
                        };
                        break;
                    default:
                        throw new DomainException(app\i18n('Invalid criteria'));
                }

                $val = is_array($val) ? $val : [$val];
                $match = false;

                foreach ($val as $v) {
                    if ($isCol) {
                        $v = $item[$v] ?? null;
                    }

                    if ($call($a, $v)) {
                        $match = true;
                        break;
                    }
                }

                if (!$match) {
                    unset($data[$id]);
                    break 2;
                }
            }
        }
    }

    return $data;
}

/**
 * Order data
 */
function order(array $data, array $order): array
{
    uasort(
        $data,
        function (array $a, array $b) use ($order): int {
            foreach ($order as $key => $dir) {
                $factor = $dir === 'desc' ? -1 : 1;

                if ($result = ($a[$key] ?? null) <=> ($b[$key] ?? null)) {
                    return $result * $factor;
                }
            }

            return 0;
        }
    );

    return $data;
}

/**
 * Applies callback to all elements of given array with passing additional params
 */
function map(callable $call, array $data, ...$params): array
{
    foreach ($data as $key => $val) {
        $data[$key] = $call($val, ...$params);
    }

    return $data;
}

/**
 * Replaces elements from passed arrays into the base array without adding new keys
 */
function replace(array $base, array $data, array ...$add): array
{
    return array_intersect_key(array_replace($base, $data, ...$add), $base);
}

/**
 * Extracts given keys
 */
function extract(array $data, array $keys): array
{
    return $data && $keys ? replace(array_fill_keys(array_intersect($keys, array_keys($data)), null), $data) : [];
}
