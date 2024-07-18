<?php
include '../controllers/helpers/connect_to_database.php';
require_once '../controllers/helpers/redirect_to_custom_error.php';
require_once '../controllers/helpers/sanitize_functions.php';
require_once 'custom_error.php';

function getTotalQuestions($conn) {
    try{
        $sql = "SELECT COUNT(*) AS total_questions FROM survey_questions";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['total_questions'];
    }catch(Exception $e){
        redirect_to_custom_error("Server Error","Unable to connect server");
    }
}


function getUserAnsweredQuestions($conn, $username) {
    try{
        $username = sanitize_string($username);
        $sql = "SELECT COUNT(DISTINCT question_id) AS answered_questions FROM temp_response_table WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['answered_questions'];
    }catch(Exception $e){
        redirect_to_custom_error("Server Error","Unable to connect server");
    }

}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['response'])) {
    session_start();
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        
    } else {
        redirect_to_custom_error("Session Error", "Username doesn't exist ; )");
        exit;
    }


    $responses = $_POST['response'];
    $other_texts = isset($_POST['other_text']) ? $_POST['other_text'] : array();
    $conn = connect_to_database();

    foreach ($responses as $question_id => $response) {
        // Get the question type for the current question
        // Delete existing responses for this question_id and username
        try{
            $question_type_sql = "SELECT question_type FROM survey_questions WHERE question_id = ?";
            $question_type_stmt = $conn->prepare($question_type_sql);
            $question_type_stmt->bind_param("i", $question_id);
            $question_type_stmt->execute();
            $question_type_stmt->bind_result($question_type);
            $question_type_stmt->fetch();
            $question_type_stmt->close();
        }catch(Exception $e) {
            redirect_to_custom_error("Server Error","Unable to connect server");
        }

        // Delete existing responses for this question_id and username
        try{
            $delete_sql = "DELETE FROM temp_response_table WHERE username = ? AND question_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("si", $username, $question_id);
            $delete_stmt->execute();
            $delete_stmt->close();
        }catch(Exception $e) {
            redirect_to_custom_error("Server Error","Unable to connect server");
        }


        // Initialize other_response variable
        $other_response = '';

        // Handle insertion of new response based on question type
        if ($question_type == 'limit' && is_array($response)) {
            $other_response = isset($_POST['other_text'][$question_id]) ? $_POST['other_text'][$question_id] : '';
            $other_response = sanitize_string($other_response);
            $limit_value = sanitize_numeric($limit_value);
            foreach ($response as $option_id => $limit_value) {
                if (is_numeric($limit_value) && $option_id > 0) {
                    try{
                        $sql = "INSERT INTO temp_response_table (username, question_id, option_id, limit_value) VALUES (?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("siii", $username, $question_id, $option_id, $limit_value);
                        $stmt->execute();
                        $stmt->close();
                    }catch(Exception $e) {
                        redirect_to_custom_error("Server Error","Unable to connect server");
                    }
                }
            }
        } elseif ($question_type == 'multiple' && is_array($response)) {
            $other_response = isset($_POST['other_text'][$question_id]) ? $_POST['other_text'][$question_id] : '';
            $other_response = sanitize_string($other_response);
            foreach ($response as $option_id) {
                if ($option_id > 0) {
                    try{
                        $sql = "INSERT INTO temp_response_table (username, question_id, option_id) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("sis", $username, $question_id, $option_id);
                        $stmt->execute();
                        $stmt->close();
                    }catch(Exception $e) {
                        redirect_to_custom_error("Server Error","Unable to connect server");
                    }
                }
            }
        } elseif ($question_type == 'single') {
            $response_value = isset($response) ? $response : 0;
            $other_response = isset($_POST['other_text'][$question_id]) ? $_POST['other_text'][$question_id] : '';
            $other_response = sanitize_string($other_response);
            try{
                $sql = "INSERT INTO temp_response_table (username, question_id, option_id) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sis", $username, $question_id, $response_value);
                $stmt->execute();
                $stmt->close();
            }catch(Exception $e) {
                redirect_to_custom_error("Server Error","Unable to connect server");
            }
        } elseif ($question_type == 'string') {
            $string_response = isset($response[$question_id]) ? $response[$question_id] : '';
            $other_response = isset($_POST['string_response'][$question_id]) ? $_POST['string_response'][$question_id] : '';
            $string_response = sanitize_string($string_response);
            try{
                $sql = "INSERT INTO temp_response_table (username, question_id, string_response) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $username, $question_id, $string_response);
                $stmt->execute();
                $stmt->close();
            }catch(Exception $e) {
                redirect_to_custom_error("Server Error","Unable to connect server");
            }
        }
        else{
            try{
                $sql = "INSERT INTO temp_response_table (username, question_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $username, $question_id);
                $stmt->execute();
                $stmt->close();
            }catch(Exception $e){
                redirect_to_custom_error("Server Error","Unable to connect server");
            }
        }
        $other_response = isset($_POST['other_text'][$question_id]) ? $_POST['other_text'][$question_id] : '';
        $other_response = sanitize_string($other_response);
        if (!empty($other_response)) {
            try{
                $sql = "INSERT INTO temp_response_table (username, question_id, other_response) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $username, $question_id, $other_response);
                $stmt->execute();
                $stmt->close();
            }catch(Exception $e) {
                redirect_to_custom_error("Server Error","Unable to connect server");
            }
        }
    }

    // Retrieve total number of questions and user's answered questions
    $total_questions = getTotalQuestions($conn);
    $answered_questions = getUserAnsweredQuestions($conn, $username);

    if ($answered_questions == $total_questions) {
        // Begin transaction
        $conn->begin_transaction();

        try {

            $user_already_submitted = "SELECT COUNT(*) FROM users_submitted WHERE username = ?";
            $user_already_submitted_res = $conn->prepare($user_already_submitted);
            $user_already_submitted_res->bind_param("s", $username);
            $user_already_submitted_res->execute();
            $user_already_submitted_res->bind_result($user_already_submitted_count);
            $user_already_submitted_res->fetch();
            $user_already_submitted_res->close();
            if($user_already_submitted_count > 0){
                redirect_to_custom_error("User already submitted the Survey", "Please contact the admin for further details");
                exit;  
            }


            // Transfer responses from temp_response_table to survey_responses
            $sql_transfer = "INSERT INTO survey_responses (username, question_id, option_id, limit_value, string_response, other_response)
                             SELECT username, question_id, option_id, limit_value, string_response, other_response
                             FROM temp_response_table
                             WHERE username = ?";
            $stmt_transfer = $conn->prepare($sql_transfer);
            $stmt_transfer->bind_param("s", $username);
            $stmt_transfer->execute();
            $stmt_transfer->close();

            // Delete responses from temp_response_table
            $sql_delete = "DELETE FROM temp_response_table WHERE username = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("s", $username);
            $stmt_delete->execute();
            $stmt_delete->close();

            // Insert into users_submitted
            $sql_submission = "INSERT INTO users_submitted (username, submission_timestamp) VALUES (?, CURRENT_TIMESTAMP)";
            $stmt_submission = $conn->prepare($sql_submission);
            $stmt_submission->bind_param("s", $username);
            $stmt_submission->execute();
            $stmt_submission->close();

            // Commit transaction
            $conn->commit();
            echo "Survey submitted successfully.";
        } catch (Exception $e) {
            // Rollback transaction in case of error
            $conn->rollback();
            redirect_to_custom_error("Server Error","Unable to connect server");
        }
    } else {
        echo "You have not answered all the questions.";
    }

    $conn->close();
}
?>
