<?php
declare(strict_types = 1);

namespace qnd;

use RuntimeException;

/**
 * Load data from file
 *
 * @param string $file
 *
 * @return array
 */
function arr_load(string $file): array
{
    return is_readable($file) && ($data = include $file) && is_array($data) ? $data : [];
}

/**
 * Filter data by given criteria
 *
 * @param array $data
 * @param array $crit
 *
 * @return array
 */
function arr_filter(array $data, array $crit): array
{
    return array_filter(
        $data,
        function (array $item) use ($crit) {
            foreach ($crit as $part) {
                $part = is_array($part[0]) ? $part : [$part];

                foreach ($part as $c) {
                    if (!array_key_exists($c[0], $item)) {
                        return false;
                    }

                    $a = $item[$c[0]];
                    $val = $c[1] ?? null;
                    $op = $c[2] ?? CRIT['='];

                    if (empty(CRIT[$op]) || is_array($val) && !$val) {
                        throw new RuntimeException(_('Invalid criteria'));
                    }

                    switch ($op) {
                        case CRIT['=']:
                            $call = function ($a, $v) {
                                return $a === $v;
                            };
                            break;
                        case CRIT['!=']:
                            $call = function ($a, $v) {
                                return $a !== $v;
                            };
                            break;
                        case CRIT['>']:
                            $call = function ($a, $v) {
                                return $a > $v;
                            };
                            break;
                        case CRIT['>=']:
                            $call = function ($a, $v) {
                                return $a >= $v;
                            };
                            break;
                        case CRIT['<']:
                            $call = function ($a, $v) {
                                return $a < $v;
                            };
                            break;
                        case CRIT['<=']:
                            $call = function ($a, $v) {
                                return $a <= $v;
                            };
                            break;
                        case CRIT['~']:
                        case CRIT['@@']:
                            $call = function ($a, $v) {
                                return stripos($a, $v) !== false;
                            };
                            break;
                        case CRIT['!~']:
                        case CRIT['!!']:
                            $call = function ($a, $v) {
                                return stripos($a, $v) === false;
                            };
                            break;
                        case CRIT['~^']:
                            $call = function ($a, $v) {
                                return stripos($a, $v) === 0;
                            };
                            break;
                        case CRIT['!~^']:
                            $call = function ($a, $v) {
                                return stripos($a, $v) !== 0;
                            };
                            break;
                        case CRIT['~$']:
                            $call = function ($a, $v) {
                                return strtolower(substr($a, -strlen($v))) === strtolower($v);
                            };
                            break;
                        case CRIT['!~$']:
                            $call = function ($a, $v) {
                                return strtolower(substr($a, -strlen($v))) !== strtolower($v);
                            };
                            break;
                        default:
                            throw new RuntimeException(_('Invalid criteria'));
                    }

                    $val = is_array($val) ? $val : [$val];
                    $match = false;

                    foreach ($val as $v) {
                        if ($call($a, $v)) {
                            $match = true;
                            break;
                        }
                    }

                    if (!$match) {
                        return false;
                    }
                }
            }

            return true;
        }
    );
}

/**
 * Order data
 *
 * @param array $data
 * @param array $order
 *
 * @return array
 */
function arr_order(array $data, array $order): array
{
    uasort(
        $data,
        function (array $a, array $b) use ($order) {
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
