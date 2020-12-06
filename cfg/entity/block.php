<?php
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
            'max' => 255,
        ],
        'entity_id' => [
            'name' => 'Entity',
            'type' => 'entity_id',
            'required' => true,
        ],
        'content' => [
            'name' => 'Content',
            'type' => 'editor',
            'nullable' => true,
        ],
    ],
];
