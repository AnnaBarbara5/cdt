<?php

declare(strict_types=1);

use App\Http\PublicConfigHandler;

require __DIR__ . '/src/bootstrap.php';

(new PublicConfigHandler())->handle();