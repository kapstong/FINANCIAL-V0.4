<?php
/**
 * API Response Handler
 * Standardized JSON response formatting for the API
 */

class ApiResponse {
    
    /**
     * Send success response
     */
    public function success($data = [], $message = 'Success', $statusCode = 200) {
        http_response_code($statusCode);
        
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s'),
            'api_version' => '1.0'
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit();
    }
    
    /**
     * Send error response
     */
    public function error($message = 'An error occurred', $statusCode = 400, $errorCode = null) {
        http_response_code($statusCode);
        
        $response = [
            'status' => 'error',
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'api_version' => '1.0'
        ];
        
        if ($errorCode) {
            $response['error_code'] = $errorCode;
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit();
    }
    
    /**
     * Send paginated response
     */
    public function paginated($data, $page = 1, $limit = 10, $total = 0, $message = 'Success') {
        $totalPages = ceil($total / $limit);
        
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'current_page' => (int)$page,
                'per_page' => (int)$limit,
                'total_records' => (int)$total,
                'total_pages' => (int)$totalPages,
                'has_next_page' => $page < $totalPages,
                'has_prev_page' => $page > 1
            ],
            'timestamp' => date('Y-m-d H:i:s'),
            'api_version' => '1.0'
        ];
        
        http_response_code(200);
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit();
    }
    
    /**
     * Send validation error response
     */
    public function validationError($errors) {
        http_response_code(422);
        
        $response = [
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s'),
            'api_version' => '1.0'
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit();
    }
    
    /**
     * Send created response (for POST requests)
     */
    public function created($data = [], $message = 'Resource created successfully') {
        $this->success($data, $message, 201);
    }
    
    /**
     * Send no content response (for DELETE requests)
     */
    public function noContent() {
        http_response_code(204);
        exit();
    }
    
    /**
     * Get request body as JSON
     */
    public function getRequestBody() {
        $json = file_get_contents('php://input');
        return json_decode($json, true);
    }
    
    /**
     * Log API request for debugging
     */
    public function logRequest($endpoint, $method, $data = []) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoint' => $endpoint,
            'method' => $method,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'data' => $data
        ];
        
        // Log to file (optional)
        $logFile = '../logs/api_' . date('Y-m-d') . '.log';
        if (is_dir('../logs')) {
            file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND);
        }
    }
}
?>