<?php
return [
    'attr' => [
        'name' => 'Attribute',
        'actions' => ['admin', 'create', 'delete', 'edit'],
        'attr' => [
            'id' => [
                'name' => 'Id',
                'generator' => 'auto',
                'type' => 'int',
            ],
            'entity_id' => [
                'name' => 'Entity',
                'type' => 'select.varchar',
                'required' => true,
                'opt' => ['all', ['entity']],
                'actions' => ['admin', 'edit'],
            ],
            'uid' => [
                'name' => 'Id',
                'type' => 'text',
                'required' => true,
                'actions' => ['admin', 'edit'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'actions' => ['admin', 'edit', 'index', 'view'],
            ],
            'sort' => [
                'name' => 'Sort',
                'type' => 'int',
                'actions' => ['admin', 'edit'],
            ],
            'type' => [
                'name' => 'Type',
                'type' => 'select.varchar',
                'required' => true,
                'opt' => ['data', ['attr']],
                'actions' => ['admin', 'edit'],
            ],
            'required' => [
                'name' => 'Required',
                'type' => 'checkbox.bool',
                'actions' => ['edit'],
            ],
            'uniq' => [
                'name' => 'Unique',
                'type' => 'checkbox.bool',
                'actions' => ['edit'],
            ],
            'searchable' => [
                'name' => 'Searchable',
                'type' => 'checkbox.bool',
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
                'opt' => ['data', ['action', 'attr']],
                'actions' => ['admin', 'edit'],
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'select.varchar',
                'opt' => ['all', ['project']],
            ],
        ],
    ],
    'content' => [
        'name' => 'Content',
        'actions' => ['index'],
        'attr' => [
            'id' => [
                'name' => 'Id',
                'generator' => 'auto',
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
                'type' => 'select.varchar',
                'opt' => ['all', ['entity']],
            ],
            'active' => [
                'name' => 'Active',
                'sort' => -800,
                'type' => 'checkbox.bool',
                'actions' => ['admin', 'edit'],
            ],
            'content' => [
                'name' => 'Content',
                'sort' => -700,
                'type' => 'rte',
                'nullable' => true,
                'searchable' => true,
                'actions' => ['edit', 'view'],
            ],
            'search' => [
                'name' => 'Search Index',
                'sort' => -600,
                'type' => 'index',
                'nullable' => true,
            ],
            'created' => [
                'name' => 'Created',
                'generator' => 'auto',
                'sort' => -400,
                'type' => 'datetime',
            ],
            'creator' => [
                'name' => 'Creator',
                'sort' => -300,
                'type' => 'select.int',
                'nullable' => true,
                'opt' => ['all', ['user']],
            ],
            'modified' => [
                'name' => 'Modified',
                'generator' => 'auto',
                'sort' => -200,
                'type' => 'datetime',
                'actions' => ['admin'],
            ],
            'modifier' => [
                'name' => 'Modifier',
                'sort' => -100,
                'type' => 'select.int',
                'nullable' => true,
                'opt' => ['all', ['user']],
                'actions' => ['admin'],
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'select.varchar',
                'opt' => ['all', ['project']],
            ],
        ],
    ],
    'entity' => [
        'name' => 'Entity',
        'actions' => ['admin', 'create', 'delete', 'edit'],
        'attr' => [
            'id' => [
                'name' => 'Id',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['admin', 'edit'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'actions' => ['admin', 'edit', 'index', 'view'],
            ],
            'actions' => [
                'name' => 'Actions',
                'type' => 'multicheckbox',
                'opt' => ['data', ['action', 'entity']],
                'actions' => ['admin', 'edit'],
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox.bool',
                'actions' => ['admin'],
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'select.varchar',
                'opt' => ['all', ['project']],
            ],
        ],
    ],
    'menu' => [
        'name' => 'Menu',
        'actions' => ['admin', 'create', 'delete', 'edit'],
        'attr' => [
            'id' => [
                'name' => 'Id',
                'generator' => 'auto',
                'type' => 'int',
            ],
            'uid' => [
                'name' => 'Id',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['admin', 'edit'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'actions' => ['admin', 'edit', 'index', 'view'],
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox.bool',
                'actions' => ['admin'],
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'select.varchar',
                'opt' => ['all', ['project']],
            ],
        ],
    ],
    'node' => [
        'name' => 'Node',
        'model' => 'node',
        'actions' => ['admin', 'create', 'delete', 'edit'],
        'attr' => [
            'id' => [
                'name' => 'Id',
                'generator' => 'auto',
                'type' => 'int',
                'actions' => ['admin'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'actions' => ['admin', 'edit', 'index', 'view'],
            ],
            'target' => [
                'name' => 'Target',
                'type' => 'text',
                'actions' => ['admin', 'edit'],
            ],
            'root_id' => [
                'name' => 'Menu',
                'type' => 'select.int',
                'opt' => ['all', ['menu']],
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
            'parent_id' => [
                'name' => 'Parent',
                'type' => 'select.int',
                'nullable' => true,
                'opt' => ['all', ['node']],
                'actions' => ['admin'],
            ],
            'level' => [
                'name' => 'Level',
                'type' => 'int',
                'actions' => ['admin'],
            ],
            'mode' => [
                'name' => 'Mode',
                'virtual' => true,
                'type' => 'select.varchar',
                'opt' => [['after' => 'After', 'before' => 'Before', 'child' => 'Child']],
                'actions' => ['edit'],
            ],
            'position' => [
                'name' => 'Position',
                'generator' => 'auto',
                'type' => 'select.varchar',
                'required' => true,
                'opt' => ['opt_position'],
                'actions' => ['edit'],
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'select.varchar',
                'opt' => ['all', ['project']],
            ],
        ],
    ],
    'project' => [
        'name' => 'Project',
        'actions' => ['admin', 'create', 'delete', 'edit'],
        'attr' => [
            'id' => [
                'name' => 'Id',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['admin', 'edit'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'actions' => ['admin', 'edit', 'index', 'view'],
            ],
            'host' => [
                'name' => 'Host',
                'type' => 'text',
                'nullable' => true,
                'uniq' => true,
                'actions' => ['admin', 'edit'],
            ],
            'theme' => [
                'name' => 'Theme',
                'type' => 'select.varchar',
                'nullable' => true,
                'opt' => ['opt_theme'],
                'actions' => ['admin', 'edit'],
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox.bool',
                'actions' => ['admin', 'edit'],
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox.bool',
                'actions' => ['admin'],
            ],
        ],
    ],
    'role' => [
        'name' => 'Role',
        'actions' => ['admin', 'create', 'delete', 'edit'],
        'attr' => [
            'id' => [
                'name' => 'Id',
                'generator' => 'auto',
                'type' => 'int',
                'actions' => ['admin'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['admin', 'edit', 'index', 'view'],
            ],
            'privilege' => [
                'name' => 'Privileges',
                'type' => 'multiselect',
                'opt' => ['opt_privilege'],
                'actions' => ['edit'],
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox.bool',
                'actions' => ['admin', 'edit'],
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox.bool',
                'actions' => ['admin'],
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'select.varchar',
                'opt' => ['all', ['project']],
            ],
        ],
    ],
    'url' => [
        'name' => 'URL',
        'actions' => ['admin', 'create', 'delete', 'edit'],
        'attr' => [
            'id' => [
                'name' => 'Id',
                'generator' => 'auto',
                'type' => 'int',
                'actions' => ['admin'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['admin', 'edit', 'index', 'view'],
            ],
            'target' => [
                'name' => 'Target',
                'type' => 'text',
                'required' => true,
                'actions' => ['admin', 'edit'],
            ],
            'redirect' => [
                'name' => 'Redirect',
                'type' => 'checkbox.bool',
                'actions' => ['admin', 'edit'],
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox.bool',
                'actions' => ['admin'],
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'select.varchar',
                'opt' => ['all', ['project']],
            ],
        ],
    ],
    'user' => [
        'name' => 'User',
        'actions' => ['admin', 'create', 'delete', 'edit'],
        'attr' => [
            'id' => [
                'name' => 'Id',
                'generator' => 'auto',
                'type' => 'int',
                'actions' => ['admin'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['admin', 'edit', 'index', 'view'],
            ],
            'username' => [
                'name' => 'Username',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['admin', 'edit', 'index', 'view'],
            ],
            'password' => [
                'name' => 'Password',
                'type' => 'password',
                'required' => true,
                'actions' => ['edit'],
            ],
            'role_id' => [
                'name' => 'Role',
                'type' => 'select.int',
                'required' => true,
                'opt' => ['all', ['role']],
                'actions' => ['admin', 'edit'],
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox.bool',
                'actions' => ['admin', 'edit'],
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox.bool',
                'actions' => ['admin'],
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'select.varchar',
                'opt' => ['all', ['project']],
            ],
        ],
    ],
];
