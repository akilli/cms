<?php
declare(strict_types=1);

return [
    'main-content' => [
        'type' => 'index',
        'parent_id' => 'content',
        'sort' => 20,
        'cfg' => [
            'action' => ['view', 'edit', 'delete'],
            'add' => true,
            'filterable' => true,
            'pager' => true,
            'searchable' => true,
            'sortable' => true,
            'table' => true,
        ],
    ],
];
