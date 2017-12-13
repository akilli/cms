<?php
return [
    'name' => 'Versions',
    'type' => 'db',
    'attr' => [
        'id' => [
            'name' => 'ID',
            'auto' => true,
            'type' => 'int',
        ],
        'name' => [
            'name' => 'Name',
            'type' => 'text',
            'required' => true,
            'searchable' => true,
            'maxlength' => 100,
        ],
        'ent' => [
            'name' => 'Entity',
            'type' => 'text',
            'required' => true,
            'searchable' => true,
            'maxlength' => 50,
        ],
        'ent_id' => [
            'name' => 'Entity-ID',
            'type' => 'int',
            'required' => true,
        ],
        'status' => [
            'name' => 'Status',
            'type' => 'status',
            'required' => true,
        ],
        'date' => [
            'name' => 'Date',
            'type' => 'datetime',
        ],
        'data' => [
            'name' => 'Data',
            'type' => 'json',
        ],
    ],
];
