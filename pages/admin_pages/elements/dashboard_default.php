<?php
// Include database connection
include_once '../../controllers/helpers/connect_to_database.php';
include '../../controllers/admin_controller/admin_auth/admin_session_check.php';
include_once '../../controllers/helpers/redirect_to_custom_error.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Query to get total users
$conn = connect_to_database();

try {
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

    // Query to count both 'Yes' and 'No' responses
    $countQuery = "
        SELECT 
            COUNT(CASE WHEN is_member = 'Yes' THEN 1 END) AS total_yes_in_complques,
            COUNT(CASE WHEN is_member = 'No' THEN 1 END) AS total_no_in_complques
        FROM complques
    ";
    $result = $conn->query($countQuery);

    if ($result) {
        $row = $result->fetch_assoc();
        $totalYesCount = $row['total_yes_in_complques'];
        $totalNoCount = $row['total_no_in_complques'];
    } else {
        $totalYesCount = 0;
        $totalNoCount = 0;
    }

} catch (Exception $e) {
    $conn->close();
    redirect_to_custom_error("Server Error", "Unable to connect server");
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
    <link href="../../bootstrap/bootstrap.min.css" rel="stylesheet">
    <script src="../../bootstrap/bootstrap.min.js"></script>
    <script src="../../dependencies/chart.js"></script>
    <script src="../../dependencies/jquery.slim.min.js"></script>
</head>

<body>
    <div class="container">
        <h1><b>Dashboard</b></h1>
        <hr>
        </hr>
        <div class="container" id="dashborad_con">

            <button class="btn btn-primary" data-toggle="modal" data-target="#exportModal">Export Database</button>
            <br><br />
            <div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exportModalLabel">Export Database</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="exportForm" action="elements/export_database.php" method="post">
                                <div class="form-group">
                                    <label for="exportType">Select export type:</label>
                                    <select class="form-control" id="exportType" name="exportType">
                                        <option value="sql">SQL (SQL Dump)</option>
                                        <option value="json">JSON</option>
                                        <option value="pdf">PDF</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" form="exportForm">Export</button>
                        </div>
                    </div>
                </div>
            </div>

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
                            <canvas id="myChart1"></canvas>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-header">Yes/ No responses</div>
                        <div class="card-body">
                            <canvas id="myChart2"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        $(document).ready(function () {
            var ctx = document.getElementById('myChart1').getContext('2d');
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


        $(document).ready(function () {
            var ctx = document.getElementById('myChart2').getContext('2d');
            // Check if both values are 0
            if (<?php echo $totalYesCount; ?> === 0 && <?php echo $totalNoCount; ?> === 0) {
                var data = [0, 1]; // Show "Responses not found"
                var backgroundColor = ['rgba(192, 192, 192, 0.5)']; // Grey color for "Responses not found"
                var borderColor = ['rgba(192, 192, 192, 1)'];
                var labels = ['Responses Not Found'];
            } else {
                var data = [<?php echo $totalYesCount; ?>, <?php echo $totalNoCount; ?>];
                var backgroundColor = ['rgba(54, 162, 235, 0.5)', 'rgba(255, 159, 64, 0.5)']; // Different colors
                var borderColor = ['rgba(54, 162, 235, 1)', 'rgba(255, 159, 64, 1)'];
                var labels = ['Yes Responses', 'No Responses'];
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
                            labels: {
                                generateLabels: function (chart) {
                                    var data = chart.data;
                                    if (data.labels.length && data.datasets.length) {
                                        return data.labels.map(function (label, i) {
                                            var meta = chart.getDatasetMeta(0);
                                            var style = meta.controller.getStyle(i);

                                            return {
                                                text: label + ' : ' + data.datasets[0].data[i],
                                                fillStyle: style.backgroundColor,
                                                strokeStyle: style.borderColor,
                                                lineWidth: style.borderWidth,
                                                hidden: isNaN(data.datasets[0].data[i]) || meta.data[i].hidden,
                                                index: i
                                            };
                                        });
                                    }
                                    return [];
                                }
                            }
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