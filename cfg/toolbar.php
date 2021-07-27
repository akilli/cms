<?php
return [
    'home' => ['name' => 'Homepage', 'url' => '/', 'sort' => 100],
    'dashboard' => ['name' => 'Dashboard', 'privilege' => 'account:dashboard', 'url' => '/account/dashboard', 'sort' => 200],
    'page' => ['sort' => 300],
    'layout' => ['sort' => 400],
    'block' => ['sort' => 500],
    'file' => ['sort' => 600],
    'role' => ['sort' => 700],
    'account' => ['sort' => 800],
    'profile' => ['name' => 'Profile', 'privilege' => 'account:profile', 'url' => '/account/profile', 'sort' => 900],
    'logout' => ['name' => 'Logout', 'privilege' => 'account:logout', 'url' => '/account/logout', 'sort' => 1000],
];
