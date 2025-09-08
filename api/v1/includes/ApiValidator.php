<?php
/**
 * API Validator
 * Input validation and sanitization for API requests
 */

class ApiValidator {
    
    private $errors = [];
    
    /**
     * Validate required fields
     */
    public function required($data, $fields) {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->errors[$field] = "Field '{$field}' is required";
            }
        }
        return $this;
    }
    
    /**
     * Validate email format
     */
    public function email($data, $field) {
        if (isset($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "Field '{$field}' must be a valid email address";
        }
        return $this;
    }
    
    /**
     * Validate numeric values
     */
    public function numeric($data, $field, $min = null, $max = null) {
        if (isset($data[$field])) {
            if (!is_numeric($data[$field])) {
                $this->errors[$field] = "Field '{$field}' must be numeric";
            } else {
                $value = (float)$data[$field];
                if ($min !== null && $value < $min) {
                    $this->errors[$field] = "Field '{$field}' must be at least {$min}";
                }
                if ($max !== null && $value > $max) {
                    $this->errors[$field] = "Field '{$field}' must not exceed {$max}";
                }
            }
        }
        return $this;
    }
    
    /**
     * Validate date format
     */
    public function date($data, $field, $format = 'Y-m-d') {
        if (isset($data[$field])) {
            $date = DateTime::createFromFormat($format, $data[$field]);
            if (!$date || $date->format($format) !== $data[$field]) {
                $this->errors[$field] = "Field '{$field}' must be a valid date in format {$format}";
            }
        }
        return $this;
    }
    
    /**
     * Validate string length
     */
    public function length($data, $field, $min = null, $max = null) {
        if (isset($data[$field])) {
            $length = strlen($data[$field]);
            if ($min !== null && $length < $min) {
                $this->errors[$field] = "Field '{$field}' must be at least {$min} characters";
            }
            if ($max !== null && $length > $max) {
                $this->errors[$field] = "Field '{$field}' must not exceed {$max} characters";
            }
        }
        return $this;
    }
    
    /**
     * Validate enum values
     */
    public function enum($data, $field, $allowedValues) {
        if (isset($data[$field]) && !in_array($data[$field], $allowedValues)) {
            $this->errors[$field] = "Field '{$field}' must be one of: " . implode(', ', $allowedValues);
        }
        return $this;
    }
    
    /**
     * Validate decimal values for financial amounts
     */
    public function decimal($data, $field, $precision = 2) {
        if (isset($data[$field])) {
            if (!is_numeric($data[$field])) {
                $this->errors[$field] = "Field '{$field}' must be a valid decimal number";
            } else {
                $value = (string)$data[$field];
                $parts = explode('.', $value);
                if (count($parts) > 1 && strlen($parts[1]) > $precision) {
                    $this->errors[$field] = "Field '{$field}' must have at most {$precision} decimal places";
                }
            }
        }
        return $this;
    }
    
    /**
     * Custom validation rule
     */
    public function custom($data, $field, $callback, $message = null) {
        if (isset($data[$field]) && !$callback($data[$field])) {
            $this->errors[$field] = $message ?: "Field '{$field}' is invalid";
        }
        return $this;
    }
    
    /**
     * Check if validation passed
     */
    public function passes() {
        return empty($this->errors);
    }
    
    /**
     * Get validation errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Clear validation errors
     */
    public function clear() {
        $this->errors = [];
        return $this;
    }
    
    /**
     * Sanitize input data
     */
    public function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate folio charge data
     */
    public function validateFolioCharge($data) {
        return $this->required($data, ['folio_id', 'charge_type', 'description', 'unit_price'])
                   ->numeric($data, 'folio_id', 1)
                   ->enum($data, 'charge_type', ['room', 'food', 'beverage', 'tax', 'service', 'other'])
                   ->length($data, 'description', 1, 255)
                   ->decimal($data, 'unit_price')
                   ->numeric($data, 'quantity', 1)
                   ->date($data, 'charge_date');
    }
    
    /**
     * Validate POS transaction data
     */
    public function validatePosTransaction($data) {
        return $this->required($data, ['location_id', 'subtotal', 'total_amount', 'payment_method'])
                   ->numeric($data, 'location_id', 1)
                   ->decimal($data, 'subtotal')
                   ->decimal($data, 'total_amount')
                   ->enum($data, 'payment_method', ['cash', 'card', 'room_charge', 'complimentary'])
                   ->numeric($data, 'guest_count', 1);
    }
    
    /**
     * Validate journal entry data
     */
    public function validateJournalEntry($data) {
        return $this->required($data, ['account_id', 'type', 'amount', 'description'])
                   ->numeric($data, 'account_id', 1)
                   ->enum($data, 'type', ['debit', 'credit'])
                   ->decimal($data, 'amount')
                   ->length($data, 'description', 1, 500)
                   ->date($data, 'entry_date');
    }
}
?>