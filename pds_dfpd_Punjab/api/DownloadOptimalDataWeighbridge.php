<?php

require('../util/Connection.php');
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


// Check if format is specified in GET request
if (isset($_GET['format'])) {
    $format = $_GET['format'];
    
    $columns = ["District", "Name", "ID", "Storage_Point", "Capacity", "Latitude", "Longitude", "active"];

    // Map database keys to user-friendly display headers
    $header_map = [
        'District' => 'District',
        'Name' => 'Name',
        'ID' => 'Weighbridge ID',
        'Storage_Point' => 'Storage Point',
        'Capacity' => 'Capacity',
        'Latitude' => 'Latitude',
        'Longitude' => 'Longitude',
        'active' => 'Status'
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

	$query = "SELECT * FROM weighbridge WHERE 1 ORDER BY District";
    $result = mysqli_query($con,$query);
    if ($result) {
        $numrows = mysqli_num_rows($result);
        if($numrows>0){
            while($row = mysqli_fetch_array($result)){
                $temp = array();
                for($i=0;$i<count($columns);$i++){
                    $col = $columns[$i];
                    if ($col == 'active') {
                        $temp[] = $row['active'] == 1 ? 'Active' : 'InActive';
                    } else {
                        $temp[] = isset($row[$col]) ? $row[$col] : '';
                    }
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
