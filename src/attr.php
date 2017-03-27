<?php
declare(strict_types = 1);

namespace qnd;

use DomainException;
use RuntimeException;

/**
 * Cast to appropriate php type
 *
 * @param array $attr
 * @param mixed $value
 *
 * @return mixed
 */
function cast(array $attr, $value)
{
    if ($attr['nullable'] && ($value === null || $value === '')) {
        return null;
    }

    if ($attr['backend'] === 'bool') {
        return (bool) $value;
    }

    if ($attr['backend'] === 'int') {
        return (int) $value;
    }

    if ($attr['backend'] === 'decimal') {
        return (float) $value;
    }

    if ($attr['multiple'] && is_array($value)) {
        foreach ($value as $k => $v) {
            $value[$k] = cast($attr, $v);
        }

        return $value;
    }

    return (string) $value;
}

/**
 * Check wheter attribute can be ignored
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function ignorable(array $attr, array $item): bool
{
    return !empty($item['_old'][$attr['id']]) && $attr['frontend'] === 'file';
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

    if ($attr['type'] === 'entity') {
        return array_column(all(...$attr['opt']), 'name', 'id');
    }

    if (empty($attr['opt'][0])) {
        return [];
    }

    if (is_array($attr['opt'][0])) {
        return $attr['opt'][0];
    }

    $call = fqn($attr['opt'][0]);
    $params = $attr['opt'][1] ?? [];

    return $call(...$params);
}

/**
 * Privilege options
 *
 * @return array
 */
function opt_privilege(): array
{
    return array_map(
        function ($item) {
            return $item['name'];
        },
        array_filter(
            data('privilege'),
            function ($item) {
                return empty($item['callback']);
            }
        )
    );
}

/**
 * Loader
 *
 * @param array $attr
 * @param array $item
 *
 * @return mixed
 */
function loader(array $attr, array $item)
{
    $item[$attr['id']] = cast($attr, $item[$attr['id']] ?? null);

    return $attr['loader'] && ($call = fqn('loader_' . $attr['loader'])) ? $call($attr, $item) : $item[$attr['id']];
}

/**
 * JSON loader
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 */
function loader_json(array $attr, array $item): array
{
    if (empty($item[$attr['id']])) {
        return [];
    }

    if (is_array($item[$attr['id']])) {
        return $item[$attr['id']];
    }

    return json_decode($item[$attr['id']], true) ?: [];
}

/**
 * Saver
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 */
function saver(array $attr, array $item): array
{
    return $attr['saver'] && ($call = fqn('saver_' . $attr['saver'])) ? $call($attr, $item) : $item;
}

/**
 * Password saver
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 */
function saver_password(array $attr, array $item): array
{
    if ($item[$attr['id']]) {
        $item[$attr['id']] = password_hash($item[$attr['id']], PASSWORD_DEFAULT);
    }

    return $item;
}

/**
 * File saver
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 *
 * @throws RuntimeException
 */
function saver_file(array $attr, array $item): array
{
    $file = http_files('data')[$item['_id']][$attr['id']] ?? null;

    if ($item[$attr['id']] && (!$file || !file_upload($file['tmp_name'], $item[$attr['id']]))) {
        throw new RuntimeException(_('File upload failed'));
    }

    return $item;
}

/**
 * Validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 */
function validator(array $attr, array $item): array
{
    $item[$attr['id']] = cast($attr, $item[$attr['id']] ?? null);

    if ($attr['nullable'] && $item[$attr['id']] === null) {
        return $item;
    }

    $attr['opt'] = opt($attr);

    if ($attr['validator']) {
        $call = fqn('validator_' . $attr['validator']);
        $item = $call($attr, $item);
    }

    validator_uniq($attr, $item);
    validator_required($attr, $item);
    validator_boundary($attr, $item);

    return $item;
}

/**
 * Required validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_required(array $attr, array $item): array
{
    if ($attr['required'] && ($item[$attr['id']] === null || $item[$attr['id']] === '') && !ignorable($attr, $item)) {
        throw new DomainException(_('%s is required', $attr['name']));
    }

    return $item;
}

/**
 * Unique validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_uniq(array $attr, array $item): array
{
    if ($attr['uniq'] && $item[$attr['id']] !== ($item['_old'][$attr['id']] ?? null) && size($item['_entity']['id'], [$attr['id'] => $item[$attr['id']]])) {
        throw new DomainException(_('%s must be unique', $attr['name']));
    }

    return $item;
}

/**
 * Boundary validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_boundary(array $attr, array $item): array
{
    $values = $attr['multiple'] && is_array($item[$attr['id']]) ? $item[$attr['id']] : [$item[$attr['id']]];

    foreach ($values as $value) {
        if (in_array($attr['backend'], ['json', 'text', 'varchar'])) {
            $value = strlen($value);
        }

        if ($attr['minval'] > 0 && $value < $attr['minval'] || $attr['maxval'] > 0 && $value > $attr['maxval']) {
            throw new DomainException(_('Value out of range'));
        }
    }

    return $item;
}

/**
 * Option validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_opt(array $attr, array $item): array
{
    if (is_array($item[$attr['id']])) {
        $item[$attr['id']] = array_filter(
            $item[$attr['id']],
            function ($value) {
                return !empty($value) || !is_string($value);
            }
        );
    }

    if (!empty($item[$attr['id']]) || is_scalar($item[$attr['id']]) && !is_string($item[$attr['id']])) {
        foreach ((array) $item[$attr['id']] as $v) {
            if (!isset($attr['opt'][$v])) {
                throw new DomainException(_('Invalid option for attribute %s', $attr['name']));
            }
        }
    }

    return $item;
}

/**
 * Text validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 */
function validator_text(array $attr, array $item): array
{
    $item[$attr['id']] = trim((string) filter_var($item[$attr['id']], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR));

    return $item;
}

/**
 * ID validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 */
function validator_id(array $attr, array $item): array
{
    $item = validator_text($attr, $item);
    $item[$attr['id']] = filter_id($item[$attr['id']]);

    return $item;
}

/**
 * Color validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_color(array $attr, array $item): array
{
    if ($item[$attr['id']] && !preg_match('/#[a-f0-9]{6}/', $item[$attr['id']])) {
         throw new DomainException(_('Invalid color'));
    }

    return $item;
}

/**
 * Email validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_email(array $attr, array $item): array
{
    if ($item[$attr['id']] && !($item[$attr['id']] = filter_var($item[$attr['id']], FILTER_VALIDATE_EMAIL))) {
         throw new DomainException(_('Invalid email'));
    }

    return $item;
}

/**
 * URL validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_url(array $attr, array $item): array
{
    if ($item[$attr['id']] && !($item[$attr['id']] = filter_var($item[$attr['id']], FILTER_VALIDATE_URL))) {
         throw new DomainException(_('Invalid URL'));
    }

    return $item;
}

/**
 * JSON validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_json(array $attr, array $item): array
{
    if ($item[$attr['id']] && json_decode($item[$attr['id']], true) === null) {
         throw new DomainException(_('Invalid JSON notation'));
    }

    if (!$item[$attr['id']]) {
        $item[$attr['id']] = '[]';
    }

    return $item;
}

/**
 * Rich text validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 */
function validator_rte(array $attr, array $item): array
{
    $item[$attr['id']] = filter_html($item[$attr['id']]);

    return $item;
}

/**
 * Date validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_date(array $attr, array $item): array
{
    $in = data('app', 'date.frontend');
    $out = data('app', 'date.backend');

    if ($item[$attr['id']] && !($item[$attr['id']] = filter_date($item[$attr['id']], $in, $out))) {
        throw new DomainException(_('Invalid value'));
    }

    return $item;
}

/**
 * Datetime validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_datetime(array $attr, array $item): array
{
    $in = data('app', 'datetime.frontend');
    $out = data('app', 'datetime.backend');

    if ($item[$attr['id']] && !($item[$attr['id']] = filter_date($item[$attr['id']], $in, $out))) {
        throw new DomainException(_('Invalid value'));
    }

    return $item;
}

/**
 * Time validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_time(array $attr, array $item): array
{
    $in = data('app', 'time.frontend');
    $out = data('app', 'time.backend');

    if ($item[$attr['id']] && !($item[$attr['id']] = filter_date($item[$attr['id']], $in, $out))) {
        throw new DomainException(_('Invalid value'));
    }

    return $item;
}

/**
 * File validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_file(array $attr, array $item): array
{
    if ($file = http_files('data')[$item['_id']][$attr['id']] ?? '') {
        if (!in_array($attr['type'], data('file', $file['ext']) ?? [])) {
            throw new DomainException(_('Invalid file %s', $file['name']));
        }

        if (($item['_old'][$attr['id']] ?? null) === $file['name']) {
            $item[$attr['id']] = $file['name'];
        } else {
            $item[$attr['id']] = filter_file($file['name'], project_path('media'));
        }
    }

    return $item;
}

/**
 * Editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor(array $attr, array $item): string
{
    if (!in_array($attr['context'], $attr['actions'])) {
        return '';
    }

    $item[$attr['id']] = $item[$attr['id']] ?? $attr['val'];
    $attr['opt'] = opt($attr);
    $attr['html']['id'] =  html_id($attr, $item);
    $attr['html']['name'] =  html_name($attr, $item);
    $attr['html']['data-type'] =  $attr['type'];

    if ($attr['required'] && !ignorable($attr, $item)) {
        $attr['html']['required'] = true;
    }

    if ($attr['multiple']) {
        $attr['html']['multiple'] = true;
    }

    if (!empty($item['_error'][$attr['id']])) {
        $attr['html']['class'] = empty($attr['html']['class']) ? 'invalid' : $attr['html']['class'] . ' invalid';
    }

    if ($attr['editor'] && ($call = fqn('editor_' . $attr['editor'])) && ($html = $call($attr, $item))) {
        return html_label($attr, $item) . $html . html_message($attr, $item);
    }

    return '';
}

/**
 * Select editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_select(array $attr, array $item): string
{
    $value = $item[$attr['id']];

    if (!is_array($value)) {
        $value = !$value && !is_numeric($value) ? [] : [$value];
    }

    if (empty($attr['opt'])) {
        $html = html_tag('optgroup', ['label' => _('No options configured')]);
    } else {
        $html = html_tag('option', ['value' => ''], _('Please choose'));

        foreach ($attr['opt'] as $optId => $optVal) {
            $a = ['value' => $optId];

            if (in_array($optId, $value)) {
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
 * @param array $item
 *
 * @return string
 */
function editor_opt(array $attr, array $item): string
{
    if (!$attr['opt']) {
        return html_tag('span', ['id' => $attr['html']['id']], _('No options configured'));
    } elseif ($attr['backend'] === 'bool' && $attr['frontend'] === 'checkbox') {
        $attr['opt'] = [1 => _('Yes')];
    }

    $value = $item[$attr['id']];

    if ($attr['backend'] === 'bool') {
        $value = [(int) $value];
    } elseif (!is_array($value)) {
        $value = !$value && !is_numeric($value) ? [] : [$value];
    }

    $html = '';

    foreach ($attr['opt'] as $optId => $optVal) {
        $htmlId = $attr['html']['id'] . '-' . $optId;
        $a = [
            'id' => $htmlId,
            'name' => $attr['html']['name'],
            'type' => $attr['frontend'],
            'value' => $optId,
            'checked' => in_array($optId, $value)
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
 * @param array $item
 *
 * @return string
 */
function editor_text(array $attr, array $item): string
{
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $item[$attr['id']] ? encode($item[$attr['id']]) : $item[$attr['id']];

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
 * @param array $item
 *
 * @return string
 */
function editor_password(array $attr, array $item): string
{
    $item[$attr['id']] = null;
    $attr['html']['autocomplete'] = 'off';

    return editor_text($attr, $item);
}

/**
 * Int editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_int(array $attr, array $item): string
{
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $item[$attr['id']];

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
 * @param array $item
 *
 * @return string
 */
function editor_date(array $attr, array $item): string
{
    $in = data('app', 'date.backend');
    $out = data('app', 'date.frontend');
    $item[$attr['id']] = $item[$attr['id']] ? filter_date($item[$attr['id']], $in, $out) : '';
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $item[$attr['id']];

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
 * @param array $item
 *
 * @return string
 */
function editor_datetime(array $attr, array $item): string
{
    $in = data('app', 'datetime.backend');
    $out = data('app', 'datetime.frontend');
    $item[$attr['id']] = $item[$attr['id']] ? filter_date($item[$attr['id']], $in, $out) : '';
    $attr['html']['type'] = 'datetime-local';
    $attr['html']['value'] = $item[$attr['id']];

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
 * @param array $item
 *
 * @return string
 */
function editor_time(array $attr, array $item): string
{
    $in = data('app', 'time.backend');
    $out = data('app', 'time.frontend');
    $item[$attr['id']] = $item[$attr['id']] ? filter_date($item[$attr['id']], $in, $out) : '';
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $item[$attr['id']];

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
 * @param array $item
 *
 * @return string
 */
function editor_file(array $attr, array $item): string
{
    $current = $item[$attr['id']] ? html_tag('div', [], viewer($attr, $item)) : '';
    $hidden = html_tag('input', ['name' => $attr['html']['name'], 'type' => 'hidden'], null, true);
    $attr['html']['type'] = $attr['frontend'];

    return $current . $hidden . html_tag('input', $attr['html'], null, true);
}

/**
 * Textarea editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_textarea(array $attr, array $item): string
{
    $item[$attr['id']] = $item[$attr['id']] ? encode($item[$attr['id']]) : $item[$attr['id']];

    if ($attr['minval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['minlength'] = $attr['minval'];
    }

    if ($attr['maxval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['maxlength'] = $attr['maxval'];
    }

    return html_tag('textarea', $attr['html'], $item[$attr['id']]);
}

/**
 * JSON editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_json(array $attr, array $item): string
{
    if (is_array($item[$attr['id']])) {
        $item[$attr['id']] = !empty($item[$attr['id']]) ? json_encode($item[$attr['id']]) : '';
    }

    return editor_textarea($attr, $item);
}

/**
 * Viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer(array $attr, array $item): string
{
    if (!in_array($attr['context'], $attr['actions'])) {
        return '';
    }

    $attr['opt'] = opt($attr);

    if ($attr['viewer'] && ($call = fqn('viewer_' . $attr['viewer']))) {
        return $call($attr, $item);
    }

    return $item[$attr['id']] ? encode((string) $item[$attr['id']]) : (string) $item[$attr['id']];
}

/**
 * Option viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_opt(array $attr, array $item): string
{
    if ($attr['opt'] && $item[$attr['id']]) {
        $values = array_intersect_key($attr['opt'], array_fill_keys((array) $item[$attr['id']], null));
    }

    return !empty($values) ? encode(implode(', ', $values)) : '';
}

/**
 * Date viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_date(array $attr, array $item): string
{
    return $item[$attr['id']] ? date_format(date_create($item[$attr['id']]), data('app', 'date.view')) : '';
}

/**
 * Datetime viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_datetime(array $attr, array $item): string
{
    return $item[$attr['id']] ? date_format(date_create($item[$attr['id']]), data('app', 'datetime.view')) : '';
}

/**
 * Time viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_time(array $attr, array $item): string
{
    return $item[$attr['id']] ? date_format(date_create($item[$attr['id']]), data('app', 'time.view')) : '';
}

/**
 * Rich text viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_rte(array $attr, array $item): string
{
    return (string) $item[$attr['id']];
}

/**
 * Iframe viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_iframe(array $attr, array $item): string
{
    return $item[$attr['id']] ? html_tag('figure', ['class' => 'iframe'], html_tag('iframe', ['src' => $item[$attr['id']], 'allowfullscreen' => true])) : '';
}

/**
 * Audio viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_audio(array $attr, array $item): string
{
    return $item[$attr['id']] ? html_tag('audio', ['src' => url_media($item[$attr['id']]), 'controls' => true]) : '';
}

/**
 * Embed viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_embed(array $attr, array $item): string
{
    return $item[$attr['id']] ? html_tag('embed', ['src' => url_media($item[$attr['id']])], null, true) : '';
}

/**
 * File viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_file(array $attr, array $item): string
{
    return $item[$attr['id']] ? html_tag('a', ['href' => url_media($item[$attr['id']])], $item[$attr['id']]) : '';
}

/**
 * Image viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_image(array $attr, array $item): string
{
    return $item[$attr['id']] ? html_tag('img', ['src' => image($item[$attr['id']], $attr['context']), 'alt' => $item[$attr['id']]], null, true) : '';
}

/**
 * Object viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_object(array $attr, array $item): string
{
    return $item[$attr['id']] ? html_tag('object', ['data' => url_media($item[$attr['id']])]) : '';
}

/**
 * Video viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_video(array $attr, array $item): string
{
    return $item[$attr['id']] ? html_tag('video', ['src' => url_media($item[$attr['id']]), 'controls' => true]) : '';
}

/**
 * Filesize viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_filesize(array $attr, array $item): string
{
    if (!$item[$attr['id']]) {
        return '';
    }

    if ($item[$attr['id']] < 1000) {
        return $item[$attr['id']] . ' B';
    }

    if ($item[$attr['id']] > 1000000) {
        return round($item[$attr['id']] / 1000000, 1) . ' MB';
    }

    return round($item[$attr['id']] / 1000, 1) . ' kB';
}
