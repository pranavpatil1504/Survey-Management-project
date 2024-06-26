<?php
require_once '../../controllers/helpers/connect_to_database.php';

function getSurveyResults() {
    $conn = connect_to_database();

    // Query to get results based on question types
    $sql = "SELECT sq.question_text, sq.question_type, so.option_text, sr.limit_value, COUNT(sr.response_id) as vote_count
            FROM survey_responses sr
            LEFT JOIN survey_options so ON sr.option_id = so.option_id
            JOIN survey_questions sq ON sr.question_id = sq.question_id
            GROUP BY sq.question_id, sr.option_id, sr.limit_value
            ORDER BY sq.question_id, so.option_id";

    $result = $conn->query($sql);

    $survey_results = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $survey_results[] = $row;
        }
    }
    $conn->close();
    return $survey_results;
}

$survey_results = getSurveyResults();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Results</title>
    <link href="../../bootstrap/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Survey Results</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Option</th>
                    <th>Votes</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($survey_results as $result): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($result['question_text']); ?></td>
                        <td><?php echo htmlspecialchars($result['option_text']); ?></td>
                        <td>
                            <?php 
                                if ($result['question_type'] == 'limit') {
                                    echo htmlspecialchars($result['vote_count']); // For limit questions, the vote count indicates the number of times the limit value was set
                                } else {
                                    echo htmlspecialchars($result['vote_count']);
                                }
                            ?>
                        </td>
                        <td>
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
</body>
</html>
