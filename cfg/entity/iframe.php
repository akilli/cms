<?php
declare(strict_types=1);

return [
    'name' => 'Iframes',
    'parent_id' => 'file',
    'action' => ['add', 'delete', 'edit', 'index'],
    'attr' => [
        'name' => [
            'name' => 'URL',
            'type' => 'iframe',
        ],
    ],
];
