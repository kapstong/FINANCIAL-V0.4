<?php
/**
 * API Authentication Handler
 * Handles JWT-based authentication for external systems
 * while maintaining compatibility with existing PHP session auth
 */

class ApiAuth {
    private $secretKey;
    private $db;
    
    public function __construct() {
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'atiera-api-secret-key-2024';
        $this->db = new Database();
    }
    
    /**
     * Generate JWT token for API access
     */
    public function generateToken($userId, $systemName = 'External System') {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $userId,
            'system' => $systemName,
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ]);
        
        $headerEncoded = $this->base64UrlEncode($header);
        $payloadEncoded = $this->base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $this->secretKey, true);
        $signatureEncoded = $this->base64UrlEncode($signature);
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }
    
    /**
     * Validate JWT token
     */
    public function validateToken($token) {
        if (!$token) {
            return false;
        }
        
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;
        
        // Verify signature
        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $this->secretKey, true);
        $expectedSignature = $this->base64UrlEncode($signature);
        
        if (!hash_equals($expectedSignature, $signatureEncoded)) {
            return false;
        }
        
        // Decode payload
        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
        
        // Check expiration
        if ($payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
    
    /**
     * Require API authentication
     */
    public function requireAuth() {
        $token = $this->getTokenFromRequest();
        $payload = $this->validateToken($token);
        
        if (!$payload) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized', 'message' => 'Invalid or expired token']);
            exit();
        }
        
        return $payload;
    }
    
    /**
     * Create API credentials for external system
     */
    public function createApiCredentials($systemName, $systemType, $propertyId = null) {
        try {
            // Create API user
            $apiUsername = 'api_' . strtolower(str_replace(' ', '_', $systemName)) . '_' . time();
            $apiPassword = bin2hex(random_bytes(16));
            $passwordHash = password_hash($apiPassword, PASSWORD_DEFAULT);
            
            // Insert API user with API role
            $this->db->execute(
                "INSERT INTO users (username, password_hash, role_id) VALUES (?, ?, ?)",
                [$apiUsername, $passwordHash, 5] // Role 5 = API User
            );
            
            $userId = $this->db->lastInsertId();
            
            // Store integration details
            $this->db->execute(
                "INSERT INTO pms_integrations (system_name, system_type, property_id, api_key_hash) VALUES (?, ?, ?, ?)",
                [$systemName, $systemType, $propertyId, hash('sha256', $apiPassword)]
            );
            
            // Generate initial token
            $token = $this->generateToken($userId, $systemName);
            
            return [
                'api_user' => $apiUsername,
                'api_key' => $apiPassword,
                'access_token' => $token,
                'token_expires' => date('Y-m-d H:i:s', time() + (24 * 60 * 60))
            ];
            
        } catch (Exception $e) {
            throw new Exception('Failed to create API credentials: ' . $e->getMessage());
        }
    }
    
    /**
     * Authenticate API credentials and return token
     */
    public function authenticateCredentials($username, $password) {
        $user = $this->db->fetchOne(
            "SELECT id, username, password_hash FROM users WHERE username = ? AND role_id = 5",
            [$username]
        );
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        
        // Update integration sync status
        $this->db->execute(
            "UPDATE pms_integrations SET last_sync_date = NOW(), sync_status = 'active' 
             WHERE api_key_hash = ?",
            [hash('sha256', $password)]
        );
        
        return $this->generateToken($user['id'], 'API Client');
    }
    
    private function getTokenFromRequest() {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        
        return $_GET['token'] ?? $_POST['token'] ?? null;
    }
    
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
?>