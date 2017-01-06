<?php
return [
    'audio' => [
        'name' => 'Audio',
        'backend' => 'varchar',
        'frontend' => 'file',
    ],
    'checkbox' => [
        'name' => 'Checkbox',
        'backend' => 'bool',
        'frontend' => 'checkbox',
    ],
    'color' => [
        'name' => 'Color',
        'backend' => 'varchar',
        'frontend' => 'color',
    ],
    'date' => [
        'name' => 'Date',
        'backend' => 'date',
        'frontend' => 'date',
    ],
    'datetime' => [
        'name' => 'Datetime',
        'backend' => 'datetime',
        'frontend' => 'datetime-local',
    ],
    'decimal' => [
        'name' => 'Decimal',
        'backend' => 'decimal',
        'frontend' => 'number',
    ],
    'email' => [
        'name' => 'Email',
        'backend' => 'varchar',
        'frontend' => 'email',
    ],
    'embed' => [
        'name' => 'Embed',
        'backend' => 'varchar',
        'frontend' => 'file',
    ],
    'file' => [
        'name' => 'File',
        'backend' => 'varchar',
        'frontend' => 'file',
    ],
    'image' => [
        'name' => 'Image',
        'backend' => 'varchar',
        'frontend' => 'file',
    ],
    'int' => [
        'name' => 'Integer',
        'backend' => 'int',
        'frontend' => 'number',
    ],
    'json' => [
        'name' => 'JSON',
        'backend' => 'json',
        'frontend' => 'textarea',
        'val' => [],
    ],
    'multicheckbox' => [
        'name' => 'Multicheckbox',
        'backend' => 'json',
        'frontend' => 'checkbox',
        'multiple' => true,
        'val' => [],
    ],
    'multiselect' => [
        'name' => 'Multiselect',
        'backend' => 'json',
        'frontend' => 'select',
        'multiple' => true,
        'val' => [],
    ],
    'password' => [
        'name' => 'Password',
        'backend' => 'varchar',
        'frontend' => 'password',
    ],
    'radio' => [
        'name' => 'Radio',
        'backend' => 'varchar',
        'frontend' => 'radio',
    ],
    'range' => [
        'name' => 'Range',
        'backend' => 'int',
        'frontend' => 'range',
    ],
    'rte' => [
        'name' => 'Rich Text Editor',
        'backend' => 'text',
        'frontend' => 'textarea',
    ],
    'select' => [
        'name' => 'Select',
        'backend' => 'varchar',
        'frontend' => 'select',
    ],
    'text' => [
        'name' => 'Text',
        'backend' => 'varchar',
        'frontend' => 'text',
    ],
    'textarea' => [
        'name' => 'Textarea',
        'backend' => 'text',
        'frontend' => 'textarea',
    ],
    'time' => [
        'name' => 'Time',
        'backend' => 'time',
        'frontend' => 'time',
    ],
    'url' => [
        'name' => 'URL',
        'backend' => 'varchar',
        'frontend' => 'url',
    ],
    'video' => [
        'name' => 'Video',
        'backend' => 'varchar',
        'frontend' => 'file',
    ],
];
