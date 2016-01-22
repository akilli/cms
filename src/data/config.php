<?php
return [
    // action
    'action.attribute' => [
        'all' => 'all',
        'edit' => 'edit',
        'view' => 'view',
        'index' => 'index',
        'list' => 'list',
        'block' => 'block',
    ],
    'action.entity' => [
        'all' => 'all',
        'create' => 'create',
        'edit' => 'edit',
        'delete' => 'delete',
        'view' => 'view',
        'index' => 'index',
        'list' => 'list',
    ],
    // file
    'file.audio' => ['mp3', 'oga', 'ogg', 'weba'],
    'file.embed' => ['avi', 'mov', 'mpg', 'ra', 'rm', 'swf', 'wav', 'wma', 'wmv'],
    'file.image' => ['gif', 'jpe', 'jpeg', 'jpg', 'png', 'svg', 'webp'],
    'file.misc' => ['doc', 'docx', 'flv', 'gz', 'odt', 'pdf', 'rar', 'tar', 'zip'],
    'file.video' => ['mp4', 'ogv', 'ogg', 'webm'],
    // filter
    'filter.html' => '<address><article><aside><footer><h1><h2><h3><h4><h5><h6><header><nav><section>'
        . '<blockquote><dd><div><dl><dt><figcaption><figure><hr><li><ol><p><pre><ul>'
        . '<a><abbr><b><bdi><bdo><br><cite><code><dfn><em><i><kbd><mark><q><rp><rt><ruby><s><samp><small>'
        . '<span><strong><sub><sup><time><u><var><wbr><del><ins>'
        . '<area><audio><canvas><embed><iframe><img><map><object><param><source><track><video>'
        . '<caption><col><colgroup><table><tbody><tfoot><thead><td><th><tr>',
    'filter.identifier' => [
        '#ä|á|à|â|å|ã#i' => 'a',
        '#é|è|ê|ë#i' => 'e',
        '#í|ì|î|ï#i' =>  'i',
        '#ö|ó|ò|ô|õ|ð|ø#i' => 'o',
        '#ü|ú|ù|û#i' =>  'u',
        '#æ#i' =>  'ae',
        '#ß#i' => 'ss',
        '#ç#i' => 'c',
        '#ñ#i' => 'n',
        '#ý#i' => 'y',
        '#[^0-9a-z\/_-]+#i' => '-',
        '#[-]+#i' => '-',
    ],
    // i18n
    'i18n.charset' => 'utf-8',
    'i18n.locale' => 'de-DE',
    'i18n.timezone' => 'Europe/Berlin',
    'i18n.date_format' => 'd.m.Y',
    'i18n.datetime_format' => 'd.m.Y H:i',
    // limit
    'limit.block' => 5,
    'limit.index' => 20,
    'limit.list' => 10,
    // meta
    'meta.title' => 'Akilli CMS',
    'meta.separator' => '|',
    'meta' => [
        'keywords' => 'Akilli CMS',
        'description' => "Akilli CMS Quick'n'Dirty",
        'viewport' => 'width=device-width, initial-scale=1, maximum-scale=1',
    ],
    // url
    'url.asset' => '',
    'url.base' => '',
];
