<?php
return [
    // Input
    'color' => [
        'name' => 'Color',
        'backend' => 'varchar',
        'frontend' => 'color',
    ],
    'email' => [
        'name' => 'Email',
        'backend' => 'varchar',
        'frontend' => 'email',
    ],
    'password' => [
        'name' => 'Password',
        'backend' => 'varchar',
        'frontend' => 'password',
    ],
    'text' => [
        'name' => 'Text',
        'backend' => 'varchar',
        'frontend' => 'text',
    ],
    'url' => [
        'name' => 'URL',
        'backend' => 'varchar',
        'frontend' => 'url',
    ],
    // Input Number
    'int' => [
        'name' => 'Integer',
        'backend' => 'int',
        'frontend' => 'number',
    ],
    'decimal' => [
        'name' => 'Decimal',
        'backend' => 'decimal',
        'frontend' => 'number',
    ],
    'range' => [
        'name' => 'Range',
        'backend' => 'int',
        'frontend' => 'range',
    ],
    // Input Date + Time
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
    'time' => [
        'name' => 'Time',
        'backend' => 'time',
        'frontend' => 'time',
    ],
    // Input File
    'audio' => [
        'name' => 'Audio',
        'backend' => 'varchar',
        'frontend' => 'file',
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
    'video' => [
        'name' => 'Video',
        'backend' => 'varchar',
        'frontend' => 'file',
    ],
    // Textarea
    'json' => [
        'name' => 'JSON',
        'backend' => 'json',
        'frontend' => 'textarea',
        'val' => [],
    ],
    'rte' => [
        'name' => 'Rich Text Editor',
        'backend' => 'text',
        'frontend' => 'textarea',
    ],
    'textarea' => [
        'name' => 'Textarea',
        'backend' => 'text',
        'frontend' => 'textarea',
    ],
    // Checkbox
    'checkbox.bool' => [
        'name' => 'Checkbox (Boolean)',
        'backend' => 'bool',
        'frontend' => 'checkbox',
    ],
    'checkbox.int' => [
        'name' => 'Checkbox (Integer)',
        'backend' => 'int',
        'frontend' => 'checkbox',
    ],
    'checkbox.varchar' => [
        'name' => 'Checkbox (Varchar)',
        'backend' => 'varchar',
        'frontend' => 'checkbox',
    ],
    'multicheckbox' => [
        'name' => 'Multicheckbox',
        'backend' => 'json',
        'frontend' => 'checkbox',
        'multiple' => true,
        'val' => [],
    ],
    // Radio
    'radio.bool' => [
        'name' => 'Radio (Boolean)',
        'backend' => 'bool',
        'frontend' => 'radio',
    ],
    'radio.int' => [
        'name' => 'Radio (Integer)',
        'backend' => 'int',
        'frontend' => 'radio',
    ],
    'radio.varchar' => [
        'name' => 'Radio (Varchar)',
        'backend' => 'varchar',
        'frontend' => 'radio',
    ],
    // Select
    'select.bool' => [
        'name' => 'Select (Boolean)',
        'backend' => 'bool',
        'frontend' => 'select',
    ],
    'select.int' => [
        'name' => 'Select (Integer)',
        'backend' => 'int',
        'frontend' => 'select',
    ],
    'select.varchar' => [
        'name' => 'Select (Varchar)',
        'backend' => 'varchar',
        'frontend' => 'select',
    ],
    'multiselect' => [
        'name' => 'Multiselect',
        'backend' => 'json',
        'frontend' => 'select',
        'multiple' => true,
        'val' => [],
    ],
];
