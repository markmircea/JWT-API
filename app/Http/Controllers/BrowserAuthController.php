<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Illuminate\Support\Facades\Log;


class BrowserAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'refresh']]);
    }

    public function login(Request $request): JsonResponse //validate creds, attempt auth, generate access/refresh token cookie
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    try {
    if (!$token = auth('api')->attempt($credentials)) {
        Log::channel('security')->warning('Failed browser login attempt', [   //logs to security.log
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $user = auth('api')->user();
    $refreshToken = $this->generateRefreshToken($user);

    return $this->respondWithRefreshTokenCookie($token, $refreshToken);

    } catch (\Exception $e) {
        return response()->json(['error' => 'An error occurred during login'], 500);
    }
}

    public function logout():JsonResponse  //logs user out removing access token and refresh cookie token
    {
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out'])
            ->withCookie(Cookie::forget('refresh_token'));
    }

    public function refresh(Request $request): JsonResponse // refresh access token w/ refresh roken and invalidates and refreshes refresh token
    {
        $refreshToken = $request->cookie('refresh_token');
        if (!$refreshToken) {
            return response()->json(['error' => 'Refresh token not found'], 400);
        }

        try {
            JWTAuth::setToken($refreshToken);
            $user = JWTAuth::authenticate();  //auth w refresh token
            if (!$user) {
                throw new TokenInvalidException();
            }

            JWTAuth::invalidate(); // invalidate refresh token

            $newAccessToken = JWTAuth::fromUser($user);  //get access token
            $newRefreshToken = $this->generateRefreshToken($user); //get refresh token

            return $this->respondWithTokenAndCookie($newAccessToken, $newRefreshToken); //return both
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token is invalid'], 401);
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not refresh token'], 500);
        }
    }

    protected function generateRefreshToken($user){                                 //helper method to generate refresh token
        return JWTAuth::customClaims(['refresh' => true])->fromUser($user);

    }

    protected function respondWithRefreshTokenCookie($refreshToken): JsonResponse // respond with just refresh token for login
    {

        $rttl = Config::get('jwt.refresh_ttl');

        return response()->json()
            ->withCookie(cookie(
                'refresh_token',
                $refreshToken,
                $rttl,
                null, //path
                null, //domain
                true, // https
                true, // httponly
                false, // raw flag for encoding
                'strict' //samesite = strict
            ));
    }

    protected function respondWithTokenAndCookie($accessToken, $refreshToken): JsonResponse // respond with cookie and access token json
    {
        $ttl = Config::get('jwt.ttl');
        $rttl = Config::get('jwt.refresh_ttl');

        return response()->json([
            'access_token' => $accessToken,
            'token_type' => 'bearer',
            'expires_in' => $ttl * 60  //exp in seconds,  ttl in min  60min *60sec 3600sec
        ])->withCookie(cookie(
            'refresh_token',
            $refreshToken,
            $rttl,
            null, //path
            null, //domain
            true, // https
            true, // httponly
            false, // raw flag for encoding
            'strict' //samesite = strict
        ));
    }



}
