<?php
include_once '../../../controllers/helpers/connect_to_database.php';
include_once '../../../controllers/config.php';
include_once '../../../dependencies/fpdf/fpdf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    load_env();
    $exportType = $_POST['exportType'];
    $conn = connect_to_database();
    $dbHost = $_ENV['DB_HOST'];
    $dbUser = $_ENV['DB_USER'];
    $dbPass = $_ENV['DB_PASS'];
    $dbName = $_ENV['DB_NAME'];

    if ($exportType === 'sql') {
        exportDatabaseAsSQL($conn, $dbName);
    } elseif ($exportType === 'json') {
        exportDatabaseAsJSON($conn);
    } elseif ($exportType === 'pdf') {
        exportDatabaseAsPDF($conn, $dbName);
    }else {
        echo "Invalid export type.";
    }
}

function exportDatabaseAsSQL($conn, $dbName) {
    try {
        $backupFile = 'backup.sql';
        $tablesResult = $conn->query('SHOW TABLES');

        $tables = [];
        while ($row = $tablesResult->fetch_array()) {
            $tables[] = $row[0];
        }

        // Function to get the foreign keys of a table
        function getForeignKeys($conn, $table) {
            $foreignKeys = [];
            $result = $conn->query("SHOW CREATE TABLE $table");
            $row = $result->fetch_assoc();
            $createTableSql = $row['Create Table'];

            // Extract foreign keys from the CREATE TABLE statement
            preg_match_all('/CONSTRAINT.*FOREIGN KEY.*REFERENCES `(.*?)`/', $createTableSql, $matches);
            if (isset($matches[1])) {
                foreach ($matches[1] as $refTable) {
                    $foreignKeys[] = $refTable;
                }
            }
            return $foreignKeys;
        }

        // Function to sort tables based on foreign key dependencies
        function sortTablesByDependencies($conn, $tables) {
            $sortedTables = [];
            $unsortedTables = $tables;

            while (!empty($unsortedTables)) {
                foreach ($unsortedTables as $key => $table) {
                    $foreignKeys = getForeignKeys($conn, $table);
                    if (empty($foreignKeys) || count(array_intersect($foreignKeys, $unsortedTables)) == 0) {
                        $sortedTables[] = $table;
                        unset($unsortedTables[$key]);
                    }
                }
            }

            return $sortedTables;
        }

        $sortedTables = sortTablesByDependencies($conn, $tables);

        $sqlDump = "";
        $insertStatements = "";

        foreach ($sortedTables as $table) {
            // Get the CREATE TABLE statement
            $createTableResult = $conn->query("SHOW CREATE TABLE $table");
            $createTableStmt = $createTableResult->fetch_assoc();
            $sqlDump .= $createTableStmt['Create Table'] . ";\n\n";

            // Get the rows of the table
            $rowsResult = $conn->query("SELECT * FROM $table");
            if ($rowsResult->num_rows > 0) {
                $insertStatements .= "INSERT INTO $table VALUES ";
                $values = [];
                while ($row = $rowsResult->fetch_assoc()) {
                    $row = array_map([$conn, 'real_escape_string'], $row);
                    $values[] = "('" . implode("', '", $row) . "')";
                }
                $insertStatements .= implode(", ", $values) . ";\n\n";
            }
        }

        // Append insert statements at the end of the SQL dump
        $sqlDump .= $insertStatements;

        file_put_contents($backupFile, $sqlDump);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($backupFile));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($backupFile));
        readfile($backupFile);
        unlink($backupFile); // Delete the file after download
        exit;
    } catch (Exception $e) {
        echo "Error exporting database: " . $e->getMessage();
    }
}


function exportDatabaseAsJSON($conn) {
    $backupFile = 'database_backup.json';
    $tables = array();
    $result = $conn->query("SHOW TABLES");

    $json = array();
    while ($row = $result->fetch_row()) {
        $table = $row[0];
        $result2 = $conn->query("SELECT * FROM $table");

        $data = array();
        while ($row2 = $result2->fetch_assoc()) {
            $data[] = $row2;
        }
        $json[$table] = $data;
    }

    file_put_contents($backupFile, json_encode($json, JSON_PRETTY_PRINT));
    header('Content-Description: File Transfer');
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename=' . basename($backupFile));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($backupFile));
    readfile($backupFile);
    unlink($backupFile); // Delete the file after download
    exit;
}

function exportDatabaseAsPDF($conn, $dbName) {
    try {
        class PDF extends FPDF {
            function Header() {
                $this->SetFont('Arial', 'B', 12);
                $this->Cell(0, 10, 'Database Export', 0, 1, 'C');
                $this->Ln(10);
            }

            function ChapterTitle($num, $label) {
                $this->SetFont('Arial', 'B', 12);
                $this->Cell(0, 10, "Table $num: $label", 0, 1);
                $this->Ln(5);
            }

            function Table($header, $data) {
                $this->SetFont('Arial', 'B', 10);
                foreach($header as $col)
                    $this->Cell(40, 7, $col, 1);
                $this->Ln();
                $this->SetFont('Arial', '', 10);
                foreach($data as $row) {
                    foreach($row as $col)
                        $this->Cell(40, 6, $col, 1);
                    $this->Ln();
                }
                $this->Ln(10);
            }
        }

        $pdf = new PDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);

        $tablesResult = $conn->query('SHOW TABLES');
        $tableNum = 1;

        while ($row = $tablesResult->fetch_array()) {
            $table = $row[0];

            // Add table title
            $pdf->ChapterTitle($tableNum, $table);

            // Get the column names
            $columnsResult = $conn->query("SHOW COLUMNS FROM $table");
            $header = [];
            while ($col = $columnsResult->fetch_assoc()) {
                $header[] = $col['Field'];
            }

            // Get the rows of the table
            $rowsResult = $conn->query("SELECT * FROM $table");
            $data = [];
            while ($row = $rowsResult->fetch_assoc()) {
                $data[] = $row;
            }

            // Add table to PDF
            $pdf->Table($header, $data);
            $tableNum++;
        }

        $backupFile = 'backup.pdf';
        $pdf->Output('F', $backupFile);

        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename=' . basename($backupFile));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($backupFile));
        readfile($backupFile);
        unlink($backupFile); // Delete the file after download
        exit;
    } catch (Exception $e) {
        echo "Error exporting database to PDF: " . $e->getMessage();
    }
}


?>