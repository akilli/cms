<?php
return [
    'audio' => [
        'backend' => 'varchar',
        'frontend' => 'file',
        'viewer' => 'cms\viewer_audio',
    ],
    'checkbox' => [
        'backend' => 'bool',
        'frontend' => 'checkbox',
    ],
    'date' => [
        'backend' => 'date',
        'frontend' => 'date',
    ],
    'datetime' => [
        'backend' => 'datetime',
        'frontend' => 'datetime',
    ],
    'decimal' => [
        'backend' => 'decimal',
        'frontend' => 'number',
    ],
    'email' => [
        'backend' => 'varchar',
        'frontend' => 'email',
    ],
    'embed' => [
        'backend' => 'varchar',
        'frontend' => 'file',
        'viewer' => 'cms\viewer_embed',
    ],
    'entity' => [
        'backend' => 'int',
        'frontend' => 'select',
    ],
    'file' => [
        'backend' => 'varchar',
        'frontend' => 'file',
    ],
    'iframe' => [
        'backend' => 'varchar',
        'frontend' => 'url',
        'viewer' => 'cms\viewer_iframe',
    ],
    'image' => [
        'backend' => 'varchar',
        'frontend' => 'file',
        'viewer' => 'cms\viewer_image',
    ],
    'int' => [
        'backend' => 'int',
        'frontend' => 'number',
    ],
    'json' => [
        'backend' => 'json',
        'frontend' => 'textarea',
        'editor' => 'cms\editor_json',
        'validator' => 'cms\validator_json',
    ],
    'object' => [
        'backend' => 'varchar',
        'frontend' => 'file',
        'viewer' => 'cms\viewer_object',
    ],
    'password' => [
        'backend' => 'varchar',
        'frontend' => 'password',
    ],
    'radio' => [
        'backend' => 'varchar',
        'frontend' => 'radio',
    ],
    'range' => [
        'backend' => 'int',
        'frontend' => 'range',
    ],
    'rte' => [
        'backend' => 'text',
        'frontend' => 'textarea',
        'viewer' => 'cms\viewer_rte',
        'validator' => 'cms\validator_rte',
    ],
    'search' => [
        'backend' => 'search',
        'frontend' => 'textarea',
    ],
    'select' => [
        'backend' => 'varchar',
        'frontend' => 'select',
    ],
    'text' => [
        'backend' => 'varchar',
        'frontend' => 'text',
    ],
    'textarea' => [
        'backend' => 'text',
        'frontend' => 'textarea',
    ],
    'time' => [
        'backend' => 'time',
        'frontend' => 'time',
    ],
    'url' => [
        'backend' => 'varchar',
        'frontend' => 'url',
    ],
    'video' => [
        'backend' => 'varchar',
        'frontend' => 'file',
        'viewer' => 'cms\viewer_video',
    ],
];
