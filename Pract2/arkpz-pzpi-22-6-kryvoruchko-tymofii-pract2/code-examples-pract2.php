<?php
// До рефакторингу
$pdo = new PDO("mysql:host=localhost; dbname=test", "root", "");
$stmt = $pdo->query("SELECT id, name, email FROM users WHERE id = 1");
$user = $stmt->fetch(PDO::FETCH_ASSOC);

function getUserName($user) {
    return $user['name'];
}

echo "User Name: " . getUserName($user);

// Після рефакторингу
class User {
    private $id;
    private $name;
    private $email;

    public function __construct($id, $name, $email) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
    }

    // Гетери для доступу до даних
    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }
}

// Додатково після рефакторингу
class User {
    private $id;
    private $name;
    private $email;

    public function __construct($id, $name, $email) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getEmail() {
        return $this->email;
    }

    public function isEmailValid() {
        return filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function displayUserInfo() {
        return "ID: {$this->id}, Name: {$this->getName()}, Email: {$this->getEmail()}";
    }
}
