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
    'file_audio' => [
        'name' => 'Audios',
        'parent_id' => 'file',
        'action' => ['admin', 'browser', 'delete', 'edit'],
        'attr' => [
            'name' => [
                'cfg.filter' => 'upload.audio',
            ],
        ],
    ],
    'file_doc' => [
        'name' => 'Documents',
        'parent_id' => 'file',
        'action' => ['admin', 'browser', 'delete', 'edit'],
        'attr' => [
            'name' => [
                'cfg.filter' => 'upload.doc',
            ],
        ],
    ],
    'file_image' => [
        'name' => 'Images',
        'parent_id' => 'file',
        'action' => ['admin', 'browser', 'delete', 'edit'],
        'attr' => [
            'name' => [
                'cfg.filter' => 'upload.image',
            ],
        ],
    ],
    'file_video' => [
        'name' => 'Videos',
        'parent_id' => 'file',
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
                'name' => 'Parent Page',
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
        'parent_id' => 'page',
        'action' => ['admin', 'delete', 'edit', 'view'],
    ],
    'page_content' => [
        'name' => 'Content Pages',
        'parent_id' => 'page',
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
        'parent_id' => 'block',
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
    'layout' => [
        'name' => 'Layout',
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
                'filter' => 'filter\id',
                'required' => true,
                'maxlength' => 100,
            ],
            'block_id' => [
                'name' => 'Block',
                'type' => 'entity',
                'required' => true,
                'ref' => 'block',
            ],
            'page_id' => [
                'name' => 'Page',
                'type' => 'page',
                'required' => true,
            ],
            'parent_id' => [
                'name' => 'Parent Block',
                'type' => 'select',
                'required' => true,
                'opt' => 'opt\block',
                'maxlength' => 100,
            ],
            'sort' => [
                'name' => 'Sort',
                'type' => 'int',
                'val' => 0,
            ],
        ],
    ],
];
