<?php
require('../util/Connection.php');
require('../util/SessionCheck.php');

require('Header.php');

$query = "SELECT * FROM optimised_table_leg1 ORDER BY last_updated DESC LIMIT 1";
$result = mysqli_query($con,$query);
$id = "";
while($row = mysqli_fetch_array($result))
{
	$id= $row["id"];
}
if ($id != "") {
	$query = "UPDATE optimised_table_leg1 SET rolled_out='1' WHERE id='$id'";
	mysqli_query($con,$query);
}

mysqli_close($con);
echo "<script>window.location.href = '../OptimisedData.php';</script>";
?>
<?php require('Fullui.php');  ?>