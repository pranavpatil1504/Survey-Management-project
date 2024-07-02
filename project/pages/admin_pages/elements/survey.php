<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the server method and action are set and valid
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $conn = connect_to_database();

    // Handle adding a new question
    if ($_POST['action'] == 'add_question') {
        $conn = connect_to_database();
        $question_text = $_POST['question_text'];
        $question_type = $_POST['question_type'];
        $sql = "INSERT INTO survey_questions (question_text, question_type) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $question_text, $question_type);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        header("Location: ?page=survey");
        exit;
    }

    // Handle editing an existing question
    if ($_POST['action'] == 'edit_question') {
        $conn = connect_to_database();
        $question_id = $_POST['question_id'];
        $question_text = $_POST['question_text'];
        $question_type = $_POST['question_type'];
        $sql = "UPDATE survey_questions SET question_text = ?, question_type = ? WHERE question_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $question_text, $question_type, $question_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        header("Location: ?page=survey");
        exit;
    }

    // Handle deleting a question
    if ($_POST['action'] == 'delete_question') {
        $conn = connect_to_database();
        $question_id = $_POST['question_id'];
        $sql = "DELETE FROM survey_questions WHERE question_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $stmt->close();
        // Also delete associated options
        $sql_options = "DELETE FROM survey_options WHERE question_id = ?";
        $stmt_options = $conn->prepare($sql_options);
        $stmt_options->bind_param("i", $question_id);
        $stmt_options->execute();
        $stmt_options->close();
        $conn->close();
        header("Location: ?page=survey");
        exit;
    }

    // Handle adding an option to a question
    if ($_POST['action'] == 'add_option' && isset($_POST['question_id'])) {
        $conn = connect_to_database();
        $question_id = $_POST['question_id'];
        $option_text = $_POST['option_text'];
        $sql = "INSERT INTO survey_options (question_id, option_text) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $question_id, $option_text);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        header("Location: ?page=survey");
        exit;
    }

    // Handle editing an existing option
    if ($_POST['action'] == 'edit_option') {
        $conn = connect_to_database();
        $option_id = $_POST['option_id'];
        $option_text = $_POST['option_text'];
        $sql = "UPDATE survey_options SET option_text = ? WHERE option_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $option_text, $option_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        header("Location: ?page=survey");
        exit;
    }

    // Handle deleting an option
    if ($_POST['action'] == 'delete_option') {
        $conn = connect_to_database();
        $option_id = $_POST['option_id'];
        $sql = "DELETE FROM survey_options WHERE option_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $option_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        header("Location: ?page=survey");
        exit;
    }
}

// Fetch and process survey questions
$survey_questions = getSurveyQuestions();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Management</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../../Bootstrap/bootstrap.min.css">
</head>

<body>
    <div class="container">
        <h1 class="mt-4 mb-4"><b>Survey Management</b></h1>
        <hr>
        </hr>

        <!-- Add Question Modal -->
        <div class="modal fade" id="addQuestionModal" tabindex="-1" role="dialog"
            aria-labelledby="addQuestionModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="?page=survey">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addQuestionModalLabel">Add Question</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="action" value="add_question">
                            <div class="form-group">
                                <label for="question_text">Question Text</label>
                                <input type="text" class="form-control" id="question_text" name="question_text"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="question_type">Question Type</label>
                                <select class="form-control" id="question_type" name="question_type" required>
                                    <option value="single">Single Choice</option>
                                    <option value="multiple">Multiple Choice</option>
                                    <option value="limit">Limit</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Question</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Question Modal -->
        <div class="modal fade" id="editQuestionModal" tabindex="-1" role="dialog"
            aria-labelledby="editQuestionModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="?page=survey">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editQuestionModalLabel">Edit Question</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="action" value="edit_question">
                            <input type="hidden" id="edit_question_id" name="question_id">
                            <div class="form-group">
                                <label for="edit_question_text">Question Text</label>
                                <input type="text" class="form-control" id="edit_question_text" name="question_text"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="edit_question_type">Question Type</label>
                                <select class="form-control" id="edit_question_type" name="question_type" required>
                                    <option value="single">Single Choice</option>
                                    <option value="multiple">Multiple Choice</option>
                                    <option value="limit">Limit</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Question Modal -->
        <div class="modal fade" id="deleteQuestionModal" tabindex="-1" role="dialog"
            aria-labelledby="deleteQuestionModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="?page=survey">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteQuestionModalLabel">Delete Question</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="action" value="delete_question">
                            <input type="hidden" id="delete_question_id" name="question_id">
                            <p>Are you sure you want to delete this question?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Option Modal -->
        <div class="modal fade" id="addOptionModal" tabindex="-1" role="dialog" aria-labelledby="addOptionModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="?page=survey">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addOptionModalLabel">Add Option</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="action" value="add_option">
                            <input type="hidden" id="add_option_question_id" name="question_id">
                            <div class="form-group">
                                <label for="option_text">Option Text</label>
                                <input type="text" class="form-control" id="option_text" name="option_text" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Option</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Option Modal -->
        <div class="modal fade" id="editOptionModal" tabindex="-1" role="dialog" aria-labelledby="editOptionModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="?page=survey">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editOptionModalLabel">Edit Option</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="action" value="edit_option">
                            <input type="hidden" id="edit_option_id" name="option_id">
                            <div class="form-group">
                                <label for="edit_option_text">Option Text</label>
                                <input type="text" class="form-control" id="edit_option_text" name="option_text"
                                    required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Option Modal -->
        <div class="modal fade" id="deleteOptionModal" tabindex="-1" role="dialog"
            aria-labelledby="deleteOptionModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="?page=survey">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteOptionModalLabel">Delete Option</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="action" value="delete_option">
                            <input type="hidden" id="delete_option_id" name="option_id">
                            <p>Are you sure you want to delete this option?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="mt-4">
            <!-- Add Question Button -->
            <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addQuestionModal"
                style="background-color:#343a40;border-color: #343a40">
                Add Question
            </button>

            <!-- List of Questions -->
            <?php foreach ($survey_questions as $question): ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($question['question_text']); ?></h5>
                        <form method="POST" action="?page=survey" class="d-inline-block">
                            <input type="hidden" name="action" value="delete_question">
                            <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
                            <button type="button" class="btn btn-danger" data-toggle="modal"
                                style="background-color:#FF000070; border:none; color: black; font-weight: 500"
                                data-target="#deleteQuestionModal"
                                data-question-id="<?php echo $question['question_id']; ?>">Delete Question</button>
                        </form>

                        <button type="button" class="btn btn-secondary ml-2" data-toggle="modal"
                            style="background-color: #9ADE7B; border: none; color: black ; font-weight: 500"
                            data-target="#editQuestionModal" data-question-id="<?php echo $question['question_id']; ?>"
                            data-question-text="<?php echo htmlspecialchars($question['question_text']); ?>"
                            data-question-type="<?php echo $question['question_type']; ?>">Edit Question</button>

                        <form method="POST" action="?page=survey" class="mt-3">
                            <input type="hidden" name="action" value="add_option">
                            <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
                            <button type="button" class="btn btn-secondary" data-toggle="modal"
                                style="background-color:#343a40;border-color: #343a40" data-target="#addOptionModal"
                                data-question-id="<?php echo $question['question_id']; ?>">Add
                                Option</button>
                        </form>

                        <ul class="list-group mt-3">
                            <?php foreach (getSurveyOptions($question['question_id']) as $option): ?>
                                <li class="list-group-item">
                                    <?php echo htmlspecialchars($option['option_text']); ?>
                                    <form method="POST" action="?page=survey" class="d-inline-block float-right ml-2">
                                        <input type="hidden" name="action" value="delete_option">
                                        <input type="hidden" name="option_id" value="<?php echo $option['option_id']; ?>">
                                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                            data-target="#deleteOptionModal"
                                            data-option-id="<?php echo $option['option_id']; ?>"
                                            style="background-color:#FF000070; border:none; color: black; font-weight: 500">Delete</button>
                                    </form>
                                    <button type="button" class="btn btn-secondary btn-sm float-right" data-toggle="modal"
                                        style="background-color: #9ADE7B; border: none; color: black ; font-weight: 500"
                                        data-target="#editOptionModal" data-option-id="<?php echo $option['option_id']; ?>"
                                        data-option-text="<?php echo htmlspecialchars($option['option_text']); ?>">Edit</button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="../../dependencies/jquery.slim.min.js"></script>
    <script src="../..dependencies/popper.min.js"></script>
    <script src="../../Bootstrap/bootstrap.min.js"></script>

    <!-- Script for handling modals -->
    <script>
        $(document).ready(function () {
            // Edit Question Modal
            $('#editQuestionModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var questionId = button.data('question-id');
                var questionText = button.data('question-text');
                var questionType = button.data('question-type');
                var modal = $(this);
                modal.find('#edit_question_id').val(questionId);
                modal.find('#edit_question_text').val(questionText);
                modal.find('#edit_question_type').val(questionType);
            });

            // Delete Question Modal
            $('#deleteQuestionModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var questionId = button.data('question-id');
                var modal = $(this);
                modal.find('#delete_question_id').val(questionId);
            });

            // Add Option Modal
            $('#addOptionModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var questionId = button.data('question-id');
                var modal = $(this);
                modal.find('#add_option_question_id').val(questionId);
            });

            // Edit Option Modal
            $('#editOptionModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var optionId = button.data('option-id');
                var optionText = button.data('option-text');
                var modal = $(this);
                modal.find('#edit_option_id').val(optionId);
                modal.find('#edit_option_text').val(optionText);
            });

            // Delete Option Modal
            $('#deleteOptionModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var optionId = button.data('option-id');
                var modal = $(this);
                modal.find('#delete_option_id').val(optionId);
            });
        });
    </script>

</body>

</html>