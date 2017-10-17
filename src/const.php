<?php
declare(strict_types = 1);

const ALL = '_all_';
const ATTR = [
    'id' => null,
    'name' => null,
    'col' => null,
    'auto' => false,
    'type' => null,
    'backend' => null,
    'frontend' => null,
    'nullable' => false,
    'required' => false,
    'unique' => false,
    'multiple' => false,
    'searchable' => false,
    'opt' => [],
    'val' => null,
    'min' => 0,
    'max' => 0,
    'minlength' => 0,
    'maxlength' => 0,
    'validator' => null,
    'viewer' => null,
];
const CRIT = [
    '=' => '=',
    '!=' => '!=',
    '>' => '>',
    '>=' => '>=',
    '<' => '>',
    '<=' => '<=',
    '~' => '~',
    '!~' => '!~',
    '~^' => '~^',
    '!~^' => '!~^',
    '~$' => '~$',
    '!~$' => '!~$',
];
const DATE = ['b' => 'Y-m-d', 'f' => 'Y-m-d'];
const DATETIME = ['b' => 'Y-m-d H:i:s', 'f' => 'Y-m-d\TH:i'];
const ENT = ['id' => null, 'name' => null, 'tab' => null, 'type' => 'db', 'act' => [], 'attr' => []];
const OPTS = ['mode' => 'all', 'index' => 'id', 'select' => [], 'order' => [], 'limit' => 0, 'offset' => 0];
const LOG = 'php://stdout';
const PRIV = ['name' => null, 'call' => null, 'active' => true, 'sort' => 0];
const SECTION = [
    'id' => null,
    'section' => null,
    'tpl' => null,
    'active' => true,
    'priv' => null,
    'parent_id' => 'root',
    'sort' => 0,
    'vars' => [],
];
const TIME = ['b' => 'H:i:s', 'f' => 'H:i'];
const UPLOAD = ['error', 'name', 'size', 'tmp_name', 'type'];
const URL = ['asset' => '/asset/', 'media' => '/media/view/', 'page' => '.html', 'theme' => '/theme/'];
