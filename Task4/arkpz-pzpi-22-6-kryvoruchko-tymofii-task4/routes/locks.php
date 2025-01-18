<?php
use App\Controllers\LockController;
use App\Middleware\AuthMiddleware;

$router->get('/locks/{parameter}', function($parameter) {
    if (is_numeric($parameter)) {
        (new LockController())->show($parameter);
    } else {
        (new LockController())->getByStatus($parameter);
    }
});


$router->get('/locks', function() {
    (new LockController())->index();
});


$router->delete('/locks/{id}', function($id) {
    (new LockController())->delete($id);
});

$router->post('/locks', function() {
    (new LockController())->store();
});

$router->put('/locks/{id}', function($id) {
    (new LockController())->update($id);
});

$router->post('/lock/unlock', function() {
    (new AuthMiddleware())->handle($_SERVER['REQUEST_URI'], function() {
        (new LockController())->unlock();
    });
});

$router->post('/lock/lock', function() {
    (new AuthMiddleware())->handle($_SERVER['REQUEST_URI'], function() {
        (new LockController())->lock();
    });
});

$router->post('/lock/status', function() {
    (new AuthMiddleware())->handle($_SERVER['REQUEST_URI'], function() {
        (new LockController())->checkStatus();
    });
});

$router->post('/lock/autoLock', function() { 
    (new AuthMiddleware())->handle($_SERVER['REQUEST_URI'], function() { 
        (new LockController())->autoLock(); 
    }); 
}); 

$router->post('/lock/updateAutoLockTime', function() { 
    (new AuthMiddleware())->handle($_SERVER['REQUEST_URI'], function() { 
        (new LockController())->updateAutoLockTime(); 
    }); 
});

$router->post('/lock/userReport', function() { 
    (new AuthMiddleware())->handle($_SERVER['REQUEST_URI'], function() { 
        (new LockController())->userReport(); 
    }); 
});

$router->post('/lock/sensorDataReport', function() { 
    (new AuthMiddleware())->handle($_SERVER['REQUEST_URI'], function() { 
        (new LockController())->sensorDataReport(); 
    }); 
});

$router->get('/lock/sensorDataReport', function() { 
    (new AuthMiddleware())->handle($_SERVER['REQUEST_URI'], function() { 
        (new LockController())->sensorDataReport(); 
    }); 
});

?>
