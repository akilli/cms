<?php
declare(strict_types=1);

return [
    'name' => 'Videos',
    'parent_id' => 'file',
    'action' => ['add', 'delete', 'edit', 'index'],
    'attr' => [
        'name' => [
            'type' => 'video',
        ],
    ],
];
