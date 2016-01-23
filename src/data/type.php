<?php
return [
    'audio' => [
        'id' => 'audio',
        'name' => 'Audio',
        'backend' => 'varchar',
        'frontend' => 'file',
    ],
    'bool.checkbox' => [
        'id' => 'bool.checkbox',
        'name' => 'Checkbox (Boolean)',
        'backend' => 'bool',
        'frontend' => 'checkbox',
    ],
    'bool.radio' => [
        'id' => 'bool.radio',
        'name' => 'Radio (Boolean)',
        'backend' => 'bool',
        'frontend' => 'radio',
    ],
    'callback' => [
        'id' => 'callback',
        'name' => 'Callback',
        'backend' => 'varchar',
        'frontend' => 'text',
        'default' => [
            'validate' => 'attribute\validate_callback',
        ],
    ],
    'date' => [
        'id' => 'date',
        'name' => 'Date',
        'backend' => 'datetime',
        'frontend' => 'date',
    ],
    'datetime' => [
        'id' => 'datetime',
        'name' => 'Datetime',
        'backend' => 'datetime',
        'frontend' => 'datetime-local',
    ],
    'decimal' => [
        'id' => 'decimal',
        'name' => 'Decimal',
        'backend' => 'decimal',
        'frontend' => 'number',
    ],
    'editor' => [
        'id' => 'editor',
        'name' => 'Editor',
        'backend' => 'text',
        'frontend' => 'textarea',
        'default' => [
            'validate' => 'attribute\validate_editor',
            'view' => 'attribute\view_editor',
            'class' => ['editor'],
        ],
    ],
    'email' => [
        'id' => 'email',
        'name' => 'Email',
        'backend' => 'varchar',
        'frontend' => 'email',
    ],
    'embed' => [
        'id' => 'embed',
        'name' => 'Embed',
        'backend' => 'varchar',
        'frontend' => 'file',
    ],
    'file' => [
        'id' => 'file',
        'name' => 'File',
        'backend' => 'varchar',
        'frontend' => 'file',
    ],
    'image' => [
        'id' => 'image',
        'name' => 'Image',
        'backend' => 'varchar',
        'frontend' => 'file',
    ],
    'int' => [
        'id' => 'int',
        'name' => 'Integer',
        'backend' => 'int',
        'frontend' => 'number',
    ],
    'int.checkbox' => [
        'id' => 'int.checkbox',
        'name' => 'Checkbox (Integer)',
        'backend' => 'int',
        'frontend' => 'checkbox',
    ],
    'int.radio' => [
        'id' => 'int.radio',
        'name' => 'Radio (Integer)',
        'backend' => 'int',
        'frontend' => 'radio',
    ],
    'int.select' => [
        'id' => 'int.select',
        'name' => 'Select (Integer)',
        'backend' => 'int',
        'frontend' => 'select',
    ],
    'json' => [
        'id' => 'json',
        'name' => 'JSON',
        'backend' => 'text',
        'frontend' => 'textarea',
        'default' => [
            'load' => 'attribute\load_json',
            'validate' => 'attribute\validate_json',
            'edit' => 'attribute\edit_json',
        ],
    ],
    'multicheckbox' => [
        'id' => 'multicheckbox',
        'name' => 'Multicheckbox',
        'backend' => 'text',
        'frontend' => 'checkbox',
        'default' => [
            'is_multiple' => true,
            'load' => 'attribute\load_json',
            'save' => 'attribute\save_multiple',
        ],
    ],
    'multiselect' => [
        'id' => 'multiselect',
        'name' => 'Multiselect',
        'backend' => 'text',
        'frontend' => 'select',
        'default' => [
            'is_multiple' => true,
            'load' => 'attribute\load_json',
            'save' => 'attribute\save_multiple',
        ],
    ],
    'password' => [
        'id' => 'password',
        'name' => 'Password',
        'backend' => 'varchar',
        'frontend' => 'password',
    ],
    'tel' => [
        'id' => 'tel',
        'name' => 'Telephone Number',
        'backend' => 'varchar',
        'frontend' => 'tel',
    ],
    'text' => [
        'id' => 'text',
        'name' => 'Text',
        'backend' => 'varchar',
        'frontend' => 'text',
    ],
    'textarea' => [
        'id' => 'textarea',
        'name' => 'Textarea',
        'backend' => 'text',
        'frontend' => 'textarea',
    ],
    'url' => [
        'id' => 'url',
        'name' => 'URL',
        'backend' => 'varchar',
        'frontend' => 'url',
    ],
    'varchar.checkbox' => [
        'id' => 'varchar.checkbox',
        'name' => 'Checkbox (Varchar)',
        'backend' => 'varchar',
        'frontend' => 'checkbox',
    ],
    'varchar.radio' => [
        'id' => 'varchar.radio',
        'name' => 'Radio (Varchar)',
        'backend' => 'varchar',
        'frontend' => 'radio',
    ],
    'varchar.select' => [
        'id' => 'varchar.select',
        'name' => 'Select (Varchar)',
        'backend' => 'varchar',
        'frontend' => 'select',
    ],
    'video' => [
        'id' => 'video',
        'name' => 'Video',
        'backend' => 'varchar',
        'frontend' => 'file',
    ],
];
