<?php
declare(strict_types=1);

return [
    'name' => 'Blocks',
    'readonly' => true,
    'action' => ['api', 'index'],
    'attr' => [
        'id' => [
            'name' => 'ID',
            'type' => 'serial',
        ],
        'name' => [
            'name' => 'Name',
            'type' => 'text',
            'required' => true,
            'max' => 100,
        ],
        'entity_id' => [
            'name' => 'Entity',
            'type' => 'entitychild',
            'required' => true,
            'max' => 50,
        ],
        'content' => [
            'name' => 'Content',
            'type' => 'editor',
        ],
        'created' => [
            'name' => 'Created',
            'type' => 'datetime',
            'auto' => true,
        ],
    ],
];
