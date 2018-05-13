<?php
declare(strict_types = 1);

namespace block;

use app;
use arr;
use attr;
use ent;
use html;
use req;
use session;
use DomainException;

/**
 * Container
 */
function container(array $§): string
{
    $html = '';

    foreach (arr\order(arr\crit(app\layout(), [['parent', $§['id']]]), ['sort' => 'asc']) as $child) {
        $html .= app\§($child['id']);
    }

    return $§['vars']['tag'] ? html\tag($§['vars']['tag'], ['id' => $§['id']], $html) : $html;
}

/**
 * Template
 */
function tpl(array $§): string
{
    if (!$§['tpl']) {
        return '';
    }

    $§['vars'] = ['id' => $§['id'], 'token' => session\token(), 'tpl' => $§['tpl']] + $§['vars'];
    $§ = function ($key) use ($§) {
        return $§['vars'][$key] ?? null;
    };
    ob_start();
    include app\tpl((string) $§('tpl'));

    return ob_get_clean();
}

/**
 * Search
 */
function search(array $§): string
{
    $§['vars']['q'] = $§['vars']['q'] ?? req\get('param')['q'] ?? null;

    return tpl($§);
}

/**
 * Pager
 */
function pager(array $§): string
{
    $§['vars']['size'] = (int) $§['vars']['size'];
    $cur = $§['vars']['cur'] ?? req\get('param')['cur'] ?? 1;
    $limit = (int) $§['vars']['limit'];
    $pages = (int) $§['vars']['pages'];
    unset($§['vars']['cur'], $§['vars']['limit'], $§['vars']['pages']);

    if ($limit <= 0 || $§['vars']['size'] <= 0) {
        return '';
    }

    $url = req\get('url');
    $total = (int) ceil($§['vars']['size'] / $limit) ?: 1;
    $cur = min(max($cur, 1), $total);
    $offset = ($cur - 1) * $limit;
    $§['vars']['max'] = min($offset + $limit, $§['vars']['size']);
    $§['vars']['min'] = $offset + 1;
    $§['vars']['links'] = [];
    $min = max(1, min($cur - intdiv($pages, 2), $total - $pages + 1));
    $max = min($min + $pages - 1, $total);

    if ($cur >= 2) {
        $lp = ['cur' => $cur === 2 ? null : $cur - 1];
        $§['vars']['links'][] = ['name' => app\i18n('Previous'), 'url' => app\url($url, $lp, true)];
    }

    for ($i = $min; $min < $max && $i <= $max; $i++) {
        $lp = ['cur' => $i === 1 ? null : $i];
        $§['vars']['links'][] = ['name' => $i, 'url' => app\url($url, $lp, true), 'active' => $i === $cur];
    }

    if ($cur < $total) {
        $§['vars']['links'][] = ['name' => app\i18n('Next'), 'url' => app\url($url, ['cur' => $cur + 1], true)];
    }

    return tpl($§);
}

/**
 * Index
 */
function index(array $§): string
{
    $§['vars']['ent'] = app\cfg('ent', $§['vars']['ent'] ?: app\get('ent'));
    $crit = is_array($§['vars']['crit']) ? $§['vars']['crit'] : [];
    $opt = ['limit' => (int) $§['vars']['limit']];
    $opt['order'] = $§['vars']['order'] && is_array($§['vars']['order']) ? $§['vars']['order'] : ['id' => 'desc'];

    if (!$§['vars']['ent'] || $opt['limit'] <= 0) {
        return '';
    }

    if (in_array('page', [$§['vars']['ent']['id'], $§['vars']['ent']['parent']])) {
        if (!$§['vars']['inaccessible']) {
            $crit[] = ['status', 'published'];
            $crit[] = ['disabled', false];
        }

        if ($§['vars']['parent']) {
            $crit[] = ['parent', $§['vars']['parent'] === true ? app\get('id') : (int) $§['vars']['parent']];
        }
    }

    unset($§['vars']['crit'], $§['vars']['inaccessible'], $§['vars']['limit'], $§['vars']['order'], $§['vars']['parent']);
    $p = arr\replace(['cur' => null, 'q' => null, 'sort' => null, 'dir' => null], req\get('param'));

    if ($§['vars']['search']) {
        if ($p['q'] = trim((string) $p['q'])) {
            $q = array_filter(explode(' ', $p['q']));
            $searchable = array_keys(arr\crit($§['vars']['ent']['attr'], [['searchable', true]])) ?: ['name'];
            $c = [];

            foreach ($searchable as $s) {
                $c[] = [$s, $q, APP['crit']['~']];
            }

            $crit[] = $c;
        }

        $search = ['id' => $§['id'] . '-search', 'type' => 'search', 'vars' => ['q' => $p['q']]];
        app\layout($search['id'], $search);
        $§['vars']['search'] = app\§($search['id']);
    } else {
        $§['vars']['search'] = null;
    }

    $size = ent\size($§['vars']['ent']['id'], $crit);
    $total = (int) ceil($size / $opt['limit']) ?: 1;
    $p['cur'] = min(max((int) $p['cur'], 1), $total);
    $opt['offset'] = ($p['cur'] - 1) * $opt['limit'];

    if ($p['sort'] && in_array($p['sort'], $§['vars']['attr'])) {
        $p['dir'] = $p['dir'] === 'desc' ? 'desc' : 'asc';
        $opt['order'] = [$p['sort'] => $p['dir']];
    } else {
        $p['sort'] = null;
        $p['dir'] = null;
    }

    if ($§['vars']['pager']) {
        $pager = ['id' => $§['id'] . '-pager', 'type' => 'pager', 'vars' => ['cur' => $p['cur'], 'limit' => $opt['limit'], 'size' => $size]];
        app\layout($pager['id'], $pager);
        $§['vars']['pager'] = app\§($pager['id']);
    } else {
        $§['vars']['pager'] = null;
    }

    $§['vars']['data'] = ent\all($§['vars']['ent']['id'], $crit, $opt);
    $§['vars']['dir'] = $p['dir'];
    $§['vars']['sort'] = $p['sort'];
    $§['vars']['title'] = app\enc($§['vars']['title'] ?? $§['vars']['ent']['name']);
    $§['vars']['url'] = req\get('url');

    return tpl($§);
}

/**
 * Form
 */
function form(array $§): string
{
    if (!$§['vars']['ent']) {
        return '';
    }

    $§['vars']['title'] = $§['vars']['title'] ? app\enc($§['vars']['title']) : null;
    $§['vars']['file'] = false;

    foreach ($§['vars']['attr'] as $aId) {
        if ($§['vars']['file'] = ($§['vars']['ent']['attr'][$aId]['type'] ?? null) === 'file') {
            break;
        }
    }

    return tpl($§);
}

/**
 * Create Form
 */
function create(array $§): string
{
    $§['vars']['ent'] = app\cfg('ent', $§['vars']['ent'] ?: app\get('ent'));

    if (($data = req\get('data')) && ent\save($§['vars']['ent']['id'], $data)) {
        if ($§['vars']['redirect']) {
            app\redirect(app\url($§['vars']['ent']['id'] . '/edit/' . $data['id']));
            return '';
        }

        $data = [];
    }

    $§['vars']['data'] = array_replace(ent\item($§['vars']['ent']), $data);
    $§['vars']['title'] = $§['vars']['title'] ?? $§['vars']['ent']['name'];

    return form($§);
}

/**
 * Login Form
 */
function login(array $§): string
{
    $§['vars']['attr'] = ['name', 'password'];
    $§['vars']['data'] = [];
    $a = ['name' => ['unique' => false, 'minlength' => 0, 'maxlength' => 0], 'password' => ['minlength' => 0, 'maxlength' => 0]];
    $§['vars']['ent']['attr'] = array_replace_recursive(app\cfg('ent', 'account')['attr'], $a);

    return form($§);
}

/**
 * View
 */
function view(array $§): string
{
    return $§['vars']['ent'] ? tpl($§) : '';
}

/**
 * Navigation
 *
 * @throws DomainException
 */
function nav(array $§): string
{
    if (!$§['vars']['data']) {
        return '';
    }

    $url = req\get('url');
    $count = count($§['vars']['data']);
    $start = current($§['vars']['data'])['level'] ?? 1;
    $level = 0;
    $i = 0;
    $html = '';
    $attrs = ['id' => $§['id']];

    if ($§['vars']['class']) {
        $attrs['class'] = $§['vars']['class'];
    }

    foreach ($§['vars']['data'] as $item) {
        if (empty($item['name'])) {
            throw new DomainException(app\i18n('Invalid data'));
        }

        $item = arr\replace(['name' => null, 'url' => null, 'disabled' => false, 'level' => $start], $item);
        $item['level'] = $item['level'] - $start + 1;
        $a = $item['url'] && !$item['disabled'] ? ['href' => $item['url']] : [];
        $class = '';

        if ($item['url'] === $url) {
            $a['class'] = 'active';
            $class .= ' class="active"';
        }

        if ($item['level'] > $level) {
             $html .= '<ul><li' . $class . '>';
        } elseif ($item['level'] < $level) {
             $html .= '</li>' . str_repeat('</ul></li>', $level - $item['level']) . '<li' . $class . '>';
        } else {
             $html .= '</li><li' . $class . '>';
        }

        $html .= html\tag('a', $a, $item['name']);
        $html .= ++$i === $count ? str_repeat('</li></ul>', $item['level']) : '';
        $level = $item['level'];
    }

    return $§['vars']['tag'] ? html\tag($§['vars']['tag'], $attrs, $html) : $html;
}

/**
 * Menu Navigation
 */
function menu(array $§): string
{
    $mode = $§['vars']['mode'];
    unset($§['vars']['mode']);
    $page = app\get('page');
    $main = app\get('main');

    if ($mode === 'sub' && (!$page || !$main)) {
        return '';
    }

    $crit = [['status', 'published'], ['ent', 'content']];
    $crit[] = $mode === 'sub' ? ['id', app\get('main')] : ['url', '/'];
    $select = ['id', 'name', 'url', 'disabled', 'menuname', 'pos', 'level'];

    if (!$root = ent\one('page', $crit, ['select' => $select])) {
        return '';
    }

    $crit = [['status', 'published'], ['ent', 'content'], ['pos', $root['pos'] . '.', APP['crit']['~^']]];

    if ($mode === 'sub') {
        $parent = $page['path'];
        unset($parent[0]);
        $crit[] = [['id', $page['path']], ['parent', $parent]];
    } else {
        $crit[] = ['menu', true];
    }

    $opt = ['select' => $select, 'order' => ['pos' => 'asc']];
    $§['vars']['data'] = ent\all('page', $crit, $opt);

    if (!$§['vars']['data']) {
        return '';
    }

    if ($§['vars']['root']) {
        $root['level']++;
        $§['vars']['data'] = [$root['id'] => $root] + $§['vars']['data'];
    }

    foreach ($§['vars']['data'] as $id => $item) {
        $§['vars']['data'][$id]['name'] = $item['menuname'] ?: $item['name'];
    }

    return nav($§);
}

/**
 * Toolbar Navigation
 */
function toolbar(array $§): string
{
    $§['vars']['data'] = app\cfg('toolbar');
    $empty = [];

    foreach ($§['vars']['data'] as $id => $item) {
        if ($item['priv'] && !app\allowed($item['priv'])) {
            unset($§['vars']['data'][$id]);
        } elseif (!$item['url']) {
            $empty[$id] = true;
        } elseif ($item['parent']) {
            unset($empty[$item['parent']]);
        }
    }

    $§['vars']['data'] = array_diff_key($§['vars']['data'], $empty);

    return nav($§);
}

/**
 * Breadcrumb Navigation
 */
function breadcrumb(array $§): string
{
    if (!$page = app\get('page')) {
        return '';
    }

    $html = '';
    $all = ent\all('page', [['id', $page['path']]], ['select' => ['id', 'name', 'url', 'disabled', 'menuname'], 'order' => ['level' => 'asc']]);

    foreach ($all as $item) {
        $a = $item['disabled'] || $page['id'] === $item['id'] ? [] : ['href' => $item['url']];
        $html .= ($html ? ' ' : '') . html\tag('a', $a, $item['menuname'] ?: $item['name']);
    }

    return $§['vars']['tag'] ? html\tag($§['vars']['tag'], ['id' => $§['id']], $html) : $html;
}

/**
 * Page banner
 */
function banner(array $§): string
{
    if (($page = app\get('page')) && $page['ent'] !== 'content') {
        $crit = [['id', $page['path']], ['ent', 'content']];
        $opt = ['select' => ['image'], 'order' => ['level' => 'desc']];
        $page = ent\one('page', $crit, $opt);
    }

    if (!$page || !($§['vars']['img'] = attr\viewer($page['_ent']['attr']['image'], $page))) {
        return '';
    }

    return tpl($§);
}


/**
 * Page sidebar
 */
function sidebar(array $§): string
{
    if (!$page = app\get('page')) {
        return '';
    }

    $html = $page['sidebar'];

    if (!$html && $§['vars']['inherit']) {
        $crit = [['id', $page['path']], ['sidebar', '', APP['crit']['!=']]];
        $opt = ['select' => ['sidebar'], 'order' => ['level' => 'desc']];
        $html = ent\one('page', $crit, $opt)['sidebar'] ?? '';
    }

    return $§['vars']['tag'] ? html\tag($§['vars']['tag'], ['id' => $§['id']], $html) : $html;
}
