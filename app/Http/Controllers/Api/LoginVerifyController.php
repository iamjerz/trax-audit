<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\ExpiredException;
use Carbon\Carbon;


class LoginVerifyController extends Controller
{
    public function validateMicrosoftToken(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token missing'], 401);
        }

        try {
            // Get Microsoft keys
            $jwks = json_decode(file_get_contents(
                'https://login.microsoftonline.com/common/discovery/v2.0/keys'
            ), true);

            // 🔥 FIX: Add 'alg' manually
            foreach ($jwks['keys'] as &$key) {
                $key['alg'] = 'RS256';
            }

            $keys = JWK::parseKeySet($jwks);

            // Decode token
            $decoded = JWT::decode($token, $keys);

            $user = (array) $decoded;
            $exp = $user['exp'];
            $readable = Carbon::createFromTimestamp($exp)->setTimezone('Asia/Manila');
            $email = $user['upn'];
            $userip = $user['ipaddr'];
            // Validate audience
            if ($user['aud'] !== env('MICROSOFT_CLIENT_ID')) {
                return response()->json(['error' => 'Invalid audience'], 401);
            }

            // ✅ Resolve the local user (token has no session) and log the extension login
            $dbUser = DB::table('users')
                ->where('email', strtolower($email))
                ->first();

            $actorName = $dbUser
                ? trim(($dbUser->first_name ?? '') . ' ' . ($dbUser->last_name ?? ''))
                : $email;

            AuditTrail::record([
                'employeeid'     => $dbUser->employeeid ?? null,
                'actor_name'     => $actorName,
                'event'          => 'login',
                'description'    => $actorName . ' logged in on the Chrome extension',
                'auditable_type' => 'auth',
                'ip_address'     => $userip,   // IP from the Microsoft token (the user's address)
                'new_values'     => ['email' => $email, 'channel' => 'chrome_extension'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Token valid',
                'email' => $email,
                'expiration' => $readable->toDateTimeString(),
                'ipadress' => $userip,
                'user' => $user
            ]);

        }catch (ExpiredException $e) {
            return response()->json(['error' => 'Token expired'], 401);

        }catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid token',
                'message' => $e->getMessage()
            ], 401);
        }
    }
}
