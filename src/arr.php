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
    $crit = array_filter($crit);

    foreach ($data as $id => $item) {
        foreach ($crit as $part) {
            $match = false;
            $part = is_array($part[0]) ? $part : [$part];

            foreach ($part as $c) {
                if (empty($c[0]) || !array_key_exists($c[0], $item)) {
                    throw new DomainException(app\i18n('Invalid criteria'));
                }

                $d = $item[$c[0]];
                $val = $c[1] ?? null;
                $op = $c[2] ?? APP['op']['='];
                $isCol = !empty($c[3]);

                if (empty(APP['op'][$op]) || !is_array($d) && is_array($val) && !$val) {
                    throw new DomainException(app\i18n('Invalid criteria'));
                }

                switch ($op) {
                    case APP['op']['=']:
                        $call = function ($a, $b): bool {
                            return $a === $b;
                        };
                        break;
                    case APP['op']['!=']:
                        $call = function ($a, $b): bool {
                            return $a !== $b;
                        };
                        break;
                    case APP['op']['>']:
                        $call = function ($a, $b): bool {
                            return $a > $b;
                        };
                        break;
                    case APP['op']['>=']:
                        $call = function ($a, $b): bool {
                            return $a >= $b;
                        };
                        break;
                    case APP['op']['<']:
                        $call = function ($a, $b): bool {
                            return $a < $b;
                        };
                        break;
                    case APP['op']['<=']:
                        $call = function ($a, $b): bool {
                            return $a <= $b;
                        };
                        break;
                    case APP['op']['~']:
                        $call = function ($a, $b): bool {
                            return stripos($a, $b) !== false;
                        };
                        break;
                    case APP['op']['!~']:
                        $call = function ($a, $b): bool {
                            return stripos($a, $b) === false;
                        };
                        break;
                    case APP['op']['^']:
                        $call = function ($a, $b): bool {
                            return stripos($a, $b) === 0;
                        };
                        break;
                    case APP['op']['!^']:
                        $call = function ($a, $b): bool {
                            return stripos($a, $b) !== 0;
                        };
                        break;
                    case APP['op']['$']:
                        $call = function ($a, $b): bool {
                            return mb_strtolower(substr($a, -mb_strlen($b))) === mb_strtolower($b);
                        };
                        break;
                    case APP['op']['!$']:
                        $call = function ($a, $b): bool {
                            return mb_strtolower(substr($a, -mb_strlen($b))) !== mb_strtolower($b);
                        };
                        break;
                    default:
                        throw new DomainException(app\i18n('Invalid criteria'));
                }

                $val = is_array($val) ? $val : [$val];

                foreach ($val as $v) {
                    if ($isCol) {
                        $v = $item[$v] ?? null;
                    }

                    if ($call($d, $v)) {
                        $match = true;
                        break 2;
                    }
                }
            }

            if (!$match) {
                unset($data[$id]);
                break;
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
