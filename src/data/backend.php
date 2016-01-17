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
            'load' => 'attribute\load_datetime',
            'validate' => 'attribute\validate_datetime',
            'edit' => 'attribute\edit_datetime',
            'view' => 'attribute\view_datetime',
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
