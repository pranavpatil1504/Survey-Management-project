<?php
// Include database connection
include_once '../../controllers/helpers/connect_to_database.php';

// Query to get total users
$conn = connect_to_database();


#admins 
$query = "SELECT COUNT(*) AS admin_count FROM privilege WHERE is_admin = 1";
$result = $conn->query($query);
if ($result) {
    $row = $result->fetch_assoc();
    $total_admins = $row['admin_count'];
} else {
    $total_admins = 0;
}

$query_total_users = "SELECT COUNT(*) as total_users FROM users";
$result_total_users = $conn->query($query_total_users);
$row_total_users = $result_total_users->fetch_assoc();
$total_users = $row_total_users['total_users'];
$total_users = $total_users - $total_admins;
// Query to get total users who submitted surveys
$query_total_users_submitted = "SELECT COUNT(*) as total_users_submitted FROM users_submitted";
$result_total_users_submitted = $conn->query($query_total_users_submitted);
$row_total_users_submitted = $result_total_users_submitted->fetch_assoc();
$total_users_submitted = $row_total_users_submitted['total_users_submitted'];

#total questions
$query_total_survey_questions = "SELECT COUNT(*) as total_survey_questions FROM survey_questions";
$result_total_survey_questions = $conn->query($query_total_survey_questions);
if ($result_total_survey_questions) {
    $row_total_survey_questions = $result_total_survey_questions->fetch_assoc();
    $total_survey_questions = $row_total_survey_questions['total_survey_questions'];
} else {
    $total_survey_questions = 0;
}



$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        #dashborad_con {
            overflow-y: hidden;
        }
    </style>
    <link href="../../../bootstrap/bootstrap.min.css" rel="stylesheet">
    <script src="../../../bootstrap/bootstrap.min.js"></script>
    <script src="../../dependencies/chart.js"></script>
</head>

<body>
    <div class="container">
        <h1><b>Dashboard</b></h1>
        <hr>
        </hr>
        <div class="container" id="dashborad_con">
            <div class="row">
                <div class="col-md-6">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-header">Total Users</div>
                        <div class="card-body">
                            <h5 class="card-title" id="total-users"><?php echo $total_users; ?></h5>
                        </div>
                    </div>
                    <div class="card text-white bg-success mb-3">
                        <div class="card-header">Users Submitted</div>
                        <div class="card-body">
                            <h5 class="card-title" id="total-users-submitted"><?php echo $total_users_submitted; ?></h5>
                        </div>
                    </div>
                    <div class="card text-white bg-info mb-3">
                        <div class="card-header">Total Survey Questions</div>
                        <div class="card-body">
                            <h5 class="card-title" id="total-survey-questions"><?php echo $total_survey_questions; ?>
                            </h5>
                        </div>
                    </div>
                    <div class="card text-white bg-info mb-3">
                        <div class="card-header">Total Admins</div>
                        <div class="card-body">
                            <h5 class="card-title" id="total-survey-questions"><?php echo $total_admins; ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">Total Responses</div>
                        <div class="card-body">
                            <canvas id="myChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        $(document).ready(function () {
            var ctx = document.getElementById('myChart').getContext('2d');
            var usersNotSubmitted = <?php echo $total_users - $total_users_submitted; ?>;

            // Check if both values are 0
            if (<?php echo $total_users_submitted; ?> === 0 && usersNotSubmitted === 0) {
                var data = [0, 1]; // Show "Users not found"
                var backgroundColor = ['rgba(255, 99, 132, 0.5)'];
                var borderColor = ['rgba(255, 99, 132, 1)'];
                var labels = ['Users Not Found'];
            } else {
                var data = [<?php echo $total_users_submitted; ?>, usersNotSubmitted];
                var backgroundColor = ['rgba(75, 192, 192, 0.5)', 'rgba(255, 99, 132, 0.5)'];
                var borderColor = ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)'];
                var labels = ['Users Submitted', 'Users Not Submitted'];
            }

            var myChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Count',
                        data: data,
                        backgroundColor: backgroundColor,
                        borderColor: borderColor,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function (tooltipItem) {
                                    return tooltipItem.label + ': ' + tooltipItem.raw;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

</body>

</html>