<?php
/**
 * Accounts API Endpoint
 * Provides access to Chart of Accounts for external systems
 */

// Require authentication for all account operations
$authPayload = $apiAuth->requireAuth();

$db = new Database();

switch ($method) {
    case 'GET':
        handleGetAccounts();
        break;
        
    default:
        $response->error('Method not allowed', 405);
}

/**
 * Get chart of accounts
 */
function handleGetAccounts() {
    global $db, $response;
    
    try {
        // Get query parameters
        $type = $_GET['type'] ?? null;
        $search = $_GET['search'] ?? null;
        $page = (int)($_GET['page'] ?? 1);
        $limit = min((int)($_GET['limit'] ?? 50), 100); // Max 100 records per page
        $offset = ($page - 1) * $limit;
        
        // Build query
        $whereConditions = [];
        $params = [];
        
        if ($type) {
            $whereConditions[] = "type = ?";
            $params[] = $type;
        }
        
        if ($search) {
            $whereConditions[] = "(code LIKE ? OR name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM accounts {$whereClause}";
        $totalResult = $db->fetchOne($countQuery, $params);
        $total = $totalResult['total'];
        
        // Get accounts
        $query = "SELECT id, code, name, type, balance, 
                         CASE 
                           WHEN type IN ('asset', 'expense') THEN 'debit'
                           WHEN type IN ('liability', 'equity', 'revenue') THEN 'credit'
                         END as normal_balance
                  FROM accounts 
                  {$whereClause} 
                  ORDER BY code 
                  LIMIT {$limit} OFFSET {$offset}";
        
        $accounts = $db->fetchAll($query, $params);
        
        // Format response
        $formattedAccounts = array_map(function($account) {
            return [
                'id' => (int)$account['id'],
                'code' => $account['code'],
                'name' => $account['name'],
                'type' => $account['type'],
                'normal_balance' => $account['normal_balance'],
                'balance' => (float)$account['balance'],
                'balance_formatted' => number_format($account['balance'], 2)
            ];
        }, $accounts);
        
        $response->paginated($formattedAccounts, $page, $limit, $total, 'Accounts retrieved successfully');
        
    } catch (Exception $e) {
        $response->error('Failed to retrieve accounts: ' . $e->getMessage(), 500);
    }
}
?>