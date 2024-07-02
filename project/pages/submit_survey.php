<?php
include '../controllers/helpers/connect_to_database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['response'])) {
    session_start();
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
    } else {
        echo "Session error: username not set.";
        exit;
    }

    $responses = $_POST['response'];
    $conn = connect_to_database();

    foreach ($responses as $question_id => $response) {
        // Get the question type for the current question
        $question_type_sql = "SELECT question_type FROM survey_questions WHERE question_id = ?";
        $question_type_stmt = $conn->prepare($question_type_sql);
        $question_type_stmt->bind_param("i", $question_id);
        $question_type_stmt->execute();
        $question_type_stmt->bind_result($question_type);
        $question_type_stmt->fetch();
        $question_type_stmt->close();

        // Handling 'limit' question type
        if ($question_type == 'limit' && is_array($response)) {
            foreach ($response as $option_id => $limit_value) {
                if (is_numeric($limit_value) && $option_id > 0) {
                    $sql = "INSERT INTO survey_responses (username, question_id, option_id, limit_value) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("siii", $username, $question_id, $option_id, $limit_value);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        } 
        // Handling 'multiple' question type
        elseif ($question_type == 'multiple' && is_array($response)) {
            foreach ($response as $option_id) {
                if ($option_id > 0) {
                    $sql = "INSERT INTO survey_responses (username, question_id, option_id) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sii", $username, $question_id, $option_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        } 
        // Handling 'single' question type
        elseif ($question_type == 'single') {
            if ($response > 0) {
                $sql = "INSERT INTO survey_responses (username, question_id, option_id) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sii", $username, $question_id, $response);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    //Handling user submission
    $sql_submission = "INSERT INTO users_submitted (username, submission_timestamp) VALUES (?, CURRENT_TIMESTAMP)";
    $stmt = $conn->prepare($sql_submission);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();

    $conn->close();
    echo "Responses saved successfully.";
} else {
    echo "Invalid request.";
}
?>
