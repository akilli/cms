<?php
return [
    'account' => [
        'name' => 'Account',
        'attr' => [
            'id' => [
                'name' => 'Id',
                'auto' => true,
                'type' => 'int',
                'actions' => ['admin'],
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
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox',
                'actions' => ['admin'],
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
                'name' => 'Id',
                'auto' => true,
                'type' => 'int',
                'actions' => ['admin'],
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
            ],
            'uniq' => [
                'name' => 'Unique',
                'type' => 'checkbox',
                'actions' => ['edit'],
            ],
            'searchable' => [
                'name' => 'Searchable',
                'type' => 'checkbox',
                'actions' => ['edit'],
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
                'name' => 'Id',
                'auto' => true,
                'sort' => -1100,
                'type' => 'int',
                'actions' => ['admin'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'sort' => -1000,
                'required' => true,
                'searchable' => true,
                'actions' => ['admin', 'edit', 'index', 'view'],
            ],
            'entity_id' => [
                'name' => 'Entity',
                'sort' => -900,
                'type' => 'entity',
                'opt' => ['entity'],
            ],
            'active' => [
                'name' => 'Active',
                'sort' => -800,
                'type' => 'checkbox',
                'actions' => ['admin', 'edit'],
            ],
            'content' => [
                'name' => 'Content',
                'sort' => -700,
                'type' => 'rte',
                'searchable' => true,
                'actions' => ['edit', 'view'],
            ],
            'search' => [
                'name' => 'Search',
                'sort' => -600,
                'type' => 'search',
            ],
            'created' => [
                'name' => 'Created',
                'sort' => -400,
                'type' => 'datetime',
            ],
            'creator' => [
                'name' => 'Creator',
                'sort' => -300,
                'type' => 'entity',
                'nullable' => true,
                'opt' => ['account'],
            ],
            'modified' => [
                'name' => 'Modified',
                'sort' => -200,
                'type' => 'datetime',
                'actions' => ['admin'],
            ],
            'modifier' => [
                'name' => 'Modifier',
                'sort' => -100,
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
    'entity' => [
        'name' => 'Entity',
        'attr' => [
            'id' => [
                'name' => 'Id',
                'auto' => true,
                'type' => 'int',
                'actions' => ['admin'],
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
            'system' => [
                'name' => 'System',
                'type' => 'checkbox',
                'actions' => ['admin'],
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => ['project'],
            ],
        ],
    ],
    'menu' => [
        'name' => 'Menu',
        'attr' => [
            'id' => [
                'name' => 'Id',
                'auto' => true,
                'type' => 'int',
                'actions' => ['admin'],
            ],
            'uid' => [
                'name' => 'UID',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['admin', 'edit'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'actions' => ['admin', 'edit'],
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => ['project'],
            ],
        ],
    ],
    'node' => [
        'name' => 'Node',
        'model' => 'nestedset',
        'attr' => [
            'id' => [
                'name' => 'Id',
                'auto' => true,
                'type' => 'int',
                'actions' => ['admin'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'actions' => ['admin', 'edit'],
            ],
            'target' => [
                'name' => 'Target',
                'type' => 'text',
                'actions' => ['admin', 'edit'],
            ],
            'root_id' => [
                'name' => 'Menu',
                'type' => 'entity',
                'opt' => ['menu'],
                'actions' => ['admin'],
            ],
            'lft' => [
                'name' => 'Position Left',
                'type' => 'int',
            ],
            'rgt' => [
                'name' => 'Position Right',
                'type' => 'int',
            ],
            'level' => [
                'name' => 'Level',
                'type' => 'int',
                'actions' => ['admin'],
            ],
            'mode' => [
                'name' => 'Mode',
                'col' => false,
                'type' => 'select',
                'opt' => ['data', ['opt', 'mode']],
                'actions' => ['edit'],
                'val' => 'after'
            ],
            'pos' => [
                'name' => 'Position',
                'col' => false,
                'type' => 'select',
                'required' => true,
                'opt' => ['opt_position'],
                'actions' => ['edit'],
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => ['project'],
            ],
        ],
    ],
    'project' => [
        'name' => 'Project',
        'attr' => [
            'id' => [
                'name' => 'Id',
                'auto' => true,
                'type' => 'int',
                'actions' => ['admin'],
            ],
            'uid' => [
                'name' => 'UID',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['admin', 'edit'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'actions' => ['admin', 'edit'],
            ],
            'host' => [
                'name' => 'Host',
                'type' => 'text',
                'uniq' => true,
                'actions' => ['admin', 'edit'],
            ],
            'theme' => [
                'name' => 'Theme',
                'type' => 'select',
                'opt' => ['opt_theme'],
                'actions' => ['admin', 'edit'],
                'val' => 'base',
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox',
                'actions' => ['admin', 'edit'],
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox',
                'actions' => ['admin'],
            ],
        ],
    ],
    'role' => [
        'name' => 'Role',
        'attr' => [
            'id' => [
                'name' => 'Id',
                'auto' => true,
                'type' => 'int',
                'actions' => ['admin'],
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
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox',
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
                'name' => 'Id',
                'auto' => true,
                'type' => 'int',
                'actions' => ['admin'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['admin', 'edit'],
            ],
            'target' => [
                'name' => 'Target',
                'type' => 'text',
                'required' => true,
                'actions' => ['admin', 'edit'],
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox',
                'actions' => ['admin'],
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => ['project'],
            ],
        ],
    ],
];
