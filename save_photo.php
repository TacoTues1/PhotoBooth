<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'photobooth');

    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
    }

    // Decode the incoming JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    $photoData = $data['photo_data'];
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // Assuming user ID is stored in the session

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    // Decode the base64 image
    $photoData = explode(',', $photoData)[1];
    $photoData = base64_decode($photoData);

    // Save the photo to the server
    $photoName = uniqid('photo_', true) . '.png';
    $photoPath = __DIR__ . '/photos/' . $photoName;
    file_put_contents($photoPath, $photoData);

    // Save photo details to the database
    $stmt = $conn->prepare("INSERT INTO photos (user_id, file_name, created_at) VALUES (?, ?, NOW())");
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