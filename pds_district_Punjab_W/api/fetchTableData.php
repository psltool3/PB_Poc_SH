<?php
require('../util/Connection.php');
require('../structures/District.php');
require('../util/SessionFunction.php');
require('../structures/Login.php');

if(!SessionCheck()){
	return;
}

$query = "SELECT * FROM optimised_table ORDER BY last_updated DESC";
$result = mysqli_query($con,$query);
$response = array();
while($row = mysqli_fetch_array($result))
{
	$id = $row["id"];
	$tablename = "optimiseddata_".$id;
	$check_query = "SHOW TABLES LIKE '$tablename'";
	$check_res = $con->query($check_query);
	if($check_res && $check_res->num_rows > 0) {
		$data_query = "SELECT 1 FROM $tablename WHERE approve_admin='yes' AND approve_district='yes' LIMIT 1";
		$data_res = mysqli_query($con, $data_query);
		if($data_res && mysqli_num_rows($data_res) > 0) {
			$temp = array();
			$temp["year"] = $row["year"];
			$temp["month"] = $row["month"];
			$temp["day"] = $row["day"];
			$temp["id"] = $row["id"];
			$temp["last_updated"] = $row["last_updated"];
			array_push($response,$temp);
			break;
		}
	}
}

echo json_encode($response);

?>