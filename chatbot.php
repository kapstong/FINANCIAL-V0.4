<?php
/**
 * ATIERA Financial System - Chatbot Interface
 * AI-powered assistant for financial queries and system guidance
 */

require_once 'includes/auth.php';
$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ATIEREX - AI Chatbot</title>
  <link rel="icon" type="image/png" href="logo2.png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <style>
    :root{
      --brand:#0f1c49; --brand-600:#0c173c; --brand-100:#e8ecf9;
      --ink:#000; --muted:#000; --ring:0 0 0 3px rgba(15,28,73,.15);
      --card-bg: rgba(255,255,255,.95); --card-border: rgba(226,232,240,.9);
    }

    /* Dark mode variables */
    html.dark {
      --ink: #e5e7eb;
      --muted: #9ca3af;
      --card-bg: rgba(17,24,39,.92);
      --card-border: rgba(71,85,105,.55);
    }

    body{ background:#fff; color:var(--ink); }
    html.dark body{
      background: linear-gradient(140deg, rgba(7,12,38,1) 50%, rgba(11,21,56,1) 50%);
      color: var(--ink);
    }

    .navbar{ background:var(--brand); color:#fff; height: 3.5rem; }
    .navbar *{ color:#fff !important; }

    .card{ background:var(--card-bg); border-radius:14px; border:1px solid var(--card-border); box-shadow:0 6px 18px rgba(2,6,23,.04) }
    html.dark .card{ box-shadow:0 16px 48px rgba(0,0,0,.5); }

    .btn{ display:inline-flex; align-items:center; gap:.5rem; padding:.55rem .95rem; border-radius:.65rem; font-weight:600; color:#000 }
    .btn-brand{ background:var(--brand); color:#fff !important } .btn-brand:hover{ background:var(--brand-600) }

    /* Chatbot specific styles */
    .chat-container {
      height: calc(100vh - 8rem);
      max-width: 800px;
      margin: 0 auto;
    }

    .chat-messages {
      height: calc(100% - 120px);
      overflow-y: auto;
      padding: 1rem;
      scroll-behavior: smooth;
    }

    .message {
      margin-bottom: 1rem;
      max-width: 80%;
      animation: fadeIn 0.3s ease-in;
    }

    .message.user {
      margin-left: auto;
      text-align: right;
    }

    .message.bot {
      margin-right: auto;
    }

    .message-content {
      padding: 0.75rem 1rem;
      border-radius: 1rem;
      word-wrap: break-word;
    }

    .message.user .message-content {
      background: var(--brand);
      color: white;
    }

    .message.bot .message-content {
      background: var(--card-bg);
      border: 1px solid var(--card-border);
      color: var(--ink);
    }

    html.dark .message.bot .message-content {
      background: var(--card-bg);
      border-color: var(--card-border);
    }

    .typing-indicator {
      display: none;
      padding: 0.75rem 1rem;
      background: var(--card-bg);
      border: 1px solid var(--card-border);
      border-radius: 1rem;
      color: var(--muted);
      font-style: italic;
    }

    .chat-input-container {
      padding: 1rem;
      border-top: 1px solid var(--card-border);
      background: var(--card-bg);
    }

    .chat-input {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid var(--card-border);
      border-radius: 0.5rem;
      outline: none;
      resize: none;
      font-size: 0.875rem;
    }

    .chat-input:focus {
      border-color: var(--brand);
      box-shadow: 0 0 0 3px rgba(15, 28, 73, 0.1);
    }

    html.dark .chat-input {
      background: #1f2937;
      border-color: #374151;
      color: #f9fafb;
    }

    .quick-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-top: 0.5rem;
    }

    .quick-action-btn {
      padding: 0.25rem 0.75rem;
      background: var(--brand-100);
      color: var(--brand);
      border: 1px solid var(--brand-100);
      border-radius: 9999px;
      font-size: 0.75rem;
      cursor: pointer;
      transition: all 0.2s;
    }

    .quick-action-btn:hover {
      background: var(--brand);
      color: white;
    }

    html.dark .quick-action-btn {
      background: rgba(15, 28, 73, 0.2);
      color: #60a5fa;
      border-color: rgba(15, 28, 73, 0.3);
    }

    html.dark .quick-action-btn:hover {
      background: var(--brand);
      color: white;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .scroll-to-bottom {
      position: fixed;
      bottom: 120px;
      right: 2rem;
      background: var(--brand);
      color: white;
      border: none;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      cursor: pointer;
      opacity: 0;
      transition: opacity 0.3s;
      z-index: 1000;
    }

    .scroll-to-bottom.visible {
      opacity: 1;
    }

    /* Enhanced mobile responsiveness */
    @media (max-width: 640px) {
      .chat-container {
        height: calc(100vh - 6rem);
        margin: 0 0.5rem;
      }

      .message {
        max-width: 90%;
      }

      .chat-messages {
        padding: 0.5rem;
      }

      .chat-input-container {
        padding: 0.75rem;
      }
    }
  </style>
</head>

<body class="min-h-screen">
  <!-- HEADER -->
  <header class="sticky top-0 z-50 border-b border-[var(--ring)] navbar backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-14 flex items-center gap-3">
      <a href="dashboard.php" class="flex items-center gap-3">
        <img src="logo2.png" alt="ATIÉRA" class="h-8 w-auto sm:h-10" draggable="false">
        <span class="font-extrabold tracking-wide text-lg">ATIEREX</span>
      </a>

      <div class="ml-auto flex items-center gap-2">
        <span class="text-sm">AI Financial Assistant</span>
      </div>

      <!-- Dark Mode Toggle -->
      <button id="headerDarkModeToggle" class="p-2 rounded hover:bg-white/10 text-white" title="Toggle dark mode">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>
      </button>

      <div class="relative">
        <button id="profileBtn" class="p-2 rounded hover:bg-white/10 flex items-center gap-2" title="Account">
          <img src="<?php echo htmlspecialchars(!empty($user['profile_image']) ? 'uploads/' . $user['profile_image'] : 'uploads/admindefault.png'); ?>"
               alt="Profile" class="w-8 h-8 rounded-full object-cover border border-white/30">
        </button>
        <div id="profileMenu" class="hidden absolute right-0 mt-2 w-56 bg-black rounded-lg shadow-xl border border-[var(--card-border)] overflow-hidden text-[var(--ink)]">
          <div class="px-4 py-2 text-xs text-slate-500 border-b border-[var(--card-border)] md:hidden">
            <span id="liveDateMobile"></span> • <span id="liveTimeMobile" class="font-mono"></span>
          </div>
          <div class="px-4 py-2 border-b border-[var(--card-border)]">
            <div class="text-sm font-medium"><?php echo htmlspecialchars($user['username']); ?></div>
            <div class="text-xs text-slate-500"><?php echo htmlspecialchars($user['role_name']); ?></div>
          </div>
          <a href="settings.php" class="block px-4 py-2 text-black hover:bg-slate-900">Settings</a>
          <a href="profile.php" class="block px-4 py-2 text-black hover:bg-slate-900">Profile</a>
          <a href="logout.php" class="block px-4 py-2 text-black hover:bg-slate-900">Logout</a>
        </div>
      </div>
    </div>
  </header>

  <!-- CHATBOT INTERFACE -->
  <main class="p-4">
    <div class="chat-container card">
      <div class="chat-messages" id="chatMessages">
        <!-- Welcome message -->
        <div class="message bot">
          <div class="message-content">
            <strong>🤖 ATIEREX - AI Chatbot</strong><br>
            Hello! I'm your AI financial assistant. I can help you with:
            <ul class="mt-2 ml-4 list-disc">
              <li>Financial reports and analysis</li>
              <li>System navigation and features</li>
              <li>Accounting questions</li>
              <li>Hotel & restaurant operations</li>
            </ul>
            <br>
            What would you like to know about your financial system?
          </div>
        </div>
      </div>

      <!-- Typing indicator -->
      <div class="message bot">
        <div class="message-content typing-indicator" id="typingIndicator">
          <span id="typingText">ATIEREX is thinking...</span>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions" id="quickActions">
        <button class="quick-action-btn" onclick="sendQuickMessage('Show me today\'s financial summary')">📊 Today's Summary</button>
        <button class="quick-action-btn" onclick="sendQuickMessage('How do I create a journal entry?')">📝 Journal Entry Help</button>
        <button class="quick-action-btn" onclick="sendQuickMessage('What are my pending tasks?')">✅ Pending Tasks</button>
        <button class="quick-action-btn" onclick="sendQuickMessage('Generate monthly report')">📈 Monthly Report</button>
        <button class="quick-action-btn" onclick="sendQuickMessage('Room revenue analysis')">🏨 Room Analysis</button>
        <button class="quick-action-btn" onclick="sendQuickMessage('Restaurant sales today')">🍽️ Restaurant Sales</button>
      </div>

      <!-- Input area -->
      <div class="chat-input-container">
        <div class="flex gap-2">
          <textarea
            id="chatInput"
            class="chat-input"
            placeholder="Ask me anything about your financial system..."
            rows="1"
            onkeydown="handleKeyPress(event)"
          ></textarea>
          <button id="sendButton" class="btn btn-brand" onclick="sendMessage()">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            Send
          </button>
        </div>
      </div>
    </div>

    <!-- Scroll to bottom button -->
    <button class="scroll-to-bottom" id="scrollToBottom" onclick="scrollToBottom()">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
      </svg>
    </button>
  </main>

  <script>
    // Chatbot knowledge base and responses
    const knowledgeBase = {
      greetings: [
        "Hello! How can I help you with your financial system today?",
        "Hi there! I'm here to assist with your ATIEREX financial management.",
        "Welcome! What financial questions do you have?"
      ],

      responses: {
        // Financial summaries
        "summary": "I can show you various financial summaries. Try asking for 'today's summary', 'monthly report', or 'year-to-date analysis'.",
        "today": "For today's financial data, check the dashboard for real-time KPIs including cash balance, AR/AP outstanding, and revenue metrics.",
        "monthly": "Monthly reports include revenue analysis, expense breakdowns, and profitability metrics. You can generate these from the Reports module.",

        // Journal entries
        "journal": "To create a journal entry: 1) Go to General Ledger → Journal Entries 2) Click 'New Entry' 3) Select accounts and enter amounts 4) Add description 5) Save and post.",
        "entry": "Journal entries record financial transactions. Each entry must have equal debits and credits to maintain accounting balance.",

        // Tasks and workflow
        "task": "Your pending tasks include: reviewing outstanding invoices, approving expense reports, and checking bank reconciliations.",
        "pending": "Check your dashboard notifications for pending approvals, overdue payments, and system alerts.",

        // Room management
        "room": "Room management includes: checking availability, managing rates, tracking occupancy, and generating revenue reports.",
        "revenue": "Room revenue is tracked automatically through folios. Check the dashboard for RevPAR and occupancy rates.",

        // Restaurant operations
        "restaurant": "Restaurant operations include POS transactions, inventory management, menu pricing, and sales analysis.",
        "pos": "POS integration automatically records sales transactions, calculates taxes, and updates inventory levels.",

        // Reports and analysis
        "report": "Available reports: Financial Statements, Revenue Analysis, Expense Reports, Tax Reports, and Custom Analytics.",
        "analysis": "Financial analysis includes trend analysis, budget vs actual comparisons, and profitability reports.",

        // System features
        "help": "I can help with: system navigation, financial concepts, report generation, data analysis, and operational guidance.",
        "feature": "Key features include: Multi-user access, Real-time dashboards, Automated calculations, API integrations, and Comprehensive reporting."
      },

      commands: {
        "show dashboard": "Redirecting to dashboard...",
        "open reports": "Opening reports module...",
        "create journal": "Opening journal entry form...",
        "view accounts": "Opening chart of accounts..."
      }
    };

    let chatMessages = document.getElementById('chatMessages');
    let chatInput = document.getElementById('chatInput');
    let typingIndicator = document.getElementById('typingIndicator');
    let scrollToBottomBtn = document.getElementById('scrollToBottom');
    let quickActions = document.getElementById('quickActions');

    // Initialize chatbot
    document.addEventListener('DOMContentLoaded', function() {
      initChatbot();
      initDarkMode();
      initProfileMenu();
    });

    function initChatbot() {
      // Auto-resize textarea
      chatInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
      });

      // Scroll detection for scroll-to-bottom button
      chatMessages.addEventListener('scroll', function() {
        const isNearBottom = chatMessages.scrollHeight - chatMessages.scrollTop - chatMessages.clientHeight < 100;
        scrollToBottomBtn.classList.toggle('visible', !isNearBottom);
      });
    }

    function sendMessage() {
      const message = chatInput.value.trim();
      if (!message) return;

      addMessage(message, 'user');
      chatInput.value = '';
      chatInput.style.height = 'auto';

      // Show typing indicator
      showTypingIndicator();

      // Process message after delay
      setTimeout(() => {
        hideTypingIndicator();
        processMessage(message);
      }, 1000 + Math.random() * 1000); // Random delay 1-2 seconds
    }

    function sendQuickMessage(message) {
      chatInput.value = message;
      sendMessage();
    }

    function handleKeyPress(event) {
      if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
      }
    }

    function addMessage(content, type) {
      const messageDiv = document.createElement('div');
      messageDiv.className = `message ${type}`;

      const contentDiv = document.createElement('div');
      contentDiv.className = 'message-content';
      contentDiv.innerHTML = content;

      messageDiv.appendChild(contentDiv);
      chatMessages.appendChild(messageDiv);

      scrollToBottom();
    }

    function processMessage(message) {
      const lowerMessage = message.toLowerCase();
      let response = generateResponse(lowerMessage);

      // Check for commands
      if (checkCommands(lowerMessage)) {
        return;
      }

      addMessage(response, 'bot');

      // Hide quick actions after first interaction
      if (quickActions.style.display !== 'none') {
        quickActions.style.display = 'none';
      }
    }

    function generateResponse(message) {
      // Greeting responses
      if (message.includes('hello') || message.includes('hi') || message.includes('hey')) {
        return knowledgeBase.greetings[Math.floor(Math.random() * knowledgeBase.greetings.length)];
      }

      // Keyword-based responses
      for (const [key, response] of Object.entries(knowledgeBase.responses)) {
        if (message.includes(key)) {
          return response;
        }
      }

      // Default responses based on message content
      if (message.includes('?')) {
        return "I'd be happy to help with that question! Could you provide more details about what you're looking for?";
      }

      if (message.includes('thank')) {
        return "You're welcome! Is there anything else I can assist you with regarding your financial system?";
      }

      if (message.includes('problem') || message.includes('issue') || message.includes('error')) {
        return "I'm sorry you're experiencing an issue. Please describe the problem in detail, and I'll guide you through troubleshooting or direct you to the appropriate support resources.";
      }

      // Fallback response
      return "I understand you're asking about: <strong>" + message + "</strong><br><br>While I don't have specific information about that topic, I can help you with:<br>• Financial reporting and analysis<br>• System navigation and features<br>• Accounting procedures<br>• Hotel and restaurant operations<br><br>What specific area would you like assistance with?";
    }

    function checkCommands(message) {
      for (const [command, response] of Object.entries(knowledgeBase.commands)) {
        if (message.includes(command)) {
          addMessage(response, 'bot');

          // Execute command actions
          setTimeout(() => {
            if (command.includes('dashboard')) {
              window.location.href = 'dashboard.php';
            } else if (command.includes('reports')) {
              window.location.href = 'Reports.php';
            } else if (command.includes('journal')) {
              window.location.href = 'General Ledger.php';
            } else if (command.includes('accounts')) {
              window.location.href = 'General Ledger.php#gl-ledger';
            }
          }, 1500);

          return true;
        }
      }
      return false;
    }

    function showTypingIndicator() {
      typingIndicator.style.display = 'block';
      const texts = ['ATIEREX is thinking...', 'Analyzing your request...', 'Searching knowledge base...', 'Preparing response...'];
      let textIndex = 0;

      const textInterval = setInterval(() => {
        document.getElementById('typingText').textContent = texts[textIndex];
        textIndex = (textIndex + 1) % texts.length;
      }, 500);

      // Store interval to clear later
      typingIndicator.dataset.interval = textInterval;
    }

    function hideTypingIndicator() {
      typingIndicator.style.display = 'none';
      if (typingIndicator.dataset.interval) {
        clearInterval(typingIndicator.dataset.interval);
      }
    }

    function scrollToBottom() {
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Dark mode functionality
    function initDarkMode() {
      const headerDarkModeToggle = document.getElementById('headerDarkModeToggle');

      function toggleDarkMode() {
        const root = document.documentElement;
        const isDark = root.classList.toggle('dark');

        localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');

        updateHeaderToggleIcon(isDark);
      }

      function updateHeaderToggleIcon(isDark) {
        if (headerDarkModeToggle) {
          const svg = headerDarkModeToggle.querySelector('svg');
          if (svg) {
            if (isDark) {
              svg.innerHTML = '<path stroke-linecap="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>';
            } else {
              svg.innerHTML = '<path stroke-linecap="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>';
            }
          }
        }
      }

      function initDarkModeOnLoad() {
        const savedMode = localStorage.getItem('darkMode');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedMode === 'enabled' || (!savedMode && prefersDark)) {
          document.documentElement.classList.add('dark');
          updateHeaderToggleIcon(true);
        } else {
          updateHeaderToggleIcon(false);
        }
      }

      if (headerDarkModeToggle) {
        headerDarkModeToggle.addEventListener('click', toggleDarkMode);
      }

      initDarkModeOnLoad();
    }

    // Profile menu functionality
    function initProfileMenu() {
      const profileBtn = document.getElementById('profileBtn');
      const profileMenu = document.getElementById('profileMenu');

      profileBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        profileMenu.classList.toggle('hidden');
      });

      document.addEventListener('click', function(e) {
        if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
          profileMenu.classList.add('hidden');
        }
      });
    }

    // Add some initial demo messages for better UX
    setTimeout(() => {
      addMessage("💡 <strong>Pro tip:</strong> Try asking me about 'monthly reports', 'journal entries', or 'room revenue analysis'!", 'bot');
    }, 3000);
  </script>
</body>
</html>
