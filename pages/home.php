<?php
include '../controllers/helpers/connect_to_database.php';

// Check if session data is provided
if (isset($_GET['session_data'])) {

    $session_data = $_GET['session_data'];

    // Decode session data
    $decoded_data = json_decode(base64_decode($session_data), true);

    // Extract session variables
    $session_token = $decoded_data['session_token'];
    $expiration_time = $decoded_data['expiration_time'];
    $username = $decoded_data['username'];

    // Connect to the database
    $db = connect_to_database();

    // Check if the username exists in users table
    $check_user_sql = "SELECT * FROM users WHERE username='$username'";
    $user_result = $db->query($check_user_sql);

    if ($user_result->num_rows > 0) {
        try{
            // Validate the session token and expiration time
            $check_session_sql = "SELECT * FROM user_session_token WHERE username='$username' AND session_token='$session_token' AND expiration_time='$expiration_time'";
            $check_session_result = $db->query($check_session_sql);

            if ($check_session_result->num_rows > 0) {
                // Check if session has expired
                if (time() > $expiration_time) {
                    header("Location: signin.php");
                    exit();
                }

                // Start session
                session_start();
                $_SESSION['session_token'] = $session_token;
                $_SESSION['expiration_time'] = $expiration_time;
                $_SESSION['username'] = $username;

                // Check if user has already submitted the survey
                $check_user_submission = "SELECT * FROM users_submitted WHERE username='$username'";
                $user_submission_result = $db->query($check_user_submission);
                if ($user_submission_result->num_rows > 0) {
                    header("Location: errors.php");
                    exit();
                }

                // Welcome message
                $welcome_message = "Welcome, $username!";
            } else {
                echo "Session data not found.";
                header("Location: signin.php");
                exit();
            }
        }catch(Exception $e){
            redirect_to_custom_error("Server Error","Unable to connect to server");
        }
    } else {
        echo "Username not found.";
        header("Location: signin.php");
        exit();
    }
} else {
    echo "Session data not provided.";
    header("Location: signin.php");
    exit();
}


// Function to fetch survey questions and options
function getSurvey()
{
    $conn = connect_to_database();

    $sql = "SELECT q.question_id, q.question_text, q.question_type, q.other_included, o.option_id, o.option_text 
            FROM survey_questions q 
            LEFT JOIN survey_options o ON q.question_id = o.question_id 
            ORDER BY q.question_id, o.option_id";
    $result = $conn->query($sql);

    $survey = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $question_id = $row['question_id'];
            if (!isset($survey[$question_id])) {
                $survey[$question_id] = [
                    'question_text' => $row['question_text'],
                    'question_type' => $row['question_type'],
                    'other_included' => $row['other_included'],
                    'options' => []
                ];
            }
            $survey[$question_id]['options'][] = ['option_id' => $row['option_id'], 'option_text' => $row['option_text']];
        }
    }
    $conn->close();
    return $survey;
}

// Fetch survey questions and options
$survey = getSurvey();

// Function to restore responses from temporary table
function restoreTempResponses($username){
    $conn = connect_to_database();

    // Query to retrieve responses from temp_response_table
    $sql_temp_responses = "SELECT tr.username, tr.question_id, tr.option_id, tr.limit_value, tr.other_response, tr.string_response, sq.question_text, sq.question_type, sq.other_included
                           FROM temp_response_table tr
                           INNER JOIN survey_questions sq ON tr.question_id = sq.question_id
                           WHERE tr.username = '$username'";
    $result = $conn->query($sql_temp_responses);

    // Array to store restored responses
    $restored_responses = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $question_id = $row['question_id'];

            // Initialize response_value and option_id based on question type
            $response_value = null;
            $option_id = null;

            if ($row['question_type'] == 'single' || $row['question_type'] == 'multiple') {
                // If option_id already exists in the array, treat as array for multiple options
                if (isset($restored_responses[$question_id]['option_id'])) {
                    // Append the new option_id to the existing array
                    $restored_responses[$question_id]['option_id'][] = $row['option_id'];
                } else {
                    // Initialize or overwrite the option_id as an array for multiple selections
                    $restored_responses[$question_id] = [
                        'question_text' => $row['question_text'],
                        'question_type' => $row['question_type'],
                        'other_included' => $row['other_included'],
                        'response_value' => $row['option_id'], // Set initial response_value
                        'option_id' => [$row['option_id']] // Initialize option_id as an array
                    ];
                }
            } elseif ($row['question_type'] == 'limit') {
                // Check if the question_id already exists in $restored_responses
                if (isset($restored_responses[$question_id])) {
                    // Append the new limit value to the existing array
                    $restored_responses[$question_id]['response_value'][$row['option_id']] = $row['limit_value'];
                } else {
                    // Create a new entry for limit type question
                    $restored_responses[$question_id] = [
                        'question_text' => $row['question_text'],
                        'question_type' => $row['question_type'],
                        'other_included' => $row['other_included'],
                        'response_value' => [$row['option_id'] => $row['limit_value']], // Initialize as an associative array
                    ];
                }
            } elseif ($row['question_type'] == 'string') {
                $response_value = $row['string_response'];
            }

            // For single and multiple type questions, if not already set for limit type
            if ($response_value !== null && !isset($restored_responses[$question_id]['response_value'])) {
                // Store or overwrite the response_value and option_id
                $restored_responses[$question_id] = [
                    'question_text' => $row['question_text'],
                    'question_type' => $row['question_type'],
                    'other_included' => $row['other_included'],
                    'response_value' => $response_value,
                    'option_id' => $option_id // Include option_id here
                ];
            }
        }
    }

    // Debugging
    // echo "<pre>";
    // echo "Debugging Output for restoreTempResponses:\n";
    // echo "Username: $username\n";
    // echo "Restored Responses:\n";
    // print_r($restored_responses);
    // echo "</pre>";

    $conn->close();
    return $restored_responses;
}



// Restore responses from temp_response_table for the current user
$restored_responses = restoreTempResponses($username);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link href="../bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="../bootstrap/bootstrap-icons.min.css" rel="stylesheet">
    <script src="../dependencies/jquery.slim.min.js"></script>
    <script src="../dependencies/popper.min.js"></script>
    <script src="../bootstrap/bootstrap.min.js"></script>
    <style>
        ::-webkit-scrollbar {
            width: 0;
            height: 0;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: transparent;
        }
        .range-labels {
    display: flex;
    justify-content: space-between;
    margin-top: 5px;
}

.custom-range {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 10px;
    background: #ddd; /* Track color */
    outline: none;
    border-radius: 5px;
    margin-top: 5px;
}

.custom-range::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    background: #007bff; /* Thumb color */
    border-radius: 50%;
    cursor: pointer;
}

.range-labels {
    display: flex;
    justify-content: space-between;
    margin-top: 5px;
}

.range-labels span {
    font-size: 12px; /* Adjust as needed */
}
.custom-cell {
            background-color: #FFFFFF; 
            color: #31363F; 
            padding: 15px; 
            text-align: center; 
            border: 1px solid #CFD8DC; 
            border-radius: 5px; 
            font-size: 16px; 
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
            transition: background-color 0.3s ease;
        }
        .custom-cell:hover {
            background-color: #F5F5F5;
        }
    </style>
</head>
<body style="background-color:#C6DCBA;">
<div class="container">
    <div id="surveyCarousel" class="carousel slide" data-ride="carousel" data-interval="false" style="margin: 90px auto">
        <div class="carousel-inner custom-container" style="background-color: #fff; border-radius: 10px">
            <?php $index = 0; ?>
            <?php foreach ($survey as $question_id => $question) : ?>
                <div class="carousel-item <?php if ($index == 0) echo 'active'; ?>">
                    <div class="card custom-card " style="border: none; border-radius: 10px">
                        <div class="card-body col-lg-12 col-xl-12 col-xs-10">
                            <h5 class="card-title custom-card-title"><?php echo $question['question_text']; ?></h5>
                            <hr style="border: 1.7px solid grey; border-radius: 2px">
                            <form class="surveyForm" method="POST">
                                <input type="hidden" name="question_id[<?php echo $question_id; ?>]" value="<?php echo $question_id; ?>">
                                <?php if ($question['question_type'] == 'single') : ?>
                                    <?php foreach ($question['options'] as $option) : ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="option_<?php echo $option['option_id']; ?>" name="response[<?php echo $question_id; ?>]" value="<?php echo $option['option_id']; ?>" <?php
                                                if (isset($restored_responses[$question_id]) && isset($restored_responses[$question_id]['response_value']) && $restored_responses[$question_id]['response_value'] == $option['option_id'])
                                                    echo 'checked';
                                            ?> required>
                                            <label class="form-check-label custom-form-check-label" for="option_<?php echo $option['option_id']; ?>"><?php echo $option['option_text']; ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if ($question['other_included'] == 1) : ?>
                                        <label class="mt-2">Other (please specify)</label>
                                        <textarea type="text" class="form-control" id="other_text_<?php echo $question_id; ?>" name="other_text[<?php echo $question_id; ?>]" rows="3" style="max-height: 100px; border-color: black; border-radius: 10px"><?php
                                            if (isset($restored_responses[$question_id]) && isset($restored_responses[$question_id]['other_response']))
                                                echo htmlspecialchars($restored_responses[$question_id]['other_response']);
                                        ?></textarea>
                                    <?php endif; ?>
                                <?php elseif ($question['question_type'] == 'multiple') : ?>
                                    <?php foreach ($question['options'] as $option) : ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="option_<?php echo $option['option_id']; ?>" name="response[<?php echo $question_id; ?>][]" value="<?php echo $option['option_id']; ?>" <?php
                                                if (isset($restored_responses[$question_id]) && is_array($restored_responses[$question_id]['response_value']) && in_array($option['option_id'], $restored_responses[$question_id]['response_value']))
                                                    echo 'checked';
                                            ?>>
                                            <label class="form-check-label custom-form-check-label" for="option_<?php echo $option['option_id']; ?>"><?php echo $option['option_text']; ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if ($question['other_included'] == 1) : ?>
                                        <label class="mt-2">Other (please specify)</label>
                                        <textarea type="text" class="form-control" id="other_text_<?php echo $question_id; ?>" name="other_text[<?php echo $question_id; ?>]" rows="3" style="max-height: 100px; border-color: black; border-radius: 10px"><?php
                                            if (isset($restored_responses[$question_id]) && isset($restored_responses[$question_id]['other_response']))
                                                echo htmlspecialchars($restored_responses[$question_id]['other_response']);
                                        ?></textarea>
                                    <?php endif; ?>
                                <?php elseif ($question['question_type'] == 'limit') : ?>
                                    <table class="table table-responsive-xl">
                                        <tbody>
                                            <tr>
                                                <td class="custom-cell">Don’t know</td>
                                                <td class="custom-cell">Not satisfied at all</td>
                                                <td class="custom-cell">Very dissatisfied</td>
                                                <td class="custom-cell">Neither satisfied nor dissatisfied</td>
                                                <td class="custom-cell">Fairly Satisfied</td>
                                                <td class="custom-cell">Very Satisfied</td>
                                            </tr>
                                            <tr>
                                                <td class="custom-cell">0</td>
                                                <td class="custom-cell">2</td>
                                                <td class="custom-cell">4</td>
                                                <td class="custom-cell">6</td>
                                                <td class="custom-cell">8</td>
                                                <td class="custom-cell">10</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <?php foreach ($question['options'] as $option) : ?>
                                        <div class="form-group">
                                            <label class="custom-form-check-label"><?php echo $option['option_text']; ?></label>
                                            <input type="range" class="custom-range form-control-range" id="option_<?php echo $option['option_id']; ?>" name="response[<?php echo $question_id; ?>][<?php echo $option['option_id']; ?>]" required min="0" max="10" step="2" >
                                            <div class="range-labels">
                                                <span class="label-start">0</span>
                                                <span class="label-middle">2</span>
                                                <span class="label-middle">4</span>
                                                <span class="label-middle">6</span>
                                                <span class="label-middle">8</span>
                                                <span class="label-end">10</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if ($question['other_included'] == 1) : ?>
                                        <label class="mt-2">Other (please specify)</label>
                                        <textarea type="text" class="form-control" id="other_text_<?php echo $question_id; ?>" name="other_text[<?php echo $question_id; ?>]" rows="3" style="max-height: 100px; border-color: black; border-radius: 10px"><?php
                                            if (isset($restored_responses[$question_id]) && isset($restored_responses[$question_id]['other_response']))
                                                echo htmlspecialchars($restored_responses[$question_id]['other_response']);
                                        ?></textarea>
                                    <?php endif; ?>
                                <?php elseif ($question['question_type'] == 'string') : ?>
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="response_<?php echo $question_id; ?>" name="response[<?php echo $question_id; ?>]" required value="<?php
                                            if (isset($restored_responses[$question_id]) && isset($restored_responses[$question_id]['response_value']))
                                                echo htmlspecialchars($restored_responses[$question_id]['response_value']);
                                        ?>">
                                        <?php if ($question['other_included'] == 1) : ?>
                                            <label class="mt-2">Other (please specify)</label>
                                            <input type="text" class="form-control" id="other_text_<?php echo $question_id; ?>" name="other_text[<?php echo $question_id; ?>]" value="<?php
                                                if (isset($restored_responses[$question_id]) && isset($restored_responses[$question_id]['other_response']))
                                                    echo htmlspecialchars($restored_responses[$question_id]['other_response']);
                                            ?>">
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between mt-3">
                                    <?php if ($index > 0) : ?>
                                        <button class="btn btn-secondary prevBtn" type="button" data-target="#surveyCarousel" data-slide="prev">Previous</button>
                                    <?php elseif ($index == 0) : ?>
                                        <a href="complques.php" class="btn btn-secondary">Previous</a>
                                    <?php endif; ?>
                                    <?php if ($index < count($survey) - 1) : ?>
                                        <button class="btn btn-primary nextBtn" type="button" data-target="#surveyCarousel" data-slide="next">Next</button>
                                    <?php else : ?>
                                        <button class="btn btn-primary submitBtn" type="button" data-toggle="modal" data-target="#surveyModal">Submit</button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php $index++; ?>
            <?php endforeach; ?>
        </div>
    </div>


    <!-- Addn details Modal -->
    <div class="modal fade" id="surveyModal" tabindex="-1" aria-labelledby="surveyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="surveyModalLabel">Further Information</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="modalForm" action="personal_details.php" method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="libraryFeedback">Kindly share your thoughts regarding improvements needed in the Library:</label>
                                    <textarea class="form-control" id="libraryFeedback" name="libraryFeedback" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="userDesignation">Designation:</label>
                                    <input type="text" class="form-control" id="userDesignation" name="userDesignation">
                                </div>
                                <div class="form-group">
                                    <label for="userDivision">Division:</label>
                                    <input type="text" class="form-control" id="userDivision" name="userDivision">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="userTel">Tel Number.:</label>
                                    <input type="tel" class="form-control" id="userTel" name="userTel">
                                </div>
                                <div class="form-group">
                                    <label for="userEmail">Official Email:</label>
                                    <input type="email" class="form-control" id="userEmail" name="userEmail">
                                </div>
                                <div class="form-group">
                                    <label for="userInterests">Areas of Interest:</label>
                                    <textarea class="form-control" id="userInterests" name="userInterests" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted" style=" font-size: small">Your personal details will not be disclosed in any kind of document if published.</p>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Message Modal -->
    <div class="modal fade" id="submitMessageModal" tabindex="-1" role="dialog"
        aria-labelledby="submitMessageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="tick-animation mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none"
                            stroke="#5cb85c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-check-circle">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="16 8 10 14 8 12"></polyline>
                        </svg>
                    </div>
                    <p class="submitMessageModalLabel"><b>Thank You for your Great Response.</b></p>
                    <button class="submitMessageModalLabel btn btn-md btn-danger" style="border-radius:5px; width:62px" onclick="destroySessionAndRedirect()">
                        <a href="signin.php" style="text-decoration: none; color: white">Close</a>
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.nextBtn').forEach(function(button) {
            button.addEventListener('click', function() {
                var form = this.closest('form');
                var formData = new FormData(form);

                fetch('submit_survey.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        console.log(data);
                        $('#surveyCarousel').carousel('next');
                    })
                    .catch(error => console.error('Error:', error));
            });
        });

    document.querySelectorAll('.prevBtn').forEach(function(button) {
        button.addEventListener('click', function() {
            $('#surveyCarousel').carousel('prev');
        });
    });

    // Handle Addn Information modal to fade out and thank you modal to appear
    document.querySelector('#modalSubmitBtn').addEventListener('click', function(event) {
        event.preventDefault(); 
        var form = document.querySelector('#modalForm');
        var formData = new FormData(form);
        fetch('personal_details.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                $('#surveyModal').modal('hide');
                $('#submitMessageModal').modal('show'); 
            } else {
                console.error('Error:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    document.querySelectorAll('.submitBtn').forEach(function(button) {
        button.addEventListener('click', function(event) {
            var form = this.closest('form');
            var formData = new FormData(form);

            fetch('submit_survey.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    console.log(data);
                    $('#surveyModal').modal('show');
                })
                .catch(error => console.error('Error:', error));
            event.preventDefault();
            });
        });
    });


    $(document).ready(function() {
        <?php foreach ($restored_responses as $question_id => $response) : ?>
            <?php if ($response['question_type'] == 'single') : ?>
                $('input[name="response[<?php echo $question_id; ?>]"][value="<?php echo $response['response_value']; ?>"]').prop('checked', true);
            <?php elseif ($response['question_type'] == 'multiple') : ?>
                <?php foreach ($response['option_id'] as $option_id) : ?>
                    $('input[name="response[<?php echo $question_id; ?>][]"][value="<?php echo $option_id; ?>"]').prop('checked', true);
                <?php endforeach; ?>
            <?php elseif ($response['question_type'] == 'limit') : ?>
                <?php foreach ($response['response_value'] as $option_id => $value) : ?>
                    $('#option_<?php echo $option_id; ?>').val('<?php echo $value; ?>');
                <?php endforeach; ?>
            <?php elseif ($response['question_type'] == 'string') : ?>
                $('#response_<?php echo $question_id; ?>').val('<?php echo $response['response_value']; ?>');
            <?php endif; ?>
        <?php endforeach; ?>
    });


</script>
            
</body>

</html>