<?php
return [
    'backend' => 'varchar',
    'frontend' => 'frontend\file',
    'filter' => 'frontend\text',
    'validator' => 'validator\file',
    'viewer' => 'viewer\audio',
    'ignorable' => true,
    'uploadable' => true,
    'accept' => [
        'audio/aac',
        'audio/flac',
        'audio/mp3',
        'audio/mpeg',
        'audio/mpeg3',
        'audio/ogg',
        'audio/wav',
        'audio/wave',
        'audio/webm',
        'audio/x-aac',
        'audio/x-flac',
        'audio/x-mp3',
        'audio/x-mpeg',
        'audio/x-mpeg3',
        'audio/x-pn-wav',
        'audio/x-wav',
    ],
];
