<?php
declare(strict_types=1);

return [
    'name' => 'URL',
    'readonly' => true,
    'attr' => [
        'id' => [
            'name' => 'ID',
            'type' => 'serial',
        ],
        'name' => [
            'name' => 'Name',
            'type' => 'urlpath',
        ],
        'target_entity_id' => [
            'name' => 'Target Entity',
            'type' => 'text',
        ],
        'target_id' => [
            'name' => 'Target',
            'type' => 'int',
        ],
    ],
];
