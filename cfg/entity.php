<?php
return [
    'account' => [
        'name' => 'Accounts',
        'action' => ['delete', 'edit', 'index', 'login', 'logout', 'profile'],
        'attr' => [
            'id' => ['name' => 'ID', 'type' => 'serial'],
            'name' => ['name' => 'Name', 'type' => 'text', 'required' => true, 'unique' => true, 'max' => 50],
            'role_id' => ['name' => 'Role', 'type' => 'entity', 'required' => true, 'ref' => 'role'],
            'username' => ['name' => 'Username', 'type' => 'uid', 'required' => true, 'unique' => true, 'max' => 50],
            'password' => ['name' => 'Password', 'type' => 'password', 'required' => true, 'min' => 8, 'max' => 255],
            'email' => ['name' => 'Email', 'type' => 'email', 'nullable' => true, 'unique' => true, 'max' => 50],
        ],
    ],
    'audio' => [
        'name' => 'Audios',
        'parent_id' => 'file',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'url' => ['type' => 'audio'],
        ],
    ],
    'block' => [
        'name' => 'Blocks',
        'readonly' => true,
        'action' => ['api', 'index'],
        'attr' => [
            'id' => ['name' => 'ID', 'type' => 'serial'],
            'name' => ['name' => 'Name', 'type' => 'text', 'required' => true, 'max' => 255],
            'entity_id' => ['name' => 'Entity', 'type' => 'entitychild', 'required' => true],
            'content' => ['name' => 'Content', 'type' => 'editor', 'nullable' => true],
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
            'url' => ['type' => 'document'],
        ],
    ],
    'file' => [
        'name' => 'Files',
        'readonly' => true,
        'action' => ['index'],
        'attr' => [
            'id' => ['name' => 'ID', 'type' => 'serial'],
            'name' => ['name' => 'Name', 'type' => 'text', 'required' => true, 'max' => 100],
            'entity_id' => ['name' => 'Entity', 'type' => 'entitychild', 'required' => true],
            'url' => ['name' => 'URL', 'type' => 'file', 'required' => true, 'unique' => true],
            'thumb' => ['name' => 'Thumbnail', 'type' => 'image', 'nullable' => true, 'unique' => true],
            'mime' => ['name' => 'MIME-Type', 'type' => 'text', 'required' => true, 'max' => 255],
            'info' => ['name' => 'Info', 'type' => 'textarea', 'nullable' => true],
        ],
    ],
    'iframe' => [
        'name' => 'Iframes',
        'parent_id' => 'file',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'url' => ['type' => 'iframe'],
        ],
    ],
    'image' => [
        'name' => 'Images',
        'parent_id' => 'file',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'url' => ['type' => 'image'],
        ],
    ],
    'layout' => [
        'name' => 'Layout',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'id' => ['name' => 'ID', 'type' => 'serial'],
            'name' => ['name' => 'Name', 'type' => 'uid', 'required' => true, 'max' => 100],
            'entity_id' => ['name' => 'Entity', 'type' => 'text', 'auto' => true],
            'block_id' => ['name' => 'Block', 'type' => 'entity', 'required' => true, 'ref' => 'block'],
            'page_id' => ['name' => 'Page', 'type' => 'entity', 'required' => true, 'ref' => 'page'],
            'parent_id' => ['name' => 'Parent Block', 'type' => 'select', 'opt' => 'block', 'required' => true, 'max' => 100],
            'sort' => ['name' => 'Sort', 'type' => 'int'],
        ],
    ],
    'page' => [
        'name' => 'Pages',
        'readonly' => true,
        'unique' => [['parent_id', 'slug']],
        'attr' => [
            'id' => ['name' => 'ID', 'type' => 'serial'],
            'name' => ['name' => 'Name', 'type' => 'text', 'required' => true, 'max' => 255],
            'entity_id' => ['name' => 'Entity', 'type' => 'entitychild', 'required' => true],
            'title' => ['name' => 'Title', 'type' => 'text', 'nullable' => true, 'max' => 255],
            'content' => ['name' => 'Main Content', 'type' => 'editor', 'nullable' => true],
            'aside' => ['name' => 'Additional Information', 'type' => 'editor', 'nullable' => true],
            'meta_title' => ['name' => 'Meta Title', 'type' => 'text', 'nullable' => true, 'max' => 80],
            'meta_description' => ['name' => 'Meta Description', 'type' => 'text', 'nullable' => true, 'max' => 300],
            'slug' => ['name' => 'Slug', 'type' => 'uid', 'required' => true, 'max' => 75],
            'url' => ['name' => 'URL', 'type' => 'text', 'auto' => true, 'unique' => true, 'max' => 400],
            'disabled' => ['name' => 'Page access disabled', 'type' => 'bool'],
            'breadcrumb' => ['name' => 'Show breadcrumb navigation', 'type' => 'bool'],
            'menu' => ['name' => 'Menu Entry', 'type' => 'bool'],
            'parent_id' => ['name' => 'Parent Page', 'type' => 'entity', 'nullable' => true, 'ref' => 'page'],
            'sort' => ['name' => 'Sort', 'type' => 'int'],
            'position' => ['name' => 'Position', 'type' => 'text', 'viewer' => 'position', 'auto' => true, 'max' => 255],
            'level' => ['name' => 'Level', 'type' => 'int', 'auto' => true],
            'path' => ['name' => 'Path', 'type' => 'multientity', 'auto' => true, 'ref' => 'page'],
            'account_id' => ['name' => 'Account', 'type' => 'entity', 'nullable' => true, 'ref' => 'account'],
            'timestamp' => ['name' => 'Timestamp', 'type' => 'datetime'],
        ],
    ],
    'role' => [
        'name' => 'Roles',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'id' => ['name' => 'ID', 'type' => 'serial'],
            'name' => ['name' => 'Name', 'type' => 'text', 'required' => true, 'unique' => true, 'max' => 50],
            'privilege' => ['name' => 'Privileges', 'type' => 'multitext', 'opt' => 'privilege'],
        ],
    ],
    'video' => [
        'name' => 'Videos',
        'parent_id' => 'file',
        'action' => ['delete', 'edit', 'index'],
        'attr' => [
            'url' => ['type' => 'video'],
        ],
    ],
];
