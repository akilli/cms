<?php
return [
    'cfg.block' => [
        'cfg\listener_block' => 10,
    ],
    'cfg.entity' => [
        'cfg\listener_entity' => 10,
    ],
    'cfg.i18n' => [
        'cfg\listener_i18n' => 10,
    ],
    'cfg.opt' => [
        'cfg\listener_opt' => 10,
    ],
    'cfg.priv' => [
        'cfg\listener_priv' => 10,
    ],
    'cfg.toolbar' => [
        'cfg\listener_toolbar' => 10,
    ],
    'layout' => [
        'layout\listener_data' => 10,
    ],
    'layout.postrender.type.root' => [
        'layout\listener_postrender_root' => 10,
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
