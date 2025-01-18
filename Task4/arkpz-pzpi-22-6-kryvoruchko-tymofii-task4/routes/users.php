<?php
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;

global $router;

$router->get('/users/{parameter}', function($parameter) {
    if (is_numeric($parameter)) {
        (new UserController())->show($parameter);
    } else {
        (new UserController())->getByRole($parameter);
    }
});

$router->get('/users', function() { 
    (new UserController())->index();  
});

$router->delete('/users', function() {
    (new UserController())->index();
});

$router->delete('/users/{id:[0-9]+}', function($UserID) {
    (new UserController())->destroy($UserID);
});

$router->post('/users', function() {
    (new UserController())->store();
});

$router->put('/users/{id}', function($UserID) {
    (new UserController())->update($UserID);
});

$router->get('/user/lock/{userId}', function($UserID) {
    (new UserController())->getLocksByUser($UserID);
});

$router->post('/register', function() { 
    (new UserController())->register(); 
}); 

$router->post('/login', function() { 
    (new UserController())->login(); 
});

$router->post('/user/updateProfile', function() {
    (new AuthMiddleware())->handle($_SERVER['REQUEST_URI'], function() {
        (new UserController())->updateProfile(); 
    });
});

$router->get('/summaryReport', function() {
    (new UserController())->getSummaryReport();
});

?>
