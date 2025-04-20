<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'photobooth');

    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
    }

    // Decode the incoming JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    $photoData = $data['photo_data'];
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    // Debugging: Log the user ID to the server logs
    error_log('User ID: ' . $userId);

    // Ensure the photos directory exists
    $photoDir = __DIR__ . '/photos';
    if (!is_dir($photoDir)) {
        mkdir($photoDir, 0777, true);
    }

    // Decode the base64 image
    $photoData = explode(',', $photoData)[1];
    $photoData = base64_decode($photoData);

    // Save the photo to the server
    $photoName = uniqid('', true) . '.png';
    $photoPath = $photoDir . '/' . $photoName;

    if (!file_put_contents($photoPath, $photoData)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save photo to server']);
        exit;
    }

    // Save photo details to the database
    $stmt = $conn->prepare("INSERT INTO photos (user_id, photo_path, captured_at) VALUES (?, ?, NOW())");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param('is', $userId, $photoName);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'url' => 'photos/' . $photoName]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save photo to database: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>