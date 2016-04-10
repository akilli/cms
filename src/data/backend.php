<?php
return [
    'bool' => [
        'id' => 'bool',
        'name' => 'Boolean',
        'default' => [
            'options' => ['No', 'Yes'],
        ],
    ],
    'datetime' => [
        'id' => 'datetime',
        'name' => 'Datetime',
        'default' => [
            'load' => 'akilli\loader_datetime',
            'validate' => 'akilli\validator_datetime',
            'edit' => 'akilli\editor_datetime',
            'view' => 'akilli\viewer_datetime',
        ],
    ],
    'decimal' => [
        'id' => 'decimal',
        'name' => 'Decimal',
        'default' => [
            'step' => 0.01,
        ],
    ],
    'int' => [
        'id' => 'int',
        'name' => 'Integer',
        'default' => [
            'step' => 1,
        ],
    ],
    'text' => [
        'id' => 'text',
        'name' => 'Text',
    ],
    'varchar' => [
        'id' => 'varchar',
        'name' => 'Varchar',
    ],
];
