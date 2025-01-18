<?php

namespace App\Controllers;

use App\Models\AccessLog;
require_once __DIR__ . '/helpers.php';

class AccessLogController {

    private $secretKey = "your_secret_key";

    public function addAccess() {
        // Перевірка, чи користувач є адміністратором
        $user = $_SESSION['user'];
        if ($user['Role'] !== 'admin') {
            jsonResponse(['message' => 'Access denied: only admins can add users'], 403);
            return;
        }

        $data = getRequestData();

        if (empty($data['UserID']) || empty($data['LockID']) || empty($data['Action'])) {
            jsonResponse(['message' => 'All fields are required: UserID, LockID, Action'], 400);
            return;
        }

        $access = new AccessLog();
        $access->UserID = $data['UserID'];
        $access->LockID = $data['LockID'];
        $access->Action = $data['Action'];
        $access->save();

        jsonResponse(['message' => 'Access granted successfully'], 201);
    }
}
