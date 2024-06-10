<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>

    <div class="container mt-5">
        <h1 class="mb-4">Quiz</h1>
        <form action="result.php" method="post">
            <div id="quizCarousel" class="carousel slide" data-ride="carousel" data-interval="false">
                <div class="carousel-inner">
                    <?php
                    include 'database.php';
                    foreach ($questions as $key => $question):
                        ?>
                        <div class="carousel-item <?php echo $key === 0 ? 'active' : ''; ?>">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $question['question']; ?></h5>
                                    <?php foreach ($question['options'] as $option): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="answer[<?php echo $key; ?>]"
                                                id="option_<?php echo $key . '_' . $option; ?>" value="<?php echo $option; ?>">
                                            <label class="form-check-label" for="option_<?php echo $key . '_' . $option; ?>">
                                                <?php echo $option; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="mt-3">
                                <?php if ($key != 0): ?>
                                    <a class="btn btn-secondary" href="#quizCarousel" role="button" data-slide="prev">
                                        Previous
                                    </a>
                                <?php endif; ?>
                                <?php if ($key == count($questions) - 1): ?>
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                <?php else: ?>
                                    <a class="btn btn-primary" href="#quizCarousel" role="button" data-slide="next">
                                        Next
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </form>
    </div>

</body>

</html>