<?php
return [
    'attribute' => [
        'id' => 'attribute',
        'name' => 'Attribute',
        'actions' => ['create', 'edit', 'delete', 'index'],
        'toolbar' => 'types',
        'sort_order' => 200,
        'attributes' => [
            'id' => [
                'name' => 'ID',
                'column' => 'id',
                'type' => 'text',
                'required' => true,
                'unambiguous' => true,
                'actions' => ['index'],
                'generator' => 'qnd\generator_id',
                'generator_base' => 'name',
            ],
            'name' => [
                'name' => 'Name',
                'column' => 'name',
                'type' => 'text',
                'required' => true,
                'actions' => ['all'],
            ],
            'type' => [
                'name' => 'Type',
                'column' => 'type',
                'type' => 'select.varchar',
                'required' => true,
                'options_callback' => 'qnd\data',
                'options_callback_param' => ['attribute'],
                'actions' => ['edit', 'index'],
            ],
            'options_entity' => [
                'name' => 'Options Entity',
                'column' => 'options_entity',
                'type' => 'select.varchar',
                'nullable' => true,
                'options_callback' => 'qnd\data',
                'options_callback_param' => ['meta'],
                'actions' => ['edit'],
            ],
            'options_callback' => [
                'name' => 'Options Callback',
                'column' => 'options_callback',
                'type' => 'callback',
                'nullable' => true,
                'actions' => ['edit'],
            ],
            'options' => [
                'name' => 'Options',
                'column' => 'options',
                'type' => 'json',
                'actions' => ['edit'],
            ],
        ],
    ],
    'content' => [
        'id' => 'content',
        'name' => 'Content',
        'attributes' => [
            'id' => [
                'name' => 'ID',
                'column' => 'id',
                'auto' => true,
                'type' => 'int',
                'actions' => ['index'],
            ],
            'name' => [
                'name' => 'Name',
                'column' => 'name',
                'type' => 'text',
                'required' => true,
                'searchable' => true,
                'actions' => ['all'],
            ],
            'entity_id' => [
                'name' => 'Entity',
                'column' => 'entity_id',
                'type' => 'select.varchar',
                'options_entity' => 'entity',
            ],
            'active' => [
                'name' => 'Active',
                'column' => 'active',
                'type' => 'checkbox.bool',
                'actions' => ['edit', 'index'],
            ],
            'system' => [
                'name' => 'System',
                'column' => 'system',
                'type' => 'checkbox.bool',
                'actions' => ['index'],
            ],
            'meta' => [
                'name' => 'Meta Tags',
                'column' => 'meta',
                'type' => 'json',
                'nullable' => true,
                'actions' => ['edit'],
            ],
            'search' => [
                'name' => 'Search Index',
                'column' => 'search',
                'type' => 'index',
                'nullable' => true,
            ],
            'created' => [
                'name' => 'Created',
                'column' => 'created',
                'sort_order' => 10000,
                'type' => 'datetime',
            ],
            'creator' => [
                'name' => 'Creator',
                'column' => 'creator',
                'sort_order' => 10010,
                'type' => 'select.int',
                'nullable' => true,
                'options_entity' => 'user',
            ],
            'modified' => [
                'name' => 'Modified',
                'column' => 'modified',
                'sort_order' => 10020,
                'type' => 'datetime',
            ],
            'modifier' => [
                'name' => 'Modifier',
                'column' => 'modifier',
                'sort_order' => 10030,
                'type' => 'select.int',
                'nullable' => true,
                'options_entity' => 'user',
            ],
        ],
    ],
    'eav' => [
        'id' => 'eav',
        'name' => 'EAV',
        'attributes' => [
            'id' => [
                'name' => 'ID',
                'column' => 'id',
                'auto' => true,
                'type' => 'int',
                'actions' => ['index'],
            ],
            'entity_id' => [
                'name' => 'Entity',
                'column' => 'entity_id',
                'type' => 'select.varchar',
                'required' => true,
                'options_entity' => 'entity',
                'actions' => ['edit', 'index'],
            ],
            'attribute_id' => [
                'name' => 'Attribute',
                'column' => 'attribute_id',
                'type' => 'select.varchar',
                'required' => true,
                'options_entity' => 'attribute',
                'actions' => ['edit', 'index'],
            ],
            'content_id' => [
                'name' => 'Content',
                'column' => 'content_id',
                'type' => 'select.int',
                'required' => true,
                'options_entity' => 'content',
                'actions' => ['edit', 'index'],
            ],
            'value_bool' => [
                'name' => 'Value Boolean',
                'column' => 'value_bool',
                'type' => 'checkbox.bool',
                'nullable' => true,
                'actions' => ['edit'],
            ],
            'value_datetime' => [
                'name' => 'Value Datetime',
                'column' => 'value_datetime',
                'nullable' => true,
                'type' => 'datetime',
                'actions' => ['edit'],
            ],
            'value_decimal' => [
                'name' => 'Value Decimal',
                'column' => 'value_decimal',
                'type' => 'decimal',
                'nullable' => true,
                'actions' => ['edit'],
            ],
            'value_int' => [
                'name' => 'Value Int',
                'column' => 'value_int',
                'type' => 'int',
                'nullable' => true,
                'actions' => ['edit'],
            ],
            'value_text' => [
                'name' => 'Value Text',
                'column' => 'value_text',
                'type' => 'textarea',
                'nullable' => true,
                'actions' => ['edit'],
            ],
            'value_varchar' => [
                'name' => 'Value Varchar',
                'column' => 'value_varchar',
                'type' => 'text',
                'nullable' => true,
                'actions' => ['edit'],
            ],
        ],
    ],
    'entity' => [
        'id' => 'entity',
        'name' => 'Entity',
        'actions' => ['create', 'edit', 'delete', 'index'],
        'toolbar' => 'types',
        'sort_order' => 100,
        'attributes' => [
            'id' => [
                'name' => 'ID',
                'column' => 'id',
                'type' => 'text',
                'required' => true,
                'unambiguous' => true,
                'actions' => ['index'],
                'generator' => 'qnd\generator_id',
                'generator_base' => 'name',
            ],
            'name' => [
                'name' => 'Name',
                'column' => 'name',
                'type' => 'text',
                'required' => true,
                'actions' => ['all'],
            ],
            'actions' => [
                'name' => 'Actions',
                'column' => 'actions',
                'type' => 'multiselect',
                'nullable' => true,
                'options_callback' => 'qnd\config',
                'options_callback_param' => ['action.entity'],
                'actions' => ['edit', 'index'],
            ],
            'toolbar' => [
                'name' => 'Toolbar',
                'column' => 'toolbar',
                'type' => 'select.varchar',
                'required' => true,
                'options_callback' => 'qnd\data',
                'options_callback_param' => ['toolbar'],
                'actions' => ['edit'],
            ],
            'sort_order' => [
                'name' => 'Order',
                'column' => 'sort_order',
                'type' => 'int',
                'actions' => ['edit'],
            ],
            'system' => [
                'name' => 'System',
                'column' => 'system',
                'type' => 'checkbox.bool',
                'actions' => ['index'],
            ],
        ],
    ],
    'menu' => [
        'id' => 'menu',
        'name' => 'Menu',
        'type' => 'nestedset',
        'actions' => ['create', 'edit', 'delete', 'index'],
        'toolbar' => 'structure',
        'sort_order' => 200,
        'attributes' => [
            'id' => [
                'name' => 'ID',
                'column' => 'id',
                'auto' => true,
                'type' => 'int',
                'actions' => ['index'],
            ],
            'name' => [
                'name' => 'Name',
                'column' => 'name',
                'type' => 'text',
                'required' => true,
                'actions' => ['all'],
            ],
            'target' => [
                'name' => 'Target',
                'column' => 'target',
                'type' => 'text',
                'required' => true,
                'actions' => ['edit', 'index'],
            ],
            'root_id' => [
                'name' => 'Menu Tree',
                'column' => 'root_id',
                'type' => 'select.varchar',
                'required' => true,
                'options_entity' => 'menu_root',
                'actions' => ['index'],
            ],
        ],
    ],
    'menu_root' => [
        'id' => 'menu_root',
        'name' => 'Menu Root',
        'actions' => ['create', 'edit', 'delete', 'index'],
        'toolbar' => 'structure',
        'sort_order' => 100,
        'attributes' => [
            'id' => [
                'name' => 'ID',
                'column' => 'id',
                'auto' => true,
                'type' => 'int',
                'actions' => ['index'],
            ],
            'name' => [
                'name' => 'Name',
                'column' => 'name',
                'type' => 'text',
                'actions' => ['all'],
                'required' => true,
            ],
            'system' => [
                'name' => 'System',
                'column' => 'system',
                'type' => 'checkbox.bool',
                'actions' => ['index'],
            ],
        ],
    ],
    'meta' => [
        'id' => 'meta',
        'name' => 'Metadata',
        'actions' => ['create', 'edit', 'delete', 'index'],
        'toolbar' => 'types',
        'sort_order' => 300,
        'attributes' => [
            'id' => [
                'name' => 'ID',
                'column' => 'id',
                'auto' => true,
                'type' => 'int',
                'actions' => ['index'],
            ],
            'entity_id' => [
                'name' => 'Entity',
                'column' => 'entity_id',
                'type' => 'select.varchar',
                'required' => true,
                'options_entity' => 'entity',
                'actions' => ['edit', 'index'],
            ],
            'attribute_id' => [
                'name' => 'Attribute',
                'column' => 'attribute_id',
                'type' => 'select.varchar',
                'required' => true,
                'options_entity' => 'attribute',
                'actions' => ['edit', 'index'],
            ],
            'sort_order' => [
                'name' => 'Order',
                'column' => 'sort_order',
                'type' => 'int',
                'actions' => ['edit', 'index'],
            ],
            'required' => [
                'name' => 'Required',
                'column' => 'required',
                'type' => 'checkbox.bool',
                'actions' => ['edit'],
            ],
            'unambiguous' => [
                'name' => 'Unambiguous',
                'column' => 'unambiguous',
                'type' => 'checkbox.bool',
                'actions' => ['edit'],
            ],
            'searchable' => [
                'name' => 'Searchable',
                'column' => 'searchable',
                'type' => 'checkbox.bool',
                'actions' => ['edit'],
            ],
            'actions' => [
                'name' => 'Actions',
                'column' => 'actions',
                'type' => 'multiselect',
                'nullable' => true,
                'options_callback' => 'qnd\config',
                'options_callback_param' => ['action.attribute'],
                'actions' => ['edit', 'index'],
            ],
        ],
    ],
    'project' => [
        'id' => 'project',
        'name' => 'Project',
        'actions' => ['create', 'edit', 'delete',  'index'],
        'toolbar' => 'system',
        'sort_order' => 100,
        'attributes' => [
            'id' => [
                'name' => 'ID',
                'column' => 'id',
                'auto' => true,
                'type' => 'int',
                'actions' => ['index'],
            ],
            'name' => [
                'name' => 'Name',
                'column' => 'name',
                'type' => 'text',
                'required' => true,
                'actions' => ['all'],
            ],
            'host' => [
                'name' => 'Host',
                'column' => 'host',
                'type' => 'text',
                'nullable' => true,
                'unambiguous' => true,
                'actions' => ['edit', 'index'],
            ],
            'active' => [
                'name' => 'Active',
                'column' => 'active',
                'type' => 'checkbox.bool',
                'actions' => ['edit', 'index'],
            ],
            'system' => [
                'name' => 'System',
                'column' => 'system',
                'type' => 'checkbox.bool',
                'actions' => ['index'],
            ],
        ],
    ],
    'rewrite' => [
        'id' => 'rewrite',
        'name' => 'Rewrite',
        'actions' => ['create', 'edit', 'delete', 'index'],
        'toolbar' => 'system',
        'sort_order' => 400,
        'attributes' => [
            'id' => [
                'name' => 'ID',
                'column' => 'id',
                'type' => 'text',
                'required' => true,
                'unambiguous' => true,
                'actions' => ['all'],
                'generator' => 'qnd\generator_id',
            ],
            'target' => [
                'name' => 'Target',
                'column' => 'target',
                'type' => 'text',
                'required' => true,
                'actions' => ['edit', 'index'],
            ],
            'redirect' => [
                'name' => 'Redirect',
                'column' => 'redirect',
                'type' => 'checkbox.bool',
                'actions' => ['edit', 'index'],
            ],
            'system' => [
                'name' => 'System',
                'column' => 'system',
                'type' => 'checkbox.bool',
                'actions' => ['index'],
            ],
        ],
    ],
    'role' => [
        'id' => 'role',
        'name' => 'Role',
        'actions' => ['create', 'edit', 'delete', 'index'],
        'toolbar' => 'system',
        'sort_order' => 300,
        'attributes' => [
            'id' => [
                'name' => 'ID',
                'column' => 'id',
                'auto' => true,
                'type' => 'int',
                'actions' => ['index'],
            ],
            'name' => [
                'name' => 'Name',
                'column' => 'name',
                'type' => 'text',
                'required' => true,
                'unambiguous' => true,
                'actions' => ['all'],
            ],
            'privilege' => [
                'name' => 'Privileges',
                'column' => 'privilege',
                'type' => 'multiselect',
                'nullable' => true,
                'options_callback' => 'qnd\privileges',
                'actions' => ['edit'],
            ],
            'active' => [
                'name' => 'Active',
                'column' => 'active',
                'type' => 'checkbox.bool',
                'actions' => ['edit', 'index'],
            ],
            'system' => [
                'name' => 'System',
                'column' => 'system',
                'type' => 'checkbox.bool',
                'actions' => ['index'],
            ],
        ],
    ],
    'user' => [
        'id' => 'user',
        'name' => 'User',
        'actions' => ['create', 'edit', 'delete',  'index'],
        'toolbar' => 'system',
        'sort_order' => 200,
        'attributes' => [
            'id' => [
                'name' => 'ID',
                'column' => 'id',
                'auto' => true,
                'type' => 'int',
                'actions' => ['index'],
            ],
            'name' => [
                'name' => 'Name',
                'column' => 'name',
                'type' => 'text',
                'required' => true,
                'actions' => ['all'],
            ],
            'email' => [
                'name' => 'E-Mail',
                'column' => 'email',
                'type' => 'email',
                'required' => true,
                'unambiguous' => true,
                'actions' => ['all'],
            ],
            'password' => [
                'name' => 'Password',
                'column' => 'password',
                'type' => 'password',
                'required' => true,
                'actions' => ['edit'],
            ],
            'role_id' => [
                'name' => 'Role',
                'column' => 'role_id',
                'type' => 'select.int',
                'required' => true,
                'options_entity' => 'role',
                'actions' => ['edit', 'index'],
            ],
            'active' => [
                'name' => 'Active',
                'column' => 'active',
                'type' => 'checkbox.bool',
                'actions' => ['edit', 'index'],
            ],
            'system' => [
                'name' => 'System',
                'column' => 'system',
                'type' => 'checkbox.bool',
                'actions' => ['index'],
            ],
        ],
    ],
];
