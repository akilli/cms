<?php
return [
    '_all_' => ['name' => 'ALL PRIVILEGES'],
    '_guest_' => ['auto' => true],
    '_user_' => ['auto' => true],
    'account:delete' => ['delegate' => '_all_'],
    'account:edit' => ['delegate' => '_all_'],
    'account:index' => ['delegate' => '_all_'],
    'account:login' => ['delegate' => '_guest_'],
    'account:logout' => ['delegate' => '_user_'],
    'account:profile' => ['delegate' => '_user_'],
    'block:api' => ['delegate' => 'block:index'],
    'contentpage:view' => ['active' => false],
    'page:view' => ['active' => false],
    'role:delete' => ['delegate' => '_all_'],
    'role:edit' => ['delegate' => '_all_'],
    'role:index' => ['delegate' => '_all_'],
];
