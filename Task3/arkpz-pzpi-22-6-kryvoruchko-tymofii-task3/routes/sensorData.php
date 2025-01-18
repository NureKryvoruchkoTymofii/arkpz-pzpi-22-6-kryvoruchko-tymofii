<?php
use App\Controllers\SensorDataController;
use App\Middleware\AuthMiddleware;

$router->get('/sensordata', function() {
    (new SensorDataController())->index();
});

$router->post('/sensordata', function() {
    (new SensorDataController())->store();
});

$router->get('/sensordata/sensor/{id}', function($SensorID) {
    (new SensorDataController())->getBySensorID($SensorID);
});

$router->delete('/sensordata/{dataId}', function($dataId) {
    (new SensorDataController())->delete($dataId);
});

$router->put('/sensordata/{dataId}', function($dataId) {
    (new SensorDataController())->update($dataId);
});

$router->get('/sensor/{id}/analysis', function ($id) use ($router) {
    $startDate = $_GET['startDate'] ?? date('Y-m-d', strtotime('-7 days'));
    $endDate = $_GET['endDate'] ?? date('Y-m-d');

    $controller = new SensorDataController();
    $controller->sensorDataAnalysis($id, $startDate, $endDate);
});

$router->get('/exportData', function() { 
    (new AuthMiddleware())->handle($_SERVER['REQUEST_URI'], function() { 
        (new SensorDataController())->exportData(); 
    });
});

$router->post('/importData', function() {
    (new AuthMiddleware())->handle($_SERVER['REQUEST_URI'], function() {
        (new SensorDataController())->importData();
    });
});

?>
