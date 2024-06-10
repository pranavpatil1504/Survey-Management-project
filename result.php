<?php
include 'database.php';

$correct_answers = 0;
$total_questions = count($questions);
$results = [];

foreach ($_POST['answer'] as $key => $selected_option) {
    $correct_answer = $questions[$key]['correct_answer'];
    $results[] = [
        'question' => $questions[$key]['question'],
        'selected_option' => $selected_option,
        'correct_answer' => $correct_answer,
        'is_correct' => $selected_option === $correct_answer
    ];

    if ($selected_option === $correct_answer) {
        $correct_answers++;
    }
}

$score = ($correct_answers / $total_questions) * 100;

usort($results, function ($a, $b) {
    return $b['is_correct'] - $a['is_correct'];
});
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5">
        <h1 class="mb-4 text-center">Quiz Result</h1>
        <h2 class="text-center">Score: <?php echo number_format($score, 2); ?>%</h2>

        <div class="list-group mt-4">
            <?php foreach ($results as $result): ?>
                <div class="list-group-item <?php echo $result['is_correct'] ? 'list-group-item-success' : 'list-group-item-danger'; ?>">
                    <p><strong>Question:</strong> <?php echo $result['question']; ?></p>
                    <p><strong>Your Answer:</strong> <?php echo $result['selected_option']; ?></p>
                    <p><strong>Correct Answer:</strong> <?php echo $result['correct_answer']; ?></p>
                    <p><strong>Result:</strong> <?php echo $result['is_correct'] ? 'Correct' : 'Incorrect'; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>

</html>
