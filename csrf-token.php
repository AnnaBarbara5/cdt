<?php

declare(strict_types=1);

use App\Http\CsrfTokenHandler;

require __DIR__ . '/src/bootstrap.php';

(new CsrfTokenHandler())->handle();