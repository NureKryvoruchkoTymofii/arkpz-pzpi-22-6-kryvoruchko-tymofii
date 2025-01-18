<?php
use App\Controllers\SensorController;

$router->get('/sensors/{parameter}', function($parameter) {
    if (is_numeric($parameter)) {
        (new SensorController())->show($parameter);
    } else {
        (new SensorController())->getByType($parameter);
    }
});

$router->get('/sensors', function() {
    (new SensorController())->index();
});

$router->delete('/sensors/{id}', function($SensorID) {
    (new SensorController())->delete($SensorID);
});

$router->post('/sensors', function() {
    (new SensorController())->store();
});

$router->put('/sensors/{id}', function($id) {
    (new SensorController())->update($id);
});

?>
