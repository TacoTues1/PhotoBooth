<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Check if layout_type is provided
    if (isset($input['layout_type'])) {
        $_SESSION['layout_type'] = $input['layout_type'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Layout type not provided.']);
    }
    exit();
}
?>