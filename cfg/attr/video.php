<?php
return [
    'backend' => 'varchar',
    'frontend' => 'frontend\file',
    'filter' => 'frontend\text',
    'validator' => 'validator\file',
    'viewer' => 'viewer\video',
    'ignorable' => true,
    'uploadable' => true,
    'accept' => ['video/mp4', 'video/ogg', 'video/webm'],
];
