<?php

require('../util/Connection.php');
require('../util/Security.php');
require('../structures/Login.php');
require ('../util/Encryption.php');
require ('../util/SessionFunction.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Something went wrong. Request denied.");
}

$userCaptcha = $_POST['captchainput'] ?? '';
$sessionCaptcha = $_SESSION['captcha'] ?? '';
unset($_SESSION['captcha']);

if (empty($userCaptcha) || empty($sessionCaptcha) || $userCaptcha !== $sessionCaptcha) {
	die("Please Check Captcha");
}

checkLoginRateLimit($con);
$person = new Login;
$person->setUsername($_POST["username"]);
$Encryption = new Encryption();
$person->setPassword($Encryption->decrypt($_POST["password"], $_SESSION['csrf_token']));

$query = "SELECT * FROM login WHERE username='".$person->getUsername()."'";
$result = mysqli_query($con,$query);
$row = mysqli_fetch_assoc($result);

if(empty($row)){
    recordLoginAttempt($con);
	die("Error : Password or Username is incorrect");
}

$dbHashedPassword = $row['password'];
if(password_verify($person->getPassword(), $dbHashedPassword)){
 if($row['role']=="admin"){
		$count = 1 + $row['count'];
		$uniqueId = uniqid();
		$authToken = md5($uniqueId);
		$currentLoginTime = date("Y-m-d H:i:s");
		$queryUpdate = "UPDATE login SET token='$authToken',lastlogin='$currentLoginTime',count='$count' WHERE username='".$person->getUsername()."'";
		mysqli_query($con,$queryUpdate);
		
		$_SESSION['user'] = $person->getUsername();
		$_SESSION['token'] = $authToken;
		
		mysqli_close($con);
		echo "<script>window.location.href = '../Home.php';</script>";
    }
	else{
		echo "<script>alert('wrong role selected');window.location.href = './Home.php';</script>";
		exit();
	}
} 
else{
    recordLoginAttempt($con);
    echo "Error : Password or Username is incorrect";
}

?>
