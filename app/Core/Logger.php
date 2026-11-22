<?php
declare(strict_types=1);

final class Logger
{
    public static function error(Throwable $exception, string $route, string $method): void
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'kids-birthday-logs';
        $context = [
            'type' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'route' => $route,
            'method' => $method,
        ];
        $json = json_encode($context, JSON_INVALID_UTF8_SUBSTITUTE);
        $line = sprintf(
            "%s\tERROR\t%s\t%s\n",
            date('c'),
            'Unhandled application error',
            $json === false ? '{}' : $json
        );

        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            error_log(rtrim($line));
            return;
        }
        if (@file_put_contents($directory . '/app.log', $line, FILE_APPEND | LOCK_EX) === false) {
            error_log(rtrim($line));
        }
    }
}
