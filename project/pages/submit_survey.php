<?php
include '../controllers/helpers/connect_to_database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['response'])) {
    session_start();
    if (isset($_SESSION['username'])) {
        $user_id = $_SESSION['username'];
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

        if ($question_type == 'limit' && is_array($response)) {
            foreach ($response as $option_id => $limit_value) {
                if (is_numeric($limit_value) && $option_id > 0) {
                    // Check if option_id exists in survey_options table
                    $check_sql = "SELECT COUNT(*) FROM survey_options WHERE option_id = ?";
                    $check_stmt = $conn->prepare($check_sql);
                    $check_stmt->bind_param("i", $option_id);
                    $check_stmt->execute();
                    $check_stmt->bind_result($count);
                    $check_stmt->fetch();
                    $check_stmt->close();

                    if ($count > 0) {
                        $sql = "INSERT INTO survey_responses (username, question_id, option_id, limit_value) VALUES (?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("siii", $user_id, $question_id, $option_id, $limit_value);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        echo "Invalid option_id: $option_id";
                        exit;
                    }
                }
            }
        } elseif ($question_type == 'multiple' && is_array($response)) {
            foreach ($response as $option_id) {
                if ($option_id > 0) {
                    // Check if option_id exists in survey_options table
                    $check_sql = "SELECT COUNT(*) FROM survey_options WHERE option_id = ?";
                    $check_stmt = $conn->prepare($check_sql);
                    $check_stmt->bind_param("i", $option_id);
                    $check_stmt->execute();
                    $check_stmt->bind_result($count);
                    $check_stmt->fetch();
                    $check_stmt->close();

                    if ($count > 0) {
                        $sql = "INSERT INTO survey_responses (username, question_id, option_id) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("sii", $user_id, $question_id, $option_id);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        echo "Invalid option_id: $option_id";
                        exit;
                    }
                }
            }
        } elseif ($question_type == 'single' && is_numeric($response)) {
            if ($response > 0) {
                // Check if option_id exists in survey_options table
                $check_sql = "SELECT COUNT(*) FROM survey_options WHERE option_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $response);
                $check_stmt->execute();
                $check_stmt->bind_result($count);
                $check_stmt->fetch();
                $check_stmt->close();

                if ($count > 0) {
                    $sql = "INSERT INTO survey_responses (username, question_id, option_id) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sii", $user_id, $question_id, $response);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    echo "Invalid option_id: $response";
                    exit;
                }
            } else {
                echo "Invalid response: $response";
                exit;
            }
        }
    }

    $conn->close();
    header("Location: home.php");
    exit;
} else {
    echo "Invalid request method or no response data.";
    exit;
}
?>
