<?php
namespace qnd;

/**
 * Returns fully qualified name
 *
 * @param string $name
 *
 * @return string
 */
function fqn(string $name): string
{
    return __NAMESPACE__ . '\\' . $name;
}

/**
 * Joins array values or keys + values with a string and optionally
 *
 * - prepends a string to each element
 * - appends a string to each element
 * - applies a callback to each value
 *
 * @param string $glue
 * @param array $pieces
 * @param array $options
 *
 * @return string
 */
function stringify(string $glue, array $pieces, array $options = null): string
{
    foreach ($pieces as $key => $value) {
        $pre = $options['pre'] ?? '';
        $post = $options['post'] ?? '';
        $sep = $options['sep'] ?? ' => ';
        $k = !empty($options['keys']) ? $key . $sep : '';
        $value = !empty($options['call']) ? $options['call']($value) : $value;
        $pieces[$key] = $pre . $k . string($value) . $post;
    }

    return implode($glue, $pieces);
}

/**
 * Converts a variable to string
 *
 * @param mixed $var
 *
 * @return string
 */
function string($var): string
{
    if (is_array($var)) {
        return '[' . stringify(', ', $var, ['keys' => true]) . ']';
    } elseif (is_object($var) && !is_callable($var, '__toString')) {
        return get_class($var);
    } elseif (is_resource($var)) {
        return get_resource_type($var);
    } elseif (is_bool($var)) {
        return $var ? 'true' : 'false';
    } elseif (is_null($var)) {
        return '';
    }

    return (string) $var;
}
