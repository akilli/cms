<?php
return [
    'account' => [
        'name' => 'Accounts',
        'action' => ['delete', 'edit', 'index', 'login', 'logout', 'profile', 'view'],
        'attr' => [
            'id' => [
                'name' => 'ID',
                'type' => 'serial',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'unique' => true,
                'max' => 50,
            ],
            'uid' => [
                'name' => 'UID',
                'type' => 'uid',
                'required' => true,
                'unique' => true,
                'editable' => false,
                'max' => 100,
            ],
            'url' => [
                'name' => 'URL',
                'type' => 'urlpath',
                'auto' => true,
                'unique' => true,
                'max' => 102,
            ],
            'role_id' => [
                'name' => 'Role',
                'type' => 'entity',
                'ref' => 'role',
                'required' => true,
            ],
            'username' => [
                'name' => 'Username',
                'type' => 'uid',
                'required' => true,
                'unique' => true,
                'max' => 50,
            ],
            'password' => [
                'name' => 'Password',
                'type' => 'password',
                'required' => true,
                'min' => 8,
                'max' => 255,
            ],
            'email' => [
                'name' => 'Email',
                'type' => 'email',
                'nullable' => true,
                'unique' => true,
                'max' => 50,
            ],
            'image' => [
                'name' => 'Image',
                'type' => 'image',
                'nullable' => true,
                'unique' => true,
                'max' => 255,
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'bool',
            ],
            'created' => [
                'name' => 'Created',
                'type' => 'datetime',
                'auto' => true,
            ],
        ],
    ],
    'audio' => [
        'name' => 'Audios',
        'parent_id' => 'file',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'name' => [
                'type' => 'audio',
            ],
        ],
    ],
    'block' => [
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
    ],
    'contentblock' => [
        'name' => 'Content Blocks',
        'parent_id' => 'block',
        'action' => ['delete', 'edit', 'index'],
    ],
    'contentpage' => [
        'name' => 'Content Pages',
        'parent_id' => 'page',
        'action' => ['delete', 'edit', 'index', 'view'],
    ],
    'document' => [
        'name' => 'Documents',
        'parent_id' => 'file',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'name' => [
                'type' => 'document',
            ],
        ],
    ],
    'file' => [
        'name' => 'Files',
        'readonly' => true,
        'attr' => [
            'id' => [
                'name' => 'ID',
                'type' => 'serial',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'file',
                'required' => true,
                'unique' => true,
                'indexable' => true,
                'max' => 255,
            ],
            'entity_id' => [
                'name' => 'Entity',
                'type' => 'entitychild',
                'required' => true,
                'max' => 50,
            ],
            'mime' => [
                'name' => 'MIME-Type',
                'type' => 'text',
                'required' => true,
                'editable' => false,
                'max' => 255,
            ],
            'thumb' => [
                'name' => 'Thumbnail',
                'type' => 'image',
                'nullable' => true,
                'unique' => true,
                'max' => 255,
            ],
            'info' => [
                'name' => 'Info',
                'type' => 'textarea',
            ],
            'created' => [
                'name' => 'Created',
                'type' => 'datetime',
                'auto' => true,
            ],
        ],
    ],
    'iframe' => [
        'name' => 'Iframes',
        'parent_id' => 'file',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'name' => [
                'name' => 'URL',
                'type' => 'iframe',
            ],
        ],
    ],
    'image' => [
        'name' => 'Images',
        'parent_id' => 'file',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'name' => [
                'type' => 'image',
            ],
        ],
    ],
    'layout' => [
        'name' => 'Layout',
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
            'block_entity_id' => [
                'name' => 'Block Entity',
                'type' => 'text',
                'auto' => true,
                'indexable' => false,
                'max' => 50,
            ],
            'block_id' => [
                'name' => 'Block',
                'type' => 'entity',
                'ref' => 'block',
                'required' => true,
            ],
            'page_id' => [
                'name' => 'Page',
                'type' => 'entity',
                'ref' => 'page',
                'required' => true,
            ],
            'parent_id' => [
                'name' => 'Parent Block',
                'type' => 'select',
                'opt' => 'block',
                'required' => true,
                'max' => 100,
            ],
            'sort' => [
                'name' => 'Sort',
                'type' => 'int',
            ],
            'created' => [
                'name' => 'Created',
                'type' => 'datetime',
                'auto' => true,
            ],
        ],
    ],
    'menu' => [
        'name' => 'Menus',
        'action' => ['delete', 'edit', 'index'],
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
            'url' => [
                'name' => 'URL',
                'type' => 'urlpath',
                'nullable' => true,
            ],
            'parent_id' => [
                'name' => 'Parent Menu Item',
                'type' => 'entity',
                'ref' => 'menu',
                'nullable' => true,
            ],
            'sort' => [
                'name' => 'Sort',
                'type' => 'int',
            ],
            'position' => [
                'name' => 'Position',
                'type' => 'position',
                'auto' => true,
                'max' => 255,
            ],
            'level' => [
                'name' => 'Level',
                'type' => 'int',
                'auto' => true,
            ],
            'path' => [
                'name' => 'Path',
                'type' => 'multientity',
                'ref' => 'menu',
                'auto' => true,
            ],
            'created' => [
                'name' => 'Created',
                'type' => 'datetime',
                'auto' => true,
            ],
        ],
    ],
    'page' => [
        'name' => 'Pages',
        'readonly' => true,
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
                'max' => 255,
            ],
            'url' => [
                'name' => 'URL',
                'type' => 'urlpath',
                'required' => true,
                'unique' => true,
            ],
            'title' => [
                'name' => 'Title',
                'type' => 'text',
                'max' => 100,
            ],
            'content' => [
                'name' => 'Main Content',
                'type' => 'editor',
            ],
            'aside' => [
                'name' => 'Additional Information',
                'type' => 'editor',
            ],
            'meta_title' => [
                'name' => 'Meta Title',
                'type' => 'text',
                'max' => 80,
            ],
            'meta_description' => [
                'name' => 'Meta Description',
                'type' => 'text',
                'max' => 300,
            ],
            'created' => [
                'name' => 'Created',
                'type' => 'datetime',
                'auto' => true,
            ],
        ],
    ],
    'role' => [
        'name' => 'Roles',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'id' => [
                'name' => 'ID',
                'type' => 'serial',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'unique' => true,
                'max' => 50,
            ],
            'privilege' => [
                'name' => 'Privileges',
                'type' => 'multitext',
                'opt' => 'privilege',
            ],
            'created' => [
                'name' => 'Created',
                'type' => 'datetime',
                'auto' => true,
            ],
        ],
    ],
    'url' => [
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
    ],
    'video' => [
        'name' => 'Videos',
        'parent_id' => 'file',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'name' => [
                'type' => 'video',
            ],
        ],
    ],
];
