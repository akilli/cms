<?php
return [
    /** @uses event\data_account() */
    'data:account' => ['event\data_account' => 100],
    /** @uses event\data_app() */
    'data:app' => ['event\data_app' => 100],
    /** @uses event\data_layout() */
    'data:layout' => ['event\data_layout' => 100],
    /** @uses event\data_request() */
    'data:request' => ['event\data_request' => 100],
    /** @uses event\entity_file_postdelete() */
    'entity:postdelete:id:file' => ['event\entity_file_postdelete' => 100],
    /** @uses event\entity_file_postsave() */
    'entity:postsave:id:file' => ['event\entity_file_postsave' => 100],
    /**
     * @uses event\entity_postvalidate_password()
     * @uses event\entity_postvalidate_unique()
     */
    'entity:postvalidate' => ['event\entity_postvalidate_password' => 100, 'event\entity_postvalidate_unique' => 200],
    /**
     * @uses event\entity_page_postvalidate_menu()
     * @uses event\entity_page_postvalidate_url()
     */
    'entity:postvalidate:id:page' => [
        'event\entity_page_postvalidate_menu' => 100,
        'event\entity_page_postvalidate_url' => 200,
    ],
    /** @uses event\entity_role_predelete() */
    'entity:predelete:id:role' => ['event\entity_role_predelete' => 100],
    /** @uses event\entity_iframe_presave() */
    'entity:presave:id:iframe' => ['event\entity_iframe_presave' => 100],
    /** @uses event\entity_page_presave() */
    'entity:presave:id:page' => ['event\entity_page_presave' => 100],
    /** @uses event\entity_file_prevalidate() */
    'entity:prevalidate:id:file' => ['event\entity_file_prevalidate' => 100],
    /** @uses event\entity_iframe_prevalidate() */
    'entity:prevalidate:id:iframe' => ['event\entity_iframe_prevalidate' => 100],
    /** @uses event\layout_postrender() */
    'layout:postrender' => ['event\layout_postrender' => 100],
    /** @uses event\layout_postrender_body() */
    'layout:postrender:id:body' => ['event\layout_postrender_body' => 100],
    /** @uses event\layout_postrender_html() */
    'layout:postrender:id:html' => ['event\layout_postrender_html' => 100],
    /** @uses event\response_html() */
    'response:html' => ['event\response_html' => 100],
    /** @uses event\response_html_account_logout() */
    'response:html:account:logout' => ['event\response_html_account_logout' => 100],
    /** @uses event\response_html_block_api() */
    'response:html:block:api' => ['event\response_html_block_api' => 100],
    /** @uses event\response_html_delete() */
    'response:html:delete' => ['event\response_html_delete' => 100],
    /** @uses event\response_json() */
    'response:json' => ['event\response_json' => 100],
];
