<?php
declare(strict_types=1);

final class ApiController
{
    public static function vendors(): void
    {
        header('Cache-Control: no-store');
        $query = query_string('q');
        $service = query_string('service');
        if (strlen($query) > 80) {
            Response::json(['ok' => false, 'error' => 'Search must be 80 characters or fewer.'], 400);
            return;
        }
        if (!in_array($service, Vendor::SERVICE_TYPES, true)) {
            $service = '';
        }
        Response::json(['ok' => true, 'data' => ['vendors' => Vendor::all($query, $service)]]);
    }
}
