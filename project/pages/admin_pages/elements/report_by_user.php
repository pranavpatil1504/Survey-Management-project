<?php
// Ensure all output is buffered until needed
ob_start();

// Include necessary files and functions
include_once '../../controllers/helpers/connect_to_database.php';

// Function to fetch user responses for a specific question
function getUserResponsesByUsername($conn, $username)
{
    // Query to fetch responses based on username
    $sql = "SELECT q.question_text,
                   GROUP_CONCAT(DISTINCT o.option_text ORDER BY o.option_id SEPARATOR ', ') AS option_texts,
                   GROUP_CONCAT(DISTINCT r.limit_value ORDER BY o.option_id SEPARATOR ', ') AS limit_values
            FROM survey_responses r
            JOIN survey_questions q ON r.question_id = q.question_id
            LEFT JOIN survey_options o ON r.option_id = o.option_id
            WHERE r.username = ?
            GROUP BY q.question_text
            ORDER BY q.question_text";

    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all responses into an array
    $responses = [];
    while ($row = $result->fetch_assoc()) {
        // Explode option_texts and limit_values into arrays
        $option_texts = explode(', ', $row['option_texts']);
        $limit_values = !empty($row['limit_values']) ? explode(', ', $row['limit_values']) : [];

        // Combine option_texts with limit_values if both are present
        $combined_options = [];
        foreach ($option_texts as $index => $option_text) {
            $combined_options[] = [
                'option_text' => $option_text,
                'limit_value' => isset($limit_values[$index]) ? $limit_values[$index] : null,
            ];
        }

        // Add combined options to responses array
        $responses[] = [
            'question_text' => $row['question_text'],
            'options' => $combined_options,
        ];
    }

    // Close the statement and return the responses
    $stmt->close();
    return $responses;
}

// Check if a username is submitted via POST
if (isset($_POST['username'])) {
    // Get username from POST data
    $username = $_POST['username'];

    // Connect to the database
    $conn = connect_to_database();

    // Fetch user responses for the specified username
    $user_responses = getUserResponsesByUsername($conn, $username);

    // Close the database connection
    $conn->close();
} else {
    // Initialize $user_responses as empty if no username is submitted
    $user_responses = [];
}

// Flush output buffer and send its contents to the browser
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Responses Report</title>
    <!-- Bootstrap CSS -->
    <link href="../../bootstrap/bootstrap.min.css" rel="stylesheet">
    <style>
        #container-userrep {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .card {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .card-body {
            padding: 15px;
        }

        .card-title {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .card-text {
            margin-bottom: 5px;
        }

        .form-inline {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container" id="container-userrep" style="background-color:#bfc7cd">
        <h1><strong>User Response Report</strong></h1>
        <hr style="border: 1px solid #373A4050; border-radius: 5px">
        </hr>
        <!-- Search Form -->
        <form method="post" class="form-inline mb-3">
            <label for="username" class="mr-2" style="font-weight: bold">Enter Username:</label>
            <input type="text" name="username" id="username" class="form-control mr-2" required>
            <button type="submit" class="btn btn-primary"
                style="background-color: #343a40; border-color:#343a40 ">Search</button>
        </form>

        <?php if (!empty($user_responses)): ?>
            <h4>Responses for User: <?php echo htmlspecialchars($username); ?></h4>
            <?php foreach ($user_responses as $response): ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Question: <?php echo htmlspecialchars($response['question_text']); ?></h5>
                        <?php foreach ($response['options'] as $option): ?>
                            <p class="card-text">
                                Option: <?php echo htmlspecialchars($option['option_text']); ?>
                                <?php if (!is_null($option['limit_value'])): ?>
                                    , Rating: <?php echo htmlspecialchars($option['limit_value']); ?>/10
                                <?php endif; ?>
                            </p>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php elseif (isset($_POST['username'])): ?>
            <p>No responses found for username: <?php echo htmlspecialchars($username); ?></p>
        <?php endif; ?>
    </div>
</body>

</html>