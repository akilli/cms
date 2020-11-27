<?php
return [
    'backend' => 'varchar',
    'frontend' => 'attr\file\frontend',
    'filter' => 'attr\text\frontend',
    'validator' => 'attr\file\validator',
    'viewer' => 'attr\image\viewer',
    'ignorable' => true,
    'uploadable' => true,
    'accept' => ['image/gif', 'image/jpeg', 'image/png', 'image/svg+xml', 'image/webp'],
];
