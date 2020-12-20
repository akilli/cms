<?php
return [
    'home' => ['name' => 'Homepage', 'url' => '/', 'sort' => 100],
    'page' => ['sort' => 200],
    'layout' => ['sort' => 300],
    'block' => ['sort' => 400],
    'file' => ['sort' => 500],
    'role' => ['sort' => 600],
    'account' => ['sort' => 700],
    'profile' => ['name' => 'Profile', 'privilege' => 'account:profile', 'url' => '/account/profile', 'sort' => 800],
    'logout' => ['name' => 'Logout', 'privilege' => 'account:logout', 'url' => '/account/logout', 'sort' => 900],
];
