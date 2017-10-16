<?php
declare(strict_types = 1);

namespace arr;

use const ent\CRIT;
use app;
use RuntimeException;

/**
 * Filter data by given criteria
 *
 * @throws RuntimeException
 */
function filter(array $data, array $crit): array
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
                $op = $c[2] ?? CRIT['='];

                if (empty(CRIT[$op]) || is_array($val) && !$val) {
                    throw new RuntimeException(app\i18n('Invalid criteria'));
                }

                switch ($op) {
                    case CRIT['=']:
                        $call = function ($a, $v): bool {
                            return $a === $v;
                        };
                        break;
                    case CRIT['!=']:
                        $call = function ($a, $v): bool {
                            return $a !== $v;
                        };
                        break;
                    case CRIT['>']:
                        $call = function ($a, $v): bool {
                            return $a > $v;
                        };
                        break;
                    case CRIT['>=']:
                        $call = function ($a, $v): bool {
                            return $a >= $v;
                        };
                        break;
                    case CRIT['<']:
                        $call = function ($a, $v): bool {
                            return $a < $v;
                        };
                        break;
                    case CRIT['<=']:
                        $call = function ($a, $v): bool {
                            return $a <= $v;
                        };
                        break;
                    case CRIT['~']:
                        $call = function ($a, $v): bool {
                            return stripos($a, $v) !== false;
                        };
                        break;
                    case CRIT['!~']:
                        $call = function ($a, $v): bool {
                            return stripos($a, $v) === false;
                        };
                        break;
                    case CRIT['~^']:
                        $call = function ($a, $v): bool {
                            return stripos($a, $v) === 0;
                        };
                        break;
                    case CRIT['!~^']:
                        $call = function ($a, $v): bool {
                            return stripos($a, $v) !== 0;
                        };
                        break;
                    case CRIT['~$']:
                        $call = function ($a, $v): bool {
                            return strtolower(substr($a, -strlen($v))) === strtolower($v);
                        };
                        break;
                    case CRIT['!~$']:
                        $call = function ($a, $v): bool {
                            return strtolower(substr($a, -strlen($v))) !== strtolower($v);
                        };
                        break;
                    default:
                        throw new RuntimeException(app\i18n('Invalid criteria'));
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
