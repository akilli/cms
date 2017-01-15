<?php
return [
    'html' => '<address><h1><h2><h3><h4><h5><h6>'
        . '<blockquote><dd><div><dl><dt><figcaption><figure><hr><li><ol><p><pre><ul>'
        . '<a><abbr><b><bdi><bdo><br><cite><code><data><dfn><em><i><kbd><mark><q><rp><rt><ruby><s><samp><small><span>'
        . '<strong><sub><sup><time><u><var><wbr>'
        . '<del><ins>'
        . '<area><audio><embed><img><map><object><param><picture><source><track><video>'
        . '<caption><col><colgroup><table><tbody><td><tfoot><th><thead><tr>'
        . '<details><summary>',
    'uid' => [
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
        '#[^0-9a-z_-]+#i' => '-',
        '#[-]+#i' => '-',
    ],
];
