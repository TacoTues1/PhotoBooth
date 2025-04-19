<?php
session_start(); // Start the session to access user data

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $photo_data = $_POST['photo_data'];

    // Decode the base64 image data
    $photo_data = str_replace('data:image/png;base64,', '', $photo_data);
    $photo_data = str_replace(' ', '+', $photo_data);
    $photo_binary = base64_decode($photo_data);

    // Save the photo to the uploads directory
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); // Create the directory if it doesn't exist
    }
    $file_name = uniqid() . '.png';
    $target_file = $target_dir . $file_name;
    file_put_contents($target_file, $photo_binary);

    // Connect to the database
    $conn = new mysqli('localhost', 'root', '', 'photobooth');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Insert the photo into the photos table
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in the session after login
    $sql = "INSERT INTO photos (user_id, photo_path) VALUES ($user_id, '$target_file')";
    if ($conn->query($sql) === TRUE) {
        echo "Photo saved successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>