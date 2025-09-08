<?php
/**
 * Authentication API Endpoint
 * Handles API authentication for external systems
 */

// Get the sub-path for auth endpoints
$authPath = isset($pathParts[1]) ? $pathParts[1] : '';

switch ($method) {
    case 'POST':
        if ($authPath === 'login') {
            handleApiLogin();
        } elseif ($authPath === 'create') {
            handleCreateApiCredentials();
        } else {
            $response->error('Invalid auth endpoint', 404);
        }
        break;
        
    case 'GET':
        if ($authPath === 'verify') {
            handleTokenVerification();
        } else {
            $response->error('Method not allowed', 405);
        }
        break;
        
    default:
        $response->error('Method not allowed', 405);
}

/**
 * Handle API login with credentials
 */
function handleApiLogin() {
    global $apiAuth, $response, $validator;
    
    $data = $response->getRequestBody();
    
    // Validate input
    $validator->required($data, ['username', 'password']);
    
    if (!$validator->passes()) {
        $response->validationError($validator->getErrors());
    }
    
    try {
        $token = $apiAuth->authenticateCredentials($data['username'], $data['password']);
        
        if (!$token) {
            $response->error('Invalid credentials', 401);
        }
        
        $response->success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 86400, // 24 hours
            'expires_at' => date('Y-m-d H:i:s', time() + 86400)
        ], 'Authentication successful');
        
    } catch (Exception $e) {
        $response->error('Authentication failed: ' . $e->getMessage(), 500);
    }
}

/**
 * Create new API credentials (admin only)
 */
function handleCreateApiCredentials() {
    global $apiAuth, $response, $validator;
    
    // Require existing PHP session authentication for admin users
    session_start();
    $auth = new Auth();
    
    if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
        $response->error('Admin access required', 403);
    }
    
    $data = $response->getRequestBody();
    
    // Validate input
    $validator->required($data, ['system_name', 'system_type'])
              ->length($data, 'system_name', 3, 100)
              ->enum($data, 'system_type', ['pms', 'pos', 'booking_engine', 'channel_manager']);
    
    if (!$validator->passes()) {
        $response->validationError($validator->getErrors());
    }
    
    try {
        $credentials = $apiAuth->createApiCredentials(
            $data['system_name'],
            $data['system_type'],
            $data['property_id'] ?? null
        );
        
        $response->created($credentials, 'API credentials created successfully');
        
    } catch (Exception $e) {
        $response->error('Failed to create API credentials: ' . $e->getMessage(), 500);
    }
}

/**
 * Verify token validity
 */
function handleTokenVerification() {
    global $apiAuth, $response;
    
    try {
        $payload = $apiAuth->requireAuth();
        
        $response->success([
            'valid' => true,
            'user_id' => $payload['user_id'],
            'system' => $payload['system'],
            'expires_at' => date('Y-m-d H:i:s', $payload['exp'])
        ], 'Token is valid');
        
    } catch (Exception $e) {
        $response->error('Token verification failed', 401);
    }
}
?>