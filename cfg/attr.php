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
        'validator' => 'validator\date',
        'viewer' => 'viewer\date',
    ],
    'datetime' => [
        'backend' => 'datetime',
        'frontend' => 'frontend\datetime',
        'filter' => 'frontend\date',
        'validator' => 'validator\datetime',
        'viewer' => 'viewer\datetime',
    ],
    'decimal' => [
        'backend' => 'decimal',
        'frontend' => 'frontend\decimal',
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
    'entity_file' => [
        'backend' => 'int',
        'frontend' => 'frontend\browser',
        'filter' => 'frontend\int',
        'validator' => 'validator\entity',
        'viewer' => 'viewer\file',
        'ref' => 'file',
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
        'viewer' => 'viewer\file',
    ],
    'image' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\file',
        'filter' => 'frontend\text',
        'validator' => 'validator\file',
        'viewer' => 'viewer\image',
        'ignorable' => true,
        'uploadable' => true,
    ],
    'int' => [
        'backend' => 'int',
        'frontend' => 'frontend\int',
    ],
    'int[]' => [
        'backend' => 'int',
        'frontend' => 'frontend\checkbox',
        'filter' => 'frontend\select',
        'validator' => 'validator\opt',
        'viewer' => 'viewer\opt',
        'multiple' => true,
    ],
    'json' => [
        'backend' => 'json',
        'frontend' => 'frontend\json',
        'filter' => 'frontend\text',
        'viewer' => 'viewer\json',
    ],
    'multientity' => [
        'backend' => 'int',
        'frontend' => 'frontend\select',
        'validator' => 'validator\multientity',
        'viewer' => 'viewer\multientity',
        'multiple' => true,
        'opt' => 'opt\entity',
    ],
    'parent' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\select',
        'validator' => 'validator\opt',
        'viewer' => 'viewer\opt',
        'opt' => 'opt\parent',
        'max' => 50,
    ],
    'password' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\password',
        'ignorable' => true,
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
    'rte' => [
        'backend' => 'text',
        'frontend' => 'frontend\textarea',
        'filter' => 'frontend\text',
        'validator' => 'validator\rte',
        'viewer' => 'viewer\rte',
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
    'status' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\radio',
        'filter' => 'frontend\select',
        'validator' => 'validator\opt',
        'viewer' => 'viewer\opt',
        'opt' => 'status',
        'opt.frontend' => 'opt\status',
        'opt.validator' => 'opt\status',
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
        'backend' => 'varchar',
        'frontend' => 'frontend\checkbox',
        'filter' => 'frontend\select',
        'validator' => 'validator\opt',
        'viewer' => 'viewer\opt',
        'multiple' => true,
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
        'validator' => 'validator\time',
        'viewer' => 'viewer\time',
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
    ],
];
