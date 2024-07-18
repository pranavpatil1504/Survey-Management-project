<?php
// Ensure all output is buffered until needed
ob_start();

// Include necessary files and functions
include_once '../../controllers/helpers/connect_to_database.php';
require_once '../../controllers/helpers/redirect_to_custom_error.php';
// Function to fetch user responses for a specific username
function getUserResponsesByUsername($conn, $username){
    try{
        // Query to fetch responses based on username
        $sql = "SELECT q.question_text,
                    o.option_text,
                    r.limit_value,
                    sr.other_response
                FROM survey_responses r
                JOIN survey_questions q ON r.question_id = q.question_id
                LEFT JOIN survey_options o ON r.option_id = o.option_id
                LEFT JOIN survey_responses sr ON r.question_id = sr.question_id AND r.username = sr.username
                WHERE r.username = ?
                ORDER BY q.question_text";

        // Prepare and execute the query
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch all responses into an array
        $responses = [];
        while ($row = $result->fetch_assoc()) {
            $question_text = $row['question_text'];

            // Initialize the question array if it doesn't exist
            if (!isset($responses[$question_text])) {
                $responses[$question_text] = [];
            }

            // Check if this is an option response
            if (!empty($row['option_text'])) {
                // Use option_text as the key to ensure uniqueness
                $option_key = 'option_' . $row['option_text'];

                // Add option to the responses if it doesn't exist
                if (!isset($responses[$question_text][$option_key])) {
                    $responses[$question_text][$option_key] = [
                        'option_text' => $row['option_text'],
                        'limit_value' => $row['limit_value'],
                    ];
                }
            } elseif (!empty($row['other_response'])) {
                // Add other response if it doesn't exist
                $responses[$question_text][] = [
                    'other_response' => $row['other_response'],
                ];
            }
        }

        // Close the statement and return the responses
        $stmt->close();
        return $responses;
    }catch(Exception $e){
        redirect_to_custom_error("Server Error","Unable to connect to server");
    }

    
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

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="container" id="container-userrep">
        <h1><strong>User Response Report</strong></h1>
        <hr style="border: 1px solid #373A4050; border-radius: 5px">
        </hr>
        <!-- Search Form -->
        <form method="post" class="form-inline mb-3">
            <label for="username" class="mr-2" style="font-weight: bold">Enter Username:</label>
            <input type="text" name="username" id="username" class="form-control mr-2" required>
            <button type="submit" class="btn btn-primary" style="background-color: #343a40; border: none">Search</button>
        </form>

        <?php if (!empty($user_responses)): ?>
            <h4>Responses for User: <?php echo htmlspecialchars($username); ?></h4>
            <div id="printable-section">
                <?php foreach ($user_responses as $question_text => $responses): ?>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Question: <?php echo htmlspecialchars($question_text); ?></h5>
                            <?php foreach ($responses as $response): ?>
                                <?php if (isset($response['option_text'])): ?>
                                    <p class="card-text">
                                        Option: <?php echo htmlspecialchars($response['option_text']); ?>
                                        <?php if (!is_null($response['limit_value'])): ?>
                                            , Rating: <?php echo htmlspecialchars($response['limit_value']); ?>/10
                                        <?php endif; ?>
                                    </p>
                                <?php elseif (isset($response['other_response'])): ?>
                                    <p class="card-text">
                                        Other Response: <?php echo htmlspecialchars($response['other_response']); ?>
                                    </p>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button id="print-button" class="btn btn-success no-print" onclick="printReport()">Print Report</button>
        <?php elseif (isset($_POST['username'])): ?>
            <p>No responses found for username: <?php echo htmlspecialchars($username); ?></p>
        <?php endif; ?>
    </div>

    <!-- Bootstrap and Print Script -->
    <script>
    function printReport() {
        // Clone the printable section
        var printableSection = document.getElementById('printable-section').cloneNode(true);

        // Hide elements that should not be printed
        var nonPrintableElements = printableSection.querySelectorAll('.no-print');
        nonPrintableElements.forEach(function(element) {
            element.style.display = 'none';
        });

        // Convert DataTable to regular HTML table for printing
        var dataTable = printableSection.querySelector('table');
        if (dataTable) {
            var printTable = dataTable.cloneNode(true);
            printTable.classList.remove('dataTable');
            printTable.classList.remove('no-footer');
            printableSection.querySelector('.dataTables_wrapper').remove();
            printableSection.appendChild(printTable);
        }

        // Create a new window
        var printWindow = window.open('', '_blank');
        printWindow.document.open();
        printWindow.document.write('<html><head><title>User Report <?php echo htmlspecialchars($username); ?></title>');
        printWindow.document.write('<link rel="stylesheet" href="../../bootstrap/bootstrap.min.css">');
        printWindow.document.write('<style>@media print {.no-print {display: none;}}</style>');
        printWindow.document.write('</head><body>');

        // Append the printable section content
        printWindow.document.write('<div class="container">');
        printWindow.document.write('<h1>User Response Report</h1>');
        printWindow.document.write('<hr>');
        printWindow.document.write(printableSection.innerHTML);
        printWindow.document.write('</div>');

        printWindow.document.write('</body></html>');
        printWindow.document.close();

        // Print the window
        printWindow.print();
        printWindow.close();
    }
</script>

</body>

</html>
