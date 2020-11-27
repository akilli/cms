<?php
return [
    'backend' => 'varchar',
    'frontend' => 'attr\file\frontend',
    'filter' => 'attr\text\frontend',
    'validator' => 'attr\file\validator',
    'viewer' => 'attr\video\viewer',
    'ignorable' => true,
    'uploadable' => true,
    'accept' => ['video/mp4', 'video/ogg', 'video/webm'],
];
