<?php
return [
    'action.attr' => [
        'all' => 'all',
        'edit' => 'edit',
        'view' => 'view',
        'index' => 'index',
        'list' => 'list',
    ],
    'action.entity' => [
        'all' => 'all',
        'create' => 'create',
        'edit' => 'edit',
        'delete' => 'delete',
        'view' => 'view',
        'index' => 'index',
        'list' => 'list',
        'import' => 'import',
        'export' => 'export',
    ],
    'entity.limit' => 20,
    'entity.pager' => 5,
    'ext.file' => [
        'csv' => 'csv',
        'doc' => 'doc',
        'docx' => 'docx',
        'flv' => 'flv',
        'gz' => 'gz',
        'html' => 'html',
        'ini' => 'ini',
        'json' => 'json',
        'odt' => 'odt',
        'pdf' => 'pdf',
        'rar' => 'rar',
        'tar' => 'tar',
        'xml' => 'xml',
        'zip' => 'zip',
    ],
    'ext.audio' => [
        'mp3' => 'mp3',
        'oga' => 'oga',
        'ogg' => 'ogg',
        'weba' => 'weba',
    ],
    'ext.embed' => [
        'avi' => 'avi',
        'mov' => 'mov',
        'mpg' => 'mpg',
        'ra' => 'ra',
        'rm' => 'rm',
        'swf' => 'swf',
        'wav' => 'wav',
        'wma' => 'wma',
        'wmv' => 'wmv',
    ],
    'ext.image' => [
        'gif' => 'gif',
        'jpe' => 'jpe',
        'jpeg' => 'jpeg',
        'jpg' => 'jpg',
        'png' => 'png',
        'svg' => 'svg',
        'webp' => 'webp',
    ],
    'ext.video' => [
        'mp4' => 'mp4',
        'ogv' => 'ogv',
        'ogg' => 'ogg',
        'webm' => 'webm',
    ],
    'filter.html' => '<address><article><aside><footer><h1><h2><h3><h4><h5><h6><header><nav><section>'
        . '<blockquote><dd><div><dl><dt><figcaption><figure><hr><li><ol><p><pre><ul>'
        . '<a><abbr><b><bdi><bdo><br><cite><code><dfn><em><i><kbd><mark><q><rp><rt><ruby><s><samp><small>'
        . '<span><strong><sub><sup><time><u><var><wbr><del><ins>'
        . '<area><audio><canvas><embed><iframe><img><map><object><param><source><track><video>'
        . '<caption><col><colgroup><table><tbody><tfoot><thead><td><th><tr>',
    'filter.id' => [
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
    'i18n.charset' => 'utf-8',
    'i18n.date' => 'd.m.Y',
    'i18n.datetime' => 'd.m.Y H:i',
    'i18n.lang' => 'de',
    'i18n.locale' => 'de-DE',
    'i18n.time' => 'H:i',
    'i18n.timezone' => 'Europe/Berlin',
    'import.end' => '<!-- IMPORT_END -->',
    'import.start' => '<!-- IMPORT_START -->',
    'import.toc' => 'import.csv',
];
