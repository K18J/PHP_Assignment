<?php

namespace Modules\Cms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SpamDetectionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $content = strtolower((string) $request->input('content', ''));
        $banned = ['viagra', 'casino', 'spam', 'buy now', 'free money'];

        foreach ($banned as $word) {
            if (str_contains($content, $word)) {
                return response()->json([
                    'message' => 'Comment flagged as spam.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        return $next($request);
    }
}

