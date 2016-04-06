<?php
return [
    'checkbox' => [
        'id' => 'checkbox',
        'name' => 'Checkbox',
        'default' => [
            'validate' => 'akilli\attribute_validate_option',
            'edit' => 'akilli\attribute_edit_input_option',
            'view' => 'akilli\attribute_view_option',
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
            'validate' => 'akilli\attribute_validate_email',
        ],
    ],
    'file' => [
        'id' => 'file',
        'name' => 'File',
        'default' => [
            'delete' => 'akilli\attribute_delete_file',
            'validate' => 'akilli\attribute_validate_file',
            'edit' => 'akilli\attribute_edit_file',
            'view' => 'akilli\attribute_view_file',
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
            'validate' => 'akilli\attribute_validate_number',
            'edit' => 'akilli\attribute_edit_number',
        ],
    ],
    'password' => [
        'id' => 'password',
        'name' => 'Password',
        'default' => [
            'is_searchable' => false,
            'save' => 'akilli\attribute_save_password',
            'edit' => 'akilli\attribute_edit_password',
            'view' => 'akilli\attribute_view',
        ],
    ],
    'radio' => [
        'id' => 'radio',
        'name' => 'Radio',
        'default' => [
            'validate' => 'akilli\attribute_validate_option',
            'edit' => 'akilli\attribute_edit_input_option',
            'view' => 'akilli\attribute_view_option',
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
            'validate' => 'akilli\attribute_validate_option',
            'edit' => 'akilli\attribute_edit_select',
            'view' => 'akilli\attribute_view_option',
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
            'edit' => 'akilli\attribute_edit_textarea',
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
            'validate' => 'akilli\attribute_validate_url',
        ],
    ],
    'week' => [
        'id' => 'week',
        'name' => 'Week',
    ],
];
