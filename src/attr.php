<?php
declare(strict_types = 1);

namespace qnd;

/**
 * Cast to appropriate php type
 *
 * @param array $attr
 * @param mixed $val
 *
 * @return mixed
 */
function cast(array $attr, $val)
{
    if ($attr['nullable'] && ($val === null || $val === '')) {
        return null;
    }

    if ($attr['backend'] === 'bool') {
        return (bool) $val;
    }

    if ($attr['backend'] === 'int') {
        return (int) $val;
    }

    if ($attr['backend'] === 'decimal') {
        return (float) $val;
    }

    if ($attr['multiple'] && is_array($val)) {
        foreach ($val as $k => $v) {
            $val[$k] = cast($attr, $v);
        }

        return $val;
    }

    return (string) $val;
}

/**
 * Check wheter attribute can be ignored
 *
 * @param array $attr
 * @param array $data
 *
 * @return bool
 */
function ignorable(array $attr, array $data): bool
{
    return !empty($data['_old'][$attr['id']]) && $attr['frontend'] === 'file';
}

/**
 * Option
 *
 * @param array $attr
 *
 * @return array
 */
function opt(array $attr): array
{
    if ($attr['backend'] === 'bool') {
        return [_('No'), _('Yes')];
    }

    if (empty($attr['opt'][0])) {
        return [];
    }

    if ($attr['type'] === 'entity') {
        return opt_entity($attr);
    }

    $args = $attr['opt'][1] ?? [];

    return call($attr['opt'][0], ...$args);
}

/**
 * Loader
 *
 * @param array $attr
 * @param array $data
 *
 * @return mixed
 */
function loader(array $attr, array $data)
{
    $data[$attr['id']] = cast($attr, $data[$attr['id']] ?? null);

    return $attr['loader'] ? call('loader_' . $attr['loader'], $attr, $data) : $data[$attr['id']];
}

/**
 * Saver
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 */
function saver(array $attr, array $data): array
{
    return $attr['saver'] ? call('saver_' . $attr['saver'], $attr, $data) : $data;
}

/**
 * Validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 */
function validator(array $attr, array $data): array
{
    $data[$attr['id']] = cast($attr, $data[$attr['id']] ?? null);

    if ($attr['nullable'] && $data[$attr['id']] === null) {
        return $data;
    }

    $attr['opt'] = opt($attr);

    if ($attr['validator']) {
        $data = call('validator_' . $attr['validator'], $attr, $data);
    }

    validator_uniq($attr, $data);
    validator_required($attr, $data);
    validator_boundary($attr, $data);

    return $data;
}

/**
 * Editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor(array $attr, array $data): string
{
    if (!in_array($attr['context'], $attr['actions'])) {
        return '';
    }

    $data[$attr['id']] = $data[$attr['id']] ?? $attr['val'];
    $attr['opt'] = opt($attr);
    $attr['html']['id'] =  'data-' . $attr['id'];
    $attr['html']['name'] =  'data[' . $attr['id'] . ']' . (!empty($attr['multiple']) ? '[]' : '');
    $attr['html']['data-type'] =  $attr['type'];
    $label = $attr['name'];
    $error = '';

    if ($attr['required'] && !ignorable($attr, $data)) {
        $attr['html']['required'] = true;
        $label .= ' ' . html('em', ['class' => 'required'], _('Required'));
    }

    if ($attr['uniq']) {
        $label .= ' ' . html('em', ['class' => 'uniq'], _('Unique'));
    }

    if ($attr['multiple']) {
        $attr['html']['multiple'] = true;
    }

    if (!empty($data['_error'][$attr['id']])) {
        $attr['html']['class'] = empty($attr['html']['class']) ? 'invalid' : $attr['html']['class'] . ' invalid';
        $error = html('div', ['class' => 'message error'], $data['_error'][$attr['id']]);
    }

    if ($attr['editor'] && ($html = call('editor_' . $attr['editor'], $attr, $data))) {
        return html('label', ['for' => $attr['html']['id']], $label) . $html . $error;
    }

    return '';
}

/**
 * Viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer(array $attr, array $data): string
{
    if (!in_array($attr['context'], $attr['actions'])) {
        return '';
    }

    $attr['opt'] = opt($attr);

    if ($attr['viewer']) {
        return call('viewer_' . $attr['viewer'], $attr, $data);
    }

    return $data[$attr['id']] ? encode((string) $data[$attr['id']]) : (string) $data[$attr['id']];
}
