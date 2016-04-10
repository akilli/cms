<?php
return [
    'checkbox' => [
        'id' => 'checkbox',
        'name' => 'Checkbox',
        'default' => [
            'validate' => 'akilli\validator_option',
            'edit' => 'akilli\editor_input_option',
            'view' => 'akilli\viewer_option',
        ],
    ],
    'color' => [
        'id' => 'color',
        'name' => 'Color',
    ],
    'date' => [
        'id' => 'date',
        'name' => 'Date',
    ],
    'datetime-local' => [
        'id' => 'datetime-local',
        'name' => 'Datetime',
    ],
    'email' => [
        'id' => 'email',
        'name' => 'Email',
        'default' => [
            'validate' => 'akilli\validator_email',
        ],
    ],
    'file' => [
        'id' => 'file',
        'name' => 'File',
        'default' => [
            'delete' => 'akilli\deleter_file',
            'validate' => 'akilli\validator_file',
            'edit' => 'akilli\editor_file',
            'view' => 'akilli\viewer_file',
            'flag' => ['_reset' => 'Reset'],
        ],
    ],
    'month' => [
        'id' => 'month',
        'name' => 'Month',
    ],
    'number' => [
        'id' => 'number',
        'name' => 'Number',
        'default' => [
            'validate' => 'akilli\validator_number',
            'edit' => 'akilli\editor_number',
        ],
    ],
    'password' => [
        'id' => 'password',
        'name' => 'Password',
        'default' => [
            'is_searchable' => false,
            'save' => 'akilli\saver_password',
            'edit' => 'akilli\editor_password',
            'view' => 'akilli\viewer',
        ],
    ],
    'radio' => [
        'id' => 'radio',
        'name' => 'Radio',
        'default' => [
            'validate' => 'akilli\validator_option',
            'edit' => 'akilli\editor_input_option',
            'view' => 'akilli\viewer_option',
        ],
    ],
    'range' => [
        'id' => 'range',
        'name' => 'Range',
    ],
    'search' => [
        'id' => 'search',
        'name' => 'Search',
    ],
    'select' => [
        'id' => 'select',
        'name' => 'Select',
        'default' => [
            'validate' => 'akilli\validator_option',
            'edit' => 'akilli\editor_select',
            'view' => 'akilli\viewer_option',
        ],
    ],
    'tel' => [
        'id' => 'tel',
        'name' => 'Telephone',
    ],
    'text' => [
        'id' => 'text',
        'name' => 'Text',
    ],
    'textarea' => [
        'id' => 'textarea',
        'name' => 'Textarea',
        'default' => [
            'edit' => 'akilli\editor_textarea',
        ],
    ],
    'time' => [
        'id' => 'time',
        'name' => 'Time',
    ],
    'url' => [
        'id' => 'url',
        'name' => 'URL',
        'default' => [
            'validate' => 'akilli\validator_url',
        ],
    ],
    'week' => [
        'id' => 'week',
        'name' => 'Week',
    ],
];
