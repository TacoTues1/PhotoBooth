<?php
session_start();
$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['photo_data'])) {
    $_SESSION['photo_data'] = $data['photo_data'];
}
?>