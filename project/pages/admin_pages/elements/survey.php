<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_question') {
        $question_text = $_POST['question_text'];
        $question_type = $_POST['question_type'];
        $conn = connect_to_database();
        $sql = "INSERT INTO survey_questions (question_text, question_type) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $question_text, $question_type);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        // Redirect back to admin_dashboard.php?page=survey after adding question
        header("Location: ?page=survey");
        exit;
    }

    if ($_POST['action'] == 'add_option' && isset($_POST['question_id'])) {
        $question_id = $_POST['question_id'];
        $option_text = $_POST['option_text'];
        $conn = connect_to_database();
        $sql = "INSERT INTO survey_options (question_id, option_text) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $question_id, $option_text);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        // Redirect back to admin_dashboard.php?page=survey after adding option
        header("Location: ?page=survey");
        exit;
    }
}
?>

<div class="container">
    <form method="POST" action="?page=survey">
        <input type="hidden" name="action" value="add_question">
        <div class="form-group">
            <label for="question_text">Question Text</label>
            <input type="text" class="form-control" id="question_text" name="question_text" required>
        </div>
        <div class="form-group">
            <label for="question_type">Question Type</label>
            <select class="form-control" id="question_type" name="question_type" required>
                <option value="single">Single Choice</option>
                <option value="multiple">Multiple Choice</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Question</button>
    </form>

    <?php foreach ($survey_questions as $question): ?>
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($question['question_text']); ?></h5>
                <form method="POST" action="?page=survey">
                    <input type="hidden" name="action" value="add_option">
                    <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
                    <div class="form-group">
                        <label for="option_text_<?php echo $question['question_id']; ?>">Option Text</label>
                        <input type="text" class="form-control" id="option_text_<?php echo $question['question_id']; ?>" name="option_text" required>
                    </div>
                    <button type="submit" class="btn btn-secondary">Add Option</button>
                </form>

                <ul class="list-group mt-3">
                    <?php
                    $options = getSurveyOptions($question['question_id']);
                    foreach ($options as $option):
                    ?>
                        <li class="list-group-item"><?php echo htmlspecialchars($option['option_text']); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endforeach; ?>
</div>
