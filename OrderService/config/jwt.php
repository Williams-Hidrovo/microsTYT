<?php

return [
    /*
    |--------------------------------------------------------------------------
    | JWT Secret
    |--------------------------------------------------------------------------
    |
    | La clave secreta debe ser idéntica a la que usa AuthService
    | para poder validar los tokens generados por ese microservicio.
    |
    */

    'secret' => env('JWT_SECRET', 'tu-clave-secreta-super-segura-de-al-menos-32-caracteres'),

    /*
    |--------------------------------------------------------------------------
    | JWT Issuer
    |--------------------------------------------------------------------------
    |
    | El emisor del token (debe coincidir con AuthService)
    |
    */

    'issuer' => env('JWT_ISSUER', 'AuthService'),

    /*
    |--------------------------------------------------------------------------
    | JWT Audience
    |--------------------------------------------------------------------------
    |
    | La audiencia del token (debe coincidir con AuthService)
    |
    */

    'audience' => env('JWT_AUDIENCE', 'AuthServiceClient'),

    /*
    |--------------------------------------------------------------------------
    | JWT Algoritmo
    |--------------------------------------------------------------------------
    |
    | El algoritmo utilizado para firmar el token
    |
    */

    'algorithm' => 'HS256',

];
