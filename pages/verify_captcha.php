<?php
session_start();

$selectedImages = json_decode($_POST['selected_images'], true);
$correctImages = $_SESSION['captcha_correct'];

// Define the correct image filenames that should be selected
$requiredImages = [
    '1.jpeg',
    '2.jpeg',
    '3.jpeg',
];

// Check if exactly the required images are selected
sort($selectedImages);
sort($requiredImages);
$success = ($selectedImages === $requiredImages);

if ($success) {
    $_SESSION['captcha_verified'] = true;
} else {
    $_SESSION['captcha_verified'] = false;
}

echo json_encode(['success' => $success]);
?>
