<?php
include_once '../../controllers/helpers/connect_to_database.php';
include_once '../../controllers/helpers/redirect_to_custom_error.php';
include_once '../../controllers/helpers/sanitize_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle clearing all questions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'clear_all_questions') {
    $conn = connect_to_database();
    try {
        // Delete all questions
        $sql = "DELETE FROM survey_questions";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->close();

        // Also delete all associated options
        $sql_options = "DELETE FROM survey_options";
        $stmt_options = $conn->prepare($sql_options);
        $stmt_options->execute();
        $stmt_options->close();
    } catch (Exception $e) {
        redirect_to_custom_error("Server Error", "Unable to connect server");
    }


    $conn->close();
    header("Location: ?page=survey_mgmt");
    exit;
}

// Handle clearing all Responses with deleting the users_submitted and compulsory question table row
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'clear_all_responses') {
    $conn = connect_to_database();
    try {
        // Delete all temprory responses
        $sql = "DELETE FROM temp_response_table";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->close();

        //delete all survey responses
        $sql_options = "DELETE FROM survey_responses";
        $stmt_options = $conn->prepare($sql_options);
        $stmt_options->execute();
        $stmt_options->close();

        // Delete the table of complques as well
        $sql_complques = "DELETE FROM complques";
        $stmt_complques = $conn->prepare($sql_complques);
        $stmt_complques->execute();
        $stmt_complques->close();

        // Delete the table of users_submitted as well
        $sql_users_submitted = "DELETE FROM users_submitted";
        $stmt_users_submitted = $conn->prepare($sql_users_submitted);
        $stmt_users_submitted->execute();
        $stmt_users_submitted->close();

        // Delete the table of personal_details as well
        $sql_personal_details = "DELETE FROM personal_details";
        $stmt_personal_details = $conn->prepare($sql_personal_details);
        $stmt_personal_details->execute();
        $stmt_personal_details->close();
    } catch (Exception $e) {
        redirect_to_custom_error("Server Error", "Unable to connect server");
    }

    $conn->close();
    header("Location: ?page=survey_mgmt");
    exit;
}
// Ensure the server method and action are set and valid
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    try {
        $conn = connect_to_database();

        // Handle adding a new question
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_question') {
            $question_text = $_POST['question_text'];
            $question_text = sanitize_string($question_text);
            $question_type = $_POST['question_type'];
            $other_included = isset($_POST['add_other_checkbox']) ? 1 : 0; // Check if the checkbox is set

            $conn = connect_to_database();
            $sql = "INSERT INTO survey_questions (question_text, question_type, other_included) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $question_text, $question_type, $other_included);

            if ($stmt->execute()) {
                header("Location: ?page=survey_mgmt");
                exit;
            } else {
                echo "Error: " . $conn->error;
            }

            $stmt->close();
            $conn->close();
        }

        // Handle editing an existing question
        if ($_POST['action'] == 'edit_question') {
            $conn = connect_to_database();
            $question_id = $_POST['question_id'];
            $question_text = $_POST['question_text'];
            $question_text = sanitize_string($question_text);
            $question_type = $_POST['question_type'];
            $sql = "UPDATE survey_questions SET question_text = ?, question_type = ? WHERE question_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $question_text, $question_type, $question_id);
            $stmt->execute();
            $stmt->close();
            $conn->close();
            header("Location: ?page=survey_mgmt");
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
            header("Location: ?page=survey_mgmt");
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
            header("Location: ?page=survey_mgmt");
            exit;
        }

        // Handle editing an existing option
        if ($_POST['action'] == 'edit_option') {
            $conn = connect_to_database();
            $option_id = $_POST['option_id'];
            $option_text = $_POST['option_text'];
            $option_text = sanitize_string($option_text);
            $sql = "UPDATE survey_options SET option_text = ? WHERE option_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $option_text, $option_id);
            $stmt->execute();
            $stmt->close();
            $conn->close();
            header("Location: ?page=survey_mgmt");
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
            header("Location: ?page=survey_mgmt");
            exit;
        }
    } catch (Exception $e) {
        redirect_to_custom_error("Server Error", "Unable to connect server");
        ;
    }
}

// Fetch and process survey_mgmt questions
$survey_questions = getSurveyQuestions();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Management</title>
    <link rel="stylesheet" href="../../bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../../dependencies/jquery.dataTables.min.css">
    <style>
        /* Apply grey color when the checkbox or select element is disabled */
        .form-check-input:disabled+.form-check-label {
            color: grey;
        }

        select:disabled,
        select option:disabled {
            color: grey;
        }
    </style>
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
                    <form method="POST" action="?page=survey_mgmt">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addQuestionModalLabel">Add Question</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                style="border: none; font-weight: 500;">
                                <span aria-hidden="true" style="color: red">&times;</span>
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
                                    <option value="string">String</option>
                                    <!-- Added option for string type -->
                                </select>
                            </div>
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="add_other_checkbox"
                                    name="add_other_checkbox">
                                <label class="form-check-label" for="add_other_checkbox">Other</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"
                                style="background-color: rgb(148, 225, 255); border: none; color: black; font-weight: 500;">Add
                                Question</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Clear All Questions Modal -->
        <div class="modal fade" id="clearAllQuestionModal" tabindex="-1" role="dialog"
            aria-labelledby="clearAllQuestionModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="?page=survey_mgmt">
                        <input type="hidden" name="action" value="clear_all_questions">
                        <div class="modal-header">
                            <h5 class="modal-title" id="clearAllQuestionModalLabel">Clear All Questions</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                style="border: none; font-weight: 500;">
                                <span aria-hidden="true" style="color: red">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Do you want to surely clear all the questions?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"
                                style="background-color: #FF000070; border: none; color: black; font-weight: 500;">
                                Clear All Questions
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Clear All Responses Modal -->
        <div class="modal fade" id="clearAllResponsesModal" tabindex="-1" role="dialog"
            aria-labelledby="clearAllResponsesModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="?page=survey_mgmt">
                        <input type="hidden" name="action" value="clear_all_responses">
                        <div class="modal-header">
                            <h5 class="modal-title" id="clearAllResponsesModalLabel">Clear All Responses</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                style="border: none; font-weight: 500;">
                                <span aria-hidden="true" style="color: red">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Do you want to surely clear all the responses?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"
                                style="background-color: #FF000070; border: none; color: black; font-weight: 500;">
                                Clear All Responses
                            </button>
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
                    <form method="POST" action="?page=survey_mgmt">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editQuestionModalLabel">Edit Question
                            </h5>
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
                                    <option value="string">String</option>
                                </select>
                            </div>
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="add_other_checkbox"
                                    name="add_other_checkbox">
                                <label class="form-check-label" for="add_other_checkbox">Other</label>
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
                    <form method="POST" action="?page=survey_mgmt">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteQuestionModalLabel">Delete
                                Question</h5>
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
                    <form method="POST" action="?page=survey_mgmt">
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
                    <form method="POST" action="?page=survey_mgmt">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editOptionModalLabel">Edit</h5>
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
                    <form method="POST" action="?page=survey_mgmt">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteOptionModalLabel">Delete</h5>
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
            <button type="button" class="btn  mb-3" data-toggle="modal" data-target="#addQuestionModal"
                style=" background-color: rgb(148, 225, 255);border: none; color: black; font-weight: 500;">
                Add Question
            </button>

            <!-- Clear All Questions Button -->
            <form method="POST" action="?page=survey_mgmt" class="d-inline-block">
                <input type="hidden" name="action" value="clear_all_questions">
                <button type="button" class="btn btn-danger mb-3" data-target="#clearAllQuestionModal"
                    data-toggle="modal"
                    style="background-color: #FF000070; border: none; color: black; font-weight: 500;">
                    Clear All Questions
                </button>
            </form>

            <form method="POST" action="?page=survey_mgmt" class="d-inline-block">
                <input type="hidden" name="action" value="clear_all_responses">
                <button type="button" class="btn btn-danger mb-3" data-target="#clearAllResponsesModal"
                    data-toggle="modal"
                    style="background-color: #FF000070; border: none; color: black; font-weight: 500;">
                    Clear All Responses
                </button>
            </form>

            <!-- List of Questions -->
            <div class="table table-responsive table-bordered" style="border: none">
                <table id="surveyQuestionsTable" class="table table-striped">
                    <thead style="background-color: #31363F; color: white">
                        <tr>
                            <th style="padding: 12px">Question ID</th>
                            <th style="padding: 12px">Question Text</th>
                            <th style="padding: 12px">Question Type</th>
                            <th style="padding: 12px">Options</th>
                            <th style="padding: 12px">Actions</th>
                        </tr>
                    </thead>
                    <tbody style="background-color: #ECEFF1">
                        <?php foreach ($survey_questions as $question): ?>
                            <tr>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px">
                                    <b><?php echo $question['question_id']; ?></b>
                                </td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px">
                                    <b><?php echo htmlspecialchars($question['question_text']); ?></b>
                                </td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px">
                                    <b><?php echo htmlspecialchars($question['question_type']); ?></b>
                                </td>
                                <td style="background-color: #FFFFFF; color: #31363F">
                                    <button type="button" class="btn btn-md options-btn" data-toggle="modal"
                                        style=" background-color: rgb(148, 225, 255);border: none; color: black; font-weight: 500;"
                                        data-target="#addOptionModal"
                                        data-question-id="<?php echo $question['question_id']; ?>">Add
                                        Options</button>
                                    <hr>
                                    <?php foreach (getSurveyOptions($question['question_id']) as $option): ?>
                                        <li style="list-style: none">
                                            <b><?php echo htmlspecialchars($option['option_text']); ?></b>
                                            <br>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-primary btn-sm edit-option-btn"
                                                    style=" margin-right: 5px; border-radius: 6px; width: 50px ; background-color: #9ADE7B; border: none; color: black; font-weight: 500;"
                                                    data-toggle="modal" data-target="#editOptionModal"
                                                    data-option-id="<?php echo $option['option_id']; ?>"
                                                    data-option-text="<?php echo htmlspecialchars($option['option_text']); ?>">Edit
                                                </button>

                                                <button type="button" class="btn btn-danger btn-sm delete-option-btn"
                                                    style=" border-radius: 5px;  width: 60px; background-color: #FF000070; border: none; color: black; font-weight: 500;"
                                                    data-toggle="modal" data-target="#deleteOptionModal"
                                                    data-option-id="<?php echo $option['option_id']; ?>">Delete
                                                </button>
                                            </div>
                                        </li>
                                        <hr>
                                    <?php endforeach; ?>
                                </td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px">
                                    <form method="POST" action="?page=survey_mgmt" class="d-inline-block">
                                        <input type="hidden" name="action" value="delete_question">
                                        <input type="hidden" name="question_id"
                                            value="<?php echo $question['question_id']; ?>">
                                        <button type="button" class="btn btn-danger btn-md" data-toggle="modal"
                                            style=" border-radius: 5px ; margin-bottom: 5px; background-color: #FF000070; border: none; color: black; font-weight: 500;"
                                            data-target="#deleteQuestionModal"
                                            data-question-id="<?php echo $question['question_id']; ?>">Delete
                                            Question</button>
                                    </form>
                                    <button type="button" class="btn btn-primary btn-md edit-question-btn"
                                        style=" border-radius: 5px;  width: 140px; background-color: #9ADE7B; border: none; color: black; font-weight: 500; "
                                        data-toggle="modal" data-target="#editQuestionModal"
                                        data-question-id="<?php echo $question['question_id']; ?>"
                                        data-question-text="<?php echo htmlspecialchars($question['question_text']); ?>"
                                        data-question-type="<?php echo htmlspecialchars($question['question_type']); ?>">Edit
                                        Question</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script src="../../dependencies/jquery.slim.min.js"></script>
        <script src="../../dependencies/popper.min.js"></script>
        <script src="../../bootstrap/bootstrap.min.js"></script>
        <script src="../../dependencies/jquery.dataTables.min.js"></script>

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

                // Clear All Questions Modal
                $('#clearAllQuestionModal').on('show.bs.modal', function (event) {
                    var button = $(event.relatedTarget); // Button that triggered the modal
                    var modal = $(this);
                    modal.find('.modal-body p').text('Do you want to surely clear all the questions?');
                });
                $('#clearAllResponsesModal').on('show.bs.modal', function (event) {
                    var button = $(event.relatedTarget); // Button that triggered the modal
                    var modal = $(this);
                    modal.find('.modal-body p').text('Do you want to surely clear all the Responses?');
                });
            });

            $(document).ready(function () {
                $('#surveyQuestionsTable').DataTable({
                    "paging": true, // Enable paging
                    "lengthChange": true, // Enable number of records per page dropdown
                    "searching": true, // Enable search box
                    "ordering": true, // Enable sorting
                    "info": true, // Show 'Showing x to x of x entries' information
                    "autoWidth": false, // Disable auto width calculation
                    "responsive": true // Enable responsive design
                });
            });
        </script>
</body>

</html>