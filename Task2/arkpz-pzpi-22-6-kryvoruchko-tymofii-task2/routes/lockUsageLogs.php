<?php

use App\Controllers\LockUsageLogController;
use App\Middleware\AuthMiddleware;
use App\Controllers\SensorDataController;


$router->post('/sensor/{id}/analysis', function ($id) use ($router) {
    $startDate = $_GET['startDate'] ?? date('Y-m-d', strtotime('-7 days'));
    $endDate = $_GET['endDate'] ?? date('Y-m-d');

    (new AuthMiddleware())->handle($_SERVER['REQUEST_URI'], function() use ($id, $startDate, $endDate) {
        $controller = new SensorDataController();
        $controller->sensorDataAnalysis($id, $startDate, $endDate);
    });
});

$router->get('/lock/{id}/activityReport', function($id) { 
    (new AuthMiddleware())->handle($_SERVER['REQUEST_URI'], function() use ($id) { 
        (new LockUsageLogController())->lockActivityReport($id); 
    }); }
);