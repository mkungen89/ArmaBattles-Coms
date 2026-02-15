<?php

namespace App\Http\Middleware;

use App\Models\OAuthAccessToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OAuthAuthenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => 'unauthorized',
                'error_description' => 'No access token provided',
            ], 401);
        }

        // Find and validate access token
        $accessToken = OAuthAccessToken::where('token', $token)
            ->with('user')
            ->first();

        if (!$accessToken || !$accessToken->isValid()) {
            return response()->json([
                'error' => 'invalid_token',
                'error_description' => 'Access token is invalid or expired',
            ], 401);
        }

        // Set authenticated user for this request
        auth()->setUser($accessToken->user);

        // Store token in request for later use
        $request->attributes->set('oauth_token', $accessToken);

        return $next($request);
    }
}
