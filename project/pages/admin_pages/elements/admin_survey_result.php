<?php
require_once '../../controllers/helpers/connect_to_database.php';

function getSurveyResultsByUser()
{
    $conn = connect_to_database();

    // Query to get results based on question types grouped by username
    $sql = "SELECT sr.username, sq.question_text, sq.question_type, so.option_text, sr.limit_value, COUNT(sr.response_id) as vote_count
            FROM survey_responses sr
            LEFT JOIN survey_options so ON sr.option_id = so.option_id
            JOIN survey_questions sq ON sr.question_id = sq.question_id
            GROUP BY sr.username, sq.question_id, sr.option_id, sr.limit_value
            ORDER BY sr.username, sq.question_id, so.option_id";

    $result = $conn->query($sql);

    $survey_results = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $survey_results[$row['username']][] = $row;
        }
    }
    $conn->close();
    return $survey_results;
}

$survey_results_by_user = getSurveyResultsByUser();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Results</title>
    <link href="../../dependencies/jquery.dataTables.min.css" rel="stylesheet">
    <link href="../../dependencies/buttons.dataTables.min.css" rel="stylesheet">
    <link href="../../bootstrap/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1><b>Survey Results</b></h1>
        <hr>
        <div class="table-container">
            <?php foreach ($survey_results_by_user as $username => $survey_results): ?>
                <h4><?php echo htmlspecialchars($username); ?>'s Result</h4>
                <div class="table-responsive">
                    <table id="surveyResultsTable_<?php echo htmlspecialchars($username); ?>" class="table table-striped table-bordered" style="border: none">
                        <thead style="background-color: #31363F; color: white">
                            <tr>
                                <th style="padding: 12px;">Question</th>
                                <th style="padding: 12px;">Option</th>
                                <th style="padding: 12px;">Votes</th>
                                <th style="padding: 12px;">Value</th>
                            </tr>
                        </thead>
                        <tbody style="background-color: #ECEFF1;">
                            <?php foreach ($survey_results as $result): ?>
                                <tr>
                                    <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;">
                                        <?php echo htmlspecialchars($result['question_text']); ?></td>
                                    <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;">
                                        <?php echo htmlspecialchars($result['option_text']); ?></td>
                                    <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;">
                                        <?php echo htmlspecialchars($result['vote_count']); ?></td>
                                    <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;">
                                        <?php
                                        if ($result['question_type'] == 'limit') {
                                            echo htmlspecialchars($result['limit_value']);
                                        } else {
                                            echo "NULL";
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <hr style="border: 2px solid grey">
                <br>
            <?php endforeach; ?>
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
    <script src="../../bootstrap/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            <?php foreach ($survey_results_by_user as $username => $survey_results): ?>
                $('#surveyResultsTable_<?php echo htmlspecialchars($username); ?>').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ],
                    "paging": true,
                    "pageLength": 5,
                    "lengthChange": true,
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "autoWidth": false,
                    "responsive": true
                });
            <?php endforeach; ?>
        });
    </script>
</body>

</html>
