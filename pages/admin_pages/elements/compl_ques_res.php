<?php
require_once '../../controllers/helpers/connect_to_database.php';
require_once '../../controllers/helpers/redirect_to_custom_error.php';

function getCompulsoryQuestionsData()
{
    $conn = connect_to_database();
    $sql = "SELECT id, username, is_member, reason, submission_date FROM complques ORDER BY submission_date DESC";
    $result = $conn->query($sql);

    $compulsory_questions = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $compulsory_questions[] = $row;
        }
    }
    $conn->close();
    return $compulsory_questions;
}

try {
    $compulsory_questions = getCompulsoryQuestionsData();
} catch (Exception $e) {
    redirect_to_custom_error("Server Error", "Unable to connect to server");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compulsory Question Responses</title>
    <link href="../../dependencies/jquery.dataTables.min.css" rel="stylesheet">
    <link href="../../dependencies/buttons.dataTables.min.css" rel="stylesheet">
    <link href="../../bootstrap/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1><b>Compulsory Question Responses</b></h1>
        <hr>
        <div class="table-container">
            <div class="table-responsive">
                <table id="compulsoryQuestionTable" class="table table-striped table-bordered" style="border: none">
                    <thead style="background-color: #31363F; color: white">
                        <tr>
                            <th style="padding: 12px;">ID</th>
                            <th style="padding: 12px;">Username</th>
                            <th style="padding: 12px;">Is Member</th>
                            <th style="padding: 12px;">Reason</th>
                            <th style="padding: 12px;">Submission Date</th>
                        </tr>
                    </thead>
                    <tbody style="background-color: #ECEFF1;">
                        <?php foreach ($compulsory_questions as $question): ?>
                            <tr>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;"><?php echo htmlspecialchars($question['id']); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;"><?php echo htmlspecialchars($question['username']); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;"><?php echo htmlspecialchars($question['is_member']); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;"><?php echo htmlspecialchars($question['reason'] ?? 'N/A'); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;"><?php echo htmlspecialchars($question['submission_date']); ?></td>
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
            $('#compulsoryQuestionTable').DataTable({
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
