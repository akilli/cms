<?php
return [
    'backend' => 'varchar',
    'frontend' => 'frontend\file',
    'filter' => 'frontend\text',
    'validator' => 'validator\file',
    'viewer' => 'viewer\image',
    'ignorable' => true,
    'uploadable' => true,
    'accept' => ['image/gif', 'image/jpeg', 'image/png', 'image/svg+xml', 'image/webp'],
];
