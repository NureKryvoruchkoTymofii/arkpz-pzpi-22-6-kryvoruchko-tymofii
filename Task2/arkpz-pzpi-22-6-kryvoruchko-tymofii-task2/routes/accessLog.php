<?php

use App\Controllers\AccessLogController;
use App\Middleware\AuthMiddleware;

$router->post('/lock/access', function() {
    (new AuthMiddleware())->handle($_SERVER['REQUEST_URI'], function() {
        (new AccessLogController())->addAccess();
    });
});
