<?php
return [
    'data:account' => [
        'event\data_account' => 100,
    ],
    'data:app' => [
        'event\data_app' => 100,
    ],
    'data:layout' => [
        'event\data_layout' => 100,
    ],
    'data:request' => [
        'event\data_request' => 100,
    ],
    'entity:postdelete' => [
        'event\entity_postdelete_uploadable' => 100,
    ],
    'entity:postsave' => [
        'event\entity_postsave_uploadable' => 100,
    ],
    'entity:postvalidate' => [
        'event\entity_postvalidate_password' => 100,
        'event\entity_postvalidate_unique' => 200,
    ],
    'entity:postvalidate:id:menu' => [
        'event\entity_menu_postvalidate' => 100,
    ],
    'entity:predelete:id:role' => [
        'event\entity_role_predelete' => 100,
    ],
    'entity:presave:id:iframe' => [
        'event\entity_iframe_presave' => 100,
    ],
    'entity:prevalidate' => [
        'event\entity_prevalidate_uploadable' => 100,
    ],
    'entity:prevalidate:id:account' => [
        'event\entity_account_prevalidate' => 100,
    ],
    'entity:prevalidate:id:file' => [
        'event\entity_file_prevalidate' => 100,
    ],
    'entity:prevalidate:id:iframe' => [
        'event\entity_iframe_prevalidate' => 100,
    ],
    'layout:postrender' => [
        'event\layout_postrender' => 100,
    ],
    'layout:postrender:id:body' => [
        'event\layout_postrender_body' => 100,
    ],
    'layout:postrender:id:html' => [
        'event\layout_postrender_html' => 100,
    ],
    'response:html' => [
        'event\response_html' => 100,
    ],
    'response:html:account:logout' => [
        'event\response_html_account_logout' => 100,
    ],
    'response:html:block:api' => [
        'event\response_html_block_api' => 100,
    ],
    'response:html:delete' => [
        'event\response_html_delete' => 100,
    ],
    'response:json' => [
        'event\response_json' => 100,
    ],
];
