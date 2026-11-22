<?php

declare(strict_types=1);

use App\Http\PainelHandler;

require __DIR__ . '/src/bootstrap.php';

PainelHandler::createDefault()->handle();