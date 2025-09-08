<?php
/**
 * Journal Entry API Endpoint
 * Handles posting of journal entries from external systems
 */

// Require authentication for all journal operations
$authPayload = $apiAuth->requireAuth();

$db = new Database();

switch ($method) {
    case 'POST':
        handlePostJournalEntry();
        break;
        
    case 'GET':
        handleGetJournalEntries();
        break;
        
    default:
        $response->error('Method not allowed', 405);
}

/**
 * Post new journal entry
 */
function handlePostJournalEntry() {
    global $db, $response, $validator, $authPayload;
    
    $data = $response->getRequestBody();
    
    // Validate the journal entry
    $validator->validateJournalEntry($data);
    
    if (!$validator->passes()) {
        $response->validationError($validator->getErrors());
    }
    
    try {
        $db->getConnection()->beginTransaction();
        
        // Verify account exists
        $account = $db->fetchOne("SELECT id, type FROM accounts WHERE id = ?", [$data['account_id']]);
        if (!$account) {
            $response->error('Account not found', 404);
        }
        
        // Insert journal entry
        $entryData = [
            'entry_date' => $data['entry_date'] ?? date('Y-m-d'),
            'account_id' => $data['account_id'],
            'description' => $data['description'],
            'type' => $data['type'],
            'amount' => $data['amount'],
            'reference' => $data['reference'] ?? 'API-' . date('YmdHis'),
            'status' => $data['status'] ?? 'posted',
            'created_by' => $authPayload['user_id']
        ];
        
        $db->execute(
            "INSERT INTO journal_entries (entry_date, account_id, description, type, amount, reference, status, created_by) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            array_values($entryData)
        );
        
        $entryId = $db->lastInsertId();
        
        // Update account balance
        $balanceChange = ($data['type'] === 'debit') ? $data['amount'] : -$data['amount'];
        
        // For normal credit balance accounts (liability, equity, revenue), reverse the logic
        if (in_array($account['type'], ['liability', 'equity', 'revenue'])) {
            $balanceChange = -$balanceChange;
        }
        
        $db->execute(
            "UPDATE accounts SET balance = balance + ? WHERE id = ?",
            [$balanceChange, $data['account_id']]
        );
        
        $db->getConnection()->commit();
        
        // Return the created entry
        $createdEntry = $db->fetchOne(
            "SELECT je.*, a.name as account_name, a.code as account_code 
             FROM journal_entries je 
             JOIN accounts a ON je.account_id = a.id 
             WHERE je.id = ?",
            [$entryId]
        );
        
        $response->created([
            'id' => (int)$createdEntry['id'],
            'entry_date' => $createdEntry['entry_date'],
            'account' => [
                'id' => (int)$createdEntry['account_id'],
                'code' => $createdEntry['account_code'],
                'name' => $createdEntry['account_name']
            ],
            'description' => $createdEntry['description'],
            'type' => $createdEntry['type'],
            'amount' => (float)$createdEntry['amount'],
            'amount_formatted' => number_format($createdEntry['amount'], 2),
            'reference' => $createdEntry['reference'],
            'status' => $createdEntry['status'],
            'created_at' => $createdEntry['created_at']
        ], 'Journal entry created successfully');
        
    } catch (Exception $e) {
        $db->getConnection()->rollBack();
        $response->error('Failed to create journal entry: ' . $e->getMessage(), 500);
    }
}

/**
 * Get journal entries
 */
function handleGetJournalEntries() {
    global $db, $response;
    
    try {
        // Get query parameters
        $accountId = $_GET['account_id'] ?? null;
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;
        $reference = $_GET['reference'] ?? null;
        $page = (int)($_GET['page'] ?? 1);
        $limit = min((int)($_GET['limit'] ?? 50), 100);
        $offset = ($page - 1) * $limit;
        
        // Build query
        $whereConditions = [];
        $params = [];
        
        if ($accountId) {
            $whereConditions[] = "je.account_id = ?";
            $params[] = $accountId;
        }
        
        if ($dateFrom) {
            $whereConditions[] = "je.entry_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $whereConditions[] = "je.entry_date <= ?";
            $params[] = $dateTo;
        }
        
        if ($reference) {
            $whereConditions[] = "je.reference LIKE ?";
            $params[] = "%{$reference}%";
        }
        
        $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM journal_entries je {$whereClause}";
        $totalResult = $db->fetchOne($countQuery, $params);
        $total = $totalResult['total'];
        
        // Get entries
        $query = "SELECT je.*, a.name as account_name, a.code as account_code, a.type as account_type
                  FROM journal_entries je 
                  JOIN accounts a ON je.account_id = a.id 
                  {$whereClause} 
                  ORDER BY je.entry_date DESC, je.id DESC
                  LIMIT {$limit} OFFSET {$offset}";
        
        $entries = $db->fetchAll($query, $params);
        
        // Format response
        $formattedEntries = array_map(function($entry) {
            return [
                'id' => (int)$entry['id'],
                'entry_date' => $entry['entry_date'],
                'account' => [
                    'id' => (int)$entry['account_id'],
                    'code' => $entry['account_code'],
                    'name' => $entry['account_name'],
                    'type' => $entry['account_type']
                ],
                'description' => $entry['description'],
                'type' => $entry['type'],
                'amount' => (float)$entry['amount'],
                'amount_formatted' => number_format($entry['amount'], 2),
                'reference' => $entry['reference'],
                'status' => $entry['status'],
                'created_at' => $entry['created_at']
            ];
        }, $entries);
        
        $response->paginated($formattedEntries, $page, $limit, $total, 'Journal entries retrieved successfully');
        
    } catch (Exception $e) {
        $response->error('Failed to retrieve journal entries: ' . $e->getMessage(), 500);
    }
}
?>