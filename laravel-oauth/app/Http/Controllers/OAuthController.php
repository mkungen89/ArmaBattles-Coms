<?php

namespace App\Http\Controllers;

use App\Models\OAuthAccessToken;
use App\Models\OAuthAuthorizationCode;
use App\Models\OAuthClient;
use App\Models\OAuthRefreshToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OAuthController extends Controller
{
    /**
     * Show the OAuth authorization page
     * GET /oauth/authorize
     */
    public function authorize(Request $request): View|JsonResponse
    {
        $request->validate([
            'client_id' => 'required|string',
            'redirect_uri' => 'required|url',
            'response_type' => 'required|in:code',
            'scope' => 'nullable|string',
            'state' => 'nullable|string',
        ]);

        // Find client
        $client = OAuthClient::find($request->client_id);

        if (!$client || $client->revoked) {
            return response()->json([
                'error' => 'invalid_client',
                'error_description' => 'Client not found or has been revoked',
            ], 400);
        }

        // Validate redirect URI
        if (!$client->validateRedirectUri($request->redirect_uri)) {
            return response()->json([
                'error' => 'invalid_request',
                'error_description' => 'Invalid redirect_uri',
            ], 400);
        }

        // If user is not logged in, redirect to login
        if (!Auth::check()) {
            session([
                'oauth_request' => $request->all(),
                'url.intended' => $request->fullUrl(),
            ]);
            return redirect()->route('login');
        }

        // Show authorization page
        return view('oauth.authorize', [
            'client' => $client,
            'scopes' => $request->scope ? explode(' ', $request->scope) : ['profile', 'email'],
            'redirect_uri' => $request->redirect_uri,
            'state' => $request->state,
        ]);
    }

    /**
     * Handle authorization approval/denial
     * POST /oauth/authorize
     */
    public function approveAuthorization(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'client_id' => 'required|string',
            'redirect_uri' => 'required|url',
            'scope' => 'nullable|string',
            'state' => 'nullable|string',
            'approve' => 'required|boolean',
        ]);

        $client = OAuthClient::find($request->client_id);

        if (!$client || $client->revoked) {
            return redirect($request->redirect_uri . '?' . http_build_query([
                'error' => 'invalid_client',
                'error_description' => 'Client not found or revoked',
                'state' => $request->state,
            ]));
        }

        // User denied authorization
        if (!$request->approve) {
            return redirect($request->redirect_uri . '?' . http_build_query([
                'error' => 'access_denied',
                'error_description' => 'User denied the authorization request',
                'state' => $request->state,
            ]));
        }

        // Create authorization code
        $code = OAuthAuthorizationCode::create([
            'id' => Str::uuid(),
            'user_id' => Auth::id(),
            'client_id' => $client->id,
            'scopes' => $request->scope ? explode(' ', $request->scope) : ['profile', 'email'],
            'code' => Str::random(64),
            'redirect_uri' => $request->redirect_uri,
            'expires_at' => now()->addMinutes(10), // Code expires in 10 minutes
            'revoked' => false,
        ]);

        // Redirect back to client with authorization code
        return redirect($request->redirect_uri . '?' . http_build_query([
            'code' => $code->code,
            'state' => $request->state,
        ]));
    }

    /**
     * Exchange authorization code for access token
     * POST /oauth/token
     */
    public function issueToken(Request $request): JsonResponse
    {
        $request->validate([
            'grant_type' => 'required|in:authorization_code,refresh_token',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
        ]);

        // Find and validate client
        $client = OAuthClient::find($request->client_id);

        if (!$client || $client->revoked) {
            return response()->json([
                'error' => 'invalid_client',
                'error_description' => 'Client authentication failed',
            ], 401);
        }

        if (!$client->validateSecret($request->client_secret)) {
            return response()->json([
                'error' => 'invalid_client',
                'error_description' => 'Invalid client credentials',
            ], 401);
        }

        // Handle different grant types
        if ($request->grant_type === 'authorization_code') {
            return $this->issueTokenFromAuthorizationCode($request, $client);
        } elseif ($request->grant_type === 'refresh_token') {
            return $this->issueTokenFromRefreshToken($request, $client);
        }

        return response()->json([
            'error' => 'unsupported_grant_type',
            'error_description' => 'The grant type is not supported',
        ], 400);
    }

    /**
     * Issue token from authorization code
     */
    protected function issueTokenFromAuthorizationCode(Request $request, OAuthClient $client): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'redirect_uri' => 'required|url',
        ]);

        // Find authorization code
        $authCode = OAuthAuthorizationCode::where('code', $request->code)
            ->where('client_id', $client->id)
            ->first();

        if (!$authCode || !$authCode->isValid()) {
            return response()->json([
                'error' => 'invalid_grant',
                'error_description' => 'Authorization code is invalid or expired',
            ], 400);
        }

        // Validate redirect URI matches
        if ($authCode->redirect_uri !== $request->redirect_uri) {
            return response()->json([
                'error' => 'invalid_grant',
                'error_description' => 'Redirect URI mismatch',
            ], 400);
        }

        // Revoke the authorization code (one-time use)
        $authCode->update(['revoked' => true]);

        // Create access token
        $accessToken = OAuthAccessToken::create([
            'id' => Str::uuid(),
            'user_id' => $authCode->user_id,
            'client_id' => $client->id,
            'scopes' => $authCode->scopes,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(30), // Token expires in 30 days
            'revoked' => false,
        ]);

        // Create refresh token
        $refreshToken = OAuthRefreshToken::create([
            'id' => Str::uuid(),
            'access_token_id' => $accessToken->id,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(90), // Refresh token expires in 90 days
            'revoked' => false,
        ]);

        return response()->json([
            'access_token' => $accessToken->token,
            'token_type' => 'Bearer',
            'expires_in' => 2592000, // 30 days in seconds
            'refresh_token' => $refreshToken->token,
            'scope' => implode(' ', $accessToken->scopes ?? []),
        ]);
    }

    /**
     * Issue new access token from refresh token
     */
    protected function issueTokenFromRefreshToken(Request $request, OAuthClient $client): JsonResponse
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        // Find refresh token
        $refreshToken = OAuthRefreshToken::where('token', $request->refresh_token)->first();

        if (!$refreshToken || !$refreshToken->isValid()) {
            return response()->json([
                'error' => 'invalid_grant',
                'error_description' => 'Refresh token is invalid or expired',
            ], 400);
        }

        // Get old access token
        $oldAccessToken = $refreshToken->accessToken;

        // Validate client matches
        if ($oldAccessToken->client_id !== $client->id) {
            return response()->json([
                'error' => 'invalid_grant',
                'error_description' => 'Client mismatch',
            ], 400);
        }

        // Revoke old tokens
        $oldAccessToken->update(['revoked' => true]);
        $refreshToken->update(['revoked' => true]);

        // Create new access token
        $accessToken = OAuthAccessToken::create([
            'id' => Str::uuid(),
            'user_id' => $oldAccessToken->user_id,
            'client_id' => $client->id,
            'scopes' => $oldAccessToken->scopes,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(30),
            'revoked' => false,
        ]);

        // Create new refresh token
        $newRefreshToken = OAuthRefreshToken::create([
            'id' => Str::uuid(),
            'access_token_id' => $accessToken->id,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(90),
            'revoked' => false,
        ]);

        return response()->json([
            'access_token' => $accessToken->token,
            'token_type' => 'Bearer',
            'expires_in' => 2592000,
            'refresh_token' => $newRefreshToken->token,
            'scope' => implode(' ', $accessToken->scopes ?? []),
        ]);
    }

    /**
     * Get authenticated user info
     * GET /oauth/user
     */
    public function userInfo(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => 'invalid_request',
                'error_description' => 'No access token provided',
            ], 401);
        }

        // Find access token
        $accessToken = OAuthAccessToken::where('token', $token)->first();

        if (!$accessToken || !$accessToken->isValid()) {
            return response()->json([
                'error' => 'invalid_token',
                'error_description' => 'Access token is invalid or expired',
            ], 401);
        }

        $user = $accessToken->user;

        // Return user info based on scopes
        $scopes = $accessToken->scopes ?? ['profile', 'email'];
        $userInfo = [];

        if (in_array('profile', $scopes)) {
            $userInfo['id'] = $user->id;
            $userInfo['name'] = $user->name;
            $userInfo['username'] = $user->username ?? $user->name;
        }

        if (in_array('email', $scopes)) {
            $userInfo['email'] = $user->email;
            $userInfo['email_verified'] = $user->email_verified_at !== null;
        }

        // Add avatar if available
        if (isset($user->avatar)) {
            $userInfo['avatar'] = $user->avatar;
        } elseif (isset($user->profile_photo_url)) {
            $userInfo['avatar'] = $user->profile_photo_url;
        }

        return response()->json($userInfo);
    }

    /**
     * Revoke access token
     * POST /oauth/revoke
     */
    public function revokeToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'token_type_hint' => 'nullable|in:access_token,refresh_token',
        ]);

        $tokenType = $request->token_type_hint ?? 'access_token';

        if ($tokenType === 'access_token') {
            $token = OAuthAccessToken::where('token', $request->token)->first();
        } else {
            $token = OAuthRefreshToken::where('token', $request->token)->first();
        }

        if ($token) {
            $token->update(['revoked' => true]);
        }

        return response()->json(['message' => 'Token revoked successfully']);
    }
}
