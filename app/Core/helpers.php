<?php
declare(strict_types=1);

final class HttpException extends RuntimeException
{
    private int $statusCode;

    public function __construct(int $statusCode, string $message = '')
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}

function db(): PDO
{
    return Database::connection();
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function form_value(string $key, $default = ''): string
{
    if (array_key_exists($key, $_POST)) {
        return is_string($_POST[$key]) ? $_POST[$key] : '';
    }
    return is_scalar($default) || $default === null ? (string) $default : '';
}

function post_int(string $key): int
{
    $value = $_POST[$key] ?? null;
    if (!is_string($value) && !is_int($value)) {
        return 0;
    }
    $digits = (string) $value;
    if ($digits === '' || !ctype_digit($digits)) {
        return 0;
    }
    $normalized = ltrim($digits, '0');
    if ($normalized === '') {
        return 0;
    }
    if (strlen($normalized) > 10 || (strlen($normalized) === 10 && strcmp($normalized, '4294967295') > 0)) {
        return 0;
    }
    return (int) $normalized;
}

function form_int_value(string $key, int $default): int
{
    return array_key_exists($key, $_POST) ? post_int($key) : $default;
}

function query_string(string $key): string
{
    $value = $_GET[$key] ?? '';
    if (!is_string($value)) {
        throw new HttpException(400, 'Search values must be text.');
    }
    return trim($value);
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $submitted = $_POST['_csrf'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        throw new HttpException(419, 'Your form expired. Please go back and try again.');
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function establish_session(array $user): void
{
    session_regenerate_id(true);
    unset($user['password_hash']);
    $_SESSION['user'] = $user;
    $_SESSION['last_activity'] = time();
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        flash('error', 'Please sign in to continue.');
        redirect('login');
    }
    return $user;
}

function require_admin(): array
{
    $user = require_login();
    if ($user['role'] !== 'admin') {
        throw new HttpException(403, 'Only an administrator can access this page.');
    }
    return $user;
}

function redirect(string $route, array $query = []): void
{
    $url = 'index.php?r=' . rawurlencode($route);
    if ($query) {
        $url .= '&' . http_build_query($query);
    }
    Response::status(303);
    header('Location: ' . $url);
    exit;
}

function render(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $viewFile = __DIR__ . '/../Views/' . $view . '.php';
    if (!is_file($viewFile)) {
        throw new RuntimeException('View not found: ' . $view);
    }
    require __DIR__ . '/../Views/layout/header.php';
    require $viewFile;
    require __DIR__ . '/../Views/layout/footer.php';
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function post_string(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

function post_raw_string(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? $value : '';
}

function validate_email(string $email): bool
{
    return strlen($email) <= 190 && (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function whole_rwf_amount(string $amount): ?float
{
    if ($amount === '' || strlen($amount) > 16 || !ctype_digit($amount)) {
        return null;
    }

    $normalized = ltrim($amount, '0');
    if ($normalized === '') {
        return 0.0;
    }
    if (strlen($normalized) > 7 || (strlen($normalized) === 7 && strcmp($normalized, '9999999') > 0)) {
        return null;
    }
    return (float) $normalized;
}

function old_value(string $key, $default = ''): string
{
    return e(form_value($key, $default));
}

function valid_password(string $password): bool
{
    $length = strlen($password);
    return $length >= 8 && $length <= 72;
}

function money_rwf($amount): string
{
    return number_format((float) $amount, 0, '.', ',') . ' RWF';
}

function request_id(): int
{
    return filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
}
