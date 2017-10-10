<?php
return [
    'checkbox' => [
        'validator' => 'cms\validator_opt',
        'editor' => 'cms\editor_opt',
        'viewer' => 'cms\viewer_opt',
    ],
    'date' => [
        'validator' => 'cms\validator_date',
        'editor' => 'cms\editor_date',
        'viewer' => 'cms\viewer_date',
    ],
    'datetime' => [
        'validator' => 'cms\validator_datetime',
        'editor' => 'cms\editor_datetime',
        'viewer' => 'cms\viewer_datetime',
    ],
    'email' => [
        'validator' => 'cms\validator_email',
        'editor' => 'cms\editor_text',
    ],
    'file' => [
        'validator' => 'cms\validator_file',
        'editor' => 'cms\editor_file',
        'viewer' => 'cms\viewer_file',
    ],
    'number' => [
        'editor' => 'cms\editor_int',
    ],
    'password' => [
        'validator' => 'cms\validator_password',
        'editor' => 'cms\editor_password',
    ],
    'radio' => [
        'validator' => 'cms\validator_opt',
        'editor' => 'cms\editor_opt',
        'viewer' => 'cms\viewer_opt',
    ],
    'range' => [
        'editor' => 'cms\editor_int',
    ],
    'select' => [
        'validator' => 'cms\validator_opt',
        'editor' => 'cms\editor_select',
        'viewer' => 'cms\viewer_opt',
    ],
    'text' => [
        'validator' => 'cms\validator_text',
        'editor' => 'cms\editor_text',
    ],
    'textarea' => [
        'validator' => 'cms\validator_text',
        'editor' => 'cms\editor_textarea',
    ],
    'time' => [
        'validator' => 'cms\validator_time',
        'editor' => 'cms\editor_time',
        'viewer' => 'cms\viewer_time',
    ],
    'url' => [
        'validator' => 'cms\validator_url',
        'editor' => 'cms\editor_text',
    ],
];
