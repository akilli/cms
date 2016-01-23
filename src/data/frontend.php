<?php
return [
    'checkbox' => [
        'id' => 'checkbox',
        'name' => 'Checkbox',
        'default' => [
            'validate' => 'attribute\validate_option',
            'edit' => 'attribute\edit_input_option',
            'view' => 'attribute\view_option',
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
            'validate' => 'attribute\validate_email',
        ],
    ],
    'file' => [
        'id' => 'file',
        'name' => 'File',
        'default' => [
            'delete' => 'attribute\delete_file',
            'validate' => 'attribute\validate_file',
            'edit' => 'attribute\edit_file',
            'view' => 'attribute\view_file',
            'flag' => ['__reset' => 'Reset'],
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
            'validate' => 'attribute\validate_number',
            'edit' => 'attribute\edit_number',
        ],
    ],
    'password' => [
        'id' => 'password',
        'name' => 'Password',
        'default' => [
            'is_searchable' => false,
            'save' => 'attribute\save_password',
            'edit' => 'attribute\edit_password',
            'view' => 'attribute\view',
        ],
    ],
    'radio' => [
        'id' => 'radio',
        'name' => 'Radio',
        'default' => [
            'validate' => 'attribute\validate_option',
            'edit' => 'attribute\edit_input_option',
            'view' => 'attribute\view_option',
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
            'validate' => 'attribute\validate_option',
            'edit' => 'attribute\edit_select',
            'view' => 'attribute\view_option',
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
            'edit' => 'attribute\edit_textarea',
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
            'validate' => 'attribute\validate_url',
        ],
    ],
    'week' => [
        'id' => 'week',
        'name' => 'Week',
    ],
];
