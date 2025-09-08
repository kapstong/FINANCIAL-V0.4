<?php
/**
 * ATIERA Financial System - Folios API Endpoint
 * Handles guest billing account operations for PMS integration
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
$folioId = end($pathParts);

// Validate folio ID if provided
if ($folioId && !is_numeric($folioId)) {
    $folioId = null;
}

switch ($method) {
    case 'GET':
        if ($folioId) {
            // Get specific folio
            getFolio($db, $response, $folioId);
        } else {
            // Get all folios with optional filters
            getFolios($db, $response);
        }
        break;

    case 'POST':
        if ($folioId) {
            $response->error('Method not allowed for specific folio', 405);
        } else {
            // Create new folio
            createFolio($db, $response, $validator);
        }
        break;

    case 'PUT':
        if (!$folioId) {
            $response->error('Folio ID required for update', 400);
        } else {
            // Update folio
            updateFolio($db, $response, $validator, $folioId);
        }
        break;

    case 'DELETE':
        if (!$folioId) {
            $response->error('Folio ID required for deletion', 400);
        } else {
            // Delete folio (only if not posted to GL)
            deleteFolio($db, $response, $folioId);
        }
        break;

    default:
        $response->error('Method not allowed', 405);
}

function getFolios($db, $response) {
    try {
        $query = "
            SELECT f.id, f.folio_number, f.guest_id, f.room_id, f.check_in_date,
                   f.check_out_date, f.actual_check_out, f.status, f.total_charges,
                   f.total_payments, f.balance, f.guest_name, f.guest_email,
                   f.guest_phone, f.special_requests, f.created_at, f.updated_at,
                   r.room_number, r.room_type
            FROM folios f
            LEFT JOIN rooms r ON f.room_id = r.id
            WHERE 1=1
        ";

        $params = [];
        $conditions = [];

        // Apply filters
        if (isset($_GET['status'])) {
            $conditions[] = "f.status = ?";
            $params[] = $_GET['status'];
        }

        if (isset($_GET['room_id'])) {
            $conditions[] = "f.room_id = ?";
            $params[] = (int)$_GET['room_id'];
        }

        if (isset($_GET['guest_id'])) {
            $conditions[] = "f.guest_id = ?";
            $params[] = (int)$_GET['guest_id'];
        }

        if (isset($_GET['date_from'])) {
            $conditions[] = "f.check_in_date >= ?";
            $params[] = $_GET['date_from'];
        }

        if (isset($_GET['date_to'])) {
            $conditions[] = "f.check_in_date <= ?";
            $params[] = $_GET['date_to'];
        }

        if (!empty($conditions)) {
            $query .= " AND " . implode(" AND ", $conditions);
        }

        // Add ordering
        $query .= " ORDER BY f.created_at DESC";

        $folios = $db->fetchAll($query, $params);

        // Format response
        $formattedFolios = array_map(function($folio) {
            return [
                'id' => (int)$folio['id'],
                'folio_number' => $folio['folio_number'],
                'guest' => [
                    'id' => $folio['guest_id'] ? (int)$folio['guest_id'] : null,
                    'name' => $folio['guest_name'],
                    'email' => $folio['guest_email'],
                    'phone' => $folio['guest_phone']
                ],
                'room' => $folio['room_id'] ? [
                    'id' => (int)$folio['room_id'],
                    'number' => $folio['room_number'],
                    'type' => $folio['room_type']
                ] : null,
                'stay' => [
                    'check_in_date' => $folio['check_in_date'],
                    'check_out_date' => $folio['check_out_date'],
                    'actual_check_out' => $folio['actual_check_out']
                ],
                'status' => $folio['status'],
                'financials' => [
                    'total_charges' => (float)$folio['total_charges'],
                    'total_payments' => (float)$folio['total_payments'],
                    'balance' => (float)$folio['balance']
                ],
                'special_requests' => $folio['special_requests'],
                'created_at' => $folio['created_at'],
                'updated_at' => $folio['updated_at']
            ];
        }, $folios);

        $response->success([
            'folios' => $formattedFolios,
            'count' => count($formattedFolios),
            'filters' => $_GET
        ]);

    } catch (Exception $e) {
        $response->error('Failed to retrieve folios: ' . $e->getMessage(), 500);
    }
}

function getFolio($db, $response, $folioId) {
    try {
        $folio = $db->fetchOne(
            "SELECT f.*, r.room_number, r.room_type, g.first_name, g.last_name
             FROM folios f
             LEFT JOIN rooms r ON f.room_id = r.id
             LEFT JOIN guest_accounts g ON f.guest_id = g.id
             WHERE f.id = ?",
            [$folioId]
        );

        if (!$folio) {
            $response->error('Folio not found', 404);
            return;
        }

        // Get folio charges (this would be linked to actual charges table in full implementation)
        $charges = $db->fetchAll(
            "SELECT * FROM folio_charges WHERE folio_id = ? ORDER BY created_at ASC",
            [$folioId]
        );

        // Get payments
        $payments = $db->fetchAll(
            "SELECT * FROM payments WHERE folio_id = ? ORDER BY payment_date ASC",
            [$folioId]
        );

        $response->success([
            'folio' => [
                'id' => (int)$folio['id'],
                'folio_number' => $folio['folio_number'],
                'guest' => [
                    'id' => $folio['guest_id'] ? (int)$folio['guest_id'] : null,
                    'name' => $folio['guest_name'] ?: ($folio['first_name'] . ' ' . $folio['last_name']),
                    'email' => $folio['guest_email'],
                    'phone' => $folio['guest_phone']
                ],
                'room' => $folio['room_id'] ? [
                    'id' => (int)$folio['room_id'],
                    'number' => $folio['room_number'],
                    'type' => $folio['room_type']
                ] : null,
                'stay' => [
                    'check_in_date' => $folio['check_in_date'],
                    'check_out_date' => $folio['check_out_date'],
                    'actual_check_out' => $folio['actual_check_out']
                ],
                'status' => $folio['status'],
                'financials' => [
                    'total_charges' => (float)$folio['total_charges'],
                    'total_payments' => (float)$folio['total_payments'],
                    'balance' => (float)$folio['balance']
                ],
                'special_requests' => $folio['special_requests'],
                'created_at' => $folio['created_at'],
                'updated_at' => $folio['updated_at']
            ],
            'charges' => array_map(function($charge) {
                return [
                    'id' => (int)$charge['id'],
                    'description' => $charge['description'],
                    'amount' => (float)$charge['amount'],
                    'date' => $charge['charge_date'],
                    'type' => $charge['charge_type']
                ];
            }, $charges),
            'payments' => array_map(function($payment) {
                return [
                    'id' => (int)$payment['id'],
                    'amount' => (float)$payment['amount'],
                    'method' => $payment['payment_method'],
                    'date' => $payment['payment_date'],
                    'reference' => $payment['reference_number']
                ];
            }, $payments)
        ]);

    } catch (Exception $e) {
        $response->error('Failed to retrieve folio: ' . $e->getMessage(), 500);
    }
}

function createFolio($db, $response, $validator) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            $response->error('Invalid JSON data', 400);
            return;
        }

        // Validate required fields
        $required = ['folio_number', 'check_in_date'];
        $validation = $validator->validateRequired($data, $required);

        if (!$validation['valid']) {
            $response->error('Missing required fields: ' . implode(', ', $validation['missing']), 400);
            return;
        }

        // Check if folio number already exists
        $existing = $db->fetchOne(
            "SELECT id FROM folios WHERE folio_number = ?",
            [$data['folio_number']]
        );

        if ($existing) {
            $response->error('Folio number already exists', 409);
            return;
        }

        // Validate room availability if room_id provided
        if (isset($data['room_id'])) {
            $roomCheck = $db->fetchOne(
                "SELECT status FROM rooms WHERE id = ?",
                [$data['room_id']]
            );

            if (!$roomCheck) {
                $response->error('Room not found', 404);
                return;
            }

            if ($roomCheck['status'] !== 'available') {
                $response->error('Room is not available', 409);
                return;
            }
        }

        $sql = "
            INSERT INTO folios (
                folio_number, guest_id, room_id, check_in_date, check_out_date,
                status, total_charges, total_payments, balance, guest_name,
                guest_email, guest_phone, special_requests
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $params = [
            $data['folio_number'],
            isset($data['guest_id']) ? (int)$data['guest_id'] : null,
            isset($data['room_id']) ? (int)$data['room_id'] : null,
            $data['check_in_date'],
            $data['check_out_date'] ?? null,
            $data['status'] ?? 'active',
            (float)($data['total_charges'] ?? 0),
            (float)($data['total_payments'] ?? 0),
            (float)($data['balance'] ?? 0),
            $data['guest_name'] ?? null,
            $data['guest_email'] ?? null,
            $data['guest_phone'] ?? null,
            $data['special_requests'] ?? null
        ];

        $db->execute($sql, $params);
        $folioId = $db->lastInsertId();

        // Update room status if room assigned
        if (isset($data['room_id'])) {
            $db->execute(
                "UPDATE rooms SET status = 'occupied' WHERE id = ?",
                [$data['room_id']]
            );
        }

        $response->success([
            'message' => 'Folio created successfully',
            'folio_id' => $folioId,
            'folio' => array_merge($data, ['id' => $folioId])
        ], 201);

    } catch (Exception $e) {
        $response->error('Failed to create folio: ' . $e->getMessage(), 500);
    }
}

function updateFolio($db, $response, $validator, $folioId) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            $response->error('Invalid JSON data', 400);
            return;
        }

        // Check if folio exists
        $existing = $db->fetchOne(
            "SELECT id, room_id, status FROM folios WHERE id = ?",
            [$folioId]
        );

        if (!$existing) {
            $response->error('Folio not found', 404);
            return;
        }

        // Build update query dynamically
        $updates = [];
        $params = [];

        $allowedFields = [
            'folio_number', 'guest_id', 'room_id', 'check_in_date', 'check_out_date',
            'actual_check_out', 'status', 'total_charges', 'total_payments', 'balance',
            'guest_name', 'guest_email', 'guest_phone', 'special_requests'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if (in_array($field, ['guest_id', 'room_id'])) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field] ? (int)$data[$field] : null;
                } elseif (in_array($field, ['total_charges', 'total_payments', 'balance'])) {
                    $updates[] = "$field = ?";
                    $params[] = (float)$data[$field];
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

        $sql = "UPDATE folios SET " . implode(', ', $updates) . " WHERE id = ?";
        $params[] = $folioId;

        $db->execute($sql, $params);

        // Handle room status changes
        if (isset($data['status']) && isset($data['room_id'])) {
            $newStatus = $data['status'] === 'checked_out' ? 'available' : 'occupied';
            $db->execute(
                "UPDATE rooms SET status = ? WHERE id = ?",
                [$newStatus, $data['room_id']]
            );
        }

        $response->success([
            'message' => 'Folio updated successfully',
            'folio_id' => $folioId,
            'updated_fields' => array_keys($data)
        ]);

    } catch (Exception $e) {
        $response->error('Failed to update folio: ' . $e->getMessage(), 500);
    }
}

function deleteFolio($db, $response, $folioId) {
    try {
        // Check if folio exists
        $folio = $db->fetchOne(
            "SELECT id, room_id, status FROM folios WHERE id = ?",
            [$folioId]
        );

        if (!$folio) {
            $response->error('Folio not found', 404);
            return;
        }

        // Only allow deletion of non-active folios
        if ($folio['status'] === 'active') {
            $response->error('Cannot delete active folio', 409);
            return;
        }

        // Free up the room if assigned
        if ($folio['room_id']) {
            $db->execute(
                "UPDATE rooms SET status = 'available' WHERE id = ?",
                [$folio['room_id']]
            );
        }

        // Delete folio
        $db->execute("DELETE FROM folios WHERE id = ?", [$folioId]);

        $response->success([
            'message' => 'Folio deleted successfully',
            'folio_id' => $folioId
        ]);

    } catch (Exception $e) {
        $response->error('Failed to delete folio: ' . $e->getMessage(), 500);
    }
}
?>
