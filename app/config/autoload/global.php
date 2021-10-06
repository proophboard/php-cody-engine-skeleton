<?php
declare(strict_types = 1);

namespace App\Config;

return [
    'environment' => getenv('PROOPH_ENV')?: 'prod',
    'event_engine' => [
        'cached_config_file' => 'data/cache/ee.cache.php',
        'cache_enabled' => true,
    ],
    'pdo' => [
        'dsn' => getenv('PDO_DSN'),
        'user' => getenv('PDO_USER'),
        'pwd' => getenv('PDO_PWD'),
    ],
];
