<?php
declare(strict_types=1);

return [
    'name' => 'Audios',
    'parent_id' => 'file',
    'action' => ['add', 'delete', 'edit', 'index'],
    'attr' => [
        'name' => [
            'type' => 'audio',
        ],
    ],
];
