<?php
require_once '../../controllers/helpers/connect_to_database.php';
require_once '../../controllers/helpers/redirect_to_custom_error.php';
require_once '../../controllers/helpers/sanitize_functions.php';
function getUsersOtherResponses()
{
    $conn = connect_to_database();
    try{
        // Query to fetch usernames, question text, question type, and their 'other' responses
        $sql = "SELECT sr.username, sr.other_response, sq.question_text, sq.question_type
                FROM survey_responses sr
                INNER JOIN survey_questions sq ON sr.question_id = sq.question_id
                WHERE sr.other_response IS NOT NULL AND sr.other_response <> ''
                ORDER BY sr.username";

        $result = $conn->query($sql);

        $users_responses = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users_responses[] = $row;
            }
        }
        $conn->close();
        return $users_responses;
    }catch(Exception $e){
        redirect_to_custom_error("Server Error","Unable to connect to server");
    }

}

$users_responses = getUsersOtherResponses();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users' Other Responses</title>
    <link href="../../dependencies/jquery.dataTables.min.css" rel="stylesheet">
    <link href="../../dependencies/buttons.dataTables.min.css" rel="stylesheet">
    <link href="../../bootstrap/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1><b>Users Other Responses</b></h1>
        <hr>
        <div class="table-container">
            <div class="table-responsive">
                <table id="usersResponsesTable" class="table table-striped table-bordered" style="border: none">
                    <thead style="background-color: #31363F; color: white">
                        <tr>
                            <th style="padding: 12px;">Username</th>
                            <th style="padding: 12px;">Question Text</th>
                            <th style="padding: 12px;">Question Type</th>
                            <th style="padding: 12px;">Other Response</th>
                        </tr>
                    </thead>
                    <tbody style="background-color: #ECEFF1;">
                        <?php foreach ($users_responses as $user_response): ?>
                            <tr>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;">
                                    <?php echo htmlspecialchars($user_response['username']); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;">
                                    <?php echo htmlspecialchars($user_response['question_text']); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;">
                                    <?php echo htmlspecialchars($user_response['question_type']); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;">
                                    <?php echo htmlspecialchars($user_response['other_response']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="../../dependencies/jquery.min.js"></script>
    <script src="../../dependencies/jquery.dataTables.min.js"></script>
    <script src="../../dependencies/dataTables.buttons.min.js"></script>
    <script src="../../dependencies/jszip.min.js"></script>
    <script src="../../dependencies/pdfmake.min.js"></script>
    <script src="../../dependencies/vfs_fonts.js"></script>
    <script src="../../dependencies/buttons.html5.min.js"></script>
    <script src="../../dependencies/buttons.print.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#usersResponsesTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                "paging": true,
                "pageLength": 10,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true
            });
        });
    </script>
</body>

</html>
