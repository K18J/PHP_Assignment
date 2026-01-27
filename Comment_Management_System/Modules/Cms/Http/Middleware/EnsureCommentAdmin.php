<?php

namespace Modules\Cms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCommentAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_admin) {
            return response()->json([
                'message' => 'You are not authorized to moderate comments.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

