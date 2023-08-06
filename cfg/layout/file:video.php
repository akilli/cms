<?php
declare(strict_types=1);

return [
    'main-content' => [
        'type' => 'index',
        'parent_id' => 'content',
        'sort' => 10,
        'cfg' => [
            'action' => ['view', 'edit', 'delete'],
            'add' => true,
            'crit' => [['mime', 'video/', APP['op']['^']]],
            'filterable' => true,
            'pager' => true,
            'searchable' => true,
            'sortable' => true,
            'table' => true,
        ],
    ],
];
