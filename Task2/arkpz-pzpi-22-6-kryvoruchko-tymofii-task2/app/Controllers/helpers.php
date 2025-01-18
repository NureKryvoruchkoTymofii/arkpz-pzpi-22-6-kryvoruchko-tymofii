<?php
function jsonResponse($data, $status = 200) {
    if (!is_array($data) && !is_object($data)) {
        $data = ['message' => $data];
    }

    header("Content-Type: application/json");
    http_response_code($status);
    echo json_encode($data);
    exit();
}

function getRequestData() {
    return json_decode(file_get_contents('php://input'), true);
}
?>
