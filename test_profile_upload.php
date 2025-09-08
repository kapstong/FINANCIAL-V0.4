<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

echo "<h1>Profile Upload Test</h1>";

// Test database connection
try {
    $db = new Database();
    $conn = $db->connect();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test getting current user
$auth = new Auth();
$user = $auth->getCurrentUser();

if ($user) {
    echo "<p style='color: green;'>✓ Current user: " . htmlspecialchars($user['username']) . " (ID: " . $user['id'] . ")</p>";
    echo "<p>Current profile_image: " . htmlspecialchars($user['profile_image'] ?? 'NULL') . "</p>";
} else {
    echo "<p style='color: red;'>✗ No current user found</p>";
    exit;
}

// Test updateProfileImage method
$testPath = "uploads/test_image_" . time() . ".png";
$result = $auth->updateProfileImage($user['id'], $testPath);

if ($result['success']) {
    echo "<p style='color: green;'>✓ updateProfileImage method works: " . $result['message'] . "</p>";
} else {
    echo "<p style='color: red;'>✗ updateProfileImage failed: " . $result['message'] . "</p>";
}

// Verify the update in database
$checkUser = $db->fetchOne("SELECT profile_image FROM users WHERE id = ?", [$user['id']]);
echo "<p>Profile image in database after test: " . htmlspecialchars($checkUser['profile_image'] ?? 'NULL') . "</p>";

// Test uploads folder
$uploadDir = 'uploads/';
if (is_dir($uploadDir)) {
    echo "<p style='color: green;'>✓ Uploads folder exists</p>";
    if (is_writable($uploadDir)) {
        echo "<p style='color: green;'>✓ Uploads folder is writable</p>";
    } else {
        echo "<p style='color: red;'>✗ Uploads folder is not writable</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Uploads folder does not exist</p>";
}

// Show recent error logs
echo "<h2>Recent Error Logs</h2>";
$logFile = ini_get('error_log');
if (file_exists($logFile)) {
    $logs = file($logFile);
    $recentLogs = array_slice($logs, -20); // Last 20 lines
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>";
    foreach ($recentLogs as $log) {
        if (strpos($log, 'profile') !== false || strpos($log, 'upload') !== false) {
            echo htmlspecialchars($log);
        }
    }
    echo "</pre>";
} else {
    echo "<p>No error log file found at: " . $logFile . "</p>";
}
?>
