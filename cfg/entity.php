<?php
return [
    'role' => [
        'name' => 'Roles',
        'action' => ['admin', 'delete', 'edit'],
        'attr' => [
            'id' => [
                'name' => 'ID',
                'type' => 'int',
                'auto' => true,
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'unique' => true,
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
        'action' => ['admin', 'delete', 'edit', 'login', 'logout', 'password'],
        'attr' => [
            'id' => [
                'name' => 'ID',
                'type' => 'int',
                'auto' => true,
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'filter' => 'filter\id',
                'required' => true,
                'unique' => true,
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
                'type' => 'password',
                'virtual' => true,
                'required' => true,
                'minlength' => 8,
                'maxlength' => 255,
            ],
            'role_id' => [
                'name' => 'Role',
                'type' => 'entity',
                'required' => true,
                'ref' => 'role',
            ],
        ],
    ],
    'file' => [
        'name' => 'Files',
        'action' => ['browser'],
        'attr' => [
            'id' => [
                'name' => 'ID',
                'type' => 'int',
                'auto' => true,
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'upload',
                'required' => true,
                'unique' => true,
                'maxlength' => 50,
            ],
            'info' => [
                'name' => 'Info',
                'type' => 'textarea',
                'required' => true,
            ],
            'entity' => [
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
        'parent' => 'file',
        'action' => ['admin', 'browser', 'delete', 'edit'],
        'attr' => [
            'name' => [
                'cfg.filter' => 'upload.audio',
            ],
        ],
    ],
    'doc' => [
        'name' => 'Documents',
        'parent' => 'file',
        'action' => ['admin', 'browser', 'delete', 'edit'],
        'attr' => [
            'name' => [
                'cfg.filter' => 'upload.doc',
            ],
        ],
    ],
    'image' => [
        'name' => 'Images',
        'parent' => 'file',
        'action' => ['admin', 'browser', 'delete', 'edit'],
        'attr' => [
            'name' => [
                'cfg.filter' => 'upload.image',
            ],
        ],
    ],
    'video' => [
        'name' => 'Videos',
        'parent' => 'file',
        'action' => ['admin', 'browser', 'delete', 'edit'],
        'attr' => [
            'name' => [
                'cfg.filter' => 'upload.video',
            ],
        ],
    ],
    'page' => [
        'name' => 'Pages',
        'attr' => [
            'id' => [
                'name' => 'ID',
                'type' => 'int',
                'auto' => true,
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
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
                'val' => '',
            ],
            'main' => [
                'name' => 'Main Content',
                'type' => 'rte',
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
            'meta_title' => [
                'name' => 'Meta Title',
                'type' => 'text',
                'val' => '',
                'maxlength' => 80,
            ],
            'meta_description' => [
                'name' => 'Meta Description',
                'type' => 'text',
                'val' => '',
                'maxlength' => 300,
            ],
            'slug' => [
                'name' => 'Slug',
                'type' => 'text',
                'filter' => 'filter\id',
                'required' => true,
                'maxlength' => 75,
            ],
            'url' => [
                'name' => 'URL',
                'type' => 'text',
                'auto' => true,
                'unique' => true,
                'maxlength' => 400,
            ],
            'disabled' => [
                'name' => 'Page access disabled',
                'type' => 'bool',
            ],
            'menu' => [
                'name' => 'Menu Entry',
                'type' => 'bool',
            ],
            'menu_name' => [
                'name' => 'Menu Name',
                'type' => 'text',
                'nullable' => true,
                'maxlength' => 255,
            ],
            'parent_id' => [
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
                'type' => 'text',
                'viewer' => 'viewer\pos',
                'auto' => true,
                'maxlength' => 255,
            ],
            'level' => [
                'name' => 'Level',
                'type' => 'int',
                'auto' => true,
            ],
            'path' => [
                'name' => 'Path',
                'type' => 'json',
                'auto' => true,
            ],
            'status' => [
                'name' => 'Status',
                'type' => 'status',
                'required' => true,
            ],
            'timestamp' => [
                'name' => 'Timestamp',
                'type' => 'datetime',
            ],
            'date' => [
                'name' => 'Date',
                'type' => 'datetime',
            ],
            'entity' => [
                'name' => 'Entity',
                'type' => 'select',
                'required' => true,
                'opt' => 'opt\child',
                'maxlength' => 50,
            ],
        ],
    ],
    'page_article' => [
        'name' => 'Articles',
        'parent' => 'page',
        'action' => ['admin', 'delete', 'edit', 'view'],
    ],
    'page_content' => [
        'name' => 'Content Pages',
        'parent' => 'page',
        'action' => ['admin', 'delete', 'edit', 'view'],
    ],
    'version' => [
        'name' => 'Versions',
        'attr' => [
            'id' => [
                'name' => 'ID',
                'type' => 'int',
                'auto' => true,
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'maxlength' => 255,
            ],
            'teaser' => [
                'name' => 'Teaser',
                'type' => 'rte',
                'required' => true,
            ],
            'main' => [
                'name' => 'Main Content',
                'type' => 'rte',
                'required' => true,
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
            'timestamp' => [
                'name' => 'Timestamp',
                'type' => 'datetime',
                'required' => true,
            ],
            'page_id' => [
                'name' => 'Page',
                'type' => 'page',
                'required' => true,
            ],
        ],
    ],
    'block' => [
        'name' => 'Blocks',
        'attr' => [
            'id' => [
                'name' => 'ID',
                'type' => 'int',
                'auto' => true,
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'maxlength' => 255,
            ],
            'entity' => [
                'name' => 'Entity',
                'type' => 'select',
                'required' => true,
                'opt' => 'opt\child',
                'maxlength' => 50,
            ],
        ],
    ],
    'block_content' => [
        'name' => 'Content Blocks',
        'parent' => 'block',
        'action' => ['admin', 'delete', 'edit'],
        'attr' => [
            'title' => [
                'name' => 'Title',
                'type' => 'text',
                'val' => '',
                'maxlength' => 255,
            ],
            'content' => [
                'name' => 'Content',
                'type' => 'rte',
                'val' => '',
            ],
        ],
    ],
];
