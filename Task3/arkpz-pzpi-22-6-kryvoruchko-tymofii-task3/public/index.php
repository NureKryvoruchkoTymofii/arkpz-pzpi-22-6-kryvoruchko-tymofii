
<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Bramus\Router\Router;

$router = new Router();


// Включаємо файли маршрутів
require_once __DIR__ . '/../routes/users.php';
require_once __DIR__ . '/../routes/locks.php';
require_once __DIR__ . '/../routes/sensors.php';
require_once __DIR__ . '/../routes/sensorData.php';
require_once __DIR__ . '/../routes/accessLog.php';
require_once __DIR__ . '/../routes/lockUsageLogs.php';
require_once __DIR__ . '/../routes/sensorsSimulator.php';

$router->run();
?>
