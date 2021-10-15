<?php
declare(strict_types=1);

namespace event;

return [
    'data:account' => [
        'data_account' => [
            'call' => data_account(...),
            'sort' => 100,
        ],
    ],
    'data:app' => [
        'data_app' => [
            'call' => data_app(...),
            'sort' => 100,
        ],
    ],
    'data:layout' => [
        'data_layout' => [
            'call' => data_layout(...),
            'sort' => 100,
        ],
    ],
    'data:request' => [
        'data_request' => [
            'call' => data_request(...),
            'sort' => 100,
        ],
    ],
    'entity:postdelete' => [
        'entity_postdelete_uploadable' => [
            'call' => entity_postdelete_uploadable(...),
            'sort' => 100,
        ],
    ],
    'entity:postsave' => [
        'entity_postsave_uploadable' => [
            'call' => entity_postsave_uploadable(...),
            'sort' => 100,
        ],
    ],
    'entity:postvalidate' => [
        'entity_postvalidate_password' => [
            'call' => entity_postvalidate_password(...),
            'sort' => 100,
        ],
        'entity_postvalidate_unique' => [
            'call' => entity_postvalidate_unique(...),
            'sort' => 200,
        ],
    ],
    'entity:postvalidate:id:menu' => [
        'entity_menu_postvalidate' => [
            'call' => entity_menu_postvalidate(...),
            'sort' => 100,
        ],
    ],
    'entity:predelete:id:role' => [
        'entity_role_predelete' => [
            'call' => entity_role_predelete(...),
            'sort' => 100,
        ],
    ],
    'entity:presave:id:file' => [
        'entity_file_presave' => [
            'call' => entity_file_presave(...),
            'sort' => 100,
        ],
    ],
    'entity:presave:id:iframe' => [
        'entity_iframe_presave' => [
            'call' => entity_iframe_presave(...),
            'sort' => 100,
        ],
    ],
    'entity:prevalidate' => [
        'entity_prevalidate_uploadable' => [
            'call' => entity_prevalidate_uploadable(...),
            'sort' => 100,
        ],
    ],
    'entity:prevalidate:id:account' => [
        'entity_prevalidate_uid' => [
            'call' => entity_prevalidate_uid(...),
            'sort' => 100,
        ],
    ],
    'entity:prevalidate:id:page' => [
        'entity_prevalidate_url' => [
            'call' => entity_prevalidate_url(...),
            'sort' => 100,
        ],
    ],
    'layout:postrender' => [
        'layout_postrender' => [
            'call' => layout_postrender(...),
            'sort' => 100,
        ],
    ],
    'response:html' => [
        'response_html' => [
            'call' => response_html(...),
            'sort' => 100,
        ],
    ],
    'response:html:account:logout' => [
        'response_html_account_logout' => [
            'call' => response_html_account_logout(...),
            'sort' => 100,
        ],
    ],
    'response:html:block:api' => [
        'response_html_block_api' => [
            'call' => response_html_block_api(...),
            'sort' => 100,
        ],
    ],
    'response:html:delete' => [
        'response_html_delete' => [
            'call' => response_html_delete(...),
            'sort' => 100,
        ],
    ],
    'response:json:delete' => [
        'response_json_delete' => [
            'call' => response_json_delete(...),
            'sort' => 100,
        ],
    ],
    'response:json:edit' => [
        'response_json_edit' => [
            'call' => response_json_edit(...),
            'sort' => 100,
        ],
    ],
    'response:json:index' => [
        'response_json_index' => [
            'call' => response_json_index(...),
            'sort' => 100,
        ],
    ],
    'response:json:view' => [
        'response_json_view' => [
            'call' => response_json_view(...),
            'sort' => 100,
        ],
    ],
];
