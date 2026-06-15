<?php 

namespace App\Services\ExternalApi;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class ExternalApiLogger
{
    public static function log(string $name, string $url, Response $response, array $context = []): void
    {
        Log::info("EXTERNAL_API_RESPONSE: {$name}", array_merge($context, [
            'url' => $url,
            'status' => $response->status(),
            'duration_ms' => $context['duration_ms'] ?? null,
            'rate_limit_remaining' => $response->header('X-RateLimit-Remaining'),
            'rate_limit_reset' => $response->header('X-RateLimit-Reset'),
            'body_snippet' => substr($response->body(), 0, 500),
        ]));
    }

    public static function error(string $name, array $context): void
    {
        Log::error("EXTERNAL_API_ERROR: {$name}", $context);
    }

    public static function rateLimited(string $name, array $context): void
    {
        Log::warning("EXTERNAL_API_RATE_LIMIT: {$name}", $context);
    }
}