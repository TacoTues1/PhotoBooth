<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['photo_ids'])) {
    $photo_ids = $_POST['photo_ids'];

    $conn = new mysqli('localhost', 'root', '', 'photobooth');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Delete selected photos from the database and filesystem
    foreach ($photo_ids as $photo_id) {
        $sql = "SELECT photo_path FROM photos WHERE id = $photo_id";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $photo = $result->fetch_assoc();
            if (file_exists($photo['photo_path'])) {
                unlink($photo['photo_path']); // Delete the file
            }
        }

        $sql = "DELETE FROM photos WHERE id = $photo_id";
        $conn->query($sql);
    }

    $conn->close();
    header("Location: gallery.php"); // Redirect back to the gallery
    exit();
} else {
    header("Location: gallery.php"); // Redirect back if no photos were selected
    exit();
}
?>