<?php
return [
    'name' => 'Documents',
    'parent_id' => 'file',
    'action' => ['delete', 'edit', 'index'],
    'attr' => [
        'url' => [
            'accept' => [
                'application/msword',
                'application/pdf',
                'application/vnd.ms-excel',
                'application/vnd.ms-excel.sheet.macroEnabled.12',
                'application/vnd.oasis.opendocument.spreadsheet',
                'application/vnd.oasis.opendocument.text',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/zip',
                'text/csv',
            ],
        ],
    ],
];
