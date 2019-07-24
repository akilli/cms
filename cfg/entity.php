<?php
return [
    'role' => [
        'name' => 'Roles',
        'action' => ['admin', 'delete', 'edit'],
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
            'priv' => [
                'name' => 'Privileges',
                'type' => 'text[]',
                'opt' => 'opt\priv',
            ],
        ],
    ],
    'account' => [
        'name' => 'Accounts',
        'action' => ['admin', 'dashboard', 'delete', 'edit', 'login', 'logout', 'profile'],
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
            'role_id' => [
                'name' => 'Role',
                'type' => 'entity',
                'required' => true,
                'ref' => 'role',
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
            'confirmation' => [
                'name' => 'Password Confirmation',
                'type' => 'password',
                'virtual' => true,
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
        ],
    ],
    'file' => [
        'name' => 'Files',
        'readonly' => true,
        'action' => ['browser'],
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
                'type' => 'entity_id',
                'required' => true,
            ],
            'url' => [
                'name' => 'URL',
                'type' => 'file',
                'required' => true,
                'unique' => true,
            ],
            'thumb' => [
                'name' => 'Thumbnail',
                'type' => 'image',
                'nullable' => true,
                'unique' => true,
            ],
            'mime' => [
                'name' => 'MIME-Type',
                'type' => 'text',
                'required' => true,
                'max' => 255,
            ],
            'info' => [
                'name' => 'Info',
                'type' => 'textarea',
                'required' => true,
            ],
        ],
    ],
    'file_audio' => [
        'name' => 'Audios',
        'parent_id' => 'file',
        'action' => ['admin', 'browser', 'delete', 'edit'],
        'attr' => [
            'url' => [
                'type' => 'audio',
            ],
        ],
    ],
    'file_doc' => [
        'name' => 'Documents',
        'parent_id' => 'file',
        'action' => ['admin', 'browser', 'delete', 'edit'],
        'attr' => [
            'url' => [
                'accept' => [
                    'application/msword', 'application/pdf', 'application/vnd.ms-excel', 'application/vnd.ms-excel.sheet.macroEnabled.12',
                    'application/vnd.oasis.opendocument.spreadsheet', 'application/vnd.oasis.opendocument.text',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/zip', 'text/csv'
                ],
            ],
        ],
    ],
    'file_iframe' => [
        'name' => 'Iframes',
        'parent_id' => 'file',
        'action' => ['admin', 'browser', 'delete', 'edit'],
        'attr' => [
            'url' => [
                'type' => 'iframe',
            ],
        ],
    ],
    'file_image' => [
        'name' => 'Images',
        'parent_id' => 'file',
        'action' => ['admin', 'browser', 'delete', 'edit'],
        'attr' => [
            'url' => [
                'type' => 'image',
            ],
        ],
    ],
    'file_video' => [
        'name' => 'Videos',
        'parent_id' => 'file',
        'action' => ['admin', 'browser', 'delete', 'edit'],
        'attr' => [
            'url' => [
                'type' => 'video',
            ],
        ],
    ],
    'page' => [
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
                'type' => 'rte',
            ],
            'aside' => [
                'name' => 'Additional Information',
                'type' => 'rte',
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
                'ref' => 'page_content',
            ],
            'sort' => [
                'name' => 'Sort',
                'type' => 'int',
            ],
            'pos' => [
                'name' => 'Position',
                'type' => 'text',
                'viewer' => 'viewer\pos',
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
                'type' => 'entity[]',
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
    'block' => [
        'name' => 'Blocks',
        'readonly' => true,
        'action' => ['api', 'browser'],
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
            'link' => [
                'name' => 'Link',
                'type' => 'urlpath',
                'nullable' => true,
                'max' => 255,
            ],
            'file' => [
                'name' => 'File',
                'type' => 'entity_file',
                'nullable' => true,
            ],
            'content' => [
                'name' => 'Content',
                'type' => 'rte',
            ],
        ],
    ],
    'block_content' => [
        'name' => 'Content Blocks',
        'parent_id' => 'block',
        'action' => ['admin', 'delete', 'edit'],
    ],
    'layout' => [
        'name' => 'Layout',
        'unique' => [['page_id', 'parent_id', 'name']],
        'action' => ['admin', 'delete', 'edit'],
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
                'ref' => 'page_content',
            ],
            'parent_id' => [
                'name' => 'Parent Block',
                'type' => 'select',
                'required' => true,
                'opt' => 'block',
                'max' => 100,
            ],
            'sort' => [
                'name' => 'Sort',
                'type' => 'int',
            ],
        ],
    ],
];
