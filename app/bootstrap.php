<?php
declare(strict_types=1);

require_once __DIR__ . '/Core/ErrorHandler.php';
require_once __DIR__ . '/Core/Logger.php';
require_once __DIR__ . '/Core/Response.php';
require_once __DIR__ . '/Core/Security.php';

ErrorHandler::register();
Security::headers();

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Expire idle sessions so an unattended browser does not stay signed in forever.
const SESSION_IDLE_SECONDS = 1800;
if (!empty($_SESSION['user'])) {
    $lastActivity = (int) ($_SESSION['last_activity'] ?? 0);
    if ($lastActivity > 0 && time() - $lastActivity > SESSION_IDLE_SECONDS) {
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['flash'][] = ['type' => 'error', 'message' => 'Your session expired. Please sign in again.'];
    } else {
        $_SESSION['last_activity'] = time();
    }
}

require_once __DIR__ . '/Core/Database.php';
require_once __DIR__ . '/Core/helpers.php';
require_once __DIR__ . '/Models/User.php';
require_once __DIR__ . '/Models/Vendor.php';
require_once __DIR__ . '/Models/Party.php';
require_once __DIR__ . '/Models/Booking.php';
require_once __DIR__ . '/Controllers/HomeController.php';
require_once __DIR__ . '/Controllers/AuthController.php';
require_once __DIR__ . '/Controllers/PartyController.php';
require_once __DIR__ . '/Controllers/VendorController.php';
require_once __DIR__ . '/Controllers/BookingController.php';
require_once __DIR__ . '/Controllers/ProfileController.php';
require_once __DIR__ . '/Controllers/UserController.php';
require_once __DIR__ . '/Controllers/ApiController.php';

// Refresh the session identity so role/name changes and deleted accounts take effect immediately.
if (!empty($_SESSION['user'])) {
    $sessionUser = User::find((int) $_SESSION['user']['user_id']);
    if (!$sessionUser) {
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['flash'][] = ['type' => 'error', 'message' => 'Your account is no longer available. Please sign in again.'];
    } else {
        $_SESSION['user'] = $sessionUser;
    }
}
