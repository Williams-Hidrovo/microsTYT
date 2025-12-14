<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="OrderService API",
 *     description="API para gestión de clientes y pedidos con autenticación JWT",
 *     @OA\Contact(
 *         email="admin@orderservice.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8001/api",
 *     description="Servidor de desarrollo"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="JWT Authorization header usando el esquema Bearer. Ejemplo: 'Authorization: Bearer {token}'"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
