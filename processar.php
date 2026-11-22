<?php

declare(strict_types=1);

use App\Http\CadastroApiHandler;

require __DIR__ . '/src/bootstrap.php';

CadastroApiHandler::createDefault()->handle();