<?php
require('../util/Connection.php');
require('../structures/District.php');
require('../util/SessionFunction.php');
require('../structures/Login.php');

if(!SessionCheck()){
	return;
}

$reviewed = "";
$approved = "";
$from_id = "";
$to_id = "";

if(isset($_POST['fromid'])){
	$from_id = $_POST['fromid'];
}

if(isset($_POST['toid'])){
	$to_id = $_POST['toid'];
}

if(isset($_POST['approved'])){
	$approved = $_POST['approved'];
}

if(isset($_POST['reviewed'])){
	$reviewed = $_POST['reviewed'];
}

$month_full = $_POST['month'];
$district = $_POST['district'];

$parts = explode('_', $month_full);

$year = $parts[0]; 
$month = $parts[1];
$day = $parts[2];
$query = "SELECT * FROM optimised_table_leg1 WHERE month='$month' AND year='$year' AND day='$day'";
$result = mysqli_query($con,$query);
$numrow = mysqli_num_rows($result);
$id = "";
if($numrow>0){
	$row = mysqli_fetch_assoc($result);
	$id = $row['id'];
}

$tablename = "optimiseddata_leg1_".$id;

$query = "SHOW TABLES LIKE '$tablename'";
$result = $con->query($query);
$data = null;

if ($result && $result->num_rows > 0) {
	if ($district == "" || $district == "all") {
		$data = array();
	} else {
		$query = "SELECT * FROM ".$tablename." WHERE 1";

		if ($district != "" && $district != "all") {
			$query .= " AND to_district = '$district'";
		}

		if ($from_id != "") {
			$query .= " AND from_id = '$from_id'";
		}

		if ($to_id != "") {
			$query .= " AND `to_id` = '$to_id'";
		}

		if ($reviewed == "reviewed") {
			$query .= " AND approve_district='yes'";
		} else if ($reviewed == "notreviewed") {
			$query .= " AND (approve_district = '' OR approve_district IS NULL)";
		}

		if ($approved == "approved") {
			$query .= " AND approve_admin='yes'";
		} else if ($approved == "notapproved") {
			$query .= " AND (approve_admin='no' OR approve_admin IS NULL)";
		}
		$result = mysqli_query($con,$query);
		while($row = mysqli_fetch_assoc($result))
		{
			$data[] = $row;
		}
	}

	$query_warehouse = "SELECT id from warehouse WHERE active='1'";
	$result_warehouse = mysqli_query($con,$query_warehouse);
	while($row_warehouse = mysqli_fetch_assoc($result_warehouse)){
		$warehouse[] = $row_warehouse;
	}
	$resultarray = [];
	if($data==null){
		$data = array();
	}
	$resultarray["data"] = $data;
	$resultarray["warehouse"] = $warehouse;
	
	echo json_encode($resultarray);
} else {
	$resultarray = [];
	$resultarray["data"] = array();
	$resultarray["warehouse"] = array();
	echo json_encode($resultarray);
}

?>