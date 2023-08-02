<?php
declare(strict_types=1);

namespace event;

return [
    'data:account' => [
        'app:data_account' => [
            'call' => data_account(...),
        ],
    ],
    'data:app' => [
        'app:data_app' => [
            'call' => data_app(...),
        ],
    ],
    'data:layout' => [
        'app:data_layout' => [
            'call' => data_layout(...),
        ],
    ],
    'data:request' => [
        'app:data_request' => [
            'call' => data_request(...),
        ],
    ],
    'entity:postdelete' => [
        'app:entity_postdelete_uploadable' => [
            'call' => entity_postdelete_uploadable(...),
        ],
    ],
    'entity:postsave' => [
        'app:entity_postsave_uploadable' => [
            'call' => entity_postsave_uploadable(...),
        ],
    ],
    'entity:postvalidate' => [
        'app:entity_postvalidate_password' => [
            'call' => entity_postvalidate_password(...),
        ],
        'app:entity_postvalidate_unique' => [
            'call' => entity_postvalidate_unique(...),
            'sort' => 10,
        ],
    ],
    'entity:postvalidate:menu' => [
        'app:entity_menu_postvalidate' => [
            'call' => entity_menu_postvalidate(...),
        ],
    ],
    'entity:predelete:role' => [
        'app:entity_role_predelete' => [
            'call' => entity_role_predelete(...),
        ],
    ],
    'entity:presave:file' => [
        'app:entity_file_presave' => [
            'call' => entity_file_presave(...),
        ],
    ],
    'entity:prevalidate' => [
        'app:entity_prevalidate_uploadable' => [
            'call' => entity_prevalidate_uploadable(...),
        ],
    ],
    'entity:prevalidate:account' => [
        'app:entity_prevalidate_uid' => [
            'call' => entity_prevalidate_uid(...),
        ],
    ],
    'entity:prevalidate:page' => [
        'app:entity_prevalidate_url' => [
            'call' => entity_prevalidate_url(...),
        ],
    ],
    'layout:postrender' => [
        'app:layout_postrender' => [
            'call' => layout_postrender(...),
        ],
    ],
    'response' => [
        'app:response' => [
            'call' => response(...),
        ],
    ],
    'response:account:logout' => [
        'app:response_account_logout' => [
            'call' => response_account_logout(...),
        ],
    ],
    'response:delete' => [
        'app:response_delete' => [
            'call' => response_delete(...),
        ],
    ],
];
