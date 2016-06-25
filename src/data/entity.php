<?php
return [
    'attr' => [
        'name' => 'Attribute',
        'actions' => ['create', 'delete', 'edit', 'export', 'import', 'index'],
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
                'opt' => ['all', ['entity', ['model' => 'eav']]],
                'actions' => ['edit', 'index'],
            ],
            'uid' => [
                'name' => 'Id',
                'type' => 'text',
                'required' => true,
                'actions' => ['edit', 'index'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'actions' => ['edit', 'index', 'list', 'view'],
            ],
            'sort' => [
                'name' => 'Sort',
                'type' => 'int',
                'actions' => ['edit', 'index'],
            ],
            'type' => [
                'name' => 'Type',
                'type' => 'select.varchar',
                'required' => true,
                'opt' => ['data', ['attr']],
                'actions' => ['edit', 'index'],
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
                'nullable' => true,
                'actions' => ['edit'],
            ],
            'actions' => [
                'name' => 'Actions',
                'type' => 'multiselect',
                'nullable' => true,
                'opt' => ['config', ['action.attr']],
                'actions' => ['edit', 'index'],
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
        'attr' => [
            'id' => [
                'name' => 'Id',
                'generator' => 'auto',
                'sort' => -1100,
                'type' => 'int',
                'actions' => ['index'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'sort' => -1000,
                'required' => true,
                'searchable' => true,
                'actions' => ['edit', 'index', 'list', 'view'],
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
                'actions' => ['edit', 'index'],
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
            'oid' => [
                'name' => 'Original Id',
                'sort' => -500,
                'type' => 'text',
                'nullable' => true,
                'uniq' => true,
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
            ],
            'modifier' => [
                'name' => 'Modifier',
                'sort' => -100,
                'type' => 'select.int',
                'nullable' => true,
                'opt' => ['all', ['user']],
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
        'actions' => ['create', 'delete', 'edit', 'export', 'import', 'index'],
        'attr' => [
            'id' => [
                'name' => 'Id',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['edit', 'index'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'actions' => ['edit', 'index', 'list', 'view'],
            ],
            'model' => [
                'name' => 'Model',
                'type' => 'select.varchar',
                'required' => true,
                'opt' => [['eav' => 'eav', 'joined' => 'joined']],
                'actions' => ['edit', 'index', 'list', 'view'],
            ],
            'actions' => [
                'name' => 'Actions',
                'type' => 'multiselect',
                'nullable' => true,
                'opt' => ['config', ['action.entity']],
                'actions' => ['edit', 'index'],
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox.bool',
                'actions' => ['index'],
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
        'actions' => ['create', 'delete', 'edit', 'export', 'import', 'index'],
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
                'actions' => ['edit', 'index'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'actions' => ['edit', 'index', 'list', 'view'],
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox.bool',
                'actions' => ['index'],
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
        'actions' => ['create', 'delete', 'edit', 'export', 'import', 'index'],
        'attr' => [
            'id' => [
                'name' => 'Id',
                'generator' => 'auto',
                'type' => 'int',
                'actions' => ['index'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'actions' => ['edit', 'index', 'list', 'view'],
            ],
            'target' => [
                'name' => 'Target',
                'type' => 'text',
                'actions' => ['edit', 'index'],
            ],
            'root_id' => [
                'name' => 'Menu',
                'generator' => 'auto',
                'type' => 'select.int',
                'opt' => ['all', ['menu']],
                'actions' => ['index'],
            ],
            'lft' => [
                'name' => 'Position Left',
                'generator' => 'auto',
                'type' => 'int',
            ],
            'rgt' => [
                'name' => 'Position Right',
                'generator' => 'auto',
                'type' => 'int',
            ],
            'parent_id' => [
                'name' => 'Parent',
                'generator' => 'auto',
                'type' => 'select.int',
                'nullable' => true,
                'opt' => ['all', ['node']],
                'actions' => ['index'],
            ],
            'level' => [
                'name' => 'Level',
                'generator' => 'auto',
                'type' => 'int',
                'actions' => ['index'],
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
        'actions' => ['create', 'delete', 'edit', 'export', 'import', 'index'],
        'attr' => [
            'id' => [
                'name' => 'Id',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['edit', 'index'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'actions' => ['edit', 'index', 'list', 'view'],
            ],
            'host' => [
                'name' => 'Host',
                'type' => 'text',
                'nullable' => true,
                'uniq' => true,
                'actions' => ['edit', 'index'],
            ],
            'theme' => [
                'name' => 'Theme',
                'type' => 'select.varchar',
                'nullable' => true,
                'opt' => ['opt_theme'],
                'actions' => ['edit', 'index'],
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox.bool',
                'actions' => ['edit', 'index'],
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox.bool',
                'actions' => ['index'],
            ],
        ],
    ],
    'rewrite' => [
        'name' => 'Rewrite',
        'actions' => ['create', 'delete', 'edit', 'export', 'import', 'index'],
        'attr' => [
            'id' => [
                'name' => 'Id',
                'generator' => 'auto',
                'type' => 'int',
                'actions' => ['index'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'uniq' => true,
                'actions' => ['edit', 'index', 'list', 'view'],
            ],
            'target' => [
                'name' => 'Target',
                'type' => 'text',
                'required' => true,
                'actions' => ['edit', 'index'],
            ],
            'redirect' => [
                'name' => 'Redirect',
                'type' => 'checkbox.bool',
                'actions' => ['edit', 'index'],
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox.bool',
                'actions' => ['index'],
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'select.varchar',
                'opt' => ['all', ['project']],
            ],
        ],
    ],
    'role' => [
        'name' => 'Role',
        'actions' => ['create', 'delete', 'edit', 'export', 'import', 'index'],
        'attr' => [
            'id' => [
                'name' => 'Id',
                'generator' => 'auto',
                'type' => 'int',
                'actions' => ['index'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['edit', 'index', 'list', 'view'],
            ],
            'privilege' => [
                'name' => 'Privileges',
                'type' => 'multiselect',
                'opt' => ['privileges'],
                'actions' => ['edit'],
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox.bool',
                'actions' => ['edit', 'index'],
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox.bool',
                'actions' => ['index'],
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
        'actions' => ['create', 'delete', 'edit', 'export', 'import', 'index'],
        'attr' => [
            'id' => [
                'name' => 'Id',
                'generator' => 'auto',
                'type' => 'int',
                'actions' => ['index'],
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['edit', 'index', 'list', 'view'],
            ],
            'username' => [
                'name' => 'Username',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['edit', 'index', 'list', 'view'],
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
                'actions' => ['edit', 'index'],
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox.bool',
                'actions' => ['edit', 'index'],
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox.bool',
                'actions' => ['index'],
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'select.varchar',
                'opt' => ['all', ['project']],
            ],
        ],
    ],
];
