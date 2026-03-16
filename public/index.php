<?php

declare(strict_types=1);

use App\Controllers\ActivityController;
use App\Controllers\AppointmentController;
use App\Controllers\AuthController;
use App\Controllers\ClientController;
use App\Controllers\ConfigController;
use App\Controllers\DashboardController;
use App\Controllers\QuoteController;
use App\Controllers\SaleController;
use App\Controllers\UserController;
use App\Core\Auth;
use App\Core\Router;

session_start();

require __DIR__ . '/../app/Core/helpers.php';

date_default_timezone_set((string) config('app.timezone', 'Europe/Rome'));

if ((bool) config('app.debug', false)) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

$GLOBALS['_flash_messages'] = $_SESSION['_flash_next'] ?? [];
unset($_SESSION['_flash_next']);
$GLOBALS['_old_input'] = $_SESSION['_old_next'] ?? [];
unset($_SESSION['_old_next']);

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/../app/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($path)) {
        require $path;
    }
});

$router = new Router();

$router->get('/', static function (): never {
    if (Auth::check()) {
        header('Location: ' . base_url('dashboard'));
    } else {
        header('Location: ' . base_url('login'));
    }
    exit;
});

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout'], ['auth' => true]);

$router->get('/dashboard', [DashboardController::class, 'index'], ['auth' => true]);

$router->get('/clients', [ClientController::class, 'index'], ['auth' => true]);
$router->get('/clients/create', [ClientController::class, 'create'], ['auth' => true]);
$router->post('/clients', [ClientController::class, 'store'], ['auth' => true]);
$router->get('/clients/{id}', [ClientController::class, 'show'], ['auth' => true]);
$router->get('/clients/{id}/edit', [ClientController::class, 'edit'], ['auth' => true]);
$router->post('/clients/{id}/update', [ClientController::class, 'update'], ['auth' => true]);
$router->post('/clients/{id}/delete', [ClientController::class, 'delete'], ['auth' => true]);

$router->get('/activities', [ActivityController::class, 'index'], ['auth' => true]);
$router->get('/activities/create', [ActivityController::class, 'create'], ['auth' => true]);
$router->post('/activities', [ActivityController::class, 'store'], ['auth' => true]);
$router->get('/activities/{id}/edit', [ActivityController::class, 'edit'], ['auth' => true]);
$router->post('/activities/{id}/update', [ActivityController::class, 'update'], ['auth' => true]);
$router->post('/activities/{id}/delete', [ActivityController::class, 'delete'], ['auth' => true]);

$router->get('/appointments', [AppointmentController::class, 'index'], ['auth' => true]);
$router->get('/appointments/create', [AppointmentController::class, 'create'], ['auth' => true]);
$router->post('/appointments', [AppointmentController::class, 'store'], ['auth' => true]);
$router->get('/appointments/{id}/edit', [AppointmentController::class, 'edit'], ['auth' => true]);
$router->post('/appointments/{id}/update', [AppointmentController::class, 'update'], ['auth' => true]);
$router->post('/appointments/{id}/delete', [AppointmentController::class, 'delete'], ['auth' => true]);

$router->get('/quotes', [QuoteController::class, 'index'], ['auth' => true]);
$router->get('/quotes/create', [QuoteController::class, 'create'], ['auth' => true]);
$router->post('/quotes', [QuoteController::class, 'store'], ['auth' => true]);
$router->get('/quotes/{id}', [QuoteController::class, 'show'], ['auth' => true]);
$router->get('/quotes/{id}/pdf', [QuoteController::class, 'downloadPdf'], ['auth' => true]);
$router->post('/quotes/{id}/status', [QuoteController::class, 'updateStatus'], ['auth' => true]);

$router->get('/sales', [SaleController::class, 'index'], ['auth' => true]);
$router->get('/sales/create', [SaleController::class, 'create'], ['auth' => true]);
$router->post('/sales', [SaleController::class, 'store'], ['auth' => true]);

$router->get('/config/pipeline', [ConfigController::class, 'pipeline'], ['auth' => true, 'role' => 'manager']);
$router->post('/config/pipeline', [ConfigController::class, 'storePipeline'], ['auth' => true, 'role' => 'manager']);
$router->post('/config/pipeline/{id}/update', [ConfigController::class, 'updatePipeline'], ['auth' => true, 'role' => 'manager']);
$router->post('/config/pipeline/{id}/delete', [ConfigController::class, 'deletePipeline'], ['auth' => true, 'role' => 'manager']);

$router->get('/config/categories', [ConfigController::class, 'categories'], ['auth' => true, 'role' => 'manager']);
$router->post('/config/categories', [ConfigController::class, 'storeCategory'], ['auth' => true, 'role' => 'manager']);
$router->post('/config/categories/{id}/update', [ConfigController::class, 'updateCategory'], ['auth' => true, 'role' => 'manager']);
$router->post('/config/categories/{id}/delete', [ConfigController::class, 'deleteCategory'], ['auth' => true, 'role' => 'manager']);

$router->get('/config/targets', [ConfigController::class, 'targets'], ['auth' => true, 'role' => 'manager']);
$router->post('/config/targets', [ConfigController::class, 'storeTargets'], ['auth' => true, 'role' => 'manager']);

$router->get('/users', [UserController::class, 'index'], ['auth' => true, 'role' => 'manager']);
$router->post('/users', [UserController::class, 'store'], ['auth' => true, 'role' => 'manager']);

$requestMethod = request_method();
$uri = current_path();

$router->dispatch($requestMethod, $uri);
