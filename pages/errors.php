<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Not Found</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f8f8;
        }

        .error-container {
            height: 400px;
            width: 800px;
            display: flex;
            align-items: center;
            background-color: #f8f8f8;
            padding: 30px;
            border-radius: 10px;
            /* box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); */
        }

        .error-image {
            width: 270px;
            height: 250px;
            margin-right: 30px;
        }

        .error-text {
            max-width: 500px;
        }

        .error-text h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 15px;
        }

        .error-text p {
            font-size: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <img src="../assets/error_img1.png" alt="Error Image" class="error-image">
        <div class="error-text">
            <h1>We're having trouble finding that site</h1>
            <p>Sorry, but the site you are looking for does not exist, has been moved, or is temporarily unavailable. Please check the URL and try again.</p>
        </div>
    </div>
</body>
</html>
