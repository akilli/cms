<?php
declare(strict_types = 1);

namespace qnd;

use DomainException;
use RuntimeException;

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
 * @todo Find a static cache solution for all options
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
        if (($data = & registry('opt.entity.' . $attr['opt'][0])) === null) {
            $data = $attr['opt'][0] === 'page' ? opt_page() : array_column(all($attr['opt'][0]), 'name', 'id');
        }

        return $data;
    }

    if (is_array($attr['opt'][0])) {
        return $attr['opt'][0];
    }

    $call = fqn($attr['opt'][0]);
    $params = $attr['opt'][1] ?? [];

    return $call(...$params);
}

/**
 * Page options
 *
 * @return array
 */
function opt_page(): array
{
    $data = [];

    foreach (all('tree', [], ['order' => ['pos' => 'asc']]) as $item) {
        $data[$item['id']] = $item['structure'] . ' ' . $item['name'];
    }

    return $data;
}

/**
 * Privilege options
 *
 * @return array
 */
function opt_privilege(): array
{
    return array_map(
        function ($data) {
            return $data['name'];
        },
        array_filter(
            data('privilege'),
            function ($data) {
                return empty($data['callback']);
            }
        )
    );
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

    return $attr['loader'] && ($call = fqn('loader_' . $attr['loader'])) ? $call($attr, $data) : $data[$attr['id']];
}

/**
 * JSON loader
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 */
function loader_json(array $attr, array $data): array
{
    if (empty($data[$attr['id']])) {
        return [];
    }

    if (is_array($data[$attr['id']])) {
        return $data[$attr['id']];
    }

    return json_decode($data[$attr['id']], true) ?: [];
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
    return $attr['saver'] && ($call = fqn('saver_' . $attr['saver'])) ? $call($attr, $data) : $data;
}

/**
 * Password saver
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 */
function saver_password(array $attr, array $data): array
{
    if ($data[$attr['id']]) {
        $data[$attr['id']] = password_hash($data[$attr['id']], PASSWORD_DEFAULT);
    }

    return $data;
}

/**
 * File saver
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws RuntimeException
 */
function saver_file(array $attr, array $data): array
{
    $file = http_files('data')[$attr['id']] ?? null;

    if ($data[$attr['id']] && (!$file || !file_upload($file['tmp_name'], $data[$attr['id']]))) {
        throw new RuntimeException(_('File upload failed'));
    }

    return $data;
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
        $call = fqn('validator_' . $attr['validator']);
        $data = $call($attr, $data);
    }

    validator_uniq($attr, $data);
    validator_required($attr, $data);
    validator_boundary($attr, $data);

    return $data;
}

/**
 * Required validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_required(array $attr, array $data): array
{
    if ($attr['required'] && ($data[$attr['id']] === null || $data[$attr['id']] === '') && !ignorable($attr, $data)) {
        throw new DomainException(_('%s is required', $attr['name']));
    }

    return $data;
}

/**
 * Unique validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_uniq(array $attr, array $data): array
{
    if ($attr['uniq'] && $data[$attr['id']] !== ($data['_old'][$attr['id']] ?? null) && size($data['_entity']['id'], [[$attr['id'], $data[$attr['id']]]])) {
        throw new DomainException(_('%s must be unique', $attr['name']));
    }

    return $data;
}

/**
 * Boundary validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_boundary(array $attr, array $data): array
{
    $vals = $attr['multiple'] && is_array($data[$attr['id']]) ? $data[$attr['id']] : [$data[$attr['id']]];

    foreach ($vals as $val) {
        if (in_array($attr['backend'], ['json', 'text', 'varchar'])) {
            $val = strlen($val);
        }

        if ($attr['minval'] > 0 && $val < $attr['minval'] || $attr['maxval'] > 0 && $val > $attr['maxval']) {
            throw new DomainException(_('Value out of range'));
        }
    }

    return $data;
}

/**
 * Option validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_opt(array $attr, array $data): array
{
    if (is_array($data[$attr['id']])) {
        $data[$attr['id']] = array_filter(
            $data[$attr['id']],
            function ($val) {
                return !empty($val) || !is_string($val);
            }
        );
    }

    if (!empty($data[$attr['id']]) || is_scalar($data[$attr['id']]) && !is_string($data[$attr['id']])) {
        foreach ((array) $data[$attr['id']] as $v) {
            if (!isset($attr['opt'][$v])) {
                throw new DomainException(_('Invalid option for attribute %s', $attr['name']));
            }
        }
    }

    return $data;
}

/**
 * Option validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_page(array $attr, array $data): array
{
    $old = $data['_old']['id'] ?? null;

    if ($data[$attr['id']] && $old && in_array($old, one('tree', [['id', $data[$attr['id']]]])['path'])) {
        throw new DomainException(_('Cannot assign the page itself or a child page as parent'));
    }

    return $data;
}

/**
 * Text validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 */
function validator_text(array $attr, array $data): array
{
    $data[$attr['id']] = trim((string) filter_var($data[$attr['id']], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR));

    return $data;
}

/**
 * ID validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 */
function validator_id(array $attr, array $data): array
{
    $data = validator_text($attr, $data);
    $data[$attr['id']] = filter_id($data[$attr['id']]);

    return $data;
}

/**
 * Color validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_color(array $attr, array $data): array
{
    if ($data[$attr['id']] && !preg_match('/#[a-f0-9]{6}/', $data[$attr['id']])) {
        throw new DomainException(_('Invalid color'));
    }

    return $data;
}

/**
 * Email validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_email(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter_var($data[$attr['id']], FILTER_VALIDATE_EMAIL))) {
        throw new DomainException(_('Invalid email'));
    }

    return $data;
}

/**
 * URL validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_url(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter_var($data[$attr['id']], FILTER_VALIDATE_URL))) {
        throw new DomainException(_('Invalid URL'));
    }

    return $data;
}

/**
 * JSON validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_json(array $attr, array $data): array
{
    if ($data[$attr['id']] && json_decode($data[$attr['id']], true) === null) {
        throw new DomainException(_('Invalid JSON notation'));
    }

    if (!$data[$attr['id']]) {
        $data[$attr['id']] = '[]';
    }

    return $data;
}

/**
 * Rich text validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 */
function validator_rte(array $attr, array $data): array
{
    $data[$attr['id']] = filter_html($data[$attr['id']]);

    return $data;
}

/**
 * Date validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_date(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter_date($data[$attr['id']], DATE['f'], DATE['b']))) {
        throw new DomainException(_('Invalid value'));
    }

    return $data;
}

/**
 * Datetime validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_datetime(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter_date($data[$attr['id']], DATETIME['f'], DATETIME['b']))) {
        throw new DomainException(_('Invalid value'));
    }

    return $data;
}

/**
 * Time validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_time(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter_date($data[$attr['id']], TIME['f'], TIME['b']))) {
        throw new DomainException(_('Invalid value'));
    }

    return $data;
}

/**
 * File validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_file(array $attr, array $data): array
{
    if ($file = http_files('data')[$attr['id']] ?? '') {
        if (!in_array($attr['type'], data('file', $file['ext']) ?? [])) {
            throw new DomainException(_('Invalid file %s', $file['name']));
        }

        if (($data['_old'][$attr['id']] ?? null) === $file['name']) {
            $data[$attr['id']] = $file['name'];
        } else {
            $data[$attr['id']] = filter_file($file['name'], path('media'));
        }
    }

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
    $attr['html']['id'] =  html_id($attr);
    $attr['html']['name'] =  html_name($attr);
    $attr['html']['data-type'] =  $attr['type'];
    $error = '';

    if ($attr['required'] && !ignorable($attr, $data)) {
        $attr['html']['required'] = true;
    }

    if ($attr['multiple']) {
        $attr['html']['multiple'] = true;
    }

    if (!empty($data['_error'][$attr['id']])) {
        $attr['html']['class'] = empty($attr['html']['class']) ? 'invalid' : $attr['html']['class'] . ' invalid';
        $error = html_tag('div', ['class' => 'message error'], $data['_error'][$attr['id']]);
    }

    if ($attr['editor'] && ($call = fqn('editor_' . $attr['editor'])) && ($html = $call($attr, $data))) {
        return html_label($attr) . $html . $error;
    }

    return '';
}

/**
 * Select editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_select(array $attr, array $data): string
{
    $val = $data[$attr['id']];

    if (!is_array($val)) {
        $val = !$val && !is_numeric($val) ? [] : [$val];
    }

    if (empty($attr['opt'])) {
        $html = html_tag('optgroup', ['label' => _('No options configured')]);
    } else {
        $html = html_tag('option', ['value' => ''], _('Please choose'));

        foreach ($attr['opt'] as $optId => $optVal) {
            $a = ['value' => $optId];

            if (in_array($optId, $val)) {
                $a['selected'] = true;
            }

            $html .= html_tag('option', $a, $optVal);
        }
    }

    return html_tag('select', $attr['html'], $html);
}

/**
 * Option editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_opt(array $attr, array $data): string
{
    if (!$attr['opt']) {
        return html_tag('span', ['id' => $attr['html']['id']], _('No options configured'));
    } elseif ($attr['backend'] === 'bool' && $attr['frontend'] === 'checkbox') {
        $attr['opt'] = [1 => _('Yes')];
    }

    $val = $data[$attr['id']];

    if ($attr['backend'] === 'bool') {
        $val = [(int) $val];
    } elseif (!is_array($val)) {
        $val = !$val && !is_numeric($val) ? [] : [$val];
    }

    $html = '';

    foreach ($attr['opt'] as $optId => $optVal) {
        $htmlId = $attr['html']['id'] . '-' . $optId;
        $a = [
            'id' => $htmlId,
            'name' => $attr['html']['name'],
            'type' => $attr['frontend'],
            'value' => $optId,
            'checked' => in_array($optId, $val)
        ];
        $a = array_replace($attr['html'], $a);
        $html .= html_tag('input', $a, null, true);
        $html .= html_tag('label', ['for' => $htmlId, 'class' => 'inline'], $optVal);
    }

    return $html;
}

/**
 * Text editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_text(array $attr, array $data): string
{
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $data[$attr['id']] ? encode($data[$attr['id']]) : $data[$attr['id']];

    if ($attr['minval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['minlength'] = $attr['minval'];
    }

    if ($attr['maxval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['maxlength'] = $attr['maxval'];
    }

    return html_tag('input', $attr['html'], null, true);
}

/**
 * Password editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_password(array $attr, array $data): string
{
    $data[$attr['id']] = null;
    $attr['html']['autocomplete'] = 'off';

    return editor_text($attr, $data);
}

/**
 * Int editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_int(array $attr, array $data): string
{
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $data[$attr['id']];

    if ($attr['minval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['min'] = $attr['minval'];
    }

    if ($attr['maxval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['max'] = $attr['maxval'];
    }

    return html_tag('input', $attr['html'], null, true);
}

/**
 * Date editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_date(array $attr, array $data): string
{
    $data[$attr['id']] = $data[$attr['id']] ? filter_date($data[$attr['id']], DATE['b'], DATE['f']) : '';
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $data[$attr['id']];

    if ($attr['minval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['min'] = $attr['minval'];
    }

    if ($attr['maxval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['max'] = $attr['maxval'];
    }

    return html_tag('input', $attr['html'], null, true);
}

/**
 * Datetime editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_datetime(array $attr, array $data): string
{
    $data[$attr['id']] = $data[$attr['id']] ? filter_date($data[$attr['id']], DATETIME['b'], DATETIME['f']) : '';
    $attr['html']['type'] = 'datetime-local';
    $attr['html']['value'] = $data[$attr['id']];

    if ($attr['minval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['min'] = $attr['minval'];
    }

    if ($attr['maxval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['max'] = $attr['maxval'];
    }

    return html_tag('input', $attr['html'], null, true);
}

/**
 * Time editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_time(array $attr, array $data): string
{
    $data[$attr['id']] = $data[$attr['id']] ? filter_date($data[$attr['id']], TIME['b'], TIME['f']) : '';
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $data[$attr['id']];

    if ($attr['minval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['min'] = $attr['minval'];
    }

    if ($attr['maxval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['max'] = $attr['maxval'];
    }

    return html_tag('input', $attr['html'], null, true);
}

/**
 * File editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_file(array $attr, array $data): string
{
    $current = $data[$attr['id']] ? html_tag('div', [], viewer($attr, $data)) : '';
    $hidden = html_tag('input', ['name' => $attr['html']['name'], 'type' => 'hidden'], null, true);
    $attr['html']['type'] = $attr['frontend'];

    return $current . $hidden . html_tag('input', $attr['html'], null, true);
}

/**
 * Textarea editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_textarea(array $attr, array $data): string
{
    $data[$attr['id']] = $data[$attr['id']] ? encode($data[$attr['id']]) : $data[$attr['id']];

    if ($attr['minval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['minlength'] = $attr['minval'];
    }

    if ($attr['maxval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['maxlength'] = $attr['maxval'];
    }

    return html_tag('textarea', $attr['html'], $data[$attr['id']]);
}

/**
 * JSON editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_json(array $attr, array $data): string
{
    if (is_array($data[$attr['id']])) {
        $data[$attr['id']] = !empty($data[$attr['id']]) ? json_encode($data[$attr['id']]) : '';
    }

    return editor_textarea($attr, $data);
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

    if ($attr['viewer'] && ($call = fqn('viewer_' . $attr['viewer']))) {
        return $call($attr, $data);
    }

    return $data[$attr['id']] ? encode((string) $data[$attr['id']]) : (string) $data[$attr['id']];
}

/**
 * Option viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_opt(array $attr, array $data): string
{
    $vals = [];

    foreach ((array) $data[$attr['id']] as $val) {
        if (isset($attr['opt'][$val])) {
            $vals[] = $attr['opt'][$val];
        }
    }

    return $vals ? encode(implode(', ', $vals)) : '';
}

/**
 * Date viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_date(array $attr, array $data): string
{
    return $data[$attr['id']] ? date_format(date_create($data[$attr['id']]), data('app', 'date')) : '';
}

/**
 * Datetime viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_datetime(array $attr, array $data): string
{
    return $data[$attr['id']] ? date_format(date_create($data[$attr['id']]), data('app', 'datetime')) : '';
}

/**
 * Time viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_time(array $attr, array $data): string
{
    return $data[$attr['id']] ? date_format(date_create($data[$attr['id']]), data('app', 'time')) : '';
}

/**
 * Rich text viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_rte(array $attr, array $data): string
{
    return (string) $data[$attr['id']];
}

/**
 * Iframe viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_iframe(array $attr, array $data): string
{
    return $data[$attr['id']] ? html_tag('figure', ['class' => 'iframe'], html_tag('iframe', ['src' => $data[$attr['id']], 'allowfullscreen' => true])) : '';
}

/**
 * Audio viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_audio(array $attr, array $data): string
{
    return $data[$attr['id']] ? html_tag('audio', ['src' => url_media($data[$attr['id']]), 'controls' => true]) : '';
}

/**
 * Embed viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_embed(array $attr, array $data): string
{
    return $data[$attr['id']] ? html_tag('embed', ['src' => url_media($data[$attr['id']])], null, true) : '';
}

/**
 * File viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_file(array $attr, array $data): string
{
    return $data[$attr['id']] ? html_tag('a', ['href' => url_media($data[$attr['id']])], $data[$attr['id']]) : '';
}

/**
 * Image viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_image(array $attr, array $data): string
{
    return $data[$attr['id']] ? html_tag('img', ['src' => image($data[$attr['id']], $attr['context']), 'alt' => $data[$attr['id']]], null, true) : '';
}

/**
 * Object viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_object(array $attr, array $data): string
{
    return $data[$attr['id']] ? html_tag('object', ['data' => url_media($data[$attr['id']])]) : '';
}

/**
 * Video viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_video(array $attr, array $data): string
{
    return $data[$attr['id']] ? html_tag('video', ['src' => url_media($data[$attr['id']]), 'controls' => true]) : '';
}
