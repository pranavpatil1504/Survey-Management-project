<?php
require_once '../../controllers/helpers/connect_to_database.php';
require_once '../../controllers/helpers/redirect_to_custom_error.php';
function getSurveyOptionsAndVotes()
{
    $conn = connect_to_database();
    // Updated query to get question_text, question_type, total_votes, and avg_limit_value
    $sql = "SELECT so.option_text, q.question_text, q.question_type, COUNT(sr.response_id) as total_votes, 
                   CASE 
                       WHEN q.question_type IN ('multiple', 'single') THEN NULL 
                       ELSE AVG(sr.limit_value) 
                   END as avg_limit_value
            FROM survey_responses sr
            LEFT JOIN survey_options so ON sr.option_id = so.option_id
            LEFT JOIN survey_questions q ON so.question_id = q.question_id
            WHERE sr.option_id IS NOT NULL
            GROUP BY sr.option_id, q.question_text, q.question_type";

    $result = $conn->query($sql);

    $options_votes = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $options_votes[] = $row;
        }
    }
    $conn->close();
    return $options_votes;
}

try{
    $options_votes = getSurveyOptionsAndVotes();
}catch(Exception $e){
    redirect_to_custom_error("Server Error","Unable to connect to server");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Result</title>
    <link href="../../dependencies/jquery.dataTables.min.css" rel="stylesheet">
    <link href="../../dependencies/buttons.dataTables.min.css" rel="stylesheet">
    <link href="../../bootstrap/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1><b>Survey Result</b></h1>
        <hr>
        <div class="table-container">
            <div class="table-responsive">
                <table id="optionsVotesTable" class="table table-striped table-bordered" style="border: none">
                    <thead style="background-color: #31363F; color: white">
                        <tr>
                        <th style="padding: 12px;">Question</th>
                            <th style="padding: 12px;">Option</th>
                            <th style="padding: 12px;">Question Type</th>
                            <th style="padding: 12px;">Total Votes</th>
                            <th style="padding: 12px;">Average Limit Value</th> <!-- Updated column header -->
                        </tr>
                    </thead>
                    <tbody style="background-color: #ECEFF1;">
                        <?php foreach ($options_votes as $option_vote): ?>
                            <tr>
                            <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;">
                                    <?php echo htmlspecialchars($option_vote['question_text']); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;">
                                    <?php echo htmlspecialchars($option_vote['option_text']); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;">
                                    <?php echo htmlspecialchars($option_vote['question_type']); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;">
                                    <?php echo htmlspecialchars($option_vote['total_votes']); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;">
                                    <?php 
                                    if ($option_vote['question_type'] == 'multiple' || $option_vote['question_type'] == 'single') {
                                        echo 'N/A'; // Display 'N/A' for avg_limit_value for multiple and single questions
                                    } else {
                                        echo htmlspecialchars(number_format($option_vote['avg_limit_value'], 2)); 
                                    }
                                    ?>
                                </td>
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
            $('#optionsVotesTable').DataTable({
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