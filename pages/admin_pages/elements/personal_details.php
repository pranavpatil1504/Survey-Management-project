<?php
require_once '../../controllers/helpers/connect_to_database.php';

function getPersonalDetails()
{
    $conn = connect_to_database();
    $sql = "SELECT * FROM personal_details";
    $result = $conn->query($sql);

    $details = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $details[] = $row;
        }
    }
    $conn->close();
    return $details;
}

$personal_details = getPersonalDetails();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Details</title>
    <link href="../../bootstrap/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1><b>Personal Details</b></h1>
        <hr>
        <div class="table-container">
            <div class="table-responsive">
                <table id="personalDetailsTable" class="table table-striped table-bordered" style="border: none">
                    <thead style="background-color: #31363F; color: white">
                        <tr>
                            <th style="padding: 12px;">ID</th>
                            <th style="padding: 12px;">Library Feedback</th>
                            <th style="padding: 12px;">Username</th>
                            <th style="padding: 12px;">Division</th>
                            <th style="padding: 12px;">Designation</th>
                            <th style="padding: 12px;">Extension Number</th>
                            <th style="padding: 12px;">Email Address</th>
                            <th style="padding: 12px;">Interests</th>
                            <th style="padding: 12px;">Submission Date</th>
                        </tr>
                    </thead>
                    <tbody style="background-color: #ECEFF1;">
                        <?php foreach ($personal_details as $detail): ?>
                            <tr>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;"><?php echo htmlspecialchars($detail['id']); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;"><?php echo htmlspecialchars($detail['libraryFeedback']?? 'N/A'); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;"><?php echo htmlspecialchars($detail['username']?? 'N/A'); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;"><?php echo htmlspecialchars($detail['userDivision']?? 'N/A'); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;"><?php echo htmlspecialchars($detail['userDesignation']?? 'N/A'); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;"><?php echo htmlspecialchars($detail['userTel']?? 'N/A'); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;"><?php echo htmlspecialchars($detail['userEmail']?? 'N/A'); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;"><?php echo htmlspecialchars($detail['userInterests']?? 'N/A'); ?></td>
                                <td style="background-color: #FFFFFF; color: #31363F; padding: 10px;"><?php echo htmlspecialchars($detail['submissionDate']?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../../dependencies/jquery.min.js"></script>
    <script src="../../dependencies/jquery.dataTables.min.js"></script>
    <script src="../../dependencies/dataTables.buttons.min.js"></script>
    <script src="../../dependencies/jszip.min.js"></script>
    <script src="../../dependencies/pdfmake.min.js"></script>
    <script src="../../dependencies/vfs_fonts.js"></script>
    <script src="../../dependencies/buttons.html5.min.js"></script>
    <script src="../../dependencies/buttons.print.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#optionsVotesTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                "paging": true,
                "pageLength": 10,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true
            });
        });
    </script>
</body>

</html>
