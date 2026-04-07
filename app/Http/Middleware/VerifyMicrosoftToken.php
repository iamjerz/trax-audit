<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Cache;

class VerifyMicrosoftToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token missing'], 401);
        }

        try {
            // ✅ Cache Microsoft public keys (faster)
            $jwks = Cache::remember('ms_jwks', 3600, function () {
                return json_decode(file_get_contents(
                    'https://login.microsoftonline.com/common/discovery/v2.0/keys'
                ), true);
            });

            // Fix alg issue
            foreach ($jwks['keys'] as &$key) {
                $key['alg'] = 'RS256';
            }

            $keys = JWK::parseKeySet($jwks);

            // Decode token
            $decoded = JWT::decode($token, $keys);
            $user = (array) $decoded;

            // ✅ Validate audience
            if ($user['aud'] !== env('MICROSOFT_CLIENT_ID')) {
                return response()->json(['error' => 'Invalid audience'], 401);
            }

            // ✅ Attach user to request (important)
            $request->attributes->set('user', $user);

        } catch (ExpiredException $e) {
            return response()->json(['error' => 'Token expired'], 401);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid token',
                'message' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}