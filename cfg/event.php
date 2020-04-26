<?php
return [
    'data.account' => [
        'event\data_account' => 100,
    ],
    'data.app' => [
        'event\data_app' => 100,
    ],
    'data.layout' => [
        'event\data_layout' => 100,
    ],
    'data.request' => [
        'event\data_request' => 100,
    ],
    'entity.postvalidate' => [
        'event\entity_postvalidate_password' => 100,
        'event\entity_postvalidate_unique' => 200,
    ],
    'entity.prevalidate.id.file' => [
        'event\entity_prevalidate_file' => 100,
    ],
    'entity.postsave.id.file' => [
        'event\entity_postsave_file' => 100,
    ],
    'entity.postdelete.id.file' => [
        'event\entity_postdelete_file' => 100,
    ],
    'entity.postvalidate.id.page' => [
        'event\entity_postvalidate_page_menu' => 100,
        'event\entity_postvalidate_page_url' => 200,
    ],
    'entity.presave.id.page' => [
        'event\entity_presave_page' => 100,
    ],
    'entity.predelete.id.role' => [
        'event\entity_predelete_role' => 100,
    ],
    'layout.postrender' => [
        'event\layout_postrender' => 100,
    ],
    'response' => [
        'event\response' => 100,
    ],
    'response.account.logout' => [
        'event\response_account_logout' => 100,
    ],
    'response.block.api' => [
        'event\response_block_api' => 100,
    ],
    'response.delete' => [
        'event\response_delete' => 100,
    ],
];
