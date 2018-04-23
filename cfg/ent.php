<?php
return [
    'role' => [
        'name' => 'Roles',
        'type' => 'db',
        'act' => ['admin', 'create', 'delete', 'edit'],
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
                'unique' => true,
                'searchable' => true,
                'maxlength' => 50,
            ],
            'priv' => [
                'name' => 'Privileges',
                'type' => 'checkbox',
                'opt' => 'opt\priv',
            ],
        ],
    ],
    'account' => [
        'name' => 'Accounts',
        'type' => 'db',
        'act' => ['admin', 'create', 'dashboard', 'delete', 'edit', 'login', 'logout', 'password'],
        'attr' => [
            'id' => [
                'name' => 'ID',
                'auto' => true,
                'type' => 'int',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'filter' => 'filter\id',
                'required' => true,
                'unique' => true,
                'searchable' => true,
                'maxlength' => 50,
            ],
            'password' => [
                'name' => 'Password',
                'type' => 'password',
                'required' => true,
                'minlength' => 8,
                'maxlength' => 255,
            ],
            'confirmation' => [
                'name' => 'Password Confirmation',
                'virtual' => true,
                'type' => 'password',
                'required' => true,
                'minlength' => 8,
                'maxlength' => 255,
            ],
            'role' => [
                'name' => 'Role',
                'type' => 'ent',
                'required' => true,
                'ent' => 'role',
            ],
        ],
    ],
    'file' => [
        'name' => 'Files',
        'type' => 'db',
        'act' => ['browser'],
        'attr' => [
            'id' => [
                'name' => 'ID',
                'auto' => true,
                'type' => 'int',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'upload',
                'required' => true,
                'unique' => true,
                'searchable' => true,
                'maxlength' => 50,
            ],
            'type' => [
                'name' => 'Filetype',
                'type' => 'text',
                'required' => true,
                'searchable' => true,
                'maxlength' => 5,
            ],
            'info' => [
                'name' => 'Info',
                'type' => 'textarea',
                'required' => true,
                'searchable' => true,
            ],
            'ent' => [
                'name' => 'Entity',
                'type' => 'select',
                'required' => true,
                'opt' => 'opt\child',
                'maxlength' => 50,
            ],
        ],
    ],
    'audio' => [
        'name' => 'Audios',
        'type' => 'db',
        'parent' => 'file',
        'act' => ['admin', 'browser', 'create', 'delete', 'edit'],
        'attr' => [
            'name' => [
                'opt' => 'audio',
            ],
        ],
    ],
    'doc' => [
        'name' => 'Documents',
        'type' => 'db',
        'parent' => 'file',
        'act' => ['admin', 'browser', 'create', 'delete', 'edit'],
        'attr' => [
            'name' => [
                'opt' => 'doc',
            ],
        ],
    ],
    'image' => [
        'name' => 'Images',
        'type' => 'db',
        'parent' => 'file',
        'act' => ['admin', 'browser', 'create', 'delete', 'edit'],
        'attr' => [
            'name' => [
                'opt' => 'image',
            ],
        ],
    ],
    'video' => [
        'name' => 'Videos',
        'type' => 'db',
        'parent' => 'file',
        'act' => ['admin', 'browser', 'create', 'delete', 'edit'],
        'attr' => [
            'name' => [
                'opt' => 'video',
            ],
        ],
    ],
    'page' => [
        'name' => 'Pages',
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
                'maxlength' => 255,
            ],
            'image' => [
                'name' => 'Image',
                'type' => 'image',
                'nullable' => true,
            ],
            'teaser' => [
                'name' => 'Teaser',
                'type' => 'rte',
                'searchable' => true,
                'val' => '',
            ],
            'main' => [
                'name' => 'Main Content',
                'type' => 'rte',
                'searchable' => true,
                'val' => '',
            ],
            'aside' => [
                'name' => 'Additional Information',
                'type' => 'rte',
                'val' => '',
            ],
            'sidebar' => [
                'name' => 'Sidebar',
                'type' => 'rte',
                'val' => '',
            ],
            'meta' => [
                'name' => 'Meta',
                'type' => 'text',
                'val' => '',
                'maxlength' => 300,
            ],
            'layout' => [
                'name' => 'Layout',
                'type' => 'select',
                'nullable' => true,
                'opt' => 'layout',
                'maxlength' => 50,
            ],
            'slug' => [
                'name' => 'Slug',
                'type' => 'text',
                'filter' => 'filter\slug',
                'required' => true,
                'maxlength' => 50,
            ],
            'url' => [
                'name' => 'URL',
                'auto' => true,
                'type' => 'text',
                'unique' => true,
                'maxlength' => 255,
            ],
            'menu' => [
                'name' => 'Menu Entry',
                'type' => 'bool',
            ],
            'parent' => [
                'name' => 'Parent',
                'type' => 'page',
                'nullable' => true,
            ],
            'sort' => [
                'name' => 'Sort',
                'type' => 'int',
                'val' => 0,
            ],
            'pos' => [
                'name' => 'Position',
                'auto' => true,
                'type' => 'text',
                'viewer' => 'viewer\pos',
                'maxlength' => 255,
            ],
            'level' => [
                'name' => 'Level',
                'auto' => true,
                'type' => 'int',
            ],
            'path' => [
                'name' => 'Path',
                'auto' => true,
                'type' => 'json',
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
            'ent' => [
                'name' => 'Entity',
                'type' => 'select',
                'required' => true,
                'opt' => 'opt\child',
                'maxlength' => 50,
            ],
        ],
    ],
    'content' => [
        'name' => 'Content Pages',
        'type' => 'db',
        'parent' => 'page',
        'act' => ['admin', 'create', 'delete', 'edit', 'view'],
    ],
    'article' => [
        'name' => 'Articles',
        'type' => 'db',
        'parent' => 'page',
        'act' => ['admin', 'create', 'delete', 'edit', 'view'],
    ],
    'version' => [
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
                'maxlength' => 255,
            ],
            'teaser' => [
                'name' => 'Teaser',
                'type' => 'rte',
                'required' => true,
                'searchable' => true,
            ],
            'main' => [
                'name' => 'Main Content',
                'type' => 'rte',
                'required' => true,
                'searchable' => true,
            ],
            'aside' => [
                'name' => 'Additional Information',
                'type' => 'rte',
                'required' => true,
            ],
            'sidebar' => [
                'name' => 'Sidebar',
                'type' => 'rte',
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
                'required' => true,
            ],
            'page' => [
                'name' => 'Page',
                'type' => 'page',
                'required' => true,
            ],
        ],
    ],
];
