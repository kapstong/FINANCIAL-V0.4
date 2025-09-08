<?php
require_once 'config/database.php';

$db = new Database();
$users = $db->fetchAll('SELECT id, username, profile_image FROM users');

echo "<h2>Users and Profile Images</h2>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Username</th><th>Profile Image</th><th>File Exists</th></tr>";

foreach ($users as $user) {
    $profileImage = $user['profile_image'] ?? 'NULL';
    $filePath = 'uploads/' . $profileImage;
    $fileExists = file_exists($filePath) ? 'YES' : 'NO';

    echo "<tr>";
    echo "<td>{$user['id']}</td>";
    echo "<td>{$user['username']}</td>";
    echo "<td>{$profileImage}</td>";
    echo "<td>{$fileExists}</td>";
    echo "</tr>";
}

echo "</table>";

// Check session data
session_start();
echo "<h2>Session Data</h2>";
if (isset($_SESSION['user_id'])) {
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "Username: " . ($_SESSION['username'] ?? 'Not set') . "<br>";
}
?>
