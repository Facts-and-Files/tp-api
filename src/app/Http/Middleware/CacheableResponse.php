<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class CacheableResponse
{
    public function handle(Request $request, Closure $next, int|string $ttl = '1h'): JsonResponse
    {
        $seconds = match($ttl) {
            '5m' => 300,
            '30m' => 1800,
            '1h' => 3600,
            '2h' => 7200,
            '1d' => 86400,
            '1w' => 608400,
            '1m' => 2592000,
            default => (int) $ttl
        };

        $cacheKey = $this->generateCacheKey($request);
        $forceFresh = $request->boolean('fresh', false);

        if ($forceFresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $seconds, function () use ($next, $request) {
            return $next($request);
        });
    }

    private function generateCacheKey(Request $request): string
    {
        $keyData = [
            'path' => $request->path(),
            'params' => $request->except(['fresh']),
        ];

        return 'api_cache_' . md5(json_encode($keyData));
    }
}
