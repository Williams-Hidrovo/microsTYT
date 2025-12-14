<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class JwtAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $this->extractToken($request);

        if (!$token) {
            return response()->json([
                'error' => 'Token no proporcionado',
                'message' => 'Se requiere un token de autenticación en el header Authorization'
            ], 401);
        }

        try {
            $decoded = JWT::decode(
                $token,
                new Key(config('jwt.secret'), config('jwt.algorithm'))
            );

            if ($decoded->iss !== config('jwt.issuer')) {
                return response()->json([
                    'error' => 'Token inválido',
                    'message' => 'El emisor del token no es válido'
                ], 401);
            }

            if ($decoded->aud !== config('jwt.audience')) {
                return response()->json([
                    'error' => 'Token inválido',
                    'message' => 'La audiencia del token no es válida'
                ], 401);
            }

            $request->attributes->add([
                'user_id' => $decoded->sub,
                'user_email' => $decoded->email ?? null,
                'user_name' => $decoded->name ?? null,
            ]);

        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json([
                'error' => 'Token expirado',
                'message' => 'El token de autenticación ha expirado'
            ], 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return response()->json([
                'error' => 'Token inválido',
                'message' => 'La firma del token no es válida'
            ], 401);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Token inválido',
                'message' => 'No se pudo validar el token: ' . $e->getMessage()
            ], 401);
        }

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader) {
            return null;
        }

        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
