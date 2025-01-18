<?php

$router->post('/simulate', function () {
    $sensorIds = [32, 36, 35];
    $controller = new \App\Controllers\SensorSimulatorController();
    $controller->simulateSelectedSensors($sensorIds);
});