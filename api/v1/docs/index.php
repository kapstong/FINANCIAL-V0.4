<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATIERA Financial System API Documentation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        pre { background: #f8f9fa; border-radius: 6px; padding: 1rem; overflow-x: auto; }
        .endpoint { border-left: 4px solid #3b82f6; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="max-w-6xl mx-auto p-6">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">ATIERA Financial System API</h1>
            <p class="text-gray-600 mb-8">RESTful API for Hotel & Restaurant Management Integration</p>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Quick Start -->
                <div class="lg:col-span-2">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">Quick Start</h2>
                    
                    <div class="space-y-6">
                        <div class="endpoint bg-blue-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-blue-900 mb-2">1. Create API Credentials</h3>
                            <p class="text-blue-700 mb-3">Admin users can create API credentials for external systems.</p>
                            <pre class="text-sm"><code>POST /api/v1/auth/create
Content-Type: application/json

{
  "system_name": "Hotel PMS System",
  "system_type": "pms",
  "property_id": 1
}</code></pre>
                        </div>
                        
                        <div class="endpoint bg-green-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-900 mb-2">2. Authenticate</h3>
                            <p class="text-green-700 mb-3">Get access token using API credentials.</p>
                            <pre class="text-sm"><code>POST /api/v1/auth/login
Content-Type: application/json

{
  "username": "api_hotel_pms_system_123456",
  "password": "your_api_key_here"
}</code></pre>
                        </div>
                        
                        <div class="endpoint bg-purple-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-900 mb-2">3. Make API Calls</h3>
                            <p class="text-purple-700 mb-3">Use the Bearer token for authenticated requests.</p>
                            <pre class="text-sm"><code>GET /api/v1/accounts
Authorization: Bearer your_token_here</code></pre>
                        </div>
                    </div>
                </div>
                
                <!-- API Info -->
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">API Information</h2>
                    <div class="bg-gray-100 rounded-lg p-4 space-y-3">
                        <div>
                            <h4 class="font-semibold text-gray-900">Base URL</h4>
                            <p class="text-sm text-gray-600">/api/v1/</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Authentication</h4>
                            <p class="text-sm text-gray-600">JWT Bearer Token</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Response Format</h4>
                            <p class="text-sm text-gray-600">JSON</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Rate Limiting</h4>
                            <p class="text-sm text-gray-600">1000 requests/hour</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Endpoints -->
            <div class="mt-12">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">Available Endpoints</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Authentication -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-blue-900 mb-3">🔐 Authentication</h3>
                        <div class="space-y-2 text-sm">
                            <div><span class="font-mono bg-blue-100 px-2 py-1 rounded">POST</span> <span class="ml-2">/auth/login</span></div>
                            <div><span class="font-mono bg-blue-100 px-2 py-1 rounded">POST</span> <span class="ml-2">/auth/create</span></div>
                            <div><span class="font-mono bg-blue-100 px-2 py-1 rounded">GET</span> <span class="ml-2">/auth/verify</span></div>
                        </div>
                    </div>
                    
                    <!-- Accounts -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-green-900 mb-3">💰 Chart of Accounts</h3>
                        <div class="space-y-2 text-sm">
                            <div><span class="font-mono bg-green-100 px-2 py-1 rounded">GET</span> <span class="ml-2">/accounts</span></div>
                            <p class="text-green-700 text-xs mt-2">Filter by type, search, pagination supported</p>
                        </div>
                    </div>
                    
                    <!-- Journal Entries -->
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-purple-900 mb-3">📝 Journal Entries</h3>
                        <div class="space-y-2 text-sm">
                            <div><span class="font-mono bg-purple-100 px-2 py-1 rounded">POST</span> <span class="ml-2">/journal</span></div>
                            <div><span class="font-mono bg-purple-100 px-2 py-1 rounded">GET</span> <span class="ml-2">/journal</span></div>
                            <p class="text-purple-700 text-xs mt-2">Automatic balance updates</p>
                        </div>
                    </div>
                    
                    <!-- Coming Soon -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">🚧 Coming Soon</h3>
                        <div class="space-y-2 text-sm text-gray-600">
                            <div>/rooms - Hotel room management</div>
                            <div>/folios - Guest folio management</div>
                            <div>/pos - POS transaction handling</div>
                            <div>/reports - Financial reports</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Response Examples -->
            <div class="mt-12">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">Response Examples</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Success Response</h3>
                        <pre class="text-sm"><code>{
  "status": "success",
  "message": "Accounts retrieved successfully",
  "data": [
    {
      "id": 1,
      "code": "4100",
      "name": "Room Revenue - Standard",
      "type": "revenue",
      "normal_balance": "credit",
      "balance": 25000.00,
      "balance_formatted": "25,000.00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 50,
    "total_records": 100,
    "total_pages": 2,
    "has_next_page": true,
    "has_prev_page": false
  },
  "timestamp": "2024-01-01 12:00:00",
  "api_version": "1.0"
}</code></pre>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Error Response</h3>
                        <pre class="text-sm"><code>{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "account_id": "Field 'account_id' is required",
    "amount": "Field 'amount' must be numeric"
  },
  "timestamp": "2024-01-01 12:00:00",
  "api_version": "1.0"
}</code></pre>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="mt-12 pt-8 border-t border-gray-200 text-center text-gray-600">
                <p>ATIERA Financial System API v1.0 - Built for Hotel & Restaurant Management Integration</p>
                <p class="text-sm mt-2">For support, contact your system administrator</p>
            </div>
        </div>
    </div>
</body>
</html>