<?php
return [
    'project' => [
        'name' => 'Project',
        'attr' => [
            'id' => [
                'name' => 'ID',
                'auto' => true,
                'type' => 'int',
            ],
            'uid' => [
                'name' => 'UID',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['admin', 'edit'],
                'validator' => 'uid',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'actions' => ['admin', 'edit'],
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox',
                'actions' => ['admin', 'edit'],
                'val' => false,
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox',
                'val' => false,
            ],
        ],
    ],
    'role' => [
        'name' => 'Role',
        'attr' => [
            'id' => [
                'name' => 'ID',
                'auto' => true,
                'type' => 'int',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['admin', 'edit'],
            ],
            'privilege' => [
                'name' => 'Privileges',
                'type' => 'multiselect',
                'opt' => ['opt_privilege'],
                'actions' => ['edit'],
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox',
                'actions' => ['admin', 'edit'],
                'val' => false,
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox',
                'val' => false,
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => ['project'],
            ],
        ],
    ],
    'account' => [
        'name' => 'Account',
        'attr' => [
            'id' => [
                'name' => 'ID',
                'auto' => true,
                'type' => 'int',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['admin', 'edit'],
            ],
            'password' => [
                'name' => 'Password',
                'type' => 'password',
                'required' => true,
                'actions' => ['edit'],
            ],
            'role_id' => [
                'name' => 'Role',
                'type' => 'entity',
                'required' => true,
                'opt' => ['role'],
                'actions' => ['admin', 'edit'],
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox',
                'actions' => ['admin', 'edit'],
                'val' => false,
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox',
                'val' => false,
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => ['project'],
            ],
        ],
    ],
    'entity' => [
        'name' => 'Entity',
        'attr' => [
            'id' => [
                'name' => 'ID',
                'auto' => true,
                'type' => 'int',
            ],
            'uid' => [
                'name' => 'UID',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['admin', 'edit'],
                'validator' => 'entity',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'actions' => ['admin', 'edit'],
            ],
            'actions' => [
                'name' => 'Actions',
                'type' => 'multicheckbox',
                'opt' => ['data', ['opt', 'action.entity']],
                'actions' => ['admin', 'edit'],
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => ['project'],
            ],
        ],
    ],
    'attr' => [
        'name' => 'Attribute',
        'attr' => [
            'id' => [
                'name' => 'ID',
                'auto' => true,
                'type' => 'int',
            ],
            'entity_id' => [
                'name' => 'Entity',
                'type' => 'entity',
                'required' => true,
                'opt' => ['entity'],
                'actions' => ['admin', 'edit'],
            ],
            'uid' => [
                'name' => 'UID',
                'type' => 'text',
                'required' => true,
                'actions' => ['admin', 'edit'],
                'validator' => 'attr',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'actions' => ['admin', 'edit'],
            ],
            'sort' => [
                'name' => 'Sort',
                'type' => 'int',
                'actions' => ['admin', 'edit'],
            ],
            'type' => [
                'name' => 'Type',
                'type' => 'select',
                'required' => true,
                'opt' => ['opt_attr'],
                'actions' => ['admin', 'edit'],
            ],
            'required' => [
                'name' => 'Required',
                'type' => 'checkbox',
                'actions' => ['edit'],
                'val' => false,
            ],
            'uniq' => [
                'name' => 'Unique',
                'type' => 'checkbox',
                'actions' => ['edit'],
                'val' => false,
            ],
            'searchable' => [
                'name' => 'Searchable',
                'type' => 'checkbox',
                'actions' => ['edit'],
                'val' => false,
            ],
            'opt' => [
                'name' => 'Options',
                'type' => 'json',
                'actions' => ['edit'],
            ],
            'actions' => [
                'name' => 'Actions',
                'type' => 'multicheckbox',
                'opt' => ['data', ['opt', 'action.attr']],
                'actions' => ['admin', 'edit'],
            ],
            'val' => [
                'name' => 'Default value',
                'type' => 'text',
                'nullable' => true,
                'actions' => ['edit'],
            ],
            'minval' => [
                'name' => 'Minimal value',
                'type' => 'int',
                'actions' => ['edit'],
                'val' => 0,
            ],
            'maxval' => [
                'name' => 'Maximal value',
                'type' => 'int',
                'actions' => ['edit'],
                'val' => 0,
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => ['project'],
            ],
        ],
    ],
    'content' => [
        'name' => 'Content',
        'actions' => ['index'],
        'attr' => [
            'id' => [
                'name' => 'ID',
                'auto' => true,
                'type' => 'int',
                'actions' => ['admin'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'searchable' => true,
                'actions' => ['admin', 'edit', 'form', 'index', 'view'],
            ],
            'entity_id' => [
                'name' => 'Entity',
                'type' => 'entity',
                'opt' => ['entity'],
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox',
                'actions' => ['admin', 'edit'],
                'val' => false,
            ],
            'content' => [
                'name' => 'Content',
                'type' => 'rte',
                'searchable' => true,
                'actions' => ['edit', 'view'],
            ],
            'search' => [
                'name' => 'Search',
                'type' => 'search',
            ],
            'created' => [
                'name' => 'Created',
                'type' => 'datetime',
            ],
            'creator' => [
                'name' => 'Creator',
                'type' => 'entity',
                'nullable' => true,
                'opt' => ['account'],
            ],
            'modified' => [
                'name' => 'Modified',
                'type' => 'datetime',
                'actions' => ['admin'],
            ],
            'modifier' => [
                'name' => 'Modifier',
                'type' => 'entity',
                'nullable' => true,
                'opt' => ['account'],
                'actions' => ['admin'],
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => ['project'],
            ],
        ],
    ],
    'url' => [
        'name' => 'URL',
        'attr' => [
            'id' => [
                'name' => 'ID',
                'auto' => true,
                'type' => 'int',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['admin', 'edit'],
                'validator' => 'urlpath',
            ],
            'target' => [
                'name' => 'Target',
                'type' => 'text',
                'required' => true,
                'actions' => ['admin', 'edit'],
                'validator' => 'urlpath',
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox',
                'val' => false,
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => ['project'],
            ],
        ],
    ],
    'media' => [
        'name' => 'Media',
        'model' => 'media',
        'attr' => [
            'id' => [
                'name' => 'ID',
                'auto' => true,
                'type' => 'text',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'file',
                'required' => true,
                'uniq' => true,
                'actions' => ['admin', 'edit'],
            ],
            'size' => [
                'name' => 'Size',
                'type' => 'int',
                'actions' => ['admin'],
                'viewer' => 'filesize',
            ],
            'modified' => [
                'name' => 'Modified',
                'type' => 'datetime',
                'actions' => ['admin'],
            ],
        ],
    ],
];
