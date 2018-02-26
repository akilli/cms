<?php
return [
    'role' => [
        'name' => 'Roles',
        'type' => 'db',
        'act' => [
            'admin' => ['incl' => ['name']],
            'delete' => [],
            'edit' => [],
        ],
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
        'act' => [
            'admin' => ['incl' => ['name', 'role']],
            'delete' => [],
            'edit' => [],
            'login' => ['incl' => ['name', 'password']],
            'logout' => [],
            'password' => ['incl' => ['password', 'confirmation']],
        ],
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
                'filter' => 'filter\id',
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
    'url' => [
        'name' => 'URL',
        'type' => 'db',
        'act' => [
            'admin' => [],
            'delete' => [],
            'edit' => [],
        ],
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
                'maxlength' => 255,
                'filter' => 'filter\path',
            ],
            'target' => [
                'name' => 'Target',
                'type' => 'text',
                'required' => true,
                'searchable' => true,
                'maxlength' => 255,
                'filter' => 'filter\path',
            ],
            'redirect' => [
                'name' => 'Redirect',
                'type' => 'int',
                'frontend' => 'frontend\select',
                'nullable' => true,
                'opt' => 'redirect',
            ],
        ],
    ],
    'file' => [
        'name' => 'Files',
        'type' => 'db',
        'act' => [
            'browser' => ['incl' => ['name', 'info']],
        ],
        'attr' => [
            'id' => [
                'name' => 'ID',
                'auto' => true,
                'type' => 'int',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'file',
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
        'act' => [
            'admin' => ['excl' => ['ent']],
            'browser' => ['incl' => ['name', 'info']],
            'delete' => [],
            'edit' => ['incl' => ['name', 'info']],
        ],
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
        'act' => [
            'admin' => ['excl' => ['ent']],
            'browser' => ['incl' => ['name', 'info']],
            'delete' => [],
            'edit' => ['incl' => ['name', 'info']],
        ],
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
        'act' => [
            'admin' => ['excl' => ['ent']],
            'browser' => ['incl' => ['name', 'info']],
            'delete' => [],
            'edit' => ['incl' => ['name', 'info']],
        ],
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
        'act' => [
            'admin' => ['excl' => ['ent']],
            'browser' => ['incl' => ['name', 'info']],
            'delete' => [],
            'edit' => ['incl' => ['name', 'info']],
        ],
        'attr' => [
            'name' => [
                'opt' => 'video',
            ],
        ],
    ],
    'page' => [
        'name' => 'Pages',
        'type' => 'db',
        'act' => [
            'sitemap' => [],
        ],
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
                'type' => 'ent',
                'nullable' => true,
                'ent' => 'image',
                'viewer' => 'viewer\fileopt',
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
                'frontend' => 'frontend\textarea',
                'val' => '',
                'maxlength' => 300,
            ],
            'slug' => [
                'name' => 'Slug',
                'type' => 'text',
                'required' => true,
                'maxlength' => 50,
                'filter' => 'filter\slug',
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
                'type' => 'ent',
                'nullable' => true,
                'ent' => 'page',
                'opt' => 'opt\page',
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
                'maxlength' => 255,
                'viewer' => 'viewer\pos',
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
        'act' => [
            'admin' => ['incl' => ['name', 'pos', 'menu', 'status', 'date']],
            'delete' => [],
            'edit' => ['incl' => ['name', 'slug', 'menu', 'parent', 'sort', 'status', 'menu', 'image', 'main', 'aside', 'sidebar', 'meta']],
            'view' => ['incl' => ['image', 'main', 'aside']],
        ],
    ],
    'article' => [
        'name' => 'Articles',
        'type' => 'db',
        'parent' => 'page',
        'act' => [
            'admin' => ['incl' => ['name', 'parent', 'status', 'date']],
            'delete' => [],
            'edit' => ['incl' => ['name', 'slug', 'menu', 'parent', 'sort', 'status', 'menu', 'image', 'teaser', 'main', 'meta']],
            'index' => ['incl' => ['image', 'name', 'teaser']],
            'view' => ['incl' => ['name', 'image', 'teaser', 'main']],
        ],
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
                'type' => 'ent',
                'required' => true,
                'ent' => 'page',
            ],
        ],
    ],
];
