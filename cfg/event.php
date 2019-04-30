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
        'entity\listener_postvalidate' => 10,
    ],
    'entity.prevalidate.id.file' => [
        'entity\listener_prevalidate_file' => 10,
    ],
    'entity.presave.id.page' => [
        'entity\listener_presave_page' => 10,
    ],
    'entity.postsave.id.file' => [
        'entity\listener_postsave_file' => 10,
    ],
    'entity.postdelete.id.file' => [
        'entity\listener_postdelete_file' => 10,
    ],
    'entity.postvalidate.id.layout' => [
        'entity\listener_postvalidate_layout' => 10,
    ],
    'entity.postvalidate.id.page' => [
        'entity\listener_postvalidate_page_status' => 10,
        'entity\listener_postvalidate_page_menu' => 20,
        'entity\listener_postvalidate_page_url' => 30,
    ],
    'entity.load.id.page' => [
        'entity\listener_load_page' => 10,
    ],
    'entity.predelete.id.role' => [
        'entity\listener_predelete_role' => 10,
    ],
];
