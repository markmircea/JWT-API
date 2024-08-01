<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Illuminate\Support\Facades\Log;





class APIAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'refresh', 'blacklistRefreshToken']]);
    }



public function login(Request $request): JsonResponse //validate creds, attempt auth, generate access/refresh token cookie

{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (!$token = auth('api')->attempt($credentials)) {
        Log::channel('security')->warning('Failed API login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $user = auth('api')->user();
    $refreshToken = JWTAuth::customClaims(['refresh' => true])->fromUser($user);

    return $this->respondWithTokens($token, $refreshToken);
}





public function refresh(Request $request): JsonResponse //refreshes the access token using a valid refresh token
{

    $refreshToken = $request->input('refresh_token');
    if (!$refreshToken) {
        return response()->json(['error' => 'Refresh token not found'], 400);
    }

    try {
        JWTAuth::setToken($refreshToken);

        $user = JWTAuth::authenticate();
        if (!$user) {
            throw new TokenInvalidException();
        }

        JWTAuth::invalidate();

        $newAccessToken = JWTAuth::fromUser($user);    //generate new access token

        $newRefreshToken = JWTAuth::customClaims(['refresh' => true])->fromUser($user);  // generate new refresh token

        return $this->respondWithTokens($newAccessToken, $newRefreshToken);  // return response with new tokens
    } catch (TokenInvalidException $e) {
        return response()->json(['error' => 'Token is invalid'], 401);
    } catch (TokenExpiredException $e) {
        return response()->json(['error' => 'Token has expired'], 401);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Could not refresh token: ' . $e->getMessage()], 500);
    }
}


    protected function respondWithTokens($accessToken, $refreshToken): JsonResponse //formats the response for access and refresh tokens
    {
        $ttl = Config::get('jwt.ttl');
        $rttl = Config::get('jwt.refresh_ttl');


        return response()->json([
            'access_token' => $accessToken,
            'token_type' => 'bearer',
            'access_expires_in' => $ttl * 60, // exp in seconds, ttl in min  60min * 60sec = 3600sec
            'refresh_token' => $refreshToken,
            'refresh_expires_in' => $rttl * 60

        ]);
    }

    public function blacklistAccessToken (): JsonResponse // to invalidate access token only
    {
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }


    public function blacklistRefreshToken(Request $request): JsonResponse // to invalidate refresh token only
    {
        $refreshToken = $request->input('refresh_token');
        if (!$refreshToken) {
            return response()->json(['error' => 'Refresh token not provided.'], 400);
        }

        try {
            JWTAuth::setToken($refreshToken);
            JWTAuth::invalidate();
            return response()->json(['message' => 'Refresh token invalidated successfully'], 200);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid refresh token.'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not invalidate refresh token'], 500);
        }
    }
}
