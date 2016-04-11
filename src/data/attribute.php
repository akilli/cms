<?php
return [
    'audio' => [
        'id' => 'audio',
        'name' => 'Audio',
        'backend' => 'varchar',
        'frontend' => 'file',
        'default' => [
            'deleter' => 'akilli\deleter_file',
            'validator' => 'akilli\validator_file',
            'editor' => 'akilli\editor_file',
            'view' => 'akilli\viewer_file',
            'flag' => ['_reset' => 'Reset'],
        ],
    ],
    'bool.checkbox' => [
        'id' => 'bool.checkbox',
        'name' => 'Checkbox (Boolean)',
        'backend' => 'bool',
        'frontend' => 'checkbox',
        'default' => [
            'options' => ['No', 'Yes'],
            'validator' => 'akilli\validator_option',
            'editor' => 'akilli\editor_input_option',
            'view' => 'akilli\viewer_option',
        ],
    ],
    'bool.radio' => [
        'id' => 'bool.radio',
        'name' => 'Radio (Boolean)',
        'backend' => 'bool',
        'frontend' => 'radio',
        'default' => [
            'options' => ['No', 'Yes'],
            'validator' => 'akilli\validator_option',
            'editor' => 'akilli\editor_input_option',
            'view' => 'akilli\viewer_option',
        ],
    ],
    'callback' => [
        'id' => 'callback',
        'name' => 'Callback',
        'backend' => 'varchar',
        'frontend' => 'text',
        'default' => [
            'validator' => 'akilli\validator_callback',
            'editor' => 'akilli\editor_varchar',
        ],
    ],
    'date' => [
        'id' => 'date',
        'name' => 'Date',
        'backend' => 'datetime',
        'frontend' => 'date',
        'default' => [
            'loader' => 'akilli\loader_datetime',
            'validator' => 'akilli\validator_datetime',
            'editor' => 'akilli\editor_datetime',
            'view' => 'akilli\viewer_datetime',
        ],
    ],
    'datetime' => [
        'id' => 'datetime',
        'name' => 'Datetime',
        'backend' => 'datetime',
        'frontend' => 'datetime-local',
        'default' => [
            'loader' => 'akilli\loader_datetime',
            'validator' => 'akilli\validator_datetime',
            'editor' => 'akilli\editor_datetime',
            'view' => 'akilli\viewer_datetime',
        ],
    ],
    'decimal' => [
        'id' => 'decimal',
        'name' => 'Decimal',
        'backend' => 'decimal',
        'frontend' => 'number',
        'default' => [
            'validator' => 'akilli\validator_number',
            'editor' => 'akilli\editor_number',
            'step' => 0.01,
        ],
    ],
    'rte' => [
        'id' => 'rte',
        'name' => 'Rich Text Editor',
        'backend' => 'text',
        'frontend' => 'textarea',
        'default' => [
            'validator' => 'akilli\validator_rte',
            'editor' => 'akilli\editor_textarea',
            'view' => 'akilli\viewer_rte',
            'class' => ['rte'],
        ],
    ],
    'email' => [
        'id' => 'email',
        'name' => 'Email',
        'backend' => 'varchar',
        'frontend' => 'email',
        'default' => [
            'validator' => 'akilli\validator_email',
            'editor' => 'akilli\editor_varchar',
        ],
    ],
    'embed' => [
        'id' => 'embed',
        'name' => 'Embed',
        'backend' => 'varchar',
        'frontend' => 'file',
        'default' => [
            'deleter' => 'akilli\deleter_file',
            'validator' => 'akilli\validator_file',
            'editor' => 'akilli\editor_file',
            'view' => 'akilli\viewer_file',
            'flag' => ['_reset' => 'Reset'],
        ],
    ],
    'file' => [
        'id' => 'file',
        'name' => 'File',
        'backend' => 'varchar',
        'frontend' => 'file',
        'default' => [
            'deleter' => 'akilli\deleter_file',
            'validator' => 'akilli\validator_file',
            'editor' => 'akilli\editor_file',
            'view' => 'akilli\viewer_file',
            'flag' => ['_reset' => 'Reset'],
        ],
    ],
    'image' => [
        'id' => 'image',
        'name' => 'Image',
        'backend' => 'varchar',
        'frontend' => 'file',
        'default' => [
            'deleter' => 'akilli\deleter_file',
            'validator' => 'akilli\validator_file',
            'editor' => 'akilli\editor_file',
            'view' => 'akilli\viewer_file',
            'flag' => ['_reset' => 'Reset'],
        ],
    ],
    'int' => [
        'id' => 'int',
        'name' => 'Integer',
        'backend' => 'int',
        'frontend' => 'number',
        'default' => [
            'validator' => 'akilli\validator_number',
            'editor' => 'akilli\editor_number',
        ],
    ],
    'int.checkbox' => [
        'id' => 'int.checkbox',
        'name' => 'Checkbox (Integer)',
        'backend' => 'int',
        'frontend' => 'checkbox',
        'default' => [
            'validator' => 'akilli\validator_option',
            'editor' => 'akilli\editor_input_option',
            'view' => 'akilli\viewer_option',
        ]
    ],
    'int.radio' => [
        'id' => 'int.radio',
        'name' => 'Radio (Integer)',
        'backend' => 'int',
        'frontend' => 'radio',
        'default' => [
            'validator' => 'akilli\validator_option',
            'editor' => 'akilli\editor_input_option',
            'view' => 'akilli\viewer_option',
        ],
    ],
    'int.select' => [
        'id' => 'int.select',
        'name' => 'Select (Integer)',
        'backend' => 'int',
        'frontend' => 'select',
        'default' => [
            'validator' => 'akilli\validator_option',
            'editor' => 'akilli\editor_select',
            'view' => 'akilli\viewer_option',
        ],
    ],
    'json' => [
        'id' => 'json',
        'name' => 'JSON',
        'backend' => 'text',
        'frontend' => 'textarea',
        'default' => [
            'loader' => 'akilli\loader_json',
            'validator' => 'akilli\validator_json',
            'editor' => 'akilli\editor_json',
        ],
    ],
    'multicheckbox' => [
        'id' => 'multicheckbox',
        'name' => 'Multicheckbox',
        'backend' => 'text',
        'frontend' => 'checkbox',
        'default' => [
            'is_multiple' => true,
            'loader' => 'akilli\loader_json',
            'saver' => 'akilli\saver_multiple',
            'validator' => 'akilli\validator_option',
            'editor' => 'akilli\editor_input_option',
            'view' => 'akilli\viewer_option',
        ],
    ],
    'multiselect' => [
        'id' => 'multiselect',
        'name' => 'Multiselect',
        'backend' => 'text',
        'frontend' => 'select',
        'default' => [
            'is_multiple' => true,
            'loader' => 'akilli\loader_json',
            'saver' => 'akilli\saver_multiple',
            'validator' => 'akilli\validator_option',
            'editor' => 'akilli\editor_select',
            'view' => 'akilli\viewer_option',
        ],
    ],
    'password' => [
        'id' => 'password',
        'name' => 'Password',
        'backend' => 'varchar',
        'frontend' => 'password',
        'default' => [
            'is_searchable' => false,
            'saver' => 'akilli\saver_password',
            'validator' => 'akilli\validator_string',
            'editor' => 'akilli\editor_password',
        ],
    ],
    'text' => [
        'id' => 'text',
        'name' => 'Text',
        'backend' => 'varchar',
        'frontend' => 'text',
        'default' => [
            'validator' => 'akilli\validator_string',
            'editor' => 'akilli\editor_varchar',
        ],
    ],
    'textarea' => [
        'id' => 'textarea',
        'name' => 'Textarea',
        'backend' => 'text',
        'frontend' => 'textarea',
        'default' => [
            'validator' => 'akilli\validator_string',
            'editor' => 'akilli\editor_textarea',
        ],
    ],
    'url' => [
        'id' => 'url',
        'name' => 'URL',
        'backend' => 'varchar',
        'frontend' => 'url',
        'default' => [
            'validator' => 'akilli\validator_url',
            'editor' => 'akilli\editor_varchar',
        ],
    ],
    'varchar.checkbox' => [
        'id' => 'varchar.checkbox',
        'name' => 'Checkbox (Varchar)',
        'backend' => 'varchar',
        'frontend' => 'checkbox',
        'default' => [
            'validator' => 'akilli\validator_option',
            'editor' => 'akilli\editor_input_option',
            'view' => 'akilli\viewer_option',
        ]
    ],
    'varchar.radio' => [
        'id' => 'varchar.radio',
        'name' => 'Radio (Varchar)',
        'backend' => 'varchar',
        'frontend' => 'radio',
        'default' => [
            'validator' => 'akilli\validator_option',
            'editor' => 'akilli\editor_input_option',
            'view' => 'akilli\viewer_option',
        ],
    ],
    'varchar.select' => [
        'id' => 'varchar.select',
        'name' => 'Select (Varchar)',
        'backend' => 'varchar',
        'frontend' => 'select',
        'default' => [
            'validator' => 'akilli\validator_option',
            'editor' => 'akilli\editor_select',
            'view' => 'akilli\viewer_option',
        ],
    ],
    'video' => [
        'id' => 'video',
        'name' => 'Video',
        'backend' => 'varchar',
        'frontend' => 'file',
        'default' => [
            'deleter' => 'akilli\deleter_file',
            'validator' => 'akilli\validator_file',
            'editor' => 'akilli\editor_file',
            'view' => 'akilli\viewer_file',
            'flag' => ['_reset' => 'Reset'],
        ],
    ],
];
