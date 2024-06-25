<?php
require_once '../../controllers/helpers/connect_to_database.php';

function getSurveyResults() {
    $conn = connect_to_database();

    $sql = "SELECT sq.question_text, so.option_text, COUNT(*) as vote_count
            FROM survey_responses sr
            JOIN survey_options so ON sr.option_id = so.option_id
            JOIN survey_questions sq ON so.question_id = sq.question_id
            GROUP BY so.option_id";

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
                </tr>
            </thead>
            <tbody>
                <?php foreach ($survey_results as $result): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($result['question_text']); ?></td>
                        <td><?php echo htmlspecialchars($result['option_text']); ?></td>
                        <td><?php echo $result['vote_count']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
