<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Controllers\UserController;
use Exception;

class AuthMiddleware {
    private $secretKey;

    public function __construct() {
        $this->secretKey = "your_secret_key";
    }

    public function handle($request, $next) {
        $headers = getallheaders();

        //error_log(print_r($headers, true));

        if (!isset($headers['Authorization'])) {
            jsonResponse(['error' => 'Authorization header is missing'], 401);
            return;
        }

        $jwt = str_replace("Bearer ", "", $headers['Authorization']);
        if (!$jwt) {
            jsonResponse(['error' => 'Token is missing'], 401);
            return;
        }

        try {
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));

            //error_log(print_r($decoded, true));

            $decoded_array = (array) $decoded;
            $_SESSION['user'] = $decoded_array;

            error_log(print_r($_SESSION['user'], true));
            
        } catch (Exception $e) {
            jsonResponse(['error' => 'Invalid or expired token'], 401);
            return;
        }

        return $next($request);
    }
}
