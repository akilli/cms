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
            'load' => 'akilli\attribute_load_datetime',
            'validate' => 'akilli\validator_datetime',
            'edit' => 'akilli\attribute_edit_datetime',
            'view' => 'akilli\attribute_view_datetime',
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
