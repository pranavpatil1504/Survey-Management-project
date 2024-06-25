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
        if (is_array($response)) {
            // Multiple choice
            foreach ($response as $option_id) {
                $sql = "INSERT INTO survey_responses (username, question_id, option_id) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iii", $user_id, $question_id, $option_id);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            // Single choice
            $sql = "INSERT INTO survey_responses (username, question_id, option_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sii", $user_id, $question_id, $response);
            $stmt->execute();
            $stmt->close();
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
