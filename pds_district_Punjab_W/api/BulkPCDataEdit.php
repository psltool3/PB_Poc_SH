<?php
require('../util/Connection.php');
require('../structures/PC.php');
require('../util/SessionFunction.php');
require('../structures/Login.php');
ini_set('max_execution_time', 3000);
require('../util/Logger.php');
require('../util/Security.php');
require ('../util/Encryption.php');

$nonceValue = 'nonce_value';

require('Header.php');

$mapData = [
    "District" => "district",
    "Name of PC" => "name",
    "PC ID" => "id",
    "Storage Point" => "Storage_Point",
    "Latitude" => "latitude",
    "Longitude" => "longitude",
    "Wheat Procurement" => "Paddy_Procurement",
    "Active/Not-Active" => "active"
];

// Reverse mapping
$reverseMapData = array_flip($mapData);

$person = new Login;
$person->setUsername($_POST["username"]);
$Encryption = new Encryption();
$person->setPassword($Encryption->decrypt($_POST["password"], $nonceValue));

if($_SESSION['district_user']!=$person->getUsername()){
    echo "User is logged in with different username and password";
    return;
}

$query = "SELECT * FROM login WHERE username='".$person->getUsername()."'";
$result = mysqli_query($con,$query);
$row = mysqli_fetch_assoc($result);

$dbHashedPassword = $row['password'];

if(password_verify($person->getPassword(), $dbHashedPassword)){
$districts = [];
$query = "SELECT name FROM districts WHERE 1";
$result = mysqli_query($con,$query);
$numrows = mysqli_num_rows($result);

if($numrows>0){
    while($row=mysqli_fetch_assoc($result)){
        array_push($districts,$row["name"]);
    }
}

/* ================= STORAGE POINT MASTER ================= */

$storagePoints = [];
$res = mysqli_query($con, "SELECT name FROM storage_point");
while ($r = mysqli_fetch_assoc($res)) {
    $storagePoints[] = $r['name'];
}

function formatName($name) {
    $name = preg_replace('/[^a-zA-Z0-9_ ]/', '', $name);
    $name = ucwords(strtolower($name));
    return trim($name);
}

function isValidCoordinate($value, $coordinateType) {
    if (!is_numeric($value)) {
        return false;
    }
   
    $coordinate = floatval($value);

    switch ($coordinateType) {
        case 'latitude':
            return ($coordinate > 0 && $coordinate < 40);
        case 'longitude':
            return ($coordinate > 65);
        default:
            return false;
    }
}

function isStringNumber($stringValue) {
    return is_numeric($stringValue) && preg_match('/^\d+(\.\d+)?$/', trim($stringValue)) && floatval($stringValue) >= 0;
}

// Filter the excel data
function filterData(&$str){
    $str = str_replace("\t", "", $str);
}

$redirect = 1;

try{
    //if (isset($_POST["submit"])){
        $fileName = $_FILES["file"]["tmp_name"];
        if ($_FILES["file"]["size"] > 0) {
            $file = fopen($fileName, "r");
            $i = 0;
            $district = -1;
            $name = -1;
            $id = -1;
            $latitude = -1;
            $longitude = -1;
            $Paddy_Procurement = -1;
            $Storage_Point = -1;
            $active = -1;

            while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
                if($i>0){
                    if($district<0 or $name<0 or $id<0 or $Paddy_Procurement<0 or $Storage_Point<0 or $latitude<0 or $longitude<0 or $active<0){
                        echo "Error : You have modified Template Header, please check";
                        exit();
                    }
                    if(!isValidCoordinate($column[$latitude],'latitude') or !isValidCoordinate($column[$longitude],'longitude')){
                        echo "Error : Check Latitude and Longitude Value Latitude: ".$column[$latitude]." Longitude: ".$column[$longitude]." (Latitude must be greater than 0 and less than 40; Longitude must be greater than 65)";
                        echo "</br>";
                        $redirect = 0;
                    }

                    if(!isStringNumber($column[$Paddy_Procurement])){
                        echo "Error : Check Wheat Procurement Value: ".$column[$Paddy_Procurement]." (Must be a non-negative number)";
                        echo "</br>";
                        $redirect = 0;
                    }
                    

					if (
						!isset($column[$id]) ||
						!preg_match('/^[A-Za-z0-9]+$/', $column[$id])
					) {
						echo "Error: PC ID should not contain spaces or any special characters: " . ($column[$id] ?? 'Missing');
						echo "<br>";
						$redirect = 0;
					}	
                    // Injected Name Validation
            if (isset($name) && $name >= 0 && isset($column[$name])) {
                if (!preg_match('/^[a-zA-Z0-9_\-\s\/,\.\\&\(\)]+$/', $column[$name])) {
                    echo "Error : Name should only contain alphanumeric characters and allowed symbols: " . htmlspecialchars($column[$name]) . "<br>";
                    $redirect = 0;
                }
            }
            
            // Injected ID Validation
            if (isset($id) && $id >= 0 && isset($column[$id])) {
                if (!preg_match('/^[a-zA-Z0-9]+$/', $column[$id])) {
                    echo "Error : ID should be a continuous alphanumeric string: " . htmlspecialchars($column[$id]) . "<br>";
                    $redirect = 0;
                }
            }

            // Injected Motorable Validation
            if (isset($warehousetype) && $warehousetype >= 0 && isset($column[$warehousetype])) {
                $wt = strtolower(trim($column[$warehousetype]));
                if (in_array($wt, ['motorable', 'non-motorable', 'non motorable', 'nonmotorable'])) {
                    $column[$warehousetype] = str_replace(['-', ' '], '', $wt);
                } else {
                    echo "Error : Invalid Motorable value: " . htmlspecialchars($column[$warehousetype]) . "<br>";
                    $redirect = 0;
                }
            }
            if(!in_array($column[$district], $districts)){
                        echo "Error : Check District Name: ".$column[$district];
                        echo "</br>";
                        $redirect = 0;
                    }
                    if(!in_array($column[$Storage_Point], $storagePoints)){
                        echo "Error : Check Storage Point Name: ".$column[$Storage_Point];
                        echo "</br>";
                        $redirect = 0;
                    }
                    if(!($column[$active]==0 || $column[$active]==1)){
                        echo "Error : Check value of active/inactive column: ".$column[$active];
                        echo "</br>";
                        $redirect = 0;
                    }
                    $PC = new PC;
                    filterData($column[$latitude]);
                    filterData($column[$longitude]);
                    filterData($column[$name]);
                    filterData($column[$id]);
                    filterData($column[$Paddy_Procurement]);
                    filterData($column[$Storage_Point]);
                    filterData($column[$active]);

                    $PC->setDistrict(ucwords(strtolower($column[$district])));
                    $PC->setLatitude($column[$latitude]);
                    $PC->setLongitude($column[$longitude]);
                    $PC->setName($column[$name]);
                    $PC->setId($column[$id]);
                    $PC->setPaddyProcurement($column[$Paddy_Procurement]);
                    $PC->setStoragePoint($column[$Storage_Point]);
                    $PC->setActive($column[$active]);
                    $query_check = $PC->checkEdit($PC);
                    $query_result = mysqli_query($con, $query_check);
                    $numrows = mysqli_num_rows($query_result);
                    if($numrows==0){
                        echo "Error : in loading data as PC id doesn't exist : ".$column[$id];
                        echo "</br>";
                        $redirect = 0;
                    }
                }
                else{
                    for($j=0;$j<count($column);$j++){
                        switch($column[$j]){
                            case $reverseMapData["district"]:
                                $district = $j;
                                break;
                            case $reverseMapData["latitude"]:
                                $latitude = $j;
                                break;
                            case $reverseMapData["longitude"]:
                                $longitude = $j;
                                break;
                            case $reverseMapData["name"]:
                                $name = $j;
                                break;
                            case $reverseMapData["id"]:
                                $id = $j;
                                break;
                            case $reverseMapData["Paddy_Procurement"]:
                                $Paddy_Procurement = $j;
                                break;
                            case $reverseMapData["Storage_Point"]:
                                $Storage_Point = $j;
                                break;
                            case $reverseMapData["active"]:
                                $active = $j;
                                break;
                        }
                    }
                }
                $i = $i+1;
            }
        }
    //}
    //else{
    //  echo "Error Please Select .csv file";
    //  exit();
    //  //}
}
catch(Exception $e){
    echo "Error : Please check data in .csv file";
    exit();
}

if($redirect==0){
    exit();
}

try{
    //if (isset($_POST["submit"])){
        $fileName = $_FILES["file"]["tmp_name"];
        if ($_FILES["file"]["size"] > 0) {
            $file = fopen($fileName, "r");
            $i = 0;
            $district = -1;
            $name = -1;
            $id = -1;
            $latitude = -1;
            $longitude = -1;
            $Paddy_Procurement = -1;
            $Storage_Point = -1;
            $active = -1;

            while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
                if($i>0){
                    $PC = new PC;
                    filterData($column[$district]);
                    filterData($column[$latitude]);
                    filterData($column[$longitude]);
                    filterData($column[$name]);
                    filterData($column[$id]);
                    filterData($column[$Paddy_Procurement]);
                    filterData($column[$Storage_Point]);
                    filterData($column[$active]);

                    $PC->setDistrict($column[$district]);
                    $PC->setLatitude($column[$latitude]);
                    $PC->setLongitude($column[$longitude]);
                    $PC->setName($column[$name]);
                    $PC->setId($column[$id]);
                    $PC->setPaddyProcurement($column[$Paddy_Procurement]);
                    $PC->setStoragePoint($column[$Storage_Point]);
                    $PC->setActive($column[$active]);
                    $query_check = $PC->checkEdit($PC);
                    $query_result = mysqli_query($con, $query_check);
                    $numrows = mysqli_num_rows($query_result);
                    if($numrows==0){
                        echo "Error : in loading data as PC id doesn't exist : ".$column[$id];
                        echo "</br>";
                        $redirect = 0;
                    }
                    writeLog("User ->" ." PC Edit -> ". $_SESSION['district_user'] . "| " . $PC->getName());
                    $query_update = $PC->updateEdit($PC);
                    mysqli_query($con, $query_update);
                }
                else{
                    for($j=0;$j<count($column);$j++){
                        switch($column[$j]){
                            case $reverseMapData["district"]:
                                $district = $j;
                                break;
                            case $reverseMapData["latitude"]:
                                $latitude = $j;
                                break;
                            case $reverseMapData["longitude"]:
                                $longitude = $j;
                                break;
                            case $reverseMapData["name"]:
                                $name = $j;
                                break;
                            case $reverseMapData["id"]:
                                $id = $j;
                                break;
                            case $reverseMapData["Paddy_Procurement"]:
                                $Paddy_Procurement = $j;
                                break;
                            case $reverseMapData["Storage_Point"]:
                                $Storage_Point = $j;
                                break;
                            case $reverseMapData["active"]:
                                $active = $j;
                                break;
                        }
                    }
                }
                $i = $i+1;
            }
            if($redirect==1){
                echo "<script>window.location.href = '../PC.php';</script>";
            }
        }
}
catch(Exception $e){
    echo "Error : Please check data in  .csv file";
}
}
else{
    echo "Error : Password or Username is incorrect";
}
?>
<?php require('Fullui.php');  ?>