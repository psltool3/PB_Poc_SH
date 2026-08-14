<?php
require('../util/Connection.php');
require('../util/SessionFunction.php');
require('../util/Logger.php');

if(!SessionCheck()){
	return;
}

if (!isset($_POST["id"])) {
	echo "Error : Missing ID parameter.";
	return;
}

$id = $_POST["id"];

$log_name = isset($_SESSION['user']) ? $_SESSION['user'] : '';

// Update the optimised table where id equals the extracted ID
$sql = "UPDATE optimised_table_leg1 SET cost = '' WHERE id = '$id'";
$filteredPost = $_POST;
unset($filteredPost['username'], $filteredPost['password']);
writeLog("User ->" ." Cost for leg1 Reset ->". $_SESSION['user'] . "| Requested JSON -> " . json_encode($filteredPost). " | " . $log_name);

if ($con->query($sql) === TRUE) {
	echo "Success";
} else {
	echo "Error : updating record: " . $con->error;
	return;
}	

?>
