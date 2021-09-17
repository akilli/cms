<?php
return [
    'account' => [
        'name' => 'Accounts',
        'action' => ['delete', 'edit', 'index', 'login', 'logout', 'profile', 'view'],
        'attr' => [
            'id' => ['name' => 'ID', 'type' => 'serial'],
            'name' => ['name' => 'Name', 'type' => 'text', 'required' => true, 'unique' => true, 'max' => 50],
            'role_id' => ['name' => 'Role', 'type' => 'entity', 'ref' => 'role', 'required' => true],
            'username' => ['name' => 'Username', 'type' => 'uid', 'required' => true, 'unique' => true, 'max' => 50],
            'password' => ['name' => 'Password', 'type' => 'password', 'required' => true, 'min' => 8, 'max' => 255],
            'email' => ['name' => 'Email', 'type' => 'email', 'nullable' => true, 'unique' => true, 'max' => 50],
            'image' => ['name' => 'Image', 'type' => 'image', 'nullable' => true, 'unique' => true, 'max' => 255],
            'active' => ['name' => 'Active', 'type' => 'bool'],
            'created' => ['name' => 'Created', 'type' => 'datetime', 'auto' => true],
        ],
    ],
    'audio' => [
        'name' => 'Audios',
        'parent_id' => 'file',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'name' => ['type' => 'audio'],
            'entity_id' => ['editable' => false, 'indexable' => false],
        ],
    ],
    'block' => [
        'name' => 'Blocks',
        'readonly' => true,
        'action' => ['api', 'index'],
        'attr' => [
            'id' => ['name' => 'ID', 'type' => 'serial'],
            'name' => ['name' => 'Name', 'type' => 'text', 'required' => true, 'max' => 100],
            'entity_id' => ['name' => 'Entity', 'type' => 'entitychild', 'required' => true, 'max' => 50],
            'content' => ['name' => 'Content', 'type' => 'editor'],
            'created' => ['name' => 'Created', 'type' => 'datetime', 'auto' => true],
        ],
    ],
    'contentblock' => [
        'name' => 'Content Blocks',
        'parent_id' => 'block',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'entity_id' => ['editable' => false, 'indexable' => false],
        ],
    ],
    'contentpage' => [
        'name' => 'Content Pages',
        'parent_id' => 'page',
        'action' => ['delete', 'edit', 'index', 'view'],
        'attr' => [
            'entity_id' => ['editable' => false, 'indexable' => false],
        ],
    ],
    'document' => [
        'name' => 'Documents',
        'parent_id' => 'file',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'name' => ['type' => 'document'],
            'entity_id' => ['editable' => false, 'indexable' => false],
        ],
    ],
    'file' => [
        'name' => 'Files',
        'readonly' => true,
        'attr' => [
            'id' => ['name' => 'ID', 'type' => 'serial'],
            'name' => [
                'name' => 'Name',
                'type' => 'file',
                'required' => true,
                'unique' => true,
                'indexable' => true,
                'max' => 255,
            ],
            'entity_id' => ['name' => 'Entity', 'type' => 'entitychild', 'required' => true, 'max' => 50],
            'mime' => ['name' => 'MIME-Type', 'type' => 'text', 'required' => true, 'editable' => false, 'max' => 255],
            'thumb' => ['name' => 'Thumbnail', 'type' => 'image', 'nullable' => true, 'unique' => true, 'max' => 255],
            'info' => ['name' => 'Info', 'type' => 'textarea'],
            'created' => ['name' => 'Created', 'type' => 'datetime', 'auto' => true],
        ],
    ],
    'iframe' => [
        'name' => 'Iframes',
        'parent_id' => 'file',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'name' => ['name' => 'URL', 'type' => 'iframe'],
            'entity_id' => ['editable' => false, 'indexable' => false],
        ],
    ],
    'image' => [
        'name' => 'Images',
        'parent_id' => 'file',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'name' => ['type' => 'image'],
            'entity_id' => ['editable' => false, 'indexable' => false],
        ],
    ],
    'layout' => [
        'name' => 'Layout',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'id' => ['name' => 'ID', 'type' => 'serial'],
            'name' => ['name' => 'Name', 'type' => 'uid', 'required' => true, 'max' => 100],
            'entity_id' => ['name' => 'Entity', 'type' => 'text', 'auto' => true, 'max' => 50],
            'block_id' => ['name' => 'Block', 'type' => 'entity', 'ref' => 'block', 'required' => true],
            'page_id' => ['name' => 'Page', 'type' => 'entity', 'ref' => 'page', 'required' => true],
            'parent_id' => [
                'name' => 'Parent Block',
                'type' => 'select',
                'opt' => 'block',
                'required' => true,
                'max' => 100,
            ],
            'sort' => ['name' => 'Sort', 'type' => 'int'],
            'created' => ['name' => 'Created', 'type' => 'datetime', 'auto' => true],
        ],
    ],
    'page' => [
        'name' => 'Pages',
        'readonly' => true,
        'attr' => [
            'id' => ['name' => 'ID', 'type' => 'serial'],
            'name' => ['name' => 'Name', 'type' => 'text', 'required' => true, 'max' => 100],
            'entity_id' => ['name' => 'Entity', 'type' => 'entitychild', 'required' => true, 'max' => 255],
            'url' => ['name' => 'URL', 'type' => 'urlpath', 'required' => true, 'unique' => true, 'max' => 400],
            'title' => ['name' => 'Title', 'type' => 'text', 'max' => 100],
            'content' => ['name' => 'Main Content', 'type' => 'editor'],
            'aside' => ['name' => 'Additional Information', 'type' => 'editor'],
            'meta_title' => ['name' => 'Meta Title', 'type' => 'text', 'max' => 80],
            'meta_description' => ['name' => 'Meta Description', 'type' => 'text', 'max' => 300],
            'disabled' => ['name' => 'Page access disabled', 'type' => 'bool'],
            'breadcrumb' => ['name' => 'Show breadcrumb navigation', 'type' => 'bool'],
            'menu' => ['name' => 'Menu Entry', 'type' => 'bool'],
            'parent_id' => ['name' => 'Parent Page', 'type' => 'entity', 'ref' => 'page', 'nullable' => true],
            'sort' => ['name' => 'Sort', 'type' => 'int'],
            'position' => ['name' => 'Position', 'type' => 'position', 'auto' => true, 'max' => 255],
            'level' => ['name' => 'Level', 'type' => 'int', 'auto' => true],
            'path' => ['name' => 'Path', 'type' => 'multientity', 'ref' => 'page', 'auto' => true],
            'account_id' => [
                'name' => 'Creator',
                'type' => 'entity',
                'ref' => 'account',
                'nullable' => true,
                'editable' => false,
            ],
            'created' => ['name' => 'Created', 'type' => 'datetime', 'auto' => true],
        ],
    ],
    'role' => [
        'name' => 'Roles',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'id' => ['name' => 'ID', 'type' => 'serial'],
            'name' => ['name' => 'Name', 'type' => 'text', 'required' => true, 'unique' => true, 'max' => 50],
            'privilege' => ['name' => 'Privileges', 'type' => 'multitext', 'opt' => 'privilege'],
            'created' => ['name' => 'Created', 'type' => 'datetime', 'auto' => true],
        ],
    ],
    'video' => [
        'name' => 'Videos',
        'parent_id' => 'file',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'name' => ['type' => 'video'],
            'entity_id' => ['editable' => false, 'indexable' => false],
        ],
    ],
];
