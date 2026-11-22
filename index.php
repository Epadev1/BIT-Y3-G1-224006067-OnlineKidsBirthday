<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$routeInput = $_GET['r'] ?? 'home';
$route = is_string($routeInput) ? trim($routeInput, '/') : '';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

$routes = [
    'home' => ['GET', HomeController::class, 'index'],
    'register' => ['GET', AuthController::class, 'registerForm'],
    'auth/register' => ['POST', AuthController::class, 'register'],
    'login' => ['GET', AuthController::class, 'loginForm'],
    'auth/login' => ['POST', AuthController::class, 'login'],
    'logout' => ['POST', AuthController::class, 'logout'],
    'parties' => ['GET', PartyController::class, 'index'],
    'parties/view' => ['GET', PartyController::class, 'show'],
    'parties/create' => ['GET', PartyController::class, 'create'],
    'parties/store' => ['POST', PartyController::class, 'store'],
    'parties/edit' => ['GET', PartyController::class, 'edit'],
    'parties/update' => ['POST', PartyController::class, 'update'],
    'parties/delete' => ['POST', PartyController::class, 'delete'],
    'vendors' => ['GET', VendorController::class, 'index'],
    'vendors/manage' => ['GET', VendorController::class, 'manage'],
    'vendors/create' => ['GET', VendorController::class, 'create'],
    'vendors/store' => ['POST', VendorController::class, 'store'],
    'vendors/edit' => ['GET', VendorController::class, 'edit'],
    'vendors/update' => ['POST', VendorController::class, 'update'],
    'vendors/delete' => ['POST', VendorController::class, 'delete'],
    'bookings' => ['GET', BookingController::class, 'index'],
    'bookings/create' => ['GET', BookingController::class, 'create'],
    'bookings/store' => ['POST', BookingController::class, 'store'],
    'bookings/edit' => ['GET', BookingController::class, 'edit'],
    'bookings/update' => ['POST', BookingController::class, 'update'],
    'bookings/status' => ['POST', BookingController::class, 'status'],
    'bookings/delete' => ['POST', BookingController::class, 'delete'],
    'profile' => ['GET', ProfileController::class, 'edit'],
    'profile/update' => ['POST', ProfileController::class, 'update'],
    'users' => ['GET', UserController::class, 'index'],
    'users/create' => ['GET', UserController::class, 'create'],
    'users/store' => ['POST', UserController::class, 'store'],
    'users/edit' => ['GET', UserController::class, 'edit'],
    'users/update' => ['POST', UserController::class, 'update'],
    'users/delete' => ['POST', UserController::class, 'delete'],
    'api/vendors' => ['GET', ApiController::class, 'vendors'],
];

ob_start();
try {
    if (!isset($routes[$route])) {
        Response::status(404);
        render('errors/404', ['title' => 'Page not found']);
    } else {
        [$expectedMethod, $controller, $action] = $routes[$route];
        if ($method !== $expectedMethod) {
            Response::status(405);
            header('Allow: ' . $expectedMethod);
            render('errors/405', ['title' => 'Method not allowed']);
        } else {
            if ($method === 'POST') {
                verify_csrf();
            }
            $controller::$action();
        }
    }
} catch (HttpException $exception) {
    if (ob_get_level() > 0) {
        ob_clean();
    }
    Response::status($exception->getStatusCode());
    if (strpos($route, 'api/') === 0) {
        Response::json(['ok' => false, 'error' => $exception->getMessage()], $exception->getStatusCode());
    } else {
        render('errors/' . $exception->getStatusCode(), [
            'title' => $exception->getStatusCode() === 403 ? 'Access denied' : 'Request error',
            'message' => $exception->getMessage(),
        ]);
    }
} catch (Throwable $exception) {
    if (ob_get_level() > 0) {
        ob_clean();
    }
    Logger::error($exception, $route, $method);
    Response::status(500);
    if (strpos($route, 'api/') === 0) {
        Response::json(['ok' => false, 'error' => 'Server error'], 500);
    } else {
        render('errors/500', ['title' => 'Something went wrong']);
    }
}
