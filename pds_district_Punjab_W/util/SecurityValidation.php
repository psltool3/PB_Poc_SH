<?php

class SecurityValidation {
    public static function validateNameField($value, $fieldName) {
        if (preg_match('/^[a-zA-Z0-9_\-\s\/,\.\\\&]+$/', $value) !== 1) {
            http_response_code(400);
            echo "Error : $fieldName should only contain alphanumeric characters and allowed symbols";
            exit();
        }
        return true;
    }

    public static function validateIDField($value, $fieldName) {
        if (preg_match('/^[a-zA-Z0-9]+$/', $value) !== 1) {
            http_response_code(400);
            echo "Error : $fieldName should only contain continuous alphanumeric characters without spaces";
            exit();
        }
        return true;
    }

    public static function normalizeMotorable($value) {
        $normalized = strtolower(str_replace([' ', '-'], '', $value));
        if ($normalized === 'motorable' || $normalized === 'nonmotorable') {
            return $normalized;
        }
        http_response_code(400);
        echo "Error : Motorable/Non-Motorable field must be valid";
        exit();
    }
    
    // For bulk uploads, we return boolean or normalized string instead of exiting
    public static function isValidName($value) {
        return preg_match('/^[a-zA-Z0-9_\-\s\/,\.\\\&]+$/', $value) === 1;
    }

    public static function isValidID($value) {
        return preg_match('/^[a-zA-Z0-9]+$/', $value) === 1;
    }

    public static function getNormalizedMotorable($value) {
        $normalized = strtolower(str_replace([' ', '-'], '', $value));
        if ($normalized === 'motorable' || $normalized === 'nonmotorable') {
            return $normalized;
        }
        return false;
    }
}
?>
