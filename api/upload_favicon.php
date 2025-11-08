<?php
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['favicon'])) {
        $result = upload_favicon($_FILES['favicon']);
        if ($result['success']) {
            header('Location: ../public/admin_favicon.php?message=Favicon subido correctamente.');
        } else {
            header('Location: ../public/admin_favicon.php?error=' . urlencode($result['message']));
        }
        exit;
    }
}
