<?php
return [
    'name' => 'Media',
    'model' => 'media',
    'actions' => ['admin', 'browser', 'delete', 'edit', 'import', 'view'],
    'attr' => [
        'id' => [
            'name' => 'ID',
            'auto' => true,
            'type' => 'text',
        ],
        'name' => [
            'name' => 'Name',
            'type' => 'file',
            'required' => true,
            'uniq' => true,
            'searchable' => true,
            'actions' => ['admin', 'edit'],
        ],
        'size' => [
            'name' => 'Size',
            'type' => 'int',
            'actions' => ['admin'],
            'viewer' => 'cms\viewer_filesize',
        ],
        'file' => [
            'name' => 'File',
            'type' => 'text',
        ],
    ],
];
