<?php

require('../util/Connection.php');
require '../vendor/autoload.php';
require('../util/SessionCheck.php');
require('../util/Connection.php');
require('../structures/Warehouse.php');
require('../util/SessionFunction.php');
require('../structures/Login.php');
// session_start();
ini_set('max_execution_time', 3000);
require('../util/Logger.php');
require('../util/Security.php');
require ('../util/Encryption.php');
$nonceValue = 'nonce_value';


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


// Check if format is specified in GET request
if (isset($_GET['format'])) {
    $format = $_GET['format'];
    $district = $_SESSION['district_district'];
	$id = "";
	if(isset($_GET['month']) && $_GET['month'] != ""){
		$month_full = $_GET['month'];
		$parts = explode('_', $month_full);
		if(count($parts) >= 3){
			$year = $parts[0];
			$month = $parts[1];
			$day = $parts[2];
			$query_m = "SELECT * FROM optimised_table WHERE year='$year' AND month='$month' AND day='$day'";
			$result_m = mysqli_query($con,$query_m);
			if($result_m && mysqli_num_rows($result_m) > 0){
				$row_m = mysqli_fetch_assoc($result_m);
				$id = $row_m['id'];
			}
		}
	}
	if(empty($id)){
		$query_m = "SELECT * FROM optimised_table ORDER BY last_updated DESC LIMIT 1";
		$result_m = mysqli_query($con,$query_m);
		if($result_m && mysqli_num_rows($result_m) > 0){
			$row_m = mysqli_fetch_assoc($result_m);
			$id = $row_m['id'];
		}
	}

	$tablename = "optimiseddata_".$id;
	$type = isset($_GET['type']) ? $_GET['type'] : '';
	$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

	if($type == "rollout"){
		$columns = ["scenario","from","from_state","from_id","from_name","from_district","from_millingcentre","from_lat","from_long","to","to_state","to_id","to_name","to_district","to_millingcentre","to_lat","to_long","commodity","quantity","distance","status"];
		$headers = ["Scenario","From","From_State","From_ID","From_Name","From_District","From_Milling_Center","From_Lat","From_Long","To","To_State","To_ID","To_Name","To_District","To_Milling_Center","To_Lat","To_Long","Commodity","Quantity(Qtl)","Distance(Km)","Status"];

		$columns_pdf = ["scenario","from","from_id","from_name","from_district","from_lat","from_long","to","to_id","to_name","to_district","to_lat","to_long","commodity","quantity","distance","status"];
		$headers_pdf = ["Scenario","From","From_ID","From_Name","From_District","From_Lat","From_Long","To","To_ID","To_Name","To_District","To_Lat","To_Long","Commodity","Quantity(Qtl)","Distance(Km)","Status"];
	} else {
		$columns = ["scenario","from","from_state","from_id","from_name","from_district","from_millingcentre","from_lat","from_long","to","to_state","to_id","to_name","to_district","to_millingcentre","to_lat","to_long","commodity","quantity","distance","approve_district","reason_district","approve_admin"];
		$headers = ["Scenario","From","From_State","From_ID","From_Name","From_District","From_Milling_Center","From_Lat","From_Long","To","To_State","To_ID","To_Name","To_District","To_Milling_Center","To_Lat","To_Long","Commodity","Quantity(Qtl)","Distance(Km)","Implemented / Non Implemented","District Reason for not Implementing","Admin Approved"];

		$columns_pdf = ["scenario","from","from_id","from_name","from_district","from_lat","from_long","to","to_id","to_name","to_district","to_lat","to_long","commodity","quantity","distance","approve_district","reason_district","approve_admin"];
		$headers_pdf = ["Scenario","From","From_ID","From_Name","From_District","From_Lat","From_Long","To","To_ID","To_Name","To_District","To_Lat","To_Long","Commodity","Quantity(Qtl)","Distance(Km)","Implemented / Non Implemented","District Reason for not Implementing","Admin Approved"];
	}

	$base_filter = "to_district='$district' AND ((approve_admin='yes' AND approve_district='yes') OR (approve_admin='no' AND approve_district='no') OR (approve_admin='no'))";

	if($type == "rollout" || $status_filter != ""){
		if($status_filter == "not implemented"){
			$query = "SELECT * FROM ".$tablename." WHERE ".$base_filter." AND (status IS NULL OR status='' OR status != 'implemented')";
		} else if ($status_filter == "implemented") {
			$query = "SELECT * FROM ".$tablename." WHERE ".$base_filter." AND status='implemented'";
		} else {
			$query = "SELECT * FROM ".$tablename." WHERE ".$base_filter;
		}
	} else {
		$query = "SELECT * FROM ".$tablename." WHERE to_district='$district'";
	}
    $result = mysqli_query($con,$query);
    $numrows = $result ? mysqli_num_rows($result) : 0;
    
    $tableData = array();
    $tableData_pdf = array();
    array_push($tableData,$headers);
    array_push($tableData_pdf,$headers_pdf);

    if($numrows>0){
        while($row = mysqli_fetch_array($result)){
			if($row['new_id_admin']!=null or $row['new_id_admin']!=""){
				$id = $row['new_id_admin'];
				$query_warehouse = "SELECT latitude,longitude,district FROM warehouse WHERE id='$id'";
				$result_warehouse = mysqli_query($con,$query_warehouse);
				$numrows_warehouse = mysqli_num_rows($result_warehouse);
				if($numrows_warehouse!=0){
					$row_warehouse = mysqli_fetch_assoc($result_warehouse);
					$row["from_lat"] = $row_warehouse['latitude'];
					$row["from_long"] = $row_warehouse['longitude'];
					$row["from_district"] = $row_warehouse['district'];
				}
				$row["from_id"] = $row['new_id_admin'];
				$row["from_name"] = $row['new_name_admin'];
				$row["distance"] = $row['new_distance_admin'];
			}
			else if(($row['new_id_district']!=null or $row['new_id_district']!="") and $row['approve_admin']=="yes"){
				$id = $row['new_id_district'];
				$query_warehouse = "SELECT latitude,longitude,district FROM warehouse WHERE id='$id'";
				$result_warehouse = mysqli_query($con,$query_warehouse);
				$numrows_warehouse = mysqli_num_rows($result_warehouse);
				if($numrows_warehouse!=0){
					$row_warehouse = mysqli_fetch_assoc($result_warehouse);
					$row["from_lat"] = $row_warehouse['latitude'];
					$row["from_long"] = $row_warehouse['longitude'];
					$row["from_district"] = $row_warehouse['district'];
				}
				$row["from_id"] = $row['new_id_district'];
				$row["from_name"] = $row['new_name_district'];
				$row["distance"] = $row['new_distance_district'];
			}

			if(isset($row['status']) && strtolower($row['status']) == 'implemented'){
				$row['status'] = 'Implemented';
			} else {
				$row['status'] = 'Non Implemented';
			}

			if (isset($row['approve_district']) && $row['approve_district'] !== '') {
				if ($row['approve_district'] === 'yes' || $row['approve_district'] === 'same') {
					$row['approve_district'] = 'Implemented';
				} elseif ($row['approve_district'] === 'no') {
					$row['approve_district'] = 'Non Implemented';
				}
			} else {
				$row['approve_district'] = '';
			}

			if (!isset($row['reason_district']) || $row['reason_district'] === null) {
				$row['reason_district'] = '';
			}

			if (isset($row['approve_admin']) && $row['approve_admin'] !== '') {
				if ($row['approve_admin'] === 'yes') {
					$row['approve_admin'] = 'Approve';
				} elseif ($row['approve_admin'] === 'no') {
					$row['approve_admin'] = 'System Generated';
				}
			} else {
				$row['approve_admin'] = 'Pending';
			}

            $temp = array();
            $temp_pdf = array();
            for($i=0;$i<count($columns);$i++){
                array_push($temp,$row[$columns[$i]] ?? null);
            }
            for($i=0;$i<count($columns_pdf);$i++){
                array_push($temp_pdf,$row[$columns_pdf[$i]] ?? null);
            }
            array_push($tableData,$temp);
            array_push($tableData_pdf,$temp_pdf);
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
            $pdf = new FPDF('L', 'mm', 'A4');
			$pdf->AddPage();
			$pdf->SetFont('Arial', 'B', 15); // Set initial font size

			// Calculate column width based on the number of columns and page width
			$pageWidth = $pdf->GetPageWidth() - 20; // Subtract margins (10 mm each side)
			$numCols = count($tableData_pdf[0]) + 2; // Assuming all rows have the same number of columns
			$colWidth = $pageWidth / $numCols;
			$originalColWidth = $colWidth;

			// Function to add a row to the PDF with dynamic font size adjustment
			function addRow($pdf, $row, $colWidth, $isHeader = false) {
				global $originalColWidth;
				global $colWidth;
				$pdf->SetFillColor($isHeader ? 200 : 255, $isHeader ? 220 : 255, $isHeader ? 255 : 255);
				$i = 0;
				foreach ($row as $col) {
					$i = $i + 1;
					if($i==10){
						$colWidth = $colWidth*3;
					}else{
						$colWidth = $originalColWidth;
					}
					$fontSize = 12;
					$pdf->SetFont('Arial', 'B', $fontSize);
					// Reduce font size if text is too wide for the cell
					while ($pdf->GetStringWidth($col) > $colWidth - 2 && $fontSize > 1) {
						$fontSize -= 1;
						$pdf->SetFont('Arial', 'B', $fontSize);
					}
					$pdf->Cell($colWidth, 10, $col, 1, 0, 'C', true);
				}
				$pdf->Ln();
			}

			// Add the header
			addRow($pdf, $tableData_pdf[0], $colWidth, true);

			// Add the data rows
			$rowHeight = 10;
			$maxRowsPerPage = ($pdf->GetPageHeight() - 20) / $rowHeight; // Subtract margins (10 mm each top and bottom)

			for ($i = 1; $i < count($tableData_pdf); $i++) {
				if ($pdf->GetY() + $rowHeight > $pdf->GetPageHeight() - 10) { // Check if we need to add a new page
					$pdf->AddPage();
					addRow($pdf, $tableData_pdf[0], $colWidth, true); // Add the header again on the new page
				}
				addRow($pdf, $tableData_pdf[$i], $colWidth);
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

exit();