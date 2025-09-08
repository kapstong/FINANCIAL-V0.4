<?php
require_once 'includes/auth.php';
$auth = new Auth();
$auth->requireAuth();

// Only admins can manage API credentials
if (!$auth->isAdmin()) {
    header('Location: dashboard.php');
    exit();
}

$user = $auth->getCurrentUser();

// Handle API credential creation
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_credentials') {
        try {
            require_once 'api/v1/includes/ApiAuth.php';
            $apiAuth = new ApiAuth();
            
            $credentials = $apiAuth->createApiCredentials(
                $_POST['system_name'],
                $_POST['system_type'],
                $_POST['property_id'] ?: null
            );
            
            $message = 'API credentials created successfully! Please save these credentials securely.';
            $messageType = 'success';
            $newCredentials = $credentials;
            
        } catch (Exception $e) {
            $message = 'Failed to create API credentials: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get existing integrations
$db = new Database();
$integrations = $db->fetchAll(
    "SELECT pi.*, hp.property_name 
     FROM pms_integrations pi 
     LEFT JOIN hotel_properties hp ON pi.property_id = hp.id 
     ORDER BY pi.created_at DESC"
);
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>API Management - ATIERA Financial System</title>
  <link rel="icon" type="image/png" href="logo2.png">
  <script src="https://cdn.tailwindcss.com"></script>
  
  <style>
    :root{
      --brand:#0f1c49; --brand-600:#0c173c; --brand-100:#e8ecf9;
      --ink:#000; --muted:#000; --ring:0 0 0 3px rgba(15,28,73,.15);
      --card-bg: rgba(255,255,255,.95); --card-border: rgba(226,232,240,.9);
    }
    
    html.dark {
      --ink: #e5e7eb; --muted: #9ca3af;
      --card-bg: rgba(17,24,39,.92); --card-border: rgba(71,85,105,.55);
    }
    
    body{ background:#fff; color:var(--ink); }
    html.dark body{ background: linear-gradient(140deg, rgba(7,12,38,1) 50%, rgba(11,21,56,1) 50%); }
    
    .navbar{ background:var(--brand); color:#fff; height: 3.5rem; }
    .navbar *{ color:#fff !important; }
    
    .card{ background:var(--card-bg); border-radius:14px; border:1px solid var(--card-border); box-shadow:0 6px 18px rgba(2,6,23,.04) }
    html.dark .card{ box-shadow:0 16px 48px rgba(0,0,0,.5); }
    
    .btn{ display:inline-flex; align-items:center; gap:.5rem; padding:.55rem .95rem; border-radius:.65rem; font-weight:600; color:var(--ink) }
    .btn-brand{ background:var(--brand); color:#fff } 
    .btn-soft{ background:#fff; border:1px solid var(--card-border); color:var(--ink) }
    
    html.dark .btn-soft{ background:var(--card-bg); border-color:var(--card-border); color:var(--ink); }
    
    .alert{ border-radius:12px; padding:.75rem 1rem; margin:1rem 0; }
    .alert-success{ background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }
    .alert-error{ background:#fef2f2; border:1px solid #fecaca; color:#dc2626; }
    html.dark .alert-success{ background:#14532d; border-color:#166534; color:#bbf7d0; }
    html.dark .alert-error{ background:#7f1d1d; border-color:#dc2626; color:#fecaca; }
  </style>
</head>

<body class="bg-soft">
  <!-- Navigation Bar -->
  <nav class="navbar flex items-center justify-between px-6">
    <div class="flex items-center space-x-4">
      <a href="dashboard.php" class="text-xl font-bold">ATIERA</a>
      <span class="text-sm opacity-75">API Management</span>
    </div>
    
    <div class="flex items-center space-x-4">
      <button id="darkModeToggle" class="p-2 rounded-lg hover:bg-white/10">🌙</button>
      <a href="dashboard.php" class="btn btn-soft">← Back to Dashboard</a>
    </div>
  </nav>

  <main class="max-w-7xl mx-auto p-6 space-y-6">
    
    <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
      <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>
    
    <?php if (isset($newCredentials)): ?>
    <div class="card p-6 border-green-200 bg-green-50">
      <h3 class="text-lg font-semibold text-green-900 mb-4">🔑 New API Credentials Created</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div>
          <label class="font-medium text-green-800">API Username:</label>
          <div class="bg-green-100 p-2 rounded font-mono text-green-900"><?php echo htmlspecialchars($newCredentials['api_user']); ?></div>
        </div>
        <div>
          <label class="font-medium text-green-800">API Key:</label>
          <div class="bg-green-100 p-2 rounded font-mono text-green-900"><?php echo htmlspecialchars($newCredentials['api_key']); ?></div>
        </div>
        <div class="md:col-span-2">
          <label class="font-medium text-green-800">Access Token:</label>
          <div class="bg-green-100 p-2 rounded font-mono text-green-900 break-all"><?php echo htmlspecialchars($newCredentials['access_token']); ?></div>
        </div>
      </div>
      <p class="text-green-700 text-sm mt-4">⚠️ <strong>Important:</strong> Save these credentials securely. The API key cannot be retrieved again.</p>
    </div>
    <?php endif; ?>
    
    <!-- Create New API Credentials -->
    <div class="card p-6">
      <h2 class="text-2xl font-semibold text-gray-900 mb-6">🔐 Create API Credentials</h2>
      
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="create_credentials">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">System Name</label>
            <input type="text" name="system_name" required 
                   class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="e.g., Hotel PMS System">
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">System Type</label>
            <select name="system_type" required 
                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Select Type</option>
              <option value="pms">Property Management System (PMS)</option>
              <option value="pos">Point of Sale (POS)</option>
              <option value="booking_engine">Booking Engine</option>
              <option value="channel_manager">Channel Manager</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Property (Optional)</label>
            <select name="property_id" 
                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Select Property</option>
              <?php
              $properties = $db->fetchAll("SELECT id, property_name FROM hotel_properties WHERE status = 'active'");
              foreach ($properties as $property) {
                echo "<option value='{$property['id']}'>" . htmlspecialchars($property['property_name']) . "</option>";
              }
              ?>
            </select>
          </div>
        </div>
        
        <button type="submit" class="btn btn-brand">
          🔑 Create API Credentials
        </button>
      </form>
    </div>
    
    <!-- Existing Integrations -->
    <div class="card p-6">
      <h2 class="text-2xl font-semibold text-gray-900 mb-6">🔗 Existing API Integrations</h2>
      
      <?php if (empty($integrations)): ?>
      <div class="text-center py-8 text-gray-500">
        <p>No API integrations found. Create your first API credentials above.</p>
      </div>
      <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 font-semibold text-gray-900">System Name</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-900">Type</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-900">Property</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-900">Status</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-900">Last Sync</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-900">Created</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($integrations as $integration): ?>
            <tr class="border-b border-gray-100 hover:bg-gray-50">
              <td class="py-3 px-4 font-medium"><?php echo htmlspecialchars($integration['system_name']); ?></td>
              <td class="py-3 px-4">
                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                  <?php echo strtoupper($integration['system_type']); ?>
                </span>
              </td>
              <td class="py-3 px-4"><?php echo htmlspecialchars($integration['property_name'] ?: 'All Properties'); ?></td>
              <td class="py-3 px-4">
                <?php
                $statusColors = [
                  'active' => 'bg-green-100 text-green-800',
                  'inactive' => 'bg-gray-100 text-gray-800',
                  'error' => 'bg-red-100 text-red-800'
                ];
                $statusColor = $statusColors[$integration['sync_status']] ?? 'bg-gray-100 text-gray-800';
                ?>
                <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $statusColor; ?>">
                  <?php echo ucfirst($integration['sync_status']); ?>
                </span>
              </td>
              <td class="py-3 px-4 text-sm text-gray-600">
                <?php echo $integration['last_sync_date'] ? date('M j, Y H:i', strtotime($integration['last_sync_date'])) : 'Never'; ?>
              </td>
              <td class="py-3 px-4 text-sm text-gray-600">
                <?php echo date('M j, Y', strtotime($integration['created_at'])); ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
    
    <!-- API Documentation Link -->
    <div class="card p-6 text-center">
      <h3 class="text-lg font-semibold text-gray-900 mb-2">📚 API Documentation</h3>
      <p class="text-gray-600 mb-4">View complete API documentation with examples and endpoint details.</p>
      <a href="api/v1/docs/" target="_blank" class="btn btn-brand">
        📖 View API Documentation
      </a>
    </div>
    
  </main>

  <script>
    // Dark mode toggle
    const darkModeToggle = document.getElementById('darkModeToggle');
    const html = document.documentElement;
    
    darkModeToggle.addEventListener('click', () => {
      html.classList.toggle('dark');
      localStorage.setItem('darkMode', html.classList.contains('dark'));
    });
    
    // Load dark mode preference
    if (localStorage.getItem('darkMode') === 'true') {
      html.classList.add('dark');
    }
  </script>
</body>
</html>