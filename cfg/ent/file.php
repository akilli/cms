<?php
return [
    'name' => 'Files',
    'type' => 'db',
    'act' => [
        'admin' => ['name', 'size'],
        'asset' => [],
        'browser' => ['name', 'size'],
        'delete' => [],
        'edit' => ['name', 'info'],
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
            'opt' => [
                'aac', 'flac', 'mp3', 'oga', 'ogg', 'wav', 'weba',
                'gif', 'jpg', 'png', 'svg', 'webp',
                'mp4', 'ogv', 'webm',
                'bz2', 'csv', 'doc', 'docx', 'gz', 'odg', 'odp', 'ods', 'odt', 'pdf', 'xls', 'xlsm', 'xlsx', 'zip',
            ],
        ],
        'info' => [
            'name' => 'Info',
            'type' => 'textarea',
            'searchable' => true,
            'val' => '',
        ],
        'size' => [
            'name' => 'Size',
            'type' => 'int',
            'viewer' => 'filesize',
        ],
    ],
];
