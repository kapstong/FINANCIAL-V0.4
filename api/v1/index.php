<?php
/**
 * ATIERA Financial System API v1.0
 * RESTful API for Hotel & Restaurant Management Integration
 * 
 * This API maintains the existing PHP frontend structure while providing
 * secure endpoints for external PMS/POS system integration.
 */

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include necessary files
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once 'includes/ApiAuth.php';
require_once 'includes/ApiResponse.php';
require_once 'includes/ApiValidator.php';

// Initialize API components
$apiAuth = new ApiAuth();
$response = new ApiResponse();
$validator = new ApiValidator();

try {
    // Parse the request path
    $requestUri = $_SERVER['REQUEST_URI'];
    $basePath = '/FINANCIAL v0.2/api/v1/';
    
    // Remove base path and query parameters
    $path = str_replace($basePath, '', parse_url($requestUri, PHP_URL_PATH));
    $pathParts = array_filter(explode('/', $path));
    
    // Get HTTP method
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Route the request
    if (empty($pathParts)) {
        // API root - return API info
        $response->success([
            'name' => 'ATIERA Financial System API',
            'version' => '1.0',
            'description' => 'RESTful API for Hotel & Restaurant Management Integration',
            'endpoints' => [
                '/auth/login' => 'POST - API Authentication',
                '/rooms' => 'GET, POST - Room management',
                '/folios' => 'GET, POST - Guest folio management',
                '/pos/transactions' => 'GET, POST - POS transaction handling',
                '/accounts' => 'GET - Chart of accounts',
                '/journal' => 'POST - Journal entry posting',
                '/reports' => 'GET - Financial reports'
            ],
            'documentation' => '/api/v1/docs'
        ]);
        exit();
    }
    
    $endpoint = $pathParts[0];
    
    // Route to appropriate handler
    switch ($endpoint) {
        case 'auth':
            require_once 'endpoints/auth.php';
            break;
            
        case 'rooms':
            require_once 'endpoints/rooms.php';
            break;
            
        case 'folios':
            require_once 'endpoints/folios.php';
            break;
            
        case 'pos':
            require_once 'endpoints/pos.php';
            break;
            
        case 'accounts':
            require_once 'endpoints/accounts.php';
            break;
            
        case 'journal':
            require_once 'endpoints/journal.php';
            break;
            
        case 'reports':
            require_once 'endpoints/reports.php';
            break;
            
        case 'docs':
            require_once 'docs/index.php';
            break;
            
        default:
            $response->error('Endpoint not found', 404);
    }
    
} catch (Exception $e) {
    $response->error('Internal server error: ' . $e->getMessage(), 500);
}
?>