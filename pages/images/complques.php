<?php
session_start();
include '../controllers/helpers/connect_to_database.php';
include '../controllers/helpers/redirect_to_custom_error.php';

// Check if the session data is set
if (!isset($_SESSION['username'])) {
    header("Location: signin.php");
    exit();
}

$username = $_SESSION['username'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $is_member = $_POST['is_member'] ?? '';
    $reason = $_POST['reason'] ?? '';

    $db = connect_to_database();

 
    if ($is_member === 'Yes') {
        // Insert data for Yes into database
        $insert_sql = "INSERT INTO complques (username, is_member, submission_date) VALUES (?, ?, CURRENT_TIMESTAMP)";
        $stmt = $db->prepare($insert_sql);
        $stmt->bind_param("ss", $username, $is_member);
        
        if ($stmt->execute()) {
            // Redirect to home page with session data
            $session_data = base64_encode(json_encode(array(
                'session_token' => $_SESSION['session_token'],
                'expiration_time' => $_SESSION['expiration_time'],
                'username' => $username
            )));
            header("Location: ../pages/home.php?session_data=$session_data");
            exit();
        } else {
            $message = "Error: " . $stmt->error;
        }
    } elseif ($is_member === 'No') {
        // Insert data for No into database with reason
        $insert_sql = "INSERT INTO complques (username, is_member, reason, submission_date) VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $db->prepare($insert_sql);
        $stmt->bind_param("sss", $username, $is_member, $reason);
        
        try{
            if ($stmt->execute()) {
                // Redirect to signin page
                header("Location: signin.php");
                exit();
            } else {
                $message = "Error: " . $stmt->error;
                redirect_to_custom_error("Error",$message);
    
            }
        }catch(PDOException $e) {
            $message = "". $e->getMessage();
            redirect_to_custom_error("Error",$message);
        }

    } else {
        $message = "Please select an option.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Membership Survey</title>
    <link href="../bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="../bootstrap/bootstrap-icons.min.css" rel="stylesheet">
    <script src="../dependencies/jquery.slim.min.js"></script>
    <script src="../dependencies/popper.min.js"></script>
    <script src="../bootstrap/bootstrap.min.js"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color:#E0FBE2;
            color: #333;
        }

        .center-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .custom-container {
            max-width: 800px;
            width: 100%;
            background-color: #fff;
            border: none;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .custom-container:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        h3 {
            font-weight: bold;
            margin-bottom: 2rem;
            color: black;
            text-align: center;
            font-size: 30px;
        }

        .form-label {
            font-weight: 600;
        }

        .form-check-input {
            margin-top: 0.3rem;
        }

        .btn {
            background-color: #1A5319;
            color:#fff;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border-radius: 5px;
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        .btn:hover {
            background-color: #508D4E;
            color:#fff;
            box-shadow: 0 4px 12px rgba(0, 91, 187, 0.2);
        }

        .alert-info {
            background-color: #e9f7fd;
            border-color: #b8e2f6;
            color: #31708f;
        }

        textarea:focus,
        input[type="radio"]:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .mb-3 {
            margin-bottom: 1.5rem;
            font-size: 20px;
        }

        .form-check-label {
            margin-left: 0.5rem;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="center-container">
        <div class="custom-container">
            <h3>Library Membership Survey</h3>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Are you a member of the Central Library?</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_member" id="is_member_yes" value="Yes" required>
                        <label class="form-check-label" for="is_member_yes">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_member" id="is_member_no" value="No" required>
                        <label class="form-check-label" for="is_member_no">No</label>
                    </div>
                </div>
                <div class="mb-3" id="reasonContainer" style="display:none;">
                    <label for="reason" class="form-label">It’s quite unfortunate!...We would like to know the reason for you being not a member yet:</label>
                    <textarea class="form-control" id="reason" name="reason" rows="4" ></textarea>
                </div>
                <?php if ($message): ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var isMemberYes = document.getElementById('is_member_yes');
        var isMemberNo = document.getElementById('is_member_no');
        var reasonContainer = document.getElementById('reasonContainer');
        var reasonTextarea = document.getElementById('reason');

        // Initial check on page load
        if (isMemberYes.checked) {
            reasonContainer.style.display = 'none';
            reasonTextarea.removeAttribute('required'); // Remove required attribute
        } else if (isMemberNo.checked) {
            reasonContainer.style.display = 'block';
            reasonTextarea.setAttribute('required', 'required'); // Add required attribute
        }

        // Event listeners for radio button changes
        isMemberYes.addEventListener('change', function() {
            if (isMemberYes.checked) {
                reasonContainer.style.display = 'none';
                reasonTextarea.removeAttribute('required'); // Remove required attribute
            }
        });

        isMemberNo.addEventListener('change', function() {
            if (isMemberNo.checked) {
                reasonContainer.style.display = 'block';
                reasonTextarea.setAttribute('required', 'required'); // Add required attribute
            }
        });
    });
</script>

</body>

</html>
