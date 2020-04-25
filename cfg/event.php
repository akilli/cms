<?php
return [
    'data.account' => [
        'event\data_account' => 10,
    ],
    'data.app' => [
        'event\data_app' => 10,
    ],
    'data.layout' => [
        'event\data_layout' => 10,
    ],
    'data.request' => [
        'event\data_request' => 10,
    ],
    'entity.postvalidate' => [
        'event\entity_postvalidate_password' => 10,
        'event\entity_postvalidate_unique' => 20,
    ],
    'entity.prevalidate.id.file' => [
        'event\entity_prevalidate_file' => 10,
    ],
    'entity.postsave.id.file' => [
        'event\entity_postsave_file' => 10,
    ],
    'entity.postdelete.id.file' => [
        'event\entity_postdelete_file' => 10,
    ],
    'entity.postvalidate.id.page' => [
        'event\entity_postvalidate_page_menu' => 10,
        'event\entity_postvalidate_page_url' => 20,
    ],
    'entity.presave.id.page' => [
        'event\entity_presave_page' => 10,
    ],
    'entity.predelete.id.role' => [
        'event\entity_predelete_role' => 10,
    ],
    'layout.postrender' => [
        'event\layout_postrender' => 10,
    ],
    'response' => [
        'event\response' => 10,
    ],
    'response.account.logout' => [
        'event\response_account_logout' => 10,
    ],
    'response.block.api' => [
        'event\response_block_api' => 10,
    ],
    'response.delete' => [
        'event\response_delete' => 10,
    ],
];
