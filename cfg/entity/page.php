<?php
return [
    'name' => 'Pages',
    'readonly' => true,
    'unique' => [['parent_id', 'slug']],
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
        'title' => [
            'name' => 'Title',
            'type' => 'text',
            'nullable' => true,
            'max' => 255,
        ],
        'content' => [
            'name' => 'Main Content',
            'type' => 'editor',
            'nullable' => true,
        ],
        'aside' => [
            'name' => 'Additional Information',
            'type' => 'editor',
            'nullable' => true,
        ],
        'meta_title' => [
            'name' => 'Meta Title',
            'type' => 'text',
            'nullable' => true,
            'max' => 80,
        ],
        'meta_description' => [
            'name' => 'Meta Description',
            'type' => 'text',
            'nullable' => true,
            'max' => 300,
        ],
        'slug' => [
            'name' => 'Slug',
            'type' => 'uid',
            'required' => true,
            'max' => 75,
        ],
        'url' => [
            'name' => 'URL',
            'type' => 'text',
            'auto' => true,
            'unique' => true,
            'max' => 400,
        ],
        'disabled' => [
            'name' => 'Page access disabled',
            'type' => 'bool',
        ],
        'menu' => [
            'name' => 'Menu Entry',
            'type' => 'bool',
        ],
        'parent_id' => [
            'name' => 'Parent Page',
            'type' => 'entity',
            'nullable' => true,
            'ref' => 'page',
        ],
        'sort' => [
            'name' => 'Sort',
            'type' => 'int',
        ],
        'position' => [
            'name' => 'Position',
            'type' => 'position',
        ],
        'level' => [
            'name' => 'Level',
            'type' => 'int',
            'auto' => true,
        ],
        'path' => [
            'name' => 'Path',
            'type' => 'multientity',
            'auto' => true,
            'ref' => 'page',
        ],
        'account_id' => [
            'name' => 'Account',
            'type' => 'entity',
            'nullable' => true,
            'ref' => 'account',
        ],
        'timestamp' => [
            'name' => 'Timestamp',
            'type' => 'datetime',
        ],
    ],
];
