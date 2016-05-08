<?php
return [
    // Input
    'callback' => [
        'id' => 'callback',
        'name' => 'Callback',
        'backend' => 'varchar',
        'frontend' => 'text',
        'default' => [
            'validator' => 'qnd\validator_callback',
        ],
    ],
    'email' => [
        'id' => 'email',
        'name' => 'Email',
        'backend' => 'varchar',
        'frontend' => 'email',
        'default' => [
            'validator' => 'qnd\validator_email',
        ],
    ],
    'password' => [
        'id' => 'password',
        'name' => 'Password',
        'backend' => 'varchar',
        'frontend' => 'password',
        'default' => [
            'validator' => 'qnd\validator_string',
        ],
    ],
    'text' => [
        'id' => 'text',
        'name' => 'Text',
        'backend' => 'varchar',
        'frontend' => 'text',
        'default' => [
            'validator' => 'qnd\validator_string',
        ],
    ],
    'url' => [
        'id' => 'url',
        'name' => 'URL',
        'backend' => 'varchar',
        'frontend' => 'url',
        'default' => [
            'validator' => 'qnd\validator_url',
        ],
    ],
    // Input Number
    'int' => [
        'id' => 'int',
        'name' => 'Integer',
        'backend' => 'int',
        'frontend' => 'number',
        'default' => [
            'validator' => 'qnd\validator_number',
        ],
    ],
    'decimal' => [
        'id' => 'decimal',
        'name' => 'Decimal',
        'backend' => 'decimal',
        'frontend' => 'number',
        'default' => [
            'validator' => 'qnd\validator_number',
            'step' => 0.01,
        ],
    ],
    'range' => [
        'id' => 'range',
        'name' => 'Range',
        'backend' => 'int',
        'frontend' => 'range',
        'default' => [
            'validator' => 'qnd\validator_number',
        ],
    ],
    // Input Date
    'date' => [
        'id' => 'date',
        'name' => 'Date',
        'backend' => 'datetime',
        'frontend' => 'date',
        'default' => [
            'validator' => 'qnd\validator_datetime',
        ],
    ],
    'datetime' => [
        'id' => 'datetime',
        'name' => 'Datetime',
        'backend' => 'datetime',
        'frontend' => 'datetime-local',
        'default' => [
            'validator' => 'qnd\validator_datetime',
        ],
    ],
    // Input File
    'audio' => [
        'id' => 'audio',
        'name' => 'Audio',
        'backend' => 'varchar',
        'frontend' => 'file',
        'default' => [
            'validator' => 'qnd\validator_file',
        ],
    ],
    'embed' => [
        'id' => 'embed',
        'name' => 'Embed',
        'backend' => 'varchar',
        'frontend' => 'file',
        'default' => [
            'validator' => 'qnd\validator_file',
        ],
    ],
    'file' => [
        'id' => 'file',
        'name' => 'File',
        'backend' => 'varchar',
        'frontend' => 'file',
        'default' => [
            'validator' => 'qnd\validator_file',
        ],
    ],
    'image' => [
        'id' => 'image',
        'name' => 'Image',
        'backend' => 'varchar',
        'frontend' => 'file',
        'default' => [
            'validator' => 'qnd\validator_file',
        ],
    ],
    'video' => [
        'id' => 'video',
        'name' => 'Video',
        'backend' => 'varchar',
        'frontend' => 'file',
        'default' => [
            'validator' => 'qnd\validator_file',
        ],
    ],
    // Textarea
    'index' => [
        'id' => 'index',
        'name' => 'Search Index',
        'backend' => 'text',
        'frontend' => 'textarea',
        'default' => [
            'validator' => 'qnd\validator_string',
        ],
    ],
    'json' => [
        'id' => 'json',
        'name' => 'JSON',
        'backend' => 'text',
        'frontend' => 'textarea',
        'default' => [
            'validator' => 'qnd\validator_json',
        ],
    ],
    'rte' => [
        'id' => 'rte',
        'name' => 'Rich Text Editor',
        'backend' => 'text',
        'frontend' => 'textarea',
        'default' => [
            'validator' => 'qnd\validator_rte',
            'class' => ['rte'],
        ],
    ],
    'textarea' => [
        'id' => 'textarea',
        'name' => 'Textarea',
        'backend' => 'text',
        'frontend' => 'textarea',
        'default' => [
            'validator' => 'qnd\validator_string',
        ],
    ],
    // Checkbox
    'checkbox.bool' => [
        'id' => 'checkbox.bool',
        'name' => 'Checkbox (Boolean)',
        'backend' => 'bool',
        'frontend' => 'checkbox',
        'default' => [
            'options' => ['No', 'Yes'],
            'validator' => 'qnd\validator_option',
        ],
    ],
    'checkbox.int' => [
        'id' => 'checkbox.int',
        'name' => 'Checkbox (Integer)',
        'backend' => 'int',
        'frontend' => 'checkbox',
        'default' => [
            'validator' => 'qnd\validator_option',
        ]
    ],
    'checkbox.varchar' => [
        'id' => 'checkbox.varchar',
        'name' => 'Checkbox (Varchar)',
        'backend' => 'varchar',
        'frontend' => 'checkbox',
        'default' => [
            'validator' => 'qnd\validator_option',
        ]
    ],
    'multicheckbox' => [
        'id' => 'multicheckbox',
        'name' => 'Multicheckbox',
        'backend' => 'text',
        'frontend' => 'checkbox',
        'default' => [
            'multiple' => true,
            'validator' => 'qnd\validator_option',
        ],
    ],
    // Radio
    'radio.bool' => [
        'id' => 'radio.bool',
        'name' => 'Radio (Boolean)',
        'backend' => 'bool',
        'frontend' => 'radio',
        'default' => [
            'options' => ['No', 'Yes'],
            'validator' => 'qnd\validator_option',
        ],
    ],
    'radio.int' => [
        'id' => 'radio.int',
        'name' => 'Radio (Integer)',
        'backend' => 'int',
        'frontend' => 'radio',
        'default' => [
            'validator' => 'qnd\validator_option',
        ],
    ],
    'radio.varchar' => [
        'id' => 'radio.varchar',
        'name' => 'Radio (Varchar)',
        'backend' => 'varchar',
        'frontend' => 'radio',
        'default' => [
            'validator' => 'qnd\validator_option',
        ],
    ],
    // Select
    'select.bool' => [
        'id' => 'select.bool',
        'name' => 'Select (Boolean)',
        'backend' => 'bool',
        'frontend' => 'select',
        'default' => [
            'validator' => 'qnd\validator_option',
        ],
    ],
    'select.int' => [
        'id' => 'select.int',
        'name' => 'Select (Integer)',
        'backend' => 'int',
        'frontend' => 'select',
        'default' => [
            'validator' => 'qnd\validator_option',
        ],
    ],
    'select.varchar' => [
        'id' => 'select.varchar',
        'name' => 'Select (Varchar)',
        'backend' => 'varchar',
        'frontend' => 'select',
        'default' => [
            'validator' => 'qnd\validator_option',
        ],
    ],
    'multiselect' => [
        'id' => 'multiselect',
        'name' => 'Multiselect',
        'backend' => 'text',
        'frontend' => 'select',
        'default' => [
            'multiple' => true,
            'validator' => 'qnd\validator_option',
        ],
    ],
];
