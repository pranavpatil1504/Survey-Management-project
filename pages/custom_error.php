<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="../bootstrap/bootstrap-icons.min.css" rel="stylesheet">
    <title>Survey Already Submitted</title>
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
            width: 300px;
            height: 290px;
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
        <img src="../assets/survey_submitted.png" alt="Error Image" class="error-image">
        <div class="error-text">
            <h1><?php echo $_SESSION['error_message1']; ?></h1>
            <p><?php echo $_SESSION['error_message2']; ?></p>
            <button class="btn btn-md btn-danger" onclick="destroySessionAndRedirect()">
                <a href="signin.php" style="text-decoration: none; color: white">Close</a>
            </button>
        </div>
    </div>

    <script>
        // Function to destroy session and redirect to signin.php
        function destroySessionAndRedirect() {
            if (performance.navigation.type === 1 || performance.navigation.type === 2) {
                document.cookie = "PHPSESSID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";

                // Redirect to signin.php
                window.location.href = 'signin.php';
            }
        }

        destroySessionAndRedirect();

        // Also destroying from the chrome history stack so that if user go back the webpage will redirect it to sign.php
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                destroySessionAndRedirect();
            }
        });
    </script>
</body>

</html>