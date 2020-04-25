<?php
return [
    'audio' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\file',
        'filter' => 'frontend\text',
        'validator' => 'validator\file',
        'viewer' => 'viewer\audio',
        'ignorable' => true,
        'uploadable' => true,
        'accept' => [
            'audio/aac', 'audio/flac', 'audio/mp3', 'audio/mpeg', 'audio/mpeg3', 'audio/ogg', 'audio/wav', 'audio/wave', 'audio/webm',
            'audio/x-aac', 'audio/x-flac', 'audio/x-mp3', 'audio/x-mpeg', 'audio/x-mpeg3', 'audio/x-pn-wav', 'audio/x-wav'
        ],
    ],
    'bool' => [
        'backend' => 'bool',
        'frontend' => 'frontend\bool',
        'filter' => 'frontend\select',
        'viewer' => 'viewer\opt',
        'opt' => 'bool',
    ],
    'date' => [
        'backend' => 'date',
        'frontend' => 'frontend\date',
        'validator' => 'validator\datetime',
        'viewer' => 'viewer\datetime',
        'cfg.backend' => 'Y-m-d',
        'cfg.frontend' => 'Y-m-d',
        'cfg.viewer' => 'd.m.y',
    ],
    'datetime' => [
        'backend' => 'datetime',
        'frontend' => 'frontend\datetime',
        'filter' => 'frontend\date',
        'validator' => 'validator\datetime',
        'viewer' => 'viewer\datetime',
        'cfg.backend' => 'Y-m-d H:i:s',
        'cfg.frontend' => 'Y-m-d\TH:i',
        'cfg.viewer' => 'd.m.y',
    ],
    'decimal' => [
        'backend' => 'decimal',
        'frontend' => 'frontend\decimal',
    ],
    'editor' => [
        'backend' => 'text',
        'frontend' => 'frontend\textarea',
        'filter' => 'frontend\text',
        'validator' => 'validator\editor',
        'viewer' => 'viewer\editor',
        'cfg.validator' => '<a><app-block><article><audio><b><blockquote><br><caption><cite><details><dfn><div><em><figcaption><figure><h2><h3><i><iframe><img><kbd><li><mark><ol><p><q><section><strong><summary><table><tbody><td><tfoot><th><thead><tr><ul><video>',
    ],
    'email' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\email',
        'validator' => 'validator\email',
        'viewer' => 'viewer\email',
    ],
    'entity' => [
        'backend' => 'int',
        'frontend' => 'frontend\select',
        'validator' => 'validator\entity',
        'viewer' => 'viewer\entity',
        'opt' => 'opt\entity',
    ],
    'entity[]' => [
        'backend' => 'int[]',
        'frontend' => 'frontend\select',
        'validator' => 'validator\multientity',
        'viewer' => 'viewer\multientity',
        'opt' => 'opt\entity',
    ],
    'entity_file' => [
        'backend' => 'int',
        'frontend' => 'frontend\browser',
        'filter' => 'frontend\int',
        'validator' => 'validator\entity',
        'viewer' => 'viewer\file',
        'ref' => 'file',
    ],
    'entity_id' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\select',
        'validator' => 'validator\opt',
        'viewer' => 'viewer\opt',
        'opt' => 'opt\entity_id',
        'max' => 50,
    ],
    'file' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\file',
        'filter' => 'frontend\text',
        'validator' => 'validator\file',
        'viewer' => 'viewer\file',
        'ignorable' => true,
        'uploadable' => true,
    ],
    'iframe' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\url',
        'validator' => 'validator\url',
        'viewer' => 'viewer\iframe',
    ],
    'image' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\file',
        'filter' => 'frontend\text',
        'validator' => 'validator\file',
        'viewer' => 'viewer\image',
        'ignorable' => true,
        'uploadable' => true,
        'accept' => ['image/gif', 'image/jpeg', 'image/png', 'image/svg+xml', 'image/webp'],
    ],
    'int' => [
        'backend' => 'int',
        'frontend' => 'frontend\int',
    ],
    'int[]' => [
        'backend' => 'int[]',
        'frontend' => 'frontend\checkbox',
        'filter' => 'frontend\select',
        'validator' => 'validator\opt',
        'viewer' => 'viewer\opt',
    ],
    'json' => [
        'backend' => 'json',
        'frontend' => 'frontend\json',
        'filter' => 'frontend\text',
        'viewer' => 'viewer\json',
    ],
    'password' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\password',
        'ignorable' => true,
        'autocomplete' => 'new-password',
    ],
    'radio' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\radio',
        'filter' => 'frontend\select',
        'validator' => 'validator\opt',
        'viewer' => 'viewer\opt',
    ],
    'range' => [
        'backend' => 'int',
        'frontend' => 'frontend\range',
    ],
    'select' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\select',
        'validator' => 'validator\opt',
        'viewer' => 'viewer\opt',
    ],
    'serial' => [
        'backend' => 'int',
        'frontend' => 'frontend\int',
        'auto' => true,
    ],
    'tel' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\tel',
        'validator' => 'validator\text',
    ],
    'text' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\text',
        'validator' => 'validator\text',
    ],
    'text[]' => [
        'backend' => 'text[]',
        'frontend' => 'frontend\checkbox',
        'filter' => 'frontend\select',
        'validator' => 'validator\opt',
        'viewer' => 'viewer\opt',
    ],
    'textarea' => [
        'backend' => 'text',
        'frontend' => 'frontend\textarea',
        'filter' => 'frontend\text',
        'validator' => 'validator\text',
    ],
    'time' => [
        'backend' => 'time',
        'frontend' => 'frontend\time',
        'validator' => 'validator\datetime',
        'viewer' => 'viewer\datetime',
        'cfg.backend' => 'H:i:s',
        'cfg.frontend' => 'H:i',
        'cfg.viewer' => 'H:i',
    ],
    'uid' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\text',
        'validator' => 'validator\uid',
    ],
    'url' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\url',
        'validator' => 'validator\url',
        'viewer' => 'viewer\url',
    ],
    'urlpath' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\text',
        'validator' => 'validator\urlpath',
        'viewer' => 'viewer\url',
    ],
    'video' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\file',
        'filter' => 'frontend\text',
        'validator' => 'validator\file',
        'viewer' => 'viewer\video',
        'ignorable' => true,
        'uploadable' => true,
        'accept' => ['video/mp4', 'video/ogg', 'video/webm'],
    ],
];
