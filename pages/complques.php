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

// Connect to the database
$db = connect_to_database();

// Fetch existing entry if it exists
$existing_entry = null;
$fetch_existing_query = "SELECT is_member, reason FROM complques WHERE username = ?";
$stmt = $db->prepare($fetch_existing_query);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $existing_entry = $result->fetch_assoc();
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $is_member = $_POST['is_member'] ?? '';
    $reason = $_POST['reason'] ?? '';

    if ($is_member === 'Yes') {
        // Insert data for Yes into database
        $insert_sql = "INSERT INTO complques (username, is_member, submission_date) VALUES (?, ?, CURRENT_TIMESTAMP)
                       ON DUPLICATE KEY UPDATE is_member = VALUES(is_member), reason = NULL, submission_date = VALUES(submission_date)";
        $stmt = $db->prepare($insert_sql);
        $stmt->bind_param("ss", $username, $is_member);

        if ($stmt->execute()) {
            $stmt->close();  // Close the statement

            // Redirect to home page with session data
            $session_data = base64_encode(json_encode(
                array(
                    'session_token' => $_SESSION['session_token'],
                    'expiration_time' => $_SESSION['expiration_time'],
                    'username' => $username
                )
            ));
            header("Location: ../pages/home.php?session_data=$session_data");
            exit();
        } else {
            $message = "Error: " . $stmt->error;
            $stmt->close();  // Close the statement
            redirect_to_custom_error("Error", $message);
        }
    } elseif ($is_member === 'No') {
        // Insert or update data for No into database with reason
        $insert_sql = "INSERT INTO complques (username, is_member, reason, submission_date) VALUES (?, ?, ?, CURRENT_TIMESTAMP)
                       ON DUPLICATE KEY UPDATE is_member = VALUES(is_member), reason = VALUES(reason), submission_date = VALUES(submission_date)";
        $stmt = $db->prepare($insert_sql);
        $stmt->bind_param("sss", $username, $is_member, $reason);

        if ($stmt->execute()) {
            $stmt->close();  // Close the statement

            // Insert into users_submitted table
            try {
                $sql_submission = "INSERT INTO users_submitted (username, submission_timestamp) VALUES (?, CURRENT_TIMESTAMP)
                                   ON DUPLICATE KEY UPDATE submission_timestamp = VALUES(submission_timestamp)";
                $stmt_submission = $db->prepare($sql_submission);
                $stmt_submission->bind_param("s", $username);
                $stmt_submission->execute();
                $stmt_submission->close();  // Close the statement

                // Set a flag to show the modal
                $_SESSION['show_submit_modal'] = true;

                // Redirect back to the form to trigger the modal
                header("Location: complques.php");
                exit();
            } catch (Exception $e) {
                $message = $e->getMessage();
                redirect_to_custom_error("Error", $message);
            }
        } else {
            $message = "Error: " . $stmt->error;
            $stmt->close();  // Close the statement
            redirect_to_custom_error("Error", $message);
        }
    } else {
        $message = "Please select an option.";
        redirect_to_custom_error("Error", $message);
    }
}

// Close the database connection
$db->close();
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
            background-color: #E0FBE2;
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
            color: #fff;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border-radius: 5px;
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        .btn:hover {
            background-color: #508D4E;
            color: #fff;
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
                        <input class="form-check-input" type="radio" name="is_member" id="is_member_yes" value="Yes"
                            required <?php if (isset($existing_entry) && $existing_entry['is_member'] === 'Yes')
                                echo 'checked'; ?>>
                        <label class="form-check-label" for="is_member_yes">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_member" id="is_member_no" value="No"
                            required <?php if (isset($existing_entry) && $existing_entry['is_member'] === 'No')
                                echo 'checked'; ?>>
                        <label class="form-check-label" for="is_member_no">No</label>
                    </div>
                </div>
                <div class="mb-3" id="reasonContainer" style="display:none;">
                    <label for="reason" class="form-label">It’s quite unfortunate!...We would like to know the reason
                        for you being not a member yet:</label>
                    <textarea class="form-control" id="reason" name="reason"
                        rows="4" style="max-height: 100px; border-color: black; border-radius: 10px"><?php if (isset($existing_entry) && $existing_entry['is_member'] === 'No')
                            echo htmlspecialchars($existing_entry['reason']); ?></textarea>
                </div>
                <?php if ($message): ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isMemberNoRadio = document.getElementById('is_member_no');
            const reasonContainer = document.getElementById('reasonContainer');
            const reasonTextarea = document.getElementById('reason');

            // Check the current state of the 'No' radio button on page load
            if (isMemberNoRadio.checked) {
                reasonContainer.style.display = 'block';
            }

            // Add event listeners to toggle the visibility of the reason container
            document.getElementById('is_member_yes').addEventListener('change', function () {
                if (this.checked) {
                    reasonContainer.style.display = 'none';
                }
            });

            document.getElementById('is_member_no').addEventListener('change', function () {
                if (this.checked) {
                    reasonContainer.style.display = 'block';
                }
            });

            <?php if (isset($_SESSION['show_submit_modal']) && $_SESSION['show_submit_modal']): ?>
                // Show the modal when the form is reloaded after submission
                $('#submitMessageModal').modal('show');

                // Remove the session variable to prevent the modal from showing again
                <?php unset($_SESSION['show_submit_modal']); ?>
            <?php endif; ?>
        });

        function destroySessionAndRedirect() {
            // Remove session cookie
            document.cookie = "PHPSESSID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            
            // Redirect to signin.php
            window.location.href = 'signin.php';
        }
    </script>

    <!-- Submit Message Modal -->
    <div class="modal fade" id="submitMessageModal" tabindex="-1" role="dialog"
        aria-labelledby="submitMessageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="tick-animation mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none"
                            stroke="#5cb85c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-check-circle">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="16 8 10 14 8 12"></polyline>
                        </svg>
                    </div>
                    <p class="submitMessageModalLabel"><b>Thank You for your Great Response.</b></p>
                    <button class="submitMessageModalLabel btn btn-danger" onclick="destroySessionAndRedirect()">
                        <a href="signin.php" style="text-decoration: none; color: white">Close</a>
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>