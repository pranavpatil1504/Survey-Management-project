<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedImages = json_decode($_POST['selected_images']);

    // Verify if 1.jpeg, 2.jpeg, and 3.jpeg are selected together
    $correctImages = ['1.jpeg', '2.jpeg', '3.jpeg'];
    sort($selectedImages);
    sort($correctImages);

    if ($selectedImages === $correctImages) {
        $_SESSION['captcha_verified'] = true;
        echo json_encode(['success' => true]);
    } else {
        $_SESSION['captcha_verified'] = false;
        echo json_encode(['success' => false]);
    }


}
?>