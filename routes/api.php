<?php

declare(strict_types=1);

use App\Http\Controllers\BcvExchangeRateController;
use Faslatam\PsrFramework\Router;

Flight::group('/api', static function (): void {
  Flight::route('GET /bcv/exchange-rate', [BcvExchangeRateController::class, '__invoke']);
});

$router = new Router;

return $router;
