<?php
return [
    'name' => 'Layout',
    'unique' => [['page_id', 'parent_id', 'name']],
    'action' => ['delete', 'edit', 'index'],
    'attr' => [
        'id' => [
            'name' => 'ID',
            'type' => 'serial',
        ],
        'name' => [
            'name' => 'Name',
            'type' => 'uid',
            'required' => true,
            'max' => 100,
        ],
        'entity_id' => [
            'name' => 'Entity',
            'type' => 'text',
            'auto' => true,
        ],
        'block_id' => [
            'name' => 'Block',
            'type' => 'entity',
            'required' => true,
            'ref' => 'block',
        ],
        'page_id' => [
            'name' => 'Page',
            'type' => 'entity',
            'required' => true,
            'ref' => 'page',
        ],
        'parent_id' => [
            'name' => 'Parent Block',
            'type' => 'block',
            'required' => true,
            'max' => 100,
        ],
        'sort' => [
            'name' => 'Sort',
            'type' => 'int',
        ],
    ],
];
