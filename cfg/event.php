<?php
return [
    'data.account' => [
        'event\data_account' => 10,
    ],
    'data.app' => [
        'event\data_app' => 10,
    ],
    'layout' => [
        'event\layout' => 10,
    ],
    'layout.postrender' => [
        'event\layout_postrender' => 10,
    ],
    'layout.postrender.type.root' => [
        'event\layout_postrender_root' => 10,
    ],
    'entity.postvalidate' => [
        'event\entity_postvalidate' => 10,
    ],
    'entity.prevalidate.id.file' => [
        'event\entity_prevalidate_file' => 10,
    ],
    'entity.presave.id.page' => [
        'event\entity_presave_page' => 10,
    ],
    'entity.postsave.id.file' => [
        'event\entity_postsave_file' => 10,
    ],
    'entity.postdelete.id.file' => [
        'event\entity_postdelete_file' => 10,
    ],
    'entity.postvalidate.id.layout' => [
        'event\entity_postvalidate_layout' => 10,
    ],
    'entity.postvalidate.id.page' => [
        'event\entity_postvalidate_page_status' => 10,
        'event\entity_postvalidate_page_menu' => 20,
        'event\entity_postvalidate_page_url' => 30,
    ],
    'entity.load.id.page' => [
        'event\entity_load_page' => 10,
    ],
    'entity.predelete.id.role' => [
        'event\entity_predelete_role' => 10,
    ],
];
