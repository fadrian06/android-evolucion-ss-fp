<?php

declare(strict_types=1);

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\Login;
use App\Http\Controllers\Logout;
use App\Http\Controllers\PaySale;
use App\Http\Controllers\PayRepair;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Register;
use App\Http\Controllers\RepairController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SelectBusiness;
use App\Http\Controllers\ShowDashboardPage;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\EnsureBusinessSelected;
use App\Http\Middleware\RedirectIfAuthenticated;

Flight::group('', static function (): void {
  Flight::route('GET /ingresar', static function (): void {
    Flight::render('login', [], 'slot');
    Flight::render('components/layout');
  });

  Flight::route('POST /ingresar', [Login::class, '__invoke']);

  Flight::route('GET /registrarse', static function (): void {
    Flight::render('register', [], 'slot');
    Flight::render('components/layout');
  });

  Flight::route('POST /registrarse', [Register::class, '__invoke']);
}, [RedirectIfAuthenticated::class]);

Flight::group('', static function (): void {
  Flight::route('GET /cerrar-sesion', [Logout::class, '__invoke']);
  Flight::route('GET /negocios/@id/seleccionar', [SelectBusiness::class, '__invoke']);
  Flight::route('POST /negocios', [BusinessController::class, 'store']);
  Flight::route('POST /negocios/@id', [BusinessController::class, 'update']);
  Flight::route('GET /negocios/@id/eliminar', [BusinessController::class, 'destroy']);

  Flight::group('', static function (): void {
    Flight::route('GET /negocios', [BusinessController::class, 'index']);
    Flight::route('GET /', [ShowDashboardPage::class, '__invoke']);
    Flight::route('GET /calculadora', [CalculatorController::class, 'index']);
    Flight::route('POST /calculadora/cotizacion', [CalculatorController::class, 'store']);
    Flight::route('GET /productos', [ProductController::class, 'index']);
    Flight::route('POST /productos', [ProductController::class, 'store']);
    Flight::route('POST /productos/@id', [ProductController::class, 'update']);
    Flight::route('GET /productos/@id/eliminar', [ProductController::class, 'destroy']);
    Flight::route('GET /clientes', [ClientController::class, 'index']);
    Flight::route('POST /clientes', [ClientController::class, 'store']);
    Flight::route('POST /clientes/@id', [ClientController::class, 'update']);
    Flight::route('GET /clientes/@id/eliminar', [ClientController::class, 'destroy']);
    Flight::route('GET /ventas', [SaleController::class, 'index']);
    Flight::route('POST /ventas', [SaleController::class, 'store']);
    Flight::route('GET /ventas/@id', [SaleController::class, 'show']);
    Flight::route('POST /ventas/@id/pagar', [PaySale::class, '__invoke']);
    Flight::route('GET /reparaciones', [RepairController::class, 'index']);
    Flight::route('POST /reparaciones', [RepairController::class, 'store']);
    Flight::route('GET /reparaciones/@id', [RepairController::class, 'show']);
    Flight::route('POST /reparaciones/@id/pagar', [PayRepair::class, '__invoke']);
  }, [EnsureBusinessSelected::class]);
}, [Authenticate::class]);
