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
        'action' => ['admin', 'dashboard', 'delete', 'edit', 'login', 'logout', 'password'],
        'attr' => [
            'id' => [
                'name' => 'ID',
                'type' => 'serial',
            ],
            'name' => [
                'name' => 'Name',
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
                'type' => 'parent',
                'required' => true,
            ],
            'url' => [
                'name' => 'URL',
                'type' => 'upload',
                'required' => true,
                'unique' => true,
                'max' => 255,
            ],
            'ext' => [
                'name' => 'Extension',
                'type' => 'text',
                'required' => true,
                'max' => 10,
            ],
            'mime' => [
                'name' => 'MIME',
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
                'type' => 'parent',
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
                'type' => 'image',
                'nullable' => true,
            ],
            'teaser' => [
                'name' => 'Teaser',
                'type' => 'rtemin',
            ],
            'main' => [
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
                'type' => 'page',
                'nullable' => true,
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
                'type' => 'int[]',
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
            'teaser' => [
                'name' => 'Teaser',
                'type' => 'rtemin',
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
                'type' => 'parent',
                'required' => true,
            ],
        ],
    ],
    'block_content' => [
        'name' => 'Content Blocks',
        'parent_id' => 'block',
        'action' => ['admin', 'delete', 'edit'],
        'attr' => [
            'content' => [
                'name' => 'Content',
                'type' => 'rte',
            ],
        ],
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
