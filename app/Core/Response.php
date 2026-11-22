<?php
declare(strict_types=1);

final class Response
{
    private const REASONS = [
        201 => 'Created',
        303 => 'See Other',
        400 => 'Bad Request',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        419 => 'Authentication Timeout',
        422 => 'Unprocessable Content',
        500 => 'Internal Server Error',
    ];

    public static function status(int $code): void
    {
        if (isset(self::REASONS[$code])) {
            header(sprintf('HTTP/1.1 %d %s', $code, self::REASONS[$code]));
            return;
        }
        http_response_code($code);
    }

    public static function json(array $payload, int $status = 200): void
    {
        self::status($status);
        header('Content-Type: application/json; charset=utf-8');
        $json = json_encode($payload, JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) {
            throw new RuntimeException('JSON response encoding failed.');
        }
        echo $json;
    }
}
