<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use App\Models\User;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'sql205.infinityfree.com',  // Змінено на новий хост
    'database'  => 'if0_38128540_lock_system',  // Змінено на нову назву бази
    'username'  => 'if0_38128540',  // Змінено на новий логін
    'password'  => 'fNLBICJtOkJ',  // Змінено на новий пароль
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);


$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    $results = Capsule::select('SHOW TABLES');
    echo "З'єднання з базою даних успішне. Таблиці у базі даних:<br>";
    foreach ($results as $table) {
        $tableName = array_values((array)$table)[0];
        echo $tableName . '<br>';
    }
} catch (Exception $e) {
    echo 'Помилка підключення до бази даних: ' . $e->getMessage();
}

try {
    $users = User::all();
    echo "Users fetched successfully:<br>";
    echo json_encode($users);
} catch (\Exception $e) {
    echo 'Error fetching users: ' . $e->getMessage();
}
?>
