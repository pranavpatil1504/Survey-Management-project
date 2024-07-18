<?php
include '../controllers/helpers/connect_to_database.php';
include_once '../controllers/helpers/redirect_to_custom_error.php';
include_once '../controllers/helpers/sanitize_functions.php';
// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try{
        session_start();
        // Create connection
        $conn = connect_to_database();
        
        // Prepare data for insertion (ensure to sanitize and validate input as needed)
        $libraryFeedback = $_POST['libraryFeedback'];
        $username = $_SESSION['username'];
        $userDivision = $_POST['userDivision'];
        $userDesignation = $_POST['userDesignation'];
        $userTel = $_POST['userTel'];
        $userEmail = $_POST['userEmail'];
        $userInterests = $_POST['userInterests'];
    
        $libraryFeedback = sanitize_string($libraryFeedback);
        $username = sanitize_string($username);
        $userDivision = sanitize_string($userDivision);
        $userTel = sanitize_numeric($userTel);
        $userEmail = sanitize_email($userEmail);
        $userDesignation = sanitize_string($userDesignation);
        $userInterests = sanitize_string($userInterests);
    
        
        // Prepare an INSERT statement with placeholders
        $sql = "INSERT INTO personal_details (libraryFeedback, username, userDivision, userDesignation, userTel, userEmail, userInterests) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        // Prepare the statement
        $stmt = $conn->prepare($sql);
        
        // Bind parameters to the statement
        $stmt->bind_param("ssssiss", $libraryFeedback, $username, $userDivision, $userDesignation, $userTel, $userEmail, $userInterests);
        
        // Execute the statement
        if ($stmt->execute()) {
            // Send a success response
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        
        // Close statement and connection
        $stmt->close();
        $conn->close();
    }catch(Exception $e){
        redirect_to_custom_error("Server Error","Unable to connect to the server");
    }

} else {
    redirect_to_custom_error("Error","Invalid request method");
}
?>
