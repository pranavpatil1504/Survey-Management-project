<?php
session_start();

// Define categories and their respective image directories
$categories = [
    'traffic lights' => 'images/traffic_lights',
    'roads' => 'images/roads',
];

// Randomly select a category
$category = array_rand($categories);
$directory = $categories[$category];

// Initialize arrays for correct and selected images
$correctImages = [];
$selectedImages = [];

// Based on the selected category, prepare the CAPTCHA challenge
if ($category === 'traffic lights') {
    // Define correct images for traffic lights category
    $correctImages = [
        'images/traffic_lights/1.jpeg',
        'images/traffic_lights/2.jpeg',
        'images/traffic_lights/3.jpeg',
    ];

    // Shuffle the correct images
    shuffle($correctImages);

    // Get all images from the selected directory
    $allImages = glob("$directory/*.jpeg");

    // Shuffle all images
    shuffle($allImages);

    // Select 5 images from the shuffled list (including some incorrect ones)
    $selectedImages = array_slice($allImages, 0, 5);
} else {
    // Get all images from the selected directory
    $images = glob("$directory/*.jpeg");

    // Randomly select 9 images
    shuffle($images);
    $selectedImages = array_slice($images, 0, 9);
}

// Store the correct images and category in session variables for validation
$_SESSION['captcha_correct'] = $correctImages;
$_SESSION['captcha_category'] = $category;

$response = ['category' => $category, 'images' => []];
foreach ($selectedImages as $image) {
    $response['images'][] = ['src' => $image, 'filename' => basename($image)];
}

header('Content-Type: application/json');
echo json_encode($response);
?>
