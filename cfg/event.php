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
    'response:html' => [
        'app:response_html' => [
            'call' => response_html(...),
        ],
    ],
    'response:html:account:logout' => [
        'app:response_html_account_logout' => [
            'call' => response_html_account_logout(...),
        ],
    ],
    'response:html:delete' => [
        'app:response_html_delete' => [
            'call' => response_html_delete(...),
        ],
    ],
    'response:json:create' => [
        'app:response_json_save' => [
            'call' => response_json_save(...),
        ],
    ],
    'response:json:delete' => [
        'app:response_json_delete' => [
            'call' => response_json_delete(...),
        ],
    ],
    'response:json:edit' => [
        'app:response_json_save' => [
            'call' => response_json_save(...),
        ],
    ],
    'response:json:index' => [
        'app:response_json_index' => [
            'call' => response_json_index(...),
        ],
    ],
    'response:json:view' => [
        'app:response_json_view' => [
            'call' => response_json_view(...),
        ],
    ],
];
