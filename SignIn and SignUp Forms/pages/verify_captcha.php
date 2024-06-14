<?php
session_start();

$selectedImages = json_decode($_POST['selected_images'], true);
$correctImages = array_map('basename', $_SESSION['captcha_correct']);

$success = empty(array_diff($correctImages, $selectedImages));

if ($success) {
    $_SESSION['captcha_verified'] = true;
} else {
    $_SESSION['captcha_verified'] = false;
}

echo json_encode(['success' => $success]);
?>
