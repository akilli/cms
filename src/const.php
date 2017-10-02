<?php
declare(strict_types = 1);

namespace cms;

const ALL = '_all_';
const ATTR = [
    'id' => null,
    'name' => null,
    'col' => null,
    'auto' => false,
    'sort' => 0,
    'type' => null,
    'backend' => null,
    'frontend' => null,
    'db_type' => null,
    'pdo' => null,
    'nullable' => false,
    'required' => false,
    'uniq' => false,
    'multiple' => false,
    'searchable' => false,
    'opt' => [],
    'actions' => [],
    'val' => null,
    'min' => 0,
    'max' => 0,
    'minlength' => 0,
    'maxlength' => 0,
    'entity' => null,
    'html' => [],
    'validator' => null,
    'saver' => null,
    'loader' => null,
    'editor' => null,
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
const ENTITY = ['id' => null, 'name' => null, 'tab' => null, 'model' => null, 'actions' => [], 'attr' => []];
const LOG = 'cms.log';
const OPTS = ['mode' => 'all', 'index' => 'id', 'select' => [], 'order' => [], 'limit' => 0, 'offset' => 0];
const SECTION = [
    'id' => null,
    'section' => null,
    'template' => null,
    'vars' => [],
    'active' => true,
    'privilege' => null,
    'parent' => 'root',
    'sort' => 0,
    'children' => [],
];
const TIME = ['b' => 'H:i:s', 'f' => 'H:i'];
const URL = ['asset' => '/asset/', 'media' => '/media/view/', 'page' => '.html', 'theme' => '/theme/'];
