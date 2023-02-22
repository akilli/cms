<?php
declare(strict_types=1);

namespace cli;

return [
    'app:init' => [
        'call' => app_init(...),
    ],
    'app:upgrade' => [
        'call' => app_upgrade(...),
    ],
];
