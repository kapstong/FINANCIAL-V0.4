<?php
/**
 * ATIERA Financial System - POS API Endpoint
 * Handles point of sale transaction operations for restaurant integration
 */

require_once '../../config/database.php';
require_once '../includes/ApiAuth.php';
require_once '../includes/ApiResponse.php';
require_once '../includes/ApiValidator.php';

$apiAuth = new ApiAuth();
$response = new ApiResponse();
$validator = new ApiValidator();
$db = new Database();

// Check API authentication
if (!$apiAuth->authenticate()) {
    $response->error('Unauthorized access', 401);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$pathParts = array_filter(explode('/', $_SERVER['REQUEST_URI']));
$transactionId = end($pathParts);

// Validate transaction ID if provided
if ($transactionId && !is_numeric($transactionId)) {
    $transactionId = null;
}

switch ($method) {
    case 'GET':
        if ($transactionId) {
            // Get specific transaction
            getTransaction($db, $response, $transactionId);
        } else {
            // Get all transactions with optional filters
            getTransactions($db, $response);
        }
        break;

    case 'POST':
        if ($transactionId) {
            $response->error('Method not allowed for specific transaction', 405);
        } else {
            // Create new transaction
            createTransaction($db, $response, $validator);
        }
        break;

    case 'PUT':
        if (!$transactionId) {
            $response->error('Transaction ID required for update', 400);
        } else {
            // Update transaction (limited to certain fields)
            updateTransaction($db, $response, $validator, $transactionId);
        }
        break;

    case 'DELETE':
        if (!$transactionId) {
            $response->error('Transaction ID required for void', 400);
        } else {
            // Void transaction (soft delete)
            voidTransaction($db, $response, $transactionId);
        }
        break;

    default:
        $response->error('Method not allowed', 405);
}

function getTransactions($db, $response) {
    try {
        $query = "
            SELECT t.id, t.transaction_id, t.pos_station, t.transaction_date,
                   t.transaction_type, t.payment_method, t.subtotal, t.tax_amount,
                   t.discount_amount, t.total_amount, t.tip_amount, t.guest_count,
                   t.table_number, t.server_id, t.folio_id, t.guest_id, t.status,
                   t.created_at, t.updated_at,
                   f.folio_number, g.first_name, g.last_name
            FROM pos_transactions t
            LEFT JOIN folios f ON t.folio_id = f.id
            LEFT JOIN guest_accounts g ON t.guest_id = g.id
            WHERE 1=1
        ";

        $params = [];
        $conditions = [];

        // Apply filters
        if (isset($_GET['status'])) {
            $conditions[] = "t.status = ?";
            $params[] = $_GET['status'];
        }

        if (isset($_GET['pos_station'])) {
            $conditions[] = "t.pos_station = ?";
            $params[] = $_GET['pos_station'];
        }

        if (isset($_GET['payment_method'])) {
            $conditions[] = "t.payment_method = ?";
            $params[] = $_GET['payment_method'];
        }

        if (isset($_GET['date_from'])) {
            $conditions[] = "DATE(t.transaction_date) >= ?";
            $params[] = $_GET['date_from'];
        }

        if (isset($_GET['date_to'])) {
            $conditions[] = "DATE(t.transaction_date) <= ?";
            $params[] = $_GET['date_to'];
        }

        if (isset($_GET['folio_id'])) {
            $conditions[] = "t.folio_id = ?";
            $params[] = (int)$_GET['folio_id'];
        }

        if (isset($_GET['server_id'])) {
            $conditions[] = "t.server_id = ?";
            $params[] = $_GET['server_id'];
        }

        if (!empty($conditions)) {
            $query .= " AND " . implode(" AND ", $conditions);
        }

        // Add ordering
        $query .= " ORDER BY t.transaction_date DESC";

        $transactions = $db->fetchAll($query, $params);

        // Format response
        $formattedTransactions = array_map(function($transaction) {
            return [
                'id' => (int)$transaction['id'],
                'transaction_id' => $transaction['transaction_id'],
                'pos_station' => $transaction['pos_station'],
                'transaction_date' => $transaction['transaction_date'],
                'transaction_type' => $transaction['transaction_type'],
                'payment_method' => $transaction['payment_method'],
                'financials' => [
                    'subtotal' => (float)$transaction['subtotal'],
                    'tax_amount' => (float)$transaction['tax_amount'],
                    'discount_amount' => (float)$transaction['discount_amount'],
                    'total_amount' => (float)$transaction['total_amount'],
                    'tip_amount' => (float)$transaction['tip_amount']
                ],
                'details' => [
                    'guest_count' => (int)$transaction['guest_count'],
                    'table_number' => $transaction['table_number'],
                    'server_id' => $transaction['server_id']
                ],
                'guest' => $transaction['guest_id'] ? [
                    'id' => (int)$transaction['guest_id'],
                    'name' => $transaction['first_name'] . ' ' . $transaction['last_name']
                ] : null,
                'folio' => $transaction['folio_id'] ? [
                    'id' => (int)$transaction['folio_id'],
                    'number' => $transaction['folio_number']
                ] : null,
                'status' => $transaction['status'],
                'created_at' => $transaction['created_at'],
                'updated_at' => $transaction['updated_at']
            ];
        }, $transactions);

        $response->success([
            'transactions' => $formattedTransactions,
            'count' => count($formattedTransactions),
            'filters' => $_GET
        ]);

    } catch (Exception $e) {
        $response->error('Failed to retrieve transactions: ' . $e->getMessage(), 500);
    }
}

function getTransaction($db, $response, $transactionId) {
    try {
        $transaction = $db->fetchOne(
            "SELECT t.*, f.folio_number, g.first_name, g.last_name
             FROM pos_transactions t
             LEFT JOIN folios f ON t.folio_id = f.id
             LEFT JOIN guest_accounts g ON t.guest_id = g.id
             WHERE t.id = ?",
            [$transactionId]
        );

        if (!$transaction) {
            $response->error('Transaction not found', 404);
            return;
        }

        // Parse items JSON if exists
        $items = [];
        if ($transaction['items']) {
            $items = json_decode($transaction['items'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $items = [];
            }
        }

        $response->success([
            'transaction' => [
                'id' => (int)$transaction['id'],
                'transaction_id' => $transaction['transaction_id'],
                'pos_station' => $transaction['pos_station'],
                'transaction_date' => $transaction['transaction_date'],
                'transaction_type' => $transaction['transaction_type'],
                'payment_method' => $transaction['payment_method'],
                'financials' => [
                    'subtotal' => (float)$transaction['subtotal'],
                    'tax_amount' => (float)$transaction['tax_amount'],
                    'discount_amount' => (float)$transaction['discount_amount'],
                    'total_amount' => (float)$transaction['total_amount'],
                    'tip_amount' => (float)$transaction['tip_amount']
                ],
                'details' => [
                    'guest_count' => (int)$transaction['guest_count'],
                    'table_number' => $transaction['table_number'],
                    'server_id' => $transaction['server_id'],
                    'items' => $items
                ],
                'guest' => $transaction['guest_id'] ? [
                    'id' => (int)$transaction['guest_id'],
                    'name' => $transaction['first_name'] . ' ' . $transaction['last_name']
                ] : null,
                'folio' => $transaction['folio_id'] ? [
                    'id' => (int)$transaction['folio_id'],
                    'number' => $transaction['folio_number']
                ] : null,
                'status' => $transaction['status'],
                'created_at' => $transaction['created_at'],
                'updated_at' => $transaction['updated_at']
            ]
        ]);

    } catch (Exception $e) {
        $response->error('Failed to retrieve transaction: ' . $e->getMessage(), 500);
    }
}

function createTransaction($db, $response, $validator) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            $response->error('Invalid JSON data', 400);
            return;
        }

        // Validate required fields
        $required = ['transaction_id', 'transaction_date', 'subtotal', 'total_amount'];
        $validation = $validator->validateRequired($data, $required);

        if (!$validation['valid']) {
            $response->error('Missing required fields: ' . implode(', ', $validation['missing']), 400);
            return;
        }

        // Check if transaction ID already exists
        $existing = $db->fetchOne(
            "SELECT id FROM pos_transactions WHERE transaction_id = ?",
            [$data['transaction_id']]
        );

        if ($existing) {
            $response->error('Transaction ID already exists', 409);
            return;
        }

        // Validate folio exists if provided
        if (isset($data['folio_id'])) {
            $folioCheck = $db->fetchOne(
                "SELECT id FROM folios WHERE id = ?",
                [$data['folio_id']]
            );

            if (!$folioCheck) {
                $response->error('Folio not found', 404);
                return;
            }
        }

        // Validate guest exists if provided
        if (isset($data['guest_id'])) {
            $guestCheck = $db->fetchOne(
                "SELECT id FROM guest_accounts WHERE id = ?",
                [$data['guest_id']]
            );

            if (!$guestCheck) {
                $response->error('Guest not found', 404);
                return;
            }
        }

        // Prepare items as JSON
        $itemsJson = null;
        if (isset($data['items']) && is_array($data['items'])) {
            $itemsJson = json_encode($data['items']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $response->error('Invalid items format', 400);
                return;
            }
        }

        $sql = "
            INSERT INTO pos_transactions (
                transaction_id, pos_station, transaction_date, transaction_type,
                payment_method, subtotal, tax_amount, discount_amount, total_amount,
                tip_amount, guest_count, table_number, server_id, items,
                folio_id, guest_id, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $params = [
            $data['transaction_id'],
            $data['pos_station'] ?? null,
            $data['transaction_date'],
            $data['transaction_type'] ?? 'sale',
            $data['payment_method'] ?? 'cash',
            (float)$data['subtotal'],
            (float)($data['tax_amount'] ?? 0),
            (float)($data['discount_amount'] ?? 0),
            (float)$data['total_amount'],
            (float)($data['tip_amount'] ?? 0),
            (int)($data['guest_count'] ?? 1),
            $data['table_number'] ?? null,
            $data['server_id'] ?? null,
            $itemsJson,
            isset($data['folio_id']) ? (int)$data['folio_id'] : null,
            isset($data['guest_id']) ? (int)$data['guest_id'] : null,
            $data['status'] ?? 'completed'
        ];

        $db->execute($sql, $params);
        $transactionId = $db->lastInsertId();

        $response->success([
            'message' => 'POS transaction created successfully',
            'transaction_id' => $transactionId,
            'transaction' => array_merge($data, ['id' => $transactionId])
        ], 201);

    } catch (Exception $e) {
        $response->error('Failed to create transaction: ' . $e->getMessage(), 500);
    }
}

function updateTransaction($db, $response, $validator, $transactionId) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            $response->error('Invalid JSON data', 400);
            return;
        }

        // Check if transaction exists
        $existing = $db->fetchOne(
            "SELECT id, status FROM pos_transactions WHERE id = ?",
            [$transactionId]
        );

        if (!$existing) {
            $response->error('Transaction not found', 404);
            return;
        }

        // Only allow updates for completed transactions
        if ($existing['status'] !== 'completed') {
            $response->error('Can only update completed transactions', 409);
            return;
        }

        // Build update query dynamically (limited fields for updates)
        $updates = [];
        $params = [];

        $allowedFields = [
            'tip_amount', 'server_id', 'table_number', 'guest_count'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'tip_amount') {
                    $updates[] = "$field = ?";
                    $params[] = (float)$data[$field];
                } elseif (in_array($field, ['guest_count'])) {
                    $updates[] = "$field = ?";
                    $params[] = (int)$data[$field];
                } else {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
        }

        if (empty($updates)) {
            $response->error('No valid fields to update', 400);
            return;
        }

        $sql = "UPDATE pos_transactions SET " . implode(', ', $updates) . " WHERE id = ?";
        $params[] = $transactionId;

        $db->execute($sql, $params);

        $response->success([
            'message' => 'Transaction updated successfully',
            'transaction_id' => $transactionId,
            'updated_fields' => array_keys($data)
        ]);

    } catch (Exception $e) {
        $response->error('Failed to update transaction: ' . $e->getMessage(), 500);
    }
}

function voidTransaction($db, $response, $transactionId) {
    try {
        // Check if transaction exists
        $transaction = $db->fetchOne(
            "SELECT id, status FROM pos_transactions WHERE id = ?",
            [$transactionId]
        );

        if (!$transaction) {
            $response->error('Transaction not found', 404);
            return;
        }

        // Only allow voiding of completed transactions
        if ($transaction['status'] !== 'completed') {
            $response->error('Can only void completed transactions', 409);
            return;
        }

        // Update status to voided
        $db->execute(
            "UPDATE pos_transactions SET status = 'voided' WHERE id = ?",
            [$transactionId]
        );

        $response->success([
            'message' => 'Transaction voided successfully',
            'transaction_id' => $transactionId
        ]);

    } catch (Exception $e) {
        $response->error('Failed to void transaction: ' . $e->getMessage(), 500);
    }
}
?>
