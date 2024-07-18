<?php
// Include database connection file or connect_to_database.php
include_once '../../controllers/helpers/connect_to_database.php';
include_once '../../controllers/helpers/redirect_to_custom_error.php';
// Function to fetch survey questions with options and response counts, ignoring 'limit' type questions
function getSurveyQuestionsWithOptions($conn) {
    $sql = "SELECT q.question_id, q.question_text, q.question_type, o.option_id, o.option_text, COUNT(r.response_id) as response_count
            FROM survey_questions q
            LEFT JOIN survey_options o ON q.question_id = o.question_id
            LEFT JOIN survey_responses r ON o.option_id = r.option_id
            WHERE q.question_type != 'limit'
            GROUP BY q.question_id, o.option_id
            ORDER BY q.question_id, o.option_id";
    $result = $conn->query($sql);
    $questions = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $question_id = $row['question_id'];
            if (!isset($questions[$question_id])) {
                $questions[$question_id] = [
                    'question_text' => $row['question_text'],
                    'question_type' => $row['question_type'],
                    'options' => [],
                ];
            }

            if (!empty($row['option_id'])) {
                $questions[$question_id]['options'][] = [
                    'option_id' => $row['option_id'],
                    'option_text' => $row['option_text'],
                    'response_count' => $row['response_count'],
                ];
            }
        }
    }

    // New SQL query for questions of type 'limit' to fetch average limit values
    $sql_limit = "SELECT q.question_id, q.question_text, q.question_type, o.option_id, o.option_text, AVG(r.limit_value) as avg_limit_value
                  FROM survey_questions q
                  LEFT JOIN survey_options o ON q.question_id = o.question_id
                  LEFT JOIN survey_responses r ON o.option_id = r.option_id AND q.question_id = r.question_id
                  WHERE q.question_type = 'limit'
                  GROUP BY q.question_id, o.option_id
                  ORDER BY q.question_id, o.option_id";

    $result_limit = $conn->query($sql_limit);
    $limit_questions = [];

    if ($result_limit->num_rows > 0) {
        while ($row = $result_limit->fetch_assoc()) {
            $question_id = $row['question_id'];
            if (!isset($limit_questions[$question_id])) {
                $limit_questions[$question_id] = [
                    'question_text' => $row['question_text'],
                    'question_type' => $row['question_type'],
                    'options' => [],
                ];
            }

            if (!empty($row['option_id'])) {
                // Normalize average limit value to a scale of 0 to 10
                $normalized_value = ($row['avg_limit_value'] / 10) * 10; // Assuming avg_limit_value ranges from 0 to 10
                $limit_questions[$question_id]['options'][] = [
                    'option_id' => $row['option_id'],
                    'option_text' => $row['option_text'],
                    'avg_limit_value' => $normalized_value,
                ];
            }
        }
    }

    return [$questions, $limit_questions];
}

// Establish database connection
$conn = connect_to_database();

// Fetch survey questions with options and response counts
try{
    list($survey_questions, $limit_questions) = getSurveyQuestionsWithOptions($conn);
}catch(Exception $e){
    redirect_to_custom_error("Server Error","Unable to connect to server");
}


// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Results</title>
    <!-- Bootstrap CSS -->
    <link href="../../bootstrap/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="../../dependencies/chart.js"></script>
    <style>
        .chart-container {
            height: 400px;
            width: 100%;
            max-width: 700px;
            margin: auto;
        }
        .legend-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 10px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        .legend-color {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-4 mb-4"><strong>Survey Reports</strong></h1>
<hr></hr>
        <!-- Handle case where no survey questions are found -->
        <?php if (empty($survey_questions) && empty($limit_questions)): ?>
            <div class="alert alert-info" role="alert">
                No survey questions found or no responses recorded.
            </div>
        <?php endif; ?>
        
        
        <?php if (!empty($survey_questions)): ?>
            <?php foreach ($survey_questions as $question_id => $question): ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($question['question_text']); ?></h5>
                        <div class="chart-container">
                            <canvas id="chart_<?php echo $question_id; ?>"></canvas>
                        </div>
                        <div class="legend-container" id="legend_<?php echo $question_id; ?>"></div>
                    </div>
                </div>

                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        // Prepare data for Chart.js
                        var labels = [
                            <?php foreach ($question['options'] as $option): ?>
                                "<?php echo htmlspecialchars($option['option_text']); ?>",
                            <?php endforeach; ?>
                        ];

                        var data = [
                            <?php foreach ($question['options'] as $option): ?>
                                <?php echo $option['response_count']; ?>,
                            <?php endforeach; ?>
                        ];

                        // Function to generate a random color
                        function getRandomColor() {
                            var letters = '0123456789ABCDEF';
                            var color = '#';
                            for (var i = 0; i < 6; i++) {
                                color += letters[Math.floor(Math.random() * 16)];
                            }
                            return color;
                        }

                        // Generate random colors for each bar
                        var backgroundColors = [];
                        var borderColors = [];
                        for (var i = 0; i < labels.length; i++) {
                            var randomColor = getRandomColor();
                            backgroundColors.push(randomColor + '85'); // Adding transparency
                            borderColors.push(randomColor);
                        }

                        // Determine chart type based on question type
                        var chartType = "<?php echo $question['question_type']; ?>" === 'single' ? 'doughnut' : 'bar';

                        // Create Chart.js instance
                        var ctx = document.getElementById('chart_<?php echo $question_id; ?>').getContext('2d');
                        var chart = new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Result',
                                    data: data,
                                    backgroundColor: backgroundColors,
                                    borderColor: borderColors,
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: {
                                    duration: 2000,
                                    easing: 'easeOutBounce'
                                },
                                scales: chartType === 'bar' ? {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            stepSize: 1 // Ensuring whole number steps
                                        }
                                    }
                                } : {}
                            }
                        });

                        // Create custom legend
                        var legendContainer = document.getElementById('legend_<?php echo $question_id; ?>');
                        labels.forEach((label, index) => {
                            var legendItem = document.createElement('div');
                            legendItem.className = 'legend-item';
                            var legendColor = document.createElement('div');
                            legendColor.className = 'legend-color';
                            legendColor.style.backgroundColor = backgroundColors[index];
                            legendItem.appendChild(legendColor);
                            legendItem.appendChild(document.createTextNode(label));
                            legendContainer.appendChild(legendItem);
                        });
                    });
                </script>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($limit_questions)): ?>
            <?php foreach ($limit_questions as $question_id => $question): ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($question['question_text']); ?></h5>
                        <div class="chart-container">
                            <canvas id="chart_limit_<?php echo $question_id; ?>"></canvas>
                        </div>
                        <div class="legend-container" id="legend_limit_<?php echo $question_id; ?>"></div>
                    </div>
                </div>

                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        var labels_limit_<?php echo $question_id; ?> = [];
                        var data_limit_<?php echo $question_id; ?> = [];
                        var backgroundColors_limit_<?php echo $question_id; ?> = [];
                        var borderColors_limit_<?php echo $question_id; ?> = [];

                        <?php foreach ($question['options'] as $option): ?>
                            labels_limit_<?php echo $question_id; ?>.push("<?php echo htmlspecialchars($option['option_text']); ?>");
                            data_limit_<?php echo $question_id; ?>.push(<?php echo $option['avg_limit_value']; ?>);

                            // Generate random color for each option
                            var randomColor = getRandomColor();
                            backgroundColors_limit_<?php echo $question_id; ?>.push(randomColor + '85'); // Adding transparency
                            borderColors_limit_<?php echo $question_id; ?>.push(randomColor);
                        <?php endforeach; ?>

                        // Create Chart.js instance for limit type questions
                        var ctx_limit_<?php echo $question_id; ?> = document.getElementById('chart_limit_<?php echo $question_id; ?>').getContext('2d');
                        var chart_limit_<?php echo $question_id; ?> = new Chart(ctx_limit_<?php echo $question_id; ?>, {
                            type: 'bar',
                            data: {
                                labels: labels_limit_<?php echo $question_id; ?>,
                                datasets: [{
                                    label: 'Average Limit Value',
                                    data: data_limit_<?php echo $question_id; ?>,
                                    backgroundColor: backgroundColors_limit_<?php echo $question_id; ?>,
                                    borderColor: borderColors_limit_<?php echo $question_id; ?>,
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                indexAxis: 'y', // Use 'y' axis as the index axis for horizontal bars
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: {
                                    duration: 2000,
                                    easing: 'easeOutBounce'
                                },
                                scales: {
                                    x: {
                                        min: 0,
                                        max: 10,
                                        ticks: {
                                            stepSize: 1
                                        }
                                    }
                                }
                            }
                        });

                        // Function to generate a random color
                        function getRandomColor() {
                            var letters = '0123456789ABCDEF';
                            var color = '#';
                            for (var i = 0; i < 6; i++) {
                                color += letters[Math.floor(Math.random() * 16)];
                            }
                            return color;
                        }

                        // Create custom legend
                        var legendContainer_limit_<?php echo $question_id; ?> = document.getElementById('legend_limit_<?php echo $question_id; ?>');
                        labels_limit_<?php echo $question_id; ?>.forEach((label, index) => {
                            var legendItem = document.createElement('div');
                            legendItem.className = 'legend-item';
                            var legendColor = document.createElement('div');
                            legendColor.className = 'legend-color';
                            legendColor.style.backgroundColor = backgroundColors_limit_<?php echo $question_id; ?>[index];
                            legendItem.appendChild(legendColor);
                            legendItem.appendChild(document.createTextNode(label));
                            legendContainer_limit_<?php echo $question_id; ?>.appendChild(legendItem);
                        });
                    });
                </script>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>