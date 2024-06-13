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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAPTCHA Challenge</title>
    <link rel="stylesheet" href="../bootstrap/bootstrap.min.css">
    <style>
        .captcha-image {
            width: 100px;
            height: 100px;
            margin: 5px;
            cursor: pointer;
            border-radius: 5px;
            border: 2px solid transparent;
        }

        .captcha-image.selected {
            border-color: blue;
            border-width: 5px;
            border-radius: 10px;
        }

        .captcha-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-verify {
            margin-top: 10px;
            display: block;
            width: 100%;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center mt-5">CAPTCHA Challenge</h2>
    <p class="text-center">Select all images with <?= $category ?>.</p>
    <div class="captcha-grid">
        <?php foreach ($selectedImages as $index => $image): ?>
            <img src="<?= $image ?>" class="captcha-image" data-filename="<?= basename($image) ?>">
        <?php endforeach; ?>
    </div>
    <button id="verifyCaptcha" class="btn btn-primary btn-verify">Verify</button>
</div>

<script src="../bootstrap/bootstrap.bundle.min.js"></script>
<?php echo"<script>
    document.getElementById('verifyCaptcha').addEventListener('click', function () {
        // Get selected image filenames
        const selectedImages = Array.from(document.querySelectorAll('.captcha-image.selected'))
            .map(img => img.getAttribute('data-filename'));

        // Check if selected images match the correct images for the category
        const formData = new FormData();
        formData.append('selected_images', JSON.stringify(selectedImages));

        fetch('verify_captcha.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('CAPTCHA verification successful.');
                window.location.href = 'signin.php';
            } else {
                alert('CAPTCHA verification failed. Please try again.');
                window.location.reload();
            }
        });
    });

    // Toggle selection on image click
    document.querySelectorAll('.captcha-image').forEach(image => {
        image.addEventListener('click', function () {
            this.classList.toggle('selected');
        });
    });
</script>"; ?>
</body>
</html>
