<?php
include '../controllers/helpers/connect_to_database.php';

if (isset($_GET['session_data'])) {
    $session_data = $_GET['session_data'];

    $decoded_data = json_decode(base64_decode($session_data), true);

    $session_token = $decoded_data['session_token'];
    $expiration_time = $decoded_data['expiration_time'];
    $username = $decoded_data['username'];

    $db = connect_to_database();
    $check_user_sql = "SELECT * FROM users WHERE username='$username'";
    $user_result = $db->query($check_user_sql);

    if ($user_result->num_rows > 0) {
        $check_session_sql = "SELECT * FROM user_session_token WHERE username='$username' AND session_token='$session_token' AND expiration_time='$expiration_time'";
        $check_session_result = $db->query($check_session_sql);

        if ($check_session_result->num_rows > 0) {
            if (time() > $expiration_time) {
                header("Location: signin.php");
                exit();
            }

            session_start();
            $_SESSION['session_token'] = $session_token;
            $_SESSION['expiration_time'] = $expiration_time;
            $_SESSION['username'] = $username;

            $welcome_message = "Welcome, $username!";
        } else {
            echo "Session data not found.";
            header("Location: signin.php");
            exit();
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

function getSurvey() {
    $conn = connect_to_database();
    $sql = "SELECT q.question_id, q.question_text, q.question_type, o.option_id, o.option_text 
            FROM survey_questions q 
            LEFT JOIN survey_options o ON q.question_id = o.question_id 
            ORDER BY q.question_id, o.option_id";
    $result = $conn->query($sql);

    $survey = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $survey[$row['question_id']]['question_text'] = $row['question_text'];
            $survey[$row['question_id']]['question_type'] = $row['question_type'];
            $survey[$row['question_id']]['options'][] = ['option_id' => $row['option_id'], 'option_text' => $row['option_text']];
        }
    }
    $conn->close();
    return $survey;
}

$survey = getSurvey();
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
</head>

<body>
    <div class="container mt-5">
        <div id="surveyCarousel" class="carousel slide" data-ride="carousel" data-interval="false">
            <div class="carousel-inner">
                <?php $index = 0; ?>
                <?php foreach ($survey as $question_id => $question): ?>
                    <div class="carousel-item <?php if ($index == 0) echo 'active'; ?>">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $question['question_text']; ?></h5>
                                <form class="surveyForm" method="POST">
                                    <?php if ($question['question_type'] == 'single'): ?>
                                        <?php foreach ($question['options'] as $option): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="response[<?php echo $question_id; ?>]" value="<?php echo $option['option_id']; ?>" required>
                                                <label class="form-check-label"><?php echo $option['option_text']; ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php elseif ($question['question_type'] == 'multiple'): ?>
                                        <?php foreach ($question['options'] as $option): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="response[<?php echo $question_id; ?>][]" value="<?php echo $option['option_id']; ?>">
                                                <label class="form-check-label"><?php echo $option['option_text']; ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php elseif ($question['question_type'] == 'limit'): ?>
                                        <?php foreach ($question['options'] as $option): ?>
                                            <div class="form-group">
                                                <label><?php echo $option['option_text']; ?></label>
                                                <input type="number" class="form-control-range" name="response[<?php echo $question_id; ?>][<?php echo $option['option_id']; ?>]" required min="0" max="10">
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <div class="d-flex justify-content-between mt-3">
                                        <?php if ($index > 0): ?>
                                            <button class="btn btn-secondary prevBtn" type="button" data-target="#surveyCarousel" data-slide="prev">Previous</button>
                                        <?php endif; ?>
                                        <?php if ($index < count($survey) - 1): ?>
                                            <button class="btn btn-primary nextBtn" type="button">Next</button>
                                        <?php else: ?>
                                            <button class="btn btn-primary submitBtn" type="submit">Submit</button>
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
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Handle Next button click
            document.querySelectorAll('.nextBtn').forEach(function (button) {
                button.addEventListener('click', function () {
                    var form = this.closest('form');
                    var formData = new FormData(form);

                    fetch('submit_survey.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        console.log(data);
                        // Move to the next slide
                        $('#surveyCarousel').carousel('next');
                    })
                    .catch(error => console.error('Error:', error));
                });
            });

            // Handle Previous button click
            document.querySelectorAll('.prevBtn').forEach(function (button) {
                button.addEventListener('click', function () {
                    // Move to the previous slide
                    $('#surveyCarousel').carousel('prev');
                });
            });

            // Handle Submit button click
            document.querySelectorAll('.submitBtn').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    var form = this.closest('form');
                    var formData = new FormData(form);

                    fetch('submit_survey.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        console.log(data);
                        // Optionally redirect to another page after submission
                        window.location.href = 'home.php';
                    })
                    .catch(error => console.error('Error:', error));
                    event.preventDefault();
                });
            });
        });
    </script>
</body>
</html>