<?php

// Function to sanitize and escape HTML characters
function escapeHTML($input) {
    return htmlspecialchars(strip_tags($input, '<b><i><u><strong><em><ul><ol><li>'), ENT_QUOTES, 'UTF-8');
}

/*function whitelistInput($input) {
    // Define a whitelist of allowed characters for alphanumeric and spaces
	$allowedCharacters = "/^[a-zA-Z0-9@\.\s\-\_\#\$]+$/";

    // Check if the input matches the alphanumeric and spaces whitelist
    return preg_match($allowedCharacters, $input) ? $input : false;
}*/

function whitelistInput($input, $allowParentheses = false) {
    // Define a whitelist of allowed characters for alphanumeric and spaces
    if ($allowParentheses) {
        $allowedCharacters = "/[^a-zA-Z0-9@\.\s\-_#\$\(\)]+/";
    } else {
        $allowedCharacters = "/[^a-zA-Z0-9@\.\s\-_#\$]+/";
    }
    
    // Remove disallowed characters from the input
    $sanitizedInput = preg_replace($allowedCharacters, "", $input);

    // Return the sanitized input
    return $sanitizedInput;
}


function removeWhiteSpace($string){
	$clean_string = preg_replace('/\s+/u', ' ', $string);
	return $clean_string;
}

// Function for HTML and URL decoding followed by validation
function validateAndDecode($input) {
    // Decode HTML entities and URL encoding
    $decodedInput = urldecode(html_entity_decode($input, ENT_QUOTES, 'UTF-8'));
    
    // Check if input is not empty, strictly allowing '0' and 0
    return ($decodedInput !== '' && $decodedInput !== null && $decodedInput !== false) ? $decodedInput : false;
}

// Apply positive input validation to all elements in $_POST
foreach ($_POST as $key => $value) {
    //$_POST[$key] = removeWhiteSpace($value);
}

// Apply positive input validation to all elements in $_GET
foreach ($_GET as $key => $value) {
    $_GET[$key] = removeWhiteSpace($value);
}

// Check and sanitize all elements in $_POST
foreach ($_POST as $key => $value) {
    //$_POST[$key] = escapeHTML($value);
}

// Check and sanitize all elements in $_GET
foreach ($_GET as $key => $value) {
    //$_GET[$key] = escapeHTML($value);
}

// Apply positive input validation to all elements in $_POST
foreach ($_POST as $key => $value) {
    $lower_key = strtolower($key);
    $allowParentheses = in_array($lower_key, ['name', 'pc_name', 'mill_name', 'warehouse_name']);
    $_POST[$key] = whitelistInput($value, $allowParentheses);
}

// Apply positive input validation to all elements in $_GET
foreach ($_GET as $key => $value) {
    $lower_key = strtolower($key);
    $allowParentheses = in_array($lower_key, ['name', 'pc_name', 'mill_name', 'warehouse_name']);
    $_GET[$key] = whitelistInput($value, $allowParentheses);
}

// Apply HTML and URL decoding followed by validation to all elements in $_POST
foreach ($_POST as $key => $value) {
   $_POST[$key] = validateAndDecode($value);
}

// Apply HTML and URL decoding followed by validation to all elements in $_GET
foreach ($_GET as $key => $value) {
    $_GET[$key] = validateAndDecode($value);
}


// Security Validation for Name, ID, and Motorable Fields
foreach ($_POST as $key => $value) {
    $lower_key = strtolower($key);
    if (in_array($lower_key, ['name', 'pc_name', 'mill_name', 'warehouse_name'])) {
        if (!preg_match('/^[a-zA-Z0-9_\-\s\/,\.\\&\(\)]+$/', $value)) {
            http_response_code(400);
            die("Error : Name should only contain alphanumeric characters, spaces, and allowed symbols.");
        }
    }
    if (in_array($lower_key, ['id', 'pc_id', 'mill_id', 'warehouse_id'])) {
        if (!preg_match('/^[a-zA-Z0-9]+$/', $value)) {
            http_response_code(400);
            die("Error : ID should be a continuous alphanumeric string.");
        }
    }
    if ($lower_key === 'milling_process') {
        if (!is_numeric($value)) {
            http_response_code(400);
            die("Error : Milling Process must be numeric.");
        }
    }
    if ($lower_key === 'warehousetype') {
        $val = strtolower(trim($value));
        if (in_array($val, ['motorable', 'non-motorable', 'non motorable', 'nonmotorable'])) {
            $_POST[$key] = str_replace(['-', ' '], '', $val);
        }
    }
}

?>
