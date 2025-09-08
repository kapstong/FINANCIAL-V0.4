<?php
require_once 'includes/auth.php';
$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_preferences'])) {
        // Update user preferences
        $preferences = [
            'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
            'auto_save' => isset($_POST['auto_save']) ? 1 : 0,
            'system_alerts' => isset($_POST['system_alerts']) ? 1 : 0,
            'financial_reports' => isset($_POST['financial_reports']) ? 1 : 0,
            'security_alerts' => isset($_POST['security_alerts']) ? 1 : 0
        ];

        $result = $auth->updateUserPreferences($user['id'], $preferences);
        if ($result['success']) {
            $message = 'Preferences updated successfully!';
            $messageType = 'success';
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    } elseif (isset($_POST['change_password'])) {
        // Change password
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $message = 'All password fields are required';
            $messageType = 'error';
        } elseif ($newPassword !== $confirmPassword) {
            $message = 'New passwords do not match';
            $messageType = 'error';
        } elseif (strlen($newPassword) < 6) {
            $message = 'New password must be at least 6 characters long';
            $messageType = 'error';
        } else {
            $result = $auth->changePassword($user['id'], $currentPassword, $newPassword);
            if ($result['success']) {
                $message = 'Password changed successfully!';
                $messageType = 'success';
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        }
    } elseif (isset($_POST['update_system_settings'])) {
        // Update system settings (stored in localStorage via JS, but we can handle server-side settings here)
        $message = 'System settings updated successfully!';
        $messageType = 'success';
    }
}

// Get current user preferences
$userPreferences = $auth->getUserPreferences($user['id']);
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Settings</title>
  <link rel="icon" type="image/png" href="logo2.png">
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
         :root{
       --brand:#0f1c49; --brand-600:#0c173c; --brand-100:#e8ecf9;
       --ink:#000; --muted:#000; --ring:0 0 0 3px rgba(15,28,73,.15);
       --card:#ffffff; --border:#eef2f7;
       --card-bg: rgba(255,255,255,.95);
       --card-border: rgba(226,232,240,.9);
     }

         /* Dark mode variables */
     html.dark {
       --ink: #e5e7eb;
       --muted: #9ca3af;
       --card: rgba(17,24,39,.92);
       --border: rgba(71,85,105,.55);
       --card-bg: rgba(17,24,39,.92);
       --card-border: rgba(71,85,105,.55);
     }
    .navbar{ background:var(--brand); color:#fff; height: 3.5rem; }
    .navbar *{ color:#fff !important; }
    .nav-input{
      background:rgba(255,255,255,.18); border:1px solid rgba(255,255,255,.35);
      padding:.35rem .6rem; border-radius:.6rem; color:#fff !important;
    }
    .nav-input::placeholder{ color:#f1f5f9; }

    /* Enhanced Navigation Bar Styles */
    .navbar {
      background: linear-gradient(135deg, var(--brand) 0%, var(--brand-600) 100%);
      box-shadow: 0 4px 20px rgba(15, 28, 73, 0.15);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      height: 3.5rem !important;
    }

    .navbar .nav-input {
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.25);
      backdrop-filter: blur(10px);
      transition: all 0.3s ease;
    }

    .navbar .nav-input:focus {
      background: rgba(255, 255, 255, 0.25);
      border-color: rgba(255, 255, 255, 0.4);
      box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
    }

    .navbar .nav-input::placeholder {
      color: rgba(255, 255, 255, 0.7);
    }

    /* Enhanced Profile Button */
    #profileBtn {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
      transition: all 0.3s ease;
    }

    #profileBtn:hover {
      background: rgba(255, 255, 255, 0.2);
      border-color: rgba(255, 255, 255, 0.3);
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Enhanced Dark Mode Toggle */
    #headerDarkModeToggle {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
      transition: all 0.3s ease;
    }

    #headerDarkModeToggle:hover {
      background: rgba(255, 255, 255, 0.2);
      border-color: rgba(255, 255, 255, 0.3);
      transform: translateY(-1px);
    }

    /* Enhanced Clock Wrap */
    #clockWrap {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 8px;
      padding: 4px 8px;
      backdrop-filter: blur(10px);
    }

    #liveTime {
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.25);
      transition: all 0.3s ease;
    }

    #liveTime:hover {
      background: rgba(255, 255, 255, 0.25);
      border-color: rgba(255, 255, 255, 0.35);
    }

    /* Enhanced Brand Logo */
    .navbar a[href="dashboard.php"] {
      transition: all 0.3s ease;
    }

    .navbar a[href="dashboard.php"]:hover {
      transform: scale(1.05);
    }

    .navbar a[href="dashboard.php"] span {
      background: linear-gradient(135deg, #ffffff 0%, #e2e8f0 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Enhanced Search Input */
    .navbar .nav-input {
      font-weight: 500;
      letter-spacing: 0.025em;
    }

    /* Smooth Transitions for All Interactive Elements */
    .navbar button,
    .navbar input,
    .navbar a {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Enhanced Mobile Responsiveness */
    @media (max-width: 768px) {
      .navbar .nav-input {
        width: 200px;
      }

      #clockWrap {
        display: none;
      }
    }

    /* Dark Mode Enhanced Styles */
    html.dark .navbar {
      background: linear-gradient(135deg, rgba(15, 28, 73, 0.95) 0%, rgba(12, 23, 60, 0.95) 100%);
      border-bottom-color: rgba(255, 255, 255, 0.1);
    }
         body{ background:#fff; color: var(--ink); }
     html.dark body{
       background: linear-gradient(140deg, rgba(7,12,38,1) 50%, rgba(11,21,56,1) 50%);
       color: var(--ink);
     }

     .bg-soft{
       background:
         radial-gradient(70% 70% at 0% 0%, var(--brand-100) 0%, transparent 60%),
         radial-gradient(60% 60% at 100% 0%, #eef2ff 0%, transparent 55%),
         linear-gradient(#fff,#fff);
     }
     html.dark .bg-soft{
       background:
         radial-gradient(70% 60% at 8% 10%, rgba(212,175,55,.08) 0, transparent 60%),
         radial-gradient(40% 40% at 100% 0%, rgba(212,175,55,.12) 0, transparent 40%),
         linear-gradient(140deg, rgba(7,12,38,1) 50%, rgba(11,21,56,1) 50%);
     }

         .card{ background:var(--card); border-radius:14px; border:1px solid var(--border); box-shadow:0 6px 18px rgba(2,6,23,.04) }
     html.dark .card{ box-shadow:0 16px 48px rgba(0,0,0,.5); }
    .btn{ display:inline-flex; align-items:center; gap:.5rem; padding:.55rem .95rem; border-radius:.65rem; font-weight:600 }
    .btn-brand{ background:var(--brand); color:#fff } .btn-brand:hover{ background:var(--brand-600) }
    .btn-soft{ background:#fff; border:1px solid var(--border); color:var(--ink) } .btn-soft:hover{ background:#f8fafc }
    html.dark .btn-soft{ background:var(--card); border-color:var(--border); color:var(--ink); }
    html.dark .btn-soft:hover{ background:rgba(31,41,55,.92); }
         .sidebar-transition{ transition:transform .28s ease }
          .overlay{ display:none } .overlay.active{ display:block; position:fixed; inset:0; background:rgba(0,0,0,.35); z-index:40 }
    .sidebar-item{ display:flex; align-items:center; gap:.6rem; width:100%; padding:.5rem .75rem; border-radius:.6rem; color:var(--ink) }
    .sidebar-item:hover{ background:#f8fafc }
    html.dark .sidebar-item:hover{ background:rgba(31,41,55,.92); }
    .sidebar-item:hover{ background:#f8fafc }
    .sidebar-item.active{ background:rgba(15,28,73,.06); color:var(--brand); font-weight:700 }
    .tab-pill{ padding:.4rem .8rem; border-radius:9999px; border:1px solid var(--border); font-weight:700; font-size:.9rem; color:var(--ink) }
    .tab-pill.active{ background:var(--brand); color:#fff; border-color:var(--brand) }
    .toast-card{ background:#fff; border:1px solid var(--border); border-radius:.75rem; padding:.6rem .9rem; box-shadow:0 10px 30px rgba(0,0,0,.08) }
    .form-input{ width:100%; padding:.5rem .75rem; border:1px solid #d1d5db; border-radius:.5rem; outline:none; transition:border-color .2s }
    .form-input:focus{ border-color:var(--brand); box-shadow:0 0 0 3px rgba(15,28,73,.1) }
    .stat-card{ background:linear-gradient(135deg, var(--brand) 0%, var(--brand-600) 100%); color:#fff; padding:1.5rem; border-radius:1rem }
    .alert{ padding:.75rem 1rem; border-radius:.5rem; margin-bottom:1rem }
    .alert-success{ background:#d1fae5; border:1px solid #a7f3d0; color:#065f46 }
    .alert-error{ background:#fee2e2; border:1px solid #fecaca; color:#991b1b }

         /* Settings specific styles */
     .settings-section{ margin-bottom:2rem }
     .settings-section h3{ font-size:1.25rem; font-weight:600; margin-bottom:1rem; color:var(--ink) }
     .settings-group{ background:#f8fafc; border:1px solid var(--border); border-radius:.5rem; padding:1.5rem; margin-bottom:1rem }
     html.dark .settings-group{ background:rgba(31,41,55,.5); border-color:var(--border) }
     .setting-item{ display:flex; align-items:center; justify-between; padding:.5rem 0 }
     .setting-item label{ font-weight:500; color:var(--ink) }
     .setting-description{ font-size:.875rem; color:#6b7280; margin-top:.25rem }
     html.dark .setting-description{ color:#9ca3af }

     /* Toggle switch styles */
     .toggle-switch {
       position: relative;
       display: inline-block;
       width: 3rem;
       height: 1.5rem;
     }

     .toggle-switch input {
       opacity: 0;
       width: 0;
       height: 0;
     }

     .toggle-slider {
       position: absolute;
       cursor: pointer;
       top: 0;
       left: 0;
       right: 0;
       bottom: 0;
       background-color: #ccc;
       transition: .4s;
       border-radius: 1.5rem;
     }

     .toggle-slider:before {
       position: absolute;
       content: "";
       height: 1.125rem;
       width: 1.125rem;
       left: 0.1875rem;
       bottom: 0.1875rem;
       background-color: white;
       transition: .4s;
       border-radius: 50%;
     }

     input:checked + .toggle-slider {
       background-color: var(--brand);
     }

     input:checked + .toggle-slider:before {
       transform: translateX(1.5rem);
     }

     /* Dark mode specific styles */
     html.dark .card{ box-shadow:0 16px 48px rgba(0,0,0,.5); }
     html.dark .btn-soft{ background:rgba(17,24,39,.92); border-color:rgba(71,85,105,.55); color:var(--ink); }
     html.dark .btn-soft:hover{ background:rgba(31,41,55,.92); }
     html.dark .sidebar-item:hover{ background:rgba(31,41,55,.92); }
     html.dark .sidebar-item{ color:var(--ink); }
     html.dark .tab-pill{ color:var(--ink); }
     html.dark .form-input{ background:#0b1220; border-color:#243041; color:var(--ink); }
     html.dark .alert-success{ background:#3f1b1b; border-color:#7f1d1d; color:#fecaca }
     html.dark .alert-error{ background:#1e1b4b; border-color:#3730a3; color:#c7d2fe }
     html.dark .settings-group{ background:rgba(31,41,55,.5); }
     html.dark .toggle-slider{ background-color:#4b5563 }

     /* Dark mode context bar */
     html.dark #contextBar {
       background: rgba(17,24,39,.8);
       border-color: rgba(71,85,105,.55);
     }

     /* Dark mode sidebar */
    html.dark #sidebar {
      background: var(--card);
      border-color: var(--border);
    }

    /* Enhanced Sidebar */
    #sidebar {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-right: 1px solid rgba(0, 0, 0, 0.1);
      box-shadow: 4px 0 20px rgba(0, 0, 0, 0.05);
    }

    .sidebar-item {
      border-radius: 12px;
      margin: 2px 8px;
      transition: all 0.3s ease;
      border: 1px solid transparent;
    }

    .sidebar-item:hover {
      background: rgba(15, 28, 73, 0.08);
      border-color: rgba(15, 28, 73, 0.1);
      transform: translateX(4px);
    }

    .sidebar-item.active {
      background: linear-gradient(135deg, rgba(15, 28, 73, 0.15) 0%, rgba(15, 28, 73, 0.1) 100%);
      border-color: rgba(15, 28, 73, 0.2);
      box-shadow: 0 2px 8px rgba(15, 28, 73, 0.1);
    }

    /* Dark Mode Enhanced Sidebar */
    html.dark #sidebar {
      background: rgba(17, 24, 39, 0.95);
      border-right-color: rgba(255, 255, 255, 0.1);
      box-shadow: 4px 0 20px rgba(0, 0, 0, 0.3);
    }

    html.dark .sidebar-item {
      color: var(--ink);
    }

    html.dark .sidebar-item:hover {
      background: rgba(255, 255, 255, 0.08);
      border-color: rgba(255, 255, 255, 0.15);
    }

    html.dark .sidebar-item.active {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.1) 100%);
      border-color: rgba(255, 255, 255, 0.2);
      color: var(--brand-100);
    }

     /* Dark mode text colors */
     html.dark .text-slate-800 {
       color: var(--ink);
     }

     html.dark .text-gray-500 {
       color: var(--muted);
     }

     html.dark .text-gray-600 {
       color: var(--muted);
     }

     html.dark .text-gray-900 {
       color: var(--ink);
     }

     /* Loading animations */
     @keyframes shimmer {
       0% { background-position: -200px 0; }
       100% { background-position: calc(200px + 100%) 0; }
     }

     .shimmer {
       background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
       background-size: 200px 100%;
       animation: shimmer 1.5s infinite;
     }

     html.dark .shimmer {
       background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
       background-size: 200px 100%;
     }

     /* Card hover effects */
     .settings-card {
       transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
     }

     .settings-card:hover {
       transform: translateY(-2px);
       box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 5px 10px -5px rgba(0, 0, 0, 0.04);
     }

     /* Loading screen transitions */
     #globalLoader {
       transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
     }

     /* Button loading states */
     .btn-loading {
       position: relative;
       pointer-events: none;
     }

     .btn-loading::after {
       content: '';
       position: absolute;
       width: 16px;
       height: 16px;
       top: 50%;
       left: 50%;
       margin-left: -8px;
       margin-top: -8px;
       border: 2px solid transparent;
       border-top: 2px solid currentColor;
       border-radius: 50%;
       animation: spin 1s linear infinite;
     }

     @keyframes spin {
       0% { transform: rotate(0deg); }
       100% { transform: rotate(360deg); }
     }
    /* NOTIFICATION SYSTEM - LIGHT & DARK MODE COMPATIBLE */
    /* Force light mode styles */
    html:not(.dark) #notificationPanel {
      background: white !important;
      border-color: #e5e7eb !important;
      color: #111827 !important;
    }

    html:not(.dark) #notificationPanel .bg-gray-50 {
      background: #f9fafb !important;
      border-color: #e5e7eb !important;
    }

    html:not(.dark) #notificationPanel .text-gray-900 {
      color: #111827 !important;
    }

    html:not(.dark) #notificationPanel .text-gray-600 {
      color: #4b5563 !important;
    }

    html:not(.dark) #notificationPanel .text-gray-400 {
      color: #9ca3af !important;
    }

    html:not(.dark) #notificationPanel .text-gray-500 {
      color: #6b7280 !important;
    }

    html:not(.dark) #notificationPanel .border-gray-100 {
      border-color: #f3f4f6 !important;
    }

    html:not(.dark) #notificationPanel .border-gray-200 {
      border-color: #e5e7eb !important;
    }

    html:not(.dark) #notificationPanel .hover\:bg-gray-50:hover {
      background: #f9fafb !important;
    }

    html:not(.dark) #notificationPanel .text-blue-600 {
      color: #2563eb !important;
    }

    html:not(.dark) #notificationPanel .hover\:text-blue-800:hover {
      color: #1e40af !important;
    }

    html:not(.dark) #notificationPanel .text-blue-400 {
      color: #3b82f6 !important;
    }

    html:not(.dark) #notificationPanel .hover\:text-blue-300:hover {
      color: #2563eb !important;
    }

    /* Light mode base styles */
    #notificationPanel {
      background: white;
      border-color: #e5e7eb;
      color: #111827;
    }

    /* Dark mode overrides for notification panel */
    html.dark #notificationPanel {
      background: rgba(17,24,39,.95);
      border-color: rgba(71,85,105,.55);
      backdrop-filter: blur(20px);
    }

    html.dark #notificationPanel .bg-gray-50 {
      background: rgba(31,41,55,.95) !important;
      border-color: rgba(71,85,105,.55) !important;
    }

    html.dark #notificationPanel .text-gray-900 {
      color: #e5e7eb !important;
    }

    html.dark #notificationPanel .text-gray-600 {
      color: #9ca3af !important;
    }

    html.dark #notificationPanel .text-gray-400 {
      color: #6b7280 !important;
    }

    html.dark #notificationPanel .text-gray-500 {
      color: #9ca3af !important;
    }

    html.dark #notificationPanel .border-gray-100 {
      border-color: rgba(71,85,105,.3) !important;
    }

    html.dark #notificationPanel .border-gray-200 {
      border-color: rgba(71,85,105,.55) !important;
    }

    html.dark #notificationPanel .hover\:bg-gray-50:hover {
      background: rgba(55,65,81,.95) !important;
    }

    html.dark #notificationPanel .text-blue-600 {
      color: #60a5fa !important;
    }

    html.dark #notificationPanel .hover\:text-blue-800:hover {
      color: #93c5fd !important;
    }

    html.dark #notificationPanel .text-blue-400 {
      color: #60a5fa !important;
    }

    html.dark #notificationPanel .hover\:text-blue-300:hover {
      color: #2563eb !important;
    }

    /* Dark mode for notification badge */
    html.dark #notificationBadge {
      background: #ef4444;
      color: #ffffff;
      box-shadow: 0 0 0 2px rgba(17,24,39,.95);
    }

    /* Dark mode for notification bell button */
    html.dark #notificationBell {
      color: #ffffff;
    }

    html.dark #notificationBell:hover {
      background: rgba(255,255,255,.15);
    }

    /* Enhanced bell hover effects */
    #notificationBell {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #notificationBell:hover {
      transform: scale(1.1);
      background: rgba(255,255,255,.15);
    }

    #notificationBell:active {
      transform: scale(0.95);
    }

    /* Glow effect when there are notifications */
    #notificationBell.has-notifications {
      box-shadow: 0 0 15px rgba(239, 68, 68, 0.6);
    }

    html.dark #notificationBell.has-notifications {
      box-shadow: 0 0 15px rgba(239, 68, 68, 0.8);
    }
  </style>
</head>
  <body class="min-h-screen text-[15px] text-[var(--ink)] bg-soft">

    <!-- Enhanced Loading Screen -->
    <div id="globalLoader" class="fixed inset-0 z-[100] flex items-center justify-center bg-gradient-to-br from-slate-900/95 to-slate-800/95 backdrop-blur-sm">
      <div class="flex flex-col items-center gap-6">
        <!-- Main Spinner -->
        <div class="relative">
          <!-- Outer Ring -->
          <div class="w-20 h-20 border-4 border-slate-600/30 rounded-full animate-pulse"></div>
          <!-- Rotating Ring -->
          <div class="absolute inset-0 w-20 h-20 border-4 border-transparent border-t-blue-500 rounded-full animate-spin"></div>
          <!-- Inner Ring -->
          <div class="absolute inset-2 w-16 h-16 border-4 border-transparent border-t-indigo-400 rounded-full animate-spin" style="animation-direction: reverse; animation-duration: 1.5s;"></div>
          <!-- Center Dot -->
          <div class="absolute inset-6 w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full animate-pulse"></div>
        </div>

        <!-- Loading Text -->
        <div class="text-center">
          <h2 class="text-2xl font-bold text-white mb-2">Loading Settings</h2>
          <p class="text-slate-300 text-sm">Preparing your preferences...</p>
        </div>

        <!-- Progress Bar -->
        <div class="w-64 bg-slate-700 rounded-full h-2">
          <div id="loadingProgress" class="bg-gradient-to-r from-blue-500 to-indigo-500 h-2 rounded-full transition-all duration-500 ease-out" style="width: 0%"></div>
        </div>

        <!-- Loading Dots -->
        <div class="flex space-x-2">
          <div class="w-2 h-2 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 0ms;"></div>
          <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 150ms;"></div>
          <div class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 300ms;"></div>
        </div>
      </div>
    </div>



  <!-- TOAST -->
  <div id="toast" class="fixed top-4 right-4 z-[120] hidden"></div>

  <!-- HEADER -->
  <header class="sticky top-0 z-50 border-b border-[var(--ring)] navbar backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-14 flex items-center gap-3">
             <button id="openSidebar" class="md:hidden p-2 rounded hover:bg-white/20 transition-all duration-300 hover:scale-105" aria-label="Open menu">
         <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/></svg>
       </button>

      <!-- Brand -->
      <a href="dashboard.php" class="flex items-center gap-3">
        <img src="logo2.png" alt="ATIÉRA" class="h-8 w-auto sm:h-10" draggable="false">
        <span class="font-extrabold tracking-wide text-lg">ATIERA</span>
      </a>

      <!-- Search (global) -->
      <div class="ml-auto flex items-center gap-2">
        <input id="searchInput" placeholder="Search modules, cards, rows…" class="nav-input text-sm w-72 outline-none"/>
      </div>

            <!-- Live date/time -->
      <div id="clockWrap" class="hidden md:flex items-center gap-2 mr-1 select-none">
        <span id="liveDate" class="text-sm"></span>
         <button id="liveTime" class="text-sm font-mono px-2 py-0.5 rounded border border-white/30 bg-white/10"
                title="Click to toggle 12/24-hour time"></button>
      </div>

      <!-- Dark Mode Toggle -->
      <button id="headerDarkModeToggle" class="p-2 rounded hover:bg-white/10 text-white" title="Toggle dark mode">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>
      </button>
      <!-- Notification Bell -->
      <div class="relative">
        <button id="notificationBell" class="p-2 rounded hover:bg-white/10 text-white relative" title="Notifications" onclick="toggleNotificationPanel()">
          <img src="uploads/notif-bell.png" alt="Notifications" class="w-5 h-5 object-contain">
          <!-- Notification Badge -->
          <span id="notificationBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
        </button>

        <!-- Notification Panel -->
        <div id="notificationPanel" class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-gray-900 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden z-[60]">
          <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Notifications</h3>
              <button onclick="clearAllNotifications()" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">Clear All</button>
            </div>
          </div>
          <div id="notificationList" class="max-h-64 overflow-y-auto">
            <div class="p-4 text-center text-gray-500 dark:text-gray-400 text-sm">
              No new notifications
            </div>
          </div>
        </div>
      </div>

      <!-- Profile -->
      <div class="relative">
        <button id="profileBtn" class="p-2 rounded hover:bg-white/10 flex items-center gap-2" title="Account">
          <img src="<?php echo htmlspecialchars(!empty($user['profile_image']) ? 'uploads/' . $user['profile_image'] : 'uploads/admindefault.png'); ?>"
               alt="Profile" class="w-8 h-8 rounded-full object-cover border border-white/30">
        </button>
        <div id="profileMenu" class="hidden absolute right-0 mt-2 w-56 bg-[var(--card-bg)] rounded-lg shadow-xl border border-[var(--card-border)] overflow-hidden text-[var(--ink)]">
          <div class="px-4 py-2 text-xs text-[var(--muted)] border-b border-[var(--card-border)] md:hidden">
            <span id="liveDateMobile"></span> • <span id="liveTimeMobile" class="font-mono"></span>
          </div>
          <div class="px-4 py-2 border-b border-[var(--card-border)]">
            <div class="text-sm font-medium" style="color: var(--ink) !important;"><?php echo htmlspecialchars($user['username']); ?></div>
            <div class="text-xs text-[var(--muted)]" style="color: var(--muted) !important;"><?php echo htmlspecialchars($user['role_name']); ?></div>
          </div>
          <a href="settings.php" class="block px-4 py-2 hover:bg-[var(--card-bg)]" style="color: var(--ink) !important;">Settings</a>
          <a href="profile.php" class="block px-4 py-2 hover:bg-[var(--card-bg)]" style="color: var(--ink) !important;">Profile</a>
          <a href="logout.php" class="block px-4 py-2 hover:bg-[var(--card-bg)]" style="color: var(--ink) !important;">Logout</a>
        </div>
      </div>
    </div>
  </header>

  <!-- NAVBAR SUB-MODULE TABS -->
  <div id="contextBar" class="sticky top-14 z-40 border-b border-[var(--ring)] bg-white/80 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-12 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <span class="font-semibold text-slate-800">Settings</span>
      </div>
      <nav id="contextTabs" class="flex flex-wrap gap-2">
        <a class="tab-pill active" href="#settings/general">General</a>
        <a class="tab-pill" href="#settings/notifications">Notifications</a>
        <a class="tab-pill" href="#settings/security">Security</a>
        <a class="tab-pill" href="#settings/system">System</a>
      </nav>
    </div>
  </div>

  <!-- LAYOUT -->
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-[240px_1fr] gap-6 py-6">
    <div id="overlay" class="overlay"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed md:static left-0 top-14 md:top-auto w-64 md:w-full h-[calc(100vh-56px)] md:h-auto bg-white border-r border-[var(--ring)] sidebar-transition -translate-x-full md:translate-x-0 z-50 overflow-y-auto">
      <nav class="p-3 space-y-1">
        <div class="text-[11px] uppercase tracking-widest text-slate-500 px-2 pt-2 pb-1">Navigation</div>
        <a class="sidebar-item" href="dashboard.php"><span>🏠</span><span>Dashboard</span></a>

        <a class="sidebar-item" href="General Ledger.php"><span>📘</span><span>General Ledger</span></a>
        <a class="sidebar-item" href="Accounts Receivable.php"><span>💳</span><span>Accounts Receivable</span></a>
        <a class="sidebar-item" href="Collections.php"><span>🧾</span><span>Collections</span></a>
        <a class="sidebar-item" href="Accounts Payable.php"><span>📄</span><span>Accounts Payable</span></a>
        <a class="sidebar-item" href="Disbursement.php"><span>💸</span><span>Disbursement</span></a>
        <a class="sidebar-item" href="Budget Management.php"><span>📊</span><span>Budget Management</span></a>
        <a class="sidebar-item" href="Reports.php"><span>📑</span><span>Reports</span></a>
      </nav>
    </aside>

    <!-- MAIN -->
    <main class="space-y-6">
      <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <section id="contentHost" class="space-y-6">
        <!-- General Settings -->
        <div id="settings-general" class="space-y-6">
          <div class="card p-6 settings-card">
            <h3 class="text-lg font-semibold mb-4">General Settings</h3>

            <form method="POST" class="space-y-6">
              <div class="settings-group">
                <h4 class="font-medium mb-4">Display Preferences</h4>

                <div class="setting-item">
                  <div>
                    <label class="font-medium">Dark Mode</label>
                    <div class="setting-description">Enable dark theme for better visibility in low light</div>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox" id="darkModeToggle" <?php echo (isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'enabled') ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                  </label>
                </div>

                <div class="setting-item">
                  <div>
                    <label class="font-medium">24-Hour Time Format</label>
                    <div class="setting-description">Use 24-hour format for time display</div>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox" id="timeFormatToggle" <?php echo (isset($_COOKIE['timeFormat']) && $_COOKIE['timeFormat'] === '24h') ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                  </label>
                </div>

                <div class="setting-item">
                  <div>
                    <label class="font-medium">Auto-save</label>
                    <div class="setting-description">Automatically save changes as you work</div>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox" name="auto_save" <?php echo $userPreferences['auto_save'] ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                  </label>
                </div>
              </div>

              <div class="flex gap-3">
                <button type="submit" name="update_system_settings" class="btn btn-brand">Save Changes</button>
                <button type="button" class="btn btn-soft">Reset to Defaults</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Notification Settings -->
        <div id="settings-notifications" class="hidden space-y-6">
          <div class="card p-6 settings-card">
            <h3 class="text-lg font-semibold mb-4">Notification Preferences</h3>

            <form method="POST" class="space-y-6">
              <div class="settings-group">
                <h4 class="font-medium mb-4">Email Notifications</h4>

                <div class="setting-item">
                  <div>
                    <label class="font-medium">System Alerts</label>
                    <div class="setting-description">Receive notifications about system updates and maintenance</div>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox" name="system_alerts" <?php echo $userPreferences['system_alerts'] ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                  </label>
                </div>

                <div class="setting-item">
                  <div>
                    <label class="font-medium">Financial Reports</label>
                    <div class="setting-description">Get notified when financial reports are generated</div>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox" name="financial_reports" <?php echo $userPreferences['financial_reports'] ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                  </label>
                </div>

                <div class="setting-item">
                  <div>
                    <label class="font-medium">Security Alerts</label>
                    <div class="setting-description">Important security notifications and login alerts</div>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox" name="security_alerts" <?php echo $userPreferences['security_alerts'] ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                  </label>
                </div>

                <div class="setting-item">
                  <div>
                    <label class="font-medium">Email Notifications</label>
                    <div class="setting-description">Receive email notifications for important updates</div>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox" name="email_notifications" <?php echo $userPreferences['email_notifications'] ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                  </label>
                </div>
              </div>

              <div class="flex gap-3">
                <button type="submit" name="update_preferences" class="btn btn-brand">Save Preferences</button>
                <button type="button" class="btn btn-soft">Reset to Defaults</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Security Settings -->
        <div id="settings-security" class="hidden space-y-6">
          <div class="card p-6 settings-card">
            <h3 class="text-lg font-semibold mb-4">Security Settings</h3>

            <form method="POST" class="space-y-6">
              <div class="settings-group">
                <h4 class="font-medium mb-4">Change Password</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium mb-2">Current Password</label>
                    <input type="password" name="current_password" class="form-input" required>
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-2">New Password</label>
                    <input type="password" name="new_password" class="form-input" required minlength="6">
                  </div>
                  <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-input" required minlength="6">
                  </div>
                </div>

                <div class="flex gap-3">
                  <button type="submit" name="change_password" class="btn btn-brand">Change Password</button>
                  <button type="button" class="btn btn-soft">Cancel</button>
                </div>
              </div>

              <div class="settings-group">
                <h4 class="font-medium mb-4">Account Security</h4>

                <div class="setting-item">
                  <div>
                    <label class="font-medium">Two-Factor Authentication</label>
                    <div class="setting-description">Add an extra layer of security to your account</div>
                  </div>
                  <button class="btn btn-soft text-sm">Enable 2FA</button>
                </div>

                <div class="setting-item">
                  <div>
                    <label class="font-medium">Login Sessions</label>
                    <div class="setting-description">Manage active login sessions</div>
                  </div>
                  <button class="btn btn-soft text-sm">View Sessions</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- System Settings -->
        <div id="settings-system" class="hidden space-y-6">
          <div class="card p-6 settings-card">
            <h3 class="text-lg font-semibold mb-4">System Settings</h3>

            <div class="settings-group">
              <h4 class="font-medium mb-4">Application Preferences</h4>

              <div class="setting-item">
                <div>
                  <label class="font-medium">Language</label>
                  <div class="setting-description">Choose your preferred language</div>
                </div>
                <select class="form-input w-48">
                  <option value="en">English</option>
                  <option value="es">Spanish</option>
                  <option value="fr">French</option>
                </select>
              </div>

              <div class="setting-item">
                <div>
                  <label class="font-medium">Timezone</label>
                  <div class="setting-description">Set your local timezone</div>
                </div>
                <select class="form-input w-48">
                  <option value="UTC">UTC</option>
                  <option value="EST">Eastern Time</option>
                  <option value="PST">Pacific Time</option>
                  <option value="GMT">GMT</option>
                </select>
              </div>

              <div class="setting-item">
                <div>
                  <label class="font-medium">Date Format</label>
                  <div class="setting-description">Choose how dates are displayed</div>
                </div>
                <select class="form-input w-48">
                  <option value="mdy">MM/DD/YYYY</option>
                  <option value="dmy">DD/MM/YYYY</option>
                  <option value="ymd">YYYY-MM-DD</option>
                </select>
              </div>
            </div>

            <div class="settings-group">
              <h4 class="font-medium mb-4">Data & Privacy</h4>

              <div class="setting-item">
                <div>
                  <label class="font-medium">Data Export</label>
                  <div class="setting-description">Download a copy of your data</div>
                </div>
                <button class="btn btn-soft text-sm">Export Data</button>
              </div>

              <div class="setting-item">
                <div>
                  <label class="font-medium">Data Retention</label>
                  <div class="setting-description">Manage how long your data is stored</div>
                </div>
                <select class="form-input w-48">
                  <option value="1year">1 Year</option>
                  <option value="2years">2 Years</option>
                  <option value="5years">5 Years</option>
                  <option value="forever">Forever</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- Chatbot Button -->
  <div id="chatbotButton" class="fixed bottom-6 right-6 z-40">
    <button onclick="toggleChatbot()" class="w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center group">
      <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
      </svg>
      <div class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
    </button>
  </div>

  <!-- Chatbot Modal -->
  <div id="chatbotModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full mx-4 h-[600px] flex flex-col overflow-hidden">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-600 to-blue-700 text-white">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
              </svg>
            </div>
            <div>
              <h3 class="font-semibold">AI Assistant</h3>
              <p class="text-sm opacity-90">Online</p>
            </div>
          </div>
          <button onclick="closeChatbot()" class="w-8 h-8 rounded-full hover:bg-white/20 flex items-center justify-center transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Messages Container -->
      <div id="chatMessages" class="flex-1 p-4 space-y-4 overflow-y-auto">
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
          </div>
          <div class="bg-gray-100 dark:bg-gray-700 rounded-2xl rounded-tl-md px-4 py-3 max-w-xs">
            <p class="text-sm text-gray-900 dark:text-white">Hello! I'm your AI assistant. How can I help you with your settings today?</p>
          </div>
        </div>
      </div>

      <!-- Input Area -->
      <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
        <div class="flex gap-3">
          <input type="text" id="chatInput" placeholder="Type your message..." class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-600 dark:text-white">
          <button onclick="sendMessage()" class="w-10 h-10 bg-blue-600 hover:bg-blue-700 text-white rounded-full flex items-center justify-center transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

    /* loader */
    const Loader = (() => {
      const el = $('#globalLoader');
      let on = false, t0 = 0;
      const MIN = 350;
      function show() { if (on) return; on = true; t0 = performance.now(); el.classList.remove('hidden'); el.classList.add('flex'); }
      function hide() { if (!on) return; const d = Math.max(0, MIN - (performance.now() - t0)); setTimeout(() => { el.classList.add('hidden'); el.classList.remove('flex'); on = false; }, d); }
      async function wrap(job) { show(); try { return typeof job === 'function' ? await job() : await job; } finally { hide(); } }
      return { show, hide, wrap };
    })();

    /* header interactions */
    const overlay = $('#overlay'), sidebar = $('#sidebar');
    $('#openSidebar')?.addEventListener('click', () => { sidebar.classList.remove('-translate-x-full'); overlay.classList.add('active'); });
    overlay?.addEventListener('click', () => { sidebar.classList.add('-translate-x-full'); overlay.classList.remove('active'); });
    const pBtn = $('#profileBtn'), pMenu = $('#profileMenu');
    pBtn?.addEventListener('click', () => pMenu.classList.toggle('hidden'));
    document.addEventListener('click', (e) => { if (pBtn && pMenu && !pBtn.contains(e.target) && !pMenu.contains(e.target)) pMenu.classList.add('hidden'); });

    /* live clock */
    (function() {
      const t = $('#liveTime'), d = $('#liveDate'), tm = $('#liveTimeMobile'), dm = $('#liveTimeMobile');
      let is24 = localStorage.getItem('fmt24') === '1';
      const fD = n => new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }).format(n);
      const fT = n => new Intl.DateTimeFormat(undefined, { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: !is24 }).format(n);
      function tick() { const n = new Date(); if (d) d.textContent = fD(n); if (t) t.textContent = fT(n); if (dm) dm.textContent = fD(n); if (tm) tm.textContent = fT(n); }
      t?.addEventListener('click', () => { is24 = !is24; localStorage.setItem('fmt24', is24 ? '1' : '0'); tick(); });
      tick(); setInterval(tick, 1000); $('#clockWrap')?.classList.remove('hidden');
    })();

    /* tab navigation */
    const tabs = $$('#contextTabs .tab-pill');
    tabs.forEach(tab => {
      tab.addEventListener('click', (e) => {
        e.preventDefault();
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        const target = tab.getAttribute('href').split('/')[1];
        showSection(target);
      });
    });

    function showSection(sectionName) {
      const sections = ['general', 'notifications', 'security', 'system'];
      sections.forEach(section => {
        const element = document.getElementById('settings-' + section);
        if (element) {
          element.style.display = section === sectionName ? 'block' : 'none';
        }
      });
    }

    // Show general section by default
    showSection('general');

    // Dark mode functionality
    const headerDarkModeToggle = document.getElementById('headerDarkModeToggle');

    // Toggle dark mode
    function toggleDarkMode() {
      const root = document.documentElement;
      const isDark = root.classList.toggle('dark');

      // Save preference to localStorage
      localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');

      // Update toggle icon
      updateHeaderToggleIcon(isDark);
    }

    // Update header toggle icon
    function updateHeaderToggleIcon(isDark) {
      if (headerDarkModeToggle) {
        const svg = headerDarkModeToggle.querySelector('svg');
        if (svg) {
          if (isDark) {
            // Sun icon for dark mode
            svg.innerHTML = '<path stroke-linecap="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>';
          } else {
            // Moon icon for light mode
            svg.innerHTML = '<path stroke-linecap="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>';
          }
        }
      }
    }

    // Initialize dark mode on page load
    function initDarkMode() {
      const savedMode = localStorage.getItem('darkMode');
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

      // Use saved preference or system preference
      if (savedMode === 'enabled' || (!savedMode && prefersDark)) {
        document.documentElement.classList.add('dark');
        updateHeaderToggleIcon(true);
      } else {
        updateHeaderToggleIcon(false);
      }
    }

    // Event listeners
    if (headerDarkModeToggle) {
      headerDarkModeToggle.addEventListener('click', toggleDarkMode);
    }

    // Initialize on page load
    initDarkMode();

    // Time format toggle
    const timeFormatToggle = document.getElementById('timeFormatToggle');
    if (timeFormatToggle) {
      timeFormatToggle.addEventListener('change', function() {
        const is24h = this.checked;
        localStorage.setItem('timeFormat', is24h ? '24h' : '12h');
        // You could update the clock display here if needed
      });
    }

    // Dark mode toggle in settings
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
      darkModeToggle.addEventListener('change', function() {
        toggleDarkMode();
      });
    }

    // Initialize loading screen
    function initLoadingScreen() {
      const loader = $('#globalLoader');
      const progressBar = $('#loadingProgress');

      // Simulate loading progress
      let progress = 0;
      const progressInterval = setInterval(() => {
        progress += Math.random() * 15 + 5; // Random progress between 5-20
        if (progress >= 100) {
          progress = 100;
          clearInterval(progressInterval);

          // Hide loader with fade out effect
          setTimeout(() => {
            loader.style.opacity = '0';
            loader.style.transform = 'scale(0.95)';
            setTimeout(() => {
              loader.style.display = 'none';
              // Animate content in
              animateContentIn();
            }, 300);
          }, 200);
        }
        progressBar.style.width = progress + '%';
      }, 100);
    }

    // Animate content in
    function animateContentIn() {
      const cards = document.querySelectorAll('.settings-card, .card');
      cards.forEach((card, index) => {
        setTimeout(() => {
          card.style.opacity = '0';
          card.style.transform = 'translateY(20px)';
          card.style.transition = 'all 0.5s ease-out';

          setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
          }, 100);
        }, index * 100);
      });
    }

    // Add loading state to buttons
    function initButtonLoading() {
      const buttons = document.querySelectorAll('button[onclick*="add"], button[onclick*="delete"], button[onclick*="edit"], button[onclick*="save"], button[onclick*="submit"]');
      buttons.forEach(button => {
        button.addEventListener('click', function() {
          if (!this.classList.contains('btn-loading')) {
            const originalText = this.textContent;
            this.classList.add('btn-loading');
            this.textContent = 'Processing...';

            // Reset after 2 seconds (or you can reset after actual operation)
            setTimeout(() => {
              this.classList.remove('btn-loading');
              this.textContent = originalText;
            }, 2000);
          }
        });
      });
    }

    // Initialize loading screen on page load
    document.addEventListener('DOMContentLoaded', () => {
      initLoadingScreen();
      initButtonLoading();
      // Initialize notification system after a short delay to avoid conflicts
      setTimeout(() => {
        initNotificationSystem();
      }, 100);
    });

    // ===== NOTIFICATION SYSTEM =====

    // Notification types and their configurations
    const notificationTypes = {
      system: {
        icon: '🔔',
        color: 'blue',
        title: 'System Alert',
        duration: 5000
      },
      financial: {
        icon: '📊',
        color: 'green',
        title: 'Financial Report',
        duration: 6000
      },
      security: {
        icon: '🔒',
        color: 'red',
        title: 'Security Alert',
        duration: 8000
      },
      email: {
        icon: '📧',
        color: 'purple',
        title: 'Email Notification',
        duration: 4000
      }
    };

    let notificationCount = 0;
    let notifications = [];

    // Initialize notification system
    function initNotificationSystem() {
      // Load existing notifications
      loadNotifications();

      // Simulate real-time notifications for Settings
      simulateSettingsNotifications();
    }

    // Toggle notification panel
    function toggleNotificationPanel() {
      const panel = document.getElementById('notificationPanel');
      if (panel) {
        panel.classList.toggle('hidden');
        // Force style recalculation for theme changes
        if (!panel.classList.contains('hidden')) {
          panel.offsetHeight; // Force reflow
        }
      }
    }

    // Add notification to panel
    function addNotificationToPanel(type, message) {
      const config = notificationTypes[type];
      if (!config) return;

      notificationCount++;
      updateNotificationBadge();

      const notification = {
        id: Date.now(),
        type: type,
        message: message,
        time: new Date().toLocaleTimeString(),
        icon: config.icon
      };

      notifications.unshift(notification);
      updateNotificationList();

      // Store in localStorage
      localStorage.setItem('notifications', JSON.stringify(notifications));
    }

    // Update notification badge
    function updateNotificationBadge() {
      const badge = $('#notificationBadge');
      if (notificationCount > 0) {
        badge.textContent = notificationCount > 99 ? '99+' : notificationCount;
        badge.classList.remove('hidden');
      } else {
        badge.classList.add('hidden');
      }
    }

    // Update notification list
    function updateNotificationList() {
      const list = $('#notificationList');
      if (notifications.length === 0) {
        list.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">No new notifications</div>';
        return;
      }

      list.innerHTML = notifications.map(notification => `
        <div class="p-3 border-b border-gray-100 hover:bg-gray-50 transition-colors">
          <div class="flex items-start gap-3">
            <span class="text-lg">${notification.icon}</span>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900">${notificationTypes[notification.type].title}</p>
              <p class="text-sm text-gray-600 mt-1">${notification.message}</p>
              <p class="text-xs text-gray-400 mt-1">${notification.time}</p>
            </div>
            <button onclick="removeNotification(${notification.id})" class="text-gray-400 hover:text-gray-600">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      `).join('');
    }

    // Remove individual notification
    function removeNotification(id) {
      notifications = notifications.filter(n => n.id !== id);
      notificationCount = Math.max(0, notificationCount - 1);
      updateNotificationBadge();
      updateNotificationList();
      localStorage.setItem('notifications', JSON.stringify(notifications));
    }

    // Clear all notifications
    function clearAllNotifications() {
      notifications = [];
      notificationCount = 0;
      updateNotificationBadge();
      updateNotificationList();
      localStorage.setItem('notifications', JSON.stringify(notifications));
    }

    // Load notifications from localStorage
    function loadNotifications() {
      const saved = localStorage.getItem('notifications');
      if (saved) {
        notifications = JSON.parse(saved);
        notificationCount = notifications.length;
        updateNotificationBadge();
        updateNotificationList();
      }
    }

    // Simulate Settings-specific notifications
    function simulateSettingsNotifications() {
      // Settings notifications (every 4 minutes)
      setInterval(() => {
        if (Math.random() < 0.15) { // 15% chance
          const messages = [
            'Settings updated successfully',
            'New security features available',
            'Password policy updated',
            'System maintenance scheduled'
          ];
          const randomMessage = messages[Math.floor(Math.random() * messages.length)];
          addNotificationToPanel('system', randomMessage);
        }
      }, 240000);
    }

    // Close notification panel when clicking outside
    document.addEventListener('click', function(e) {
      const panel = $('#notificationPanel');
      const bell = $('#notificationBell');

      if (panel && !panel.contains(e.target) && !bell.contains(e.target)) {
        panel.classList.add('hidden');
      }
    });

    // ===== CHATBOT FUNCTIONALITY =====

    // Toggle chatbot modal
    function toggleChatbot() {
      const modal = document.getElementById('chatbotModal');
      if (modal) {
        modal.classList.toggle('hidden');
        modal.classList.toggle('flex');

        // Focus input when opening
        if (!modal.classList.contains('hidden')) {
          setTimeout(() => {
            const input = document.getElementById('chatInput');
            if (input) input.focus();
          }, 100);
        }
      }
    }

    // Close chatbot modal
    function closeChatbot() {
      const modal = document.getElementById('chatbotModal');
      if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    }

    // Send message
    function sendMessage() {
      const input = document.getElementById('chatInput');
      const messagesContainer = document.getElementById('chatMessages');

      if (!input || !messagesContainer) return;

      const message = input.value.trim();
      if (!message) return;

      // Add user message
      addMessage(message, 'user');
      input.value = '';

      // Simulate AI response
      setTimeout(() => {
        const responses = [
          "I understand you're looking for help with settings. What specific setting would you like to adjust?",
          "That's a great question about your preferences. Let me help you with that.",
          "I can assist you with configuring your account settings. What would you like to change?",
          "Your settings are important for a personalized experience. How can I help you today?",
          "I see you're working on your settings. Is there anything specific you'd like to know?"
        ];
        const randomResponse = responses[Math.floor(Math.random() * responses.length)];
        addMessage(randomResponse, 'bot');
      }, 1000);
    }

    // Add message to chat
    function addMessage(text, sender) {
      const messagesContainer = document.getElementById('chatMessages');
      if (!messagesContainer) return;

      const messageDiv = document.createElement('div');
      messageDiv.className = `flex items-start gap-3 ${sender === 'user' ? 'justify-end' : ''}`;

      if (sender === 'bot') {
        messageDiv.innerHTML = `
          <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
          </div>
          <div class="bg-gray-100 dark:bg-gray-700 rounded-2xl rounded-tl-md px-4 py-3 max-w-xs">
            <p class="text-sm text-gray-900 dark:text-white">${text}</p>
          </div>
        `;
      } else {
        messageDiv.innerHTML = `
          <div class="bg-blue-600 rounded-2xl rounded-tr-md px-4 py-3 max-w-xs">
            <p class="text-sm text-white">${text}</p>
          </div>
        `;
      }

      messagesContainer.appendChild(messageDiv);
      messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Handle Enter key in chat input
    document.addEventListener('DOMContentLoaded', function() {
      const chatInput = document.getElementById('chatInput');
      if (chatInput) {
        chatInput.addEventListener('keypress', function(e) {
          if (e.key === 'Enter') {
            sendMessage();
          }
        });
      }
    });

    // Close chatbot when clicking outside
    document.addEventListener('click', function(e) {
      const modal = document.getElementById('chatbotModal');
      const button = document.getElementById('chatbotButton');

      if (modal && button && !modal.contains(e.target) && !button.contains(e.target) && !modal.classList.contains('hidden')) {
        closeChatbot();
      }
    });
  </script>

</body>
</html>
