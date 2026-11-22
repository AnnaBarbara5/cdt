<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

putenv('APP_DEBUG=true');
$_ENV['APP_DEBUG'] = 'true';

putenv('DB_DRIVER=sqlite');
$_ENV['DB_DRIVER'] = 'sqlite';

putenv('DB_DATABASE=:memory:');
$_ENV['DB_DATABASE'] = ':memory:';

$adminHash = password_hash('admin-test', PASSWORD_DEFAULT);
putenv('ADMIN_PASSWORD_HASH=' . $adminHash);
$_ENV['ADMIN_PASSWORD_HASH'] = $adminHash;