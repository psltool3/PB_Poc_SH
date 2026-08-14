<?php
require('../util/Connection.php');
require('../util/SessionFunction.php');
require('../util/Logger.php');

if(!SessionCheck()){
	return;
}

require('Header.php');

foreach ($_POST as $key => $value) {
    if (substr($key, 0, 5) === 'cost_' && !empty($value)) {
        // Extract the ID — must be numeric only
        $id = substr($key, 5);
        // if (!ctype_digit((string)$id)) {
        //     echo "Error : Invalid request.";
        //     return;
        // }

        $value_temp = $value;

        // Strict validation: only allow positive integers or decimals (blocks scripts/HTML)
        if (!preg_match('/^\d+(\.\d+)?$/', trim($value_temp))) {
            echo "Error : Invalid value. Cost must be a valid positive number.";
            return;
        }

        $value = (float)$value_temp;

        // Log query with prepared statement
        $log_query = "SELECT * FROM optimised_table_leg1 WHERE id = ?";
        $log_stmt = $con->prepare($log_query);
        $log_stmt->bind_param("i", $id);
        $log_stmt->execute();
        $log_result = $log_stmt->get_result();
        if ($log_result && $row = $log_result->fetch_assoc()) {
            $user_id  = $row['year'];
            $user_id1 = $row['month'];
        }
        $log_stmt->close();

        $filteredPost = $_POST;
        unset($filteredPost['username'], $filteredPost['password']);
        writeLog("User ->" ." Cost Added ->". $_SESSION['user'] . "| Requested JSON -> " . json_encode($filteredPost). " | " . $user_id." | " . $user_id1);

        // UPDATE with prepared statement (prevents SQL injection)
        $sql = "UPDATE optimised_table_leg1 SET cost = ? WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("di", $value, $id);
        if ($stmt->execute() !== TRUE) {
            echo "Error : updating record: " . $con->error;
            $stmt->close();
            return;
        }
        $stmt->close();
    }

	echo "<script>window.location.href = '../Performa.php';</script>";
}

?>

<?php require('Fullui.php');  ?>