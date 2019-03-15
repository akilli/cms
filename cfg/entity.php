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
            'mime' => [
                'name' => 'MIME-Type',
                'type' => 'text',
                'required' => true,
                'max' => 255,
            ],
            'ext' => [
                'name' => 'File Extension',
                'type' => 'text',
                'nullable' => true,
                'max' => 10,
            ],
            'thumb_url' => [
                'name' => 'Thumbnail',
                'type' => 'image',
                'nullable' => true,
                'unique' => true,
                'accept' => ['image/gif', 'image/jpeg', 'image/png', 'image/svg+xml', 'image/webp'],
            ],
            'thumb_mime' => [
                'name' => 'Thumbnail MIME',
                'type' => 'text',
                'nullable' => true,
                'max' => 255,
            ],
            'thumb_ext' => [
                'name' => 'Thumbnail Extension',
                'type' => 'text',
                'nullable' => true,
                'max' => 10,
            ],
            'info' => [
                'name' => 'Info',
                'type' => 'textarea',
                'required' => true,
            ],
        ],
    ],
    'file_media' => [
        'name' => 'Media',
        'readonly' => true,
        'parent_id' => 'file',
        'action' => ['browser'],
        'attr' => [
            'entity_id' => [
                'opt.filter' => 'media',
            ],
        ]
    ],
    'file_audio' => [
        'name' => 'Audios',
        'parent_id' => 'file',
        'action' => ['admin', 'browser', 'delete', 'edit'],
        'attr' => [
            'url' => [
                'accept' => [
                    'audio/aac', 'audio/flac', 'audio/mp3', 'audio/mpeg', 'audio/mpeg3', 'audio/ogg', 'audio/wav', 'audio/wave', 'audio/webm',
                    'audio/x-aac', 'audio/x-flac', 'audio/x-mp3', 'audio/x-mpeg', 'audio/x-mpeg3', 'audio/x-pn-wav', 'audio/x-wav'
                ],
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
                'accept' => ['image/gif', 'image/jpeg', 'image/png', 'image/svg+xml', 'image/webp'],
            ],
        ],
    ],
    'file_video' => [
        'name' => 'Videos',
        'parent_id' => 'file',
        'action' => ['admin', 'browser', 'delete', 'edit'],
        'attr' => [
            'url' => [
                'accept' => ['video/mp4', 'video/ogg', 'video/webm'],
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
            'image' => [
                'name' => 'Image',
                'type' => 'entity_file',
                'nullable' => true,
                'ref' => 'file_image',
            ],
            'teaser' => [
                'name' => 'Teaser',
                'type' => 'rte',
                'virtual' => true,
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
            'date' => [
                'name' => 'Date',
                'type' => 'datetime',
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
            'status' => [
                'name' => 'Status',
                'type' => 'status',
                'required' => true,
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
    'version' => [
        'name' => 'Versions',
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
                'type' => 'text',
                'required' => true,
            ],
            'page_id' => [
                'name' => 'Page',
                'type' => 'entity',
                'required' => true,
                'ref' => 'page',
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
                'required' => true,
            ],
            'aside' => [
                'name' => 'Additional Information',
                'type' => 'rte',
                'required' => true,
            ],
            'account_id' => [
                'name' => 'Account',
                'type' => 'entity',
                'nullable' => true,
                'ref' => 'account',
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
        ],
    ],
    'block' => [
        'name' => 'Blocks',
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
            'media' => [
                'name' => 'Media',
                'type' => 'entity_file',
                'nullable' => true,
                'ref' => 'file_media',
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
    'layout_page' => [
        'name' => 'Page Layout',
        'readonly' => true,
        'parent_id' => 'layout',
    ],
];
