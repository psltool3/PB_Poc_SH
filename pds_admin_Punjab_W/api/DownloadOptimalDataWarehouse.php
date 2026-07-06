<?php

require('../util/Connection.php');
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


// Check if format is specified in GET request
if (isset($_GET['format'])) {
    $format = $_GET['format'];
    
    $columns = ["district","name","id","warehousetype","latitude","longitude","storage"];
    $tablename = $_GET['tableName'];
	
	$tableData = array();
    array_push($tableData,$columns);

	$query = "SELECT * FROM ".$tablename." WHERE 1";
    $result = mysqli_query($con,$query);
    $numrows = mysqli_num_rows($result);
    
    if($numrows>0){
        while($row = mysqli_fetch_array($result)){
            $temp = array();
            for($i=0;$i<count($columns);$i++){
                $colName = $columns[$i];
                if($colName == "warehousetype"){
                    $val = isset($row['warehousetype']) ? $row['warehousetype'] : (isset($row['type']) ? $row['type'] : 'N/A');
                    array_push($temp, $val);
                }
                elseif($colName == "storage"){
                    $val = 0;
                    if (isset($row['storage'])) { $val = $row['storage']; }
                    elseif (isset($row['incoming_min_mota'])) { $val = $row['incoming_min_mota'] + $row['incoming_min_patla'] + $row['incoming_min_saran']; }
                    elseif (isset($row['mota'])) { $val = $row['mota'] + $row['patla'] + $row['saran']; }
                    array_push($temp, $val);
                }
                else {
                    array_push($temp, isset($row[$colName]) ? $row[$colName] : '');
                }
            }
            array_push($tableData,$temp);
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

            // Set column names as the first row
            $columnIndex = 1;
            foreach ($columns as $columnName) {
                $sheet->setCellValueByColumnAndRow($columnIndex, 1, $columnName);
                $columnIndex++;
            }

            // Insert data tableData
            $rowIndex = 1;
            foreach ($tableData as $rowData) {
                $columnIndex = 1;
                foreach ($rowData as $value) {
                    $sheet->setCellValueByColumnAndRow($columnIndex, $rowIndex, $value);
                    $columnIndex++;
                }
                $rowIndex++;
            }


            header('Content-Type: application/vnd.ms-excel');
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
            foreach ($tableData as $row) {
                foreach ($row as $col) {
                    $pdf->Cell(22, 5, $col, 1, 0, 'C', true);
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