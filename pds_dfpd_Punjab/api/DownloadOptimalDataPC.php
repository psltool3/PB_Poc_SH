<?php

require('../util/Connection.php');
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


// Check if format is specified in GET request
if (isset($_GET['format'])) {
    $format = $_GET['format'];
    
    // Sanitize table name to prevent SQL injection
    $tablename = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['tableName']);
	
    // Dynamically retrieve column names
    $columns = array();
    $query_cols = "DESCRIBE `".$tablename."`";
    $result_cols = mysqli_query($con, $query_cols);
    if ($result_cols) {
        while ($col_row = mysqli_fetch_assoc($result_cols)) {
            $columns[] = $col_row['Field'];
        }
    }
    
    if (empty($columns)) {
        $columns = ["uniqueid","PC_district","PC_Name","PC_ID","PC_Latitude","PC_Longitute","Paddy_Procure","PC_Milling_Centre","active"];
    }

    $type = isset($_GET['type']) ? $_GET['type'] : '';
    $paddyLabel = ($type === 'W') ? 'Wheat Procurement' : 'Paddy Procure';
    $paddyLabelShort = ($type === 'W') ? 'Wheat Procurement' : 'Paddy';

    // Map database keys to user-friendly display headers
    $header_map = [
        'uniqueid' => 'Unique ID',
        'PC_district' => 'District',
        'PC_District' => 'District',
        'PC_Name' => 'Name',
        'PC_ID' => 'PC ID',
        'PC_Latitude' => 'Latitude',
        'PC_Lat' => 'Latitude',
        'PC_Longitute' => 'Longitude',
        'PC_Long' => 'Longitude',
        'Paddy_Procure' => $paddyLabel,
        'PC_Milling_Centre' => 'Milling Centre',
        'Storage_Point' => 'Storage Point',
        'PC_Paddy' => $paddyLabelShort,
        'active' => 'Active'
    ];

    $display_headers = array();
    foreach ($columns as $col) {
        if (isset($header_map[$col])) {
            $display_headers[] = $header_map[$col];
        } else {
            $display_headers[] = ucwords(str_replace('_', ' ', $col));
        }
    }

	$tableData = array();
    array_push($tableData, $display_headers);

	$query = "SELECT * FROM `".$tablename."` WHERE 1";
    $result = mysqli_query($con,$query);
    if ($result) {
        $numrows = mysqli_num_rows($result);
        if($numrows>0){
            while($row = mysqli_fetch_array($result)){
                $temp = array();
                for($i=0;$i<count($columns);$i++){
                    array_push($temp, isset($row[$columns[$i]]) ? $row[$columns[$i]] : '');
                }
                array_push($tableData,$temp);
            }
        }
    }
	
	
    
    // Filename for the downloaded file
    $filename = 'table_data';

    // Set headers for the chosen format
    switch ($format) {
        case 'csv':
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            outputCSV($tableData);
            break;

        case 'xlsx':
            // Create a new PhpSpreadsheet object
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Insert data tableData
            $rowIndex = 1;
            foreach ($tableData as $rowData) {
                $columnIndex = 1;
                foreach ($rowData as $value) {
                    $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex) . $rowIndex;
                    $sheet->setCellValue($cellAddress, $value);
                    $columnIndex++;
                }
                $rowIndex++;
            }


            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            break;

        case 'pdf':
            require('fpdf/fpdf.php');

            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 0);

            // Highlight the first row as header
            $pdf->SetFillColor(200, 220, 255); // Set background color
            $pdf->SetTextColor(0); // Reset text color
            $case = 0;
			$pdf->SetFont('helvetica', '', 7); // Font family, style (empty for regular), and size (8)
            $numCols = count($columns) > 0 ? count($columns) : 1;
            $cellWidth = floor(190 / $numCols);
            foreach ($tableData as $row) {
                foreach ($row as $col) {
                    $pdf->Cell($cellWidth, 5, $col, 1, 0, 'C', true);
                }
                $pdf->Ln();
                $pdf->SetFillColor(255, 255, 255); 
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
            echo $pdf->Output('S');
            break;

        default:
            echo 'Error : Invalid format specified.';
            break;
    }
} else {
    echo 'Error : Please specify a format in the GET request (e.g., ?format=pdf).';
}



// Function to output CSV data
function outputCSV($data) {
    $output = fopen('php://output', 'w');
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
}

//exit();