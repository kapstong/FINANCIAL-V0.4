<?php
/**
 * ATIERA Financial System - AI Assistant Component
 * Floating button and modal for chatbot integration
 */
?>

<!-- AI Assistant Floating Button -->
<button id="aiAssistantBtn" class="fixed bottom-6 right-6 z-50 w-20 h-20 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800 group overflow-hidden" title="AI Assistant">
  <img src="atierexlogo.png" alt="ATIERA AI" class="w-full h-full object-cover">
  <!-- Pulse animation ring -->
  <div class="absolute inset-0 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 animate-ping opacity-20"></div>
</button>

<!-- AI Assistant Modal -->
<div id="aiAssistantModal" class="fixed inset-0 z-[100] hidden">
  <!-- Backdrop -->
  <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm" id="aiAssistantBackdrop"></div>

  <!-- Modal Content -->
  <div class="relative flex items-center justify-center min-h-screen p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-4xl h-[80vh] flex flex-col overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="aiAssistantPanel">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-800 dark:to-gray-700">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center overflow-hidden">
            <img src="atierexlogo.png" alt="ATIERA AI" class="w-full h-full object-cover">
          </div>
          <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">ATIEREx Assistant</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">Your centralized AI financial companion</p>
          </div>
        </div>
        <button id="closeAiAssistant" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors">
          <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Chat Messages Container -->
      <div class="flex-1 overflow-y-auto p-6 space-y-4" id="aiChatMessages">
        <!-- Welcome message -->
        <div class="flex gap-3">
          <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden">
            <img src="atierexlogo.png" alt="ATIERA AI" class="w-full h-full object-cover">
          </div>
          <div class="bg-gray-100 dark:bg-gray-700 rounded-2xl rounded-tl-md px-4 py-3 max-w-[80%]">
            <p class="text-gray-900 dark:text-white">
              🤖 <strong>ATIEREx Assistant</strong><br>
              Hello! I'm your centralized AI financial companion. I can help you with:
            </p>
            <ul class="mt-2 ml-4 list-disc text-gray-700 dark:text-gray-300">
              <li>Financial reports and analysis</li>
              <li>System navigation and features</li>
              <li>Accounting questions</li>
              <li>Hotel & restaurant operations</li>
            </ul>
            <p class="mt-2 text-gray-700 dark:text-gray-300">
              What would you like to know about your financial system?
            </p>
          </div>
        </div>
      </div>

      <!-- Typing Indicator -->
      <div class="hidden px-6 pb-4" id="aiTypingIndicator">
        <div class="flex gap-3">
          <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden">
            <img src="atierexlogo.png" alt="ATIERA AI" class="w-full h-full object-cover animate-pulse">
          </div>
          <div class="bg-gray-100 dark:bg-gray-700 rounded-2xl rounded-tl-md px-4 py-3">
            <div class="flex items-center gap-2">
              <div class="flex gap-1">
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
              </div>
              <span class="text-sm text-gray-600 dark:text-gray-400">ATIEREx is thinking...</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="px-6 pb-4" id="aiQuickActions">
        <div class="flex flex-wrap gap-2">
          <button class="px-3 py-2 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-full text-sm hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors" onclick="sendAiMessage('Show me today\'s financial summary')">
            📊 Today's Summary
          </button>
          <button class="px-3 py-2 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-full text-sm hover:bg-green-200 dark:hover:bg-green-800 transition-colors" onclick="sendAiMessage('How do I create a journal entry?')">
            📝 Journal Entry Help
          </button>
          <button class="px-3 py-2 bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 rounded-full text-sm hover:bg-purple-200 dark:hover:bg-purple-800 transition-colors" onclick="sendAiMessage('What are my pending tasks?')">
            ✅ Pending Tasks
          </button>
          <button class="px-3 py-2 bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-300 rounded-full text-sm hover:bg-orange-200 dark:hover:bg-orange-800 transition-colors" onclick="sendAiMessage('Generate monthly report')">
            📈 Monthly Report
          </button>
        </div>
      </div>

      <!-- Input Area -->
      <div class="p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
        <div class="flex gap-3">
          <input
            type="text"
            id="aiChatInput"
            class="flex-1 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
            placeholder="Ask me anything about your financial system..."
            maxlength="500"
          >
          <!-- Voice Input Button -->
          <button
            id="aiVoiceButton"
            class="px-4 py-3 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 rounded-full transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed"
            title="Voice Input"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
            </svg>
          </button>
          <button
            id="aiSendButton"
            class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white rounded-full transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
          </button>
        </div>
        <!-- Voice Status -->
        <div id="voiceStatus" class="mt-3 text-center text-sm text-gray-600 dark:text-gray-400 hidden">
          <div id="voiceListening" class="flex items-center justify-center gap-2">
            <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
            <span>Listening... Click to stop</span>
          </div>
          <div id="voiceProcessing" class="flex items-center justify-center gap-2 hidden">
            <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
            <span>Processing speech...</span>
          </div>
          <div id="voiceError" class="text-red-500 hidden">
            Voice input not supported in this browser
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// AI Assistant functionality
document.addEventListener('DOMContentLoaded', function() {
  const aiBtn = document.getElementById('aiAssistantBtn');
  const aiModal = document.getElementById('aiAssistantModal');
  const aiPanel = document.getElementById('aiAssistantPanel');
  const aiBackdrop = document.getElementById('aiAssistantBackdrop');
  const closeBtn = document.getElementById('closeAiAssistant');
  const chatInput = document.getElementById('aiChatInput');
  const sendBtn = document.getElementById('aiSendButton');
  const messagesContainer = document.getElementById('aiChatMessages');
  const typingIndicator = document.getElementById('aiTypingIndicator');
  const quickActions = document.getElementById('aiQuickActions');

  let isTyping = false;

  // Open modal
  aiBtn.addEventListener('click', function() {
    aiModal.classList.remove('hidden');
    setTimeout(() => {
      aiPanel.classList.remove('scale-95', 'opacity-0');
      aiPanel.classList.add('scale-100', 'opacity-100');
    }, 10);
    chatInput.focus();
  });

  // Close modal
  function closeModal() {
    aiPanel.classList.remove('scale-100', 'opacity-100');
    aiPanel.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
      aiModal.classList.add('hidden');
    }, 300);
  }

  closeBtn.addEventListener('click', closeModal);
  aiBackdrop.addEventListener('click', closeModal);

  // Send message
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
      processAiMessage(message);
    }, 1000 + Math.random() * 1000);
  }

  sendBtn.addEventListener('click', sendMessage);

  chatInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });

  // Add message to chat
  function addMessage(content, type) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `flex gap-3 ${type === 'user' ? 'justify-end' : ''}`;

    if (type === 'user') {
      messageDiv.innerHTML = `
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-2xl rounded-tr-md px-4 py-3 max-w-[80%]">
          <p>${content}</p>
        </div>
        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
          <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
      `;
    } else {
      messageDiv.innerHTML = `
        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden">
          <img src="atierexlogo.png" alt="ATIERA AI" class="w-full h-full object-cover">
        </div>
        <div class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-2xl rounded-tl-md px-4 py-3 max-w-[80%]">
          <div class="prose prose-sm dark:prose-invert max-w-none">${content}</div>
        </div>
      `;
    }

    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    // Hide quick actions after first interaction
    if (quickActions.style.display !== 'none') {
      quickActions.style.display = 'none';
    }
  }

  // Show typing indicator
  function showTypingIndicator() {
    typingIndicator.classList.remove('hidden');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }

  // Hide typing indicator
  function hideTypingIndicator() {
    typingIndicator.classList.add('hidden');
  }

  // Process AI message (simplified version)
  function processAiMessage(message) {
    const lowerMessage = message.toLowerCase();
    let response = generateAiResponse(lowerMessage);
    addMessage(response, 'bot');
  }

  // Generate AI response
  function generateAiResponse(message) {
    const lowerMessage = message.toLowerCase();

    // Greeting responses
    if (lowerMessage.includes('hello') || lowerMessage.includes('hi') || lowerMessage.includes('hey')) {
      return "👋 <strong>Hello there!</strong> Welcome to your ATIERA Financial System! I'm your AI assistant, ready to help you navigate the world of finance. What would you like to explore today?";
    }

    // Financial summary and dashboard
    if (lowerMessage.includes('summary') || lowerMessage.includes('today') || lowerMessage.includes('dashboard')) {
      return "📊 <strong>Here's your financial snapshot:</strong><br><br>• <strong>Cash Position:</strong> ₱540,000.00 (Healthy liquidity)<br>• <strong>Accounts Receivable:</strong> ₱120,000.00 (30-day average collection)<br>• <strong>Accounts Payable:</strong> ₱80,000.00 (Well managed)<br>• <strong>Monthly Revenue:</strong> ₱388,000.00 (+12% from last month)<br><br><em>💡 Pro tip: Your cash flow looks solid! Consider the Dashboard for interactive charts and deeper insights.</em>";
    }

    // Journal entries and accounting
    if (lowerMessage.includes('journal') || lowerMessage.includes('entry') || lowerMessage.includes('accounting')) {
      return "📝 <strong>Let's create that journal entry!</strong><br><br>Here's the smart way to do it:<br><br>1. <strong>Navigate:</strong> General Ledger → Journal Entries tab<br>2. <strong>Click:</strong> The blue '+ New Entry' button<br>3. <strong>Select:</strong> Appropriate debit/credit accounts<br>4. <strong>Enter:</strong> Precise amounts (remember: debits = credits!)<br>5. <strong>Describe:</strong> Clear transaction details<br>6. <strong>Save:</strong> And post to update your books<br><br><em>🎯 Remember: Every transaction tells a story - make sure yours is accurate!</em>";
    }

    // Tasks and workflow
    if (lowerMessage.includes('pending') || lowerMessage.includes('task') || lowerMessage.includes('workflow')) {
      return "✅ <strong>Your priority tasks await!</strong><br><br>Here's what needs your attention:<br>• <strong>3 Outstanding Invoices</strong> - Review and follow up<br>• <strong>2 Expense Reports</strong> - Ready for approval<br>• <strong>Bank Reconciliation</strong> - Monthly review due<br>• <strong>Budget Forecasts</strong> - Q4 projections needed<br><br><em>🚀 Staying on top of these will keep your finances running smoothly!</em>";
    }

    // Reports and analytics
    if (lowerMessage.includes('report') || lowerMessage.includes('monthly') || lowerMessage.includes('analytics')) {
      return "📈 <strong>Ready to dive into your data?</strong><br><br>Your reporting toolkit includes:<br>• <strong>📊 Financial Statements</strong> - Balance Sheet & P&L<br>• <strong>💰 Revenue Analysis</strong> - Trend spotting made easy<br>• <strong>💸 Expense Breakdown</strong> - Where's your money going?<br>• <strong>🎯 Budget vs Actual</strong> - Performance tracking<br>• <strong>📋 Tax Reports</strong> - Compliance ready<br><br><em>💡 Try the Reports module - it's like having a CFO in your pocket!</em>";
    }

    // Collections and AR
    if (lowerMessage.includes('collection') || lowerMessage.includes('receivable') || lowerMessage.includes('invoice')) {
      return "💳 <strong>Let's optimize your collections!</strong><br><br>Smart collection strategies:<br>• <strong>Monitor Aging:</strong> Focus on 30+ day invoices<br>• <strong>Customer Communication:</strong> Friendly reminders work best<br>• <strong>Payment Terms:</strong> Consider incentives for early payment<br>• <strong>Cash Flow Planning:</strong> Forecast based on payment patterns<br><br><em>💰 Good collections = Happy cash flow! Check the Collections module for detailed insights.</em>";
    }

    // Payments and AP
    if (lowerMessage.includes('payment') || lowerMessage.includes('payable') || lowerMessage.includes('bill')) {
      return "💰 <strong>Payment optimization time!</strong><br><br>Strategic payment management:<br>• <strong>Due Date Tracking:</strong> Never miss a payment deadline<br>• <strong>Cash Flow Alignment:</strong> Time payments with cash availability<br>• <strong>Supplier Relationships:</strong> Negotiate favorable terms<br>• <strong>Discount Opportunities:</strong> Early payment incentives<br><br><em>⚡ Smart payment timing can improve your working capital!</em>";
    }

    // Budgeting and forecasting
    if (lowerMessage.includes('budget') || lowerMessage.includes('forecast') || lowerMessage.includes('planning')) {
      return "🎯 <strong>Budget mastery awaits!</strong><br><br>Your financial planning toolkit:<br>• <strong>📊 Budget vs Actual</strong> - Real-time performance<br>• <strong>🔮 Forecasting</strong> - Predictive analytics<br>• <strong>📈 Trend Analysis</strong> - Historical patterns<br>• <strong>⚠️ Variance Alerts</strong> - Stay ahead of issues<br><br><em>🎯 A good budget is like a roadmap - it keeps you on track to success!</em>";
    }

    // System navigation and help
    if (lowerMessage.includes('help') || lowerMessage.includes('feature') || lowerMessage.includes('how') || lowerMessage.includes('navigation')) {
      return "🧭 <strong>I'm your financial system guide!</strong><br><br>I can help you with:<br>• <strong>📊 Financial Analysis</strong> - Deep dive into your numbers<br>• <strong>🧭 System Navigation</strong> - Find anything, anytime<br>• <strong>📚 Accounting Procedures</strong> - Best practices & tips<br>• <strong>💡 Business Insights</strong> - Turn data into decisions<br>• <strong>🏨 Hospitality Operations</strong> - Hotel & restaurant specific<br><br><em>💡 Pro tip: Try asking specific questions like 'How do I create an invoice?' or 'Show me cash flow trends'</em>";
    }

    // Specific questions about features
    if (lowerMessage.includes('how do i') || lowerMessage.includes('how to') || lowerMessage.includes('create') || lowerMessage.includes('make')) {
      if (lowerMessage.includes('invoice')) {
        return "📄 <strong>Creating invoices made simple!</strong><br><br>Here's the quick guide:<br>1. Go to <strong>Accounts Receivable</strong><br>2. Click <strong>'New Invoice'</strong><br>3. Select your customer<br>4. Add line items with descriptions<br>5. Set payment terms<br>6. Send or print<br><br><em>⚡ Your customers will love the professional look!</em>";
      }
      if (lowerMessage.includes('customer') || lowerMessage.includes('client')) {
        return "👥 <strong>Managing customers efficiently!</strong><br><br>Customer management steps:<br>1. Navigate to <strong>Accounts Receivable</strong><br>2. Click <strong>'Customers'</strong> tab<br>3. Add new customer details<br>4. Set credit terms and limits<br>5. Link to existing transactions<br><br><em>🎯 Good customer data = Better business decisions!</em>";
      }
    }

    // Performance and insights
    if (lowerMessage.includes('performance') || lowerMessage.includes('kpi') || lowerMessage.includes('metric')) {
      return "📈 <strong>Let's analyze your performance!</strong><br><br>Key metrics to monitor:<br>• <strong>💰 Profit Margins</strong> - Are you pricing right?<br>• <strong>⚡ Cash Conversion</strong> - How fast is money moving?<br>• <strong>📊 Customer Acquisition</strong> - Growth indicators<br>• <strong>🎯 Collection Efficiency</strong> - AR management success<br><br><em>📊 Numbers tell stories - let's read yours together!</em>";
    }

    // Default engaging response
    const engagingResponses = [
      "🤔 <strong>That's an interesting question!</strong> I'm here to help with all things financial. Could you tell me more about what you're trying to accomplish?",
      "💡 <strong>Great question!</strong> I love helping with financial system navigation and analysis. What specific area would you like to explore?",
      "🎯 <strong>I'm ready to assist!</strong> Whether it's accounting, reporting, or system features, I've got you covered. What's on your mind?",
      "🚀 <strong>Let's make this productive!</strong> I can help with financial analysis, system navigation, or business insights. What would be most helpful right now?"
    ];

    return engagingResponses[Math.floor(Math.random() * engagingResponses.length)];
  }

  // Global function for quick actions
  window.sendAiMessage = function(message) {
    chatInput.value = message;
    sendMessage();
  };

  // Auto-resize input
  chatInput.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 100) + 'px';
  });

  // ESC key to close
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !aiModal.classList.contains('hidden')) {
      closeModal();
    }
  });

  // ===== VOICE CHAT FUNCTIONALITY =====

  // Voice elements
  const voiceBtn = document.getElementById('aiVoiceButton');
  const voiceStatus = document.getElementById('voiceStatus');
  const voiceListening = document.getElementById('voiceListening');
  const voiceProcessing = document.getElementById('voiceProcessing');
  const voiceError = document.getElementById('voiceError');

  // Voice state variables
  let recognition = null;
  let isListening = false;
  let isProcessing = false;
  let speechSynthesis = window.speechSynthesis;
  let currentUtterance = null;

  // Initialize voice functionality
  function initVoiceChat() {
    // Check for browser support
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
      voiceBtn.disabled = true;
      voiceBtn.title = 'Voice input not supported in this browser';
      return;
    }

    // Initialize speech recognition
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = 'en-US'; // Can be made configurable

    // Recognition event handlers
    recognition.onstart = function() {
      isListening = true;
      updateVoiceUI('listening');
      console.log('Voice recognition started');
    };

    recognition.onresult = function(event) {
      const transcript = event.results[0][0].transcript;
      console.log('Voice transcript:', transcript);

      // Stop listening and process
      stopListening();
      updateVoiceUI('processing');

      // Add the voice message to chat
      setTimeout(() => {
        addMessage(transcript, 'user');
        updateVoiceUI('idle');

        // Process the voice message
        showTypingIndicator();
        setTimeout(() => {
          hideTypingIndicator();
          processAiMessage(transcript);
        }, 1000 + Math.random() * 1000);
      }, 500);
    };

    recognition.onerror = function(event) {
      console.error('Speech recognition error:', event.error);
      stopListening();
      updateVoiceUI('error', event.error);
      setTimeout(() => updateVoiceUI('idle'), 3000);
    };

    recognition.onend = function() {
      isListening = false;
      if (!isProcessing) {
        updateVoiceUI('idle');
      }
      console.log('Voice recognition ended');
    };

    // Voice button click handler
    voiceBtn.addEventListener('click', toggleVoiceInput);
  }

  // Toggle voice input
  function toggleVoiceInput() {
    if (isListening) {
      stopListening();
    } else {
      startListening();
    }
  }

  // Start voice recognition
  function startListening() {
    if (isListening) return;

    try {
      // Request microphone permission and start recognition
      navigator.mediaDevices.getUserMedia({ audio: true })
        .then(function(stream) {
          // Stop the stream immediately after permission is granted
          stream.getTracks().forEach(track => track.stop());

          // Start speech recognition
          recognition.start();
        })
        .catch(function(err) {
          console.error('Microphone permission denied:', err);
          updateVoiceUI('error', 'Microphone access denied');
          setTimeout(() => updateVoiceUI('idle'), 3000);
        });
    } catch (error) {
      console.error('Error starting voice recognition:', error);
      updateVoiceUI('error', 'Voice recognition failed');
      setTimeout(() => updateVoiceUI('idle'), 3000);
    }
  }

  // Stop voice recognition
  function stopListening() {
    if (recognition && isListening) {
      recognition.stop();
    }
  }

  // Update voice UI state
  function updateVoiceUI(state, errorMessage = '') {
    // Reset all states
    voiceStatus.classList.add('hidden');
    voiceListening.classList.add('hidden');
    voiceProcessing.classList.add('hidden');
    voiceError.classList.add('hidden');

    // Update button appearance
    voiceBtn.classList.remove('bg-red-500', 'hover:bg-red-600', 'animate-pulse');

    switch (state) {
      case 'listening':
        voiceStatus.classList.remove('hidden');
        voiceListening.classList.remove('hidden');
        voiceBtn.classList.add('bg-red-500', 'hover:bg-red-600', 'animate-pulse');
        break;

      case 'processing':
        voiceStatus.classList.remove('hidden');
        voiceProcessing.classList.remove('hidden');
        isProcessing = true;
        break;

      case 'error':
        voiceStatus.classList.remove('hidden');
        voiceError.classList.remove('hidden');
        voiceError.textContent = errorMessage || 'Voice input error';
        break;

      case 'idle':
      default:
        isProcessing = false;
        break;
    }
  }

  // ===== TEXT-TO-SPEECH FUNCTIONALITY =====

  // Add voice output button to bot messages
  function addVoiceOutputToMessage(messageElement, text) {
    // Remove HTML tags for speech
    const cleanText = text.replace(/<[^>]*>/g, '').replace(/&[^;]+;/g, '');

    // Create voice button
    const voiceBtn = document.createElement('button');
    voiceBtn.className = 'ml-2 p-1 text-gray-400 hover:text-blue-500 transition-colors rounded';
    voiceBtn.title = 'Listen to response';
    voiceBtn.innerHTML = `
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
      </svg>
    `;

    voiceBtn.addEventListener('click', () => speakText(cleanText));

    // Add button to message
    const messageContent = messageElement.querySelector('.prose, p');
    if (messageContent) {
      messageContent.appendChild(voiceBtn);
    }
  }

  // Speak text using Web Speech API
  function speakText(text) {
    // Stop any current speech
    if (speechSynthesis.speaking) {
      speechSynthesis.cancel();
    }

    // Create utterance
    const utterance = new SpeechSynthesisUtterance(text);

    // Configure voice settings
    utterance.rate = 0.9; // Slightly slower for clarity
    utterance.pitch = 1;
    utterance.volume = 0.8;

    // Try to use a female voice if available
    const voices = speechSynthesis.getVoices();
    const preferredVoice = voices.find(voice =>
      voice.name.toLowerCase().includes('female') ||
      voice.name.toLowerCase().includes('samantha') ||
      voice.name.toLowerCase().includes('alex')
    );

    if (preferredVoice) {
      utterance.voice = preferredVoice;
    }

    // Event handlers
    utterance.onstart = function() {
      console.log('Speech started');
    };

    utterance.onend = function() {
      console.log('Speech ended');
      currentUtterance = null;
    };

    utterance.onerror = function(event) {
      console.error('Speech error:', event.error);
      currentUtterance = null;
    };

    // Store current utterance
    currentUtterance = utterance;

    // Speak the text
    speechSynthesis.speak(utterance);
  }

  // Stop current speech
  function stopSpeech() {
    if (speechSynthesis.speaking) {
      speechSynthesis.cancel();
    }
    currentUtterance = null;
  }

  // Modify addMessage to include voice output for bot messages
  const originalAddMessage = window.addMessage || addMessage;
  function enhancedAddMessage(content, type) {
    originalAddMessage(content, type);

    // Add voice output to bot messages
    if (type === 'bot') {
      setTimeout(() => {
        const messages = messagesContainer.querySelectorAll('.flex:not(.justify-end)');
        const lastBotMessage = messages[messages.length - 1];
        if (lastBotMessage) {
          addVoiceOutputToMessage(lastBotMessage, content);
        }
      }, 100);
    }
  }

  // Override the addMessage function
  window.addMessage = enhancedAddMessage;

  // Initialize voice chat when modal opens
  aiBtn.addEventListener('click', function() {
    setTimeout(() => {
      if (!recognition) {
        initVoiceChat();
      }
    }, 500);
  });

  // Stop speech when modal closes
  closeBtn.addEventListener('click', stopSpeech);
  aiBackdrop.addEventListener('click', stopSpeech);

  // Initialize voice chat on page load
  initVoiceChat();

  // ===== CHAT PERSISTENCE FUNCTIONALITY =====

  // Chat history management
  let chatHistory = [];
  const CHAT_STORAGE_KEY = 'atierex_chat_history';
  const MAX_HISTORY_LENGTH = 50; // Keep last 50 messages

  // Load chat history from localStorage
  function loadChatHistory() {
    try {
      const saved = localStorage.getItem(CHAT_STORAGE_KEY);
      if (saved) {
        chatHistory = JSON.parse(saved);
        // Display saved messages
        chatHistory.forEach(message => {
          addMessageToHistory(message.content, message.type, false);
        });
      } else {
        // Show welcome message for new users
        showWelcomeMessage();
      }
    } catch (error) {
      console.error('Error loading chat history:', error);
      showWelcomeMessage();
    }
  }

  // Save chat history to localStorage
  function saveChatHistory() {
    try {
      // Keep only the most recent messages
      if (chatHistory.length > MAX_HISTORY_LENGTH) {
        chatHistory = chatHistory.slice(-MAX_HISTORY_LENGTH);
      }
      localStorage.setItem(CHAT_STORAGE_KEY, JSON.stringify(chatHistory));
    } catch (error) {
      console.error('Error saving chat history:', error);
    }
  }

  // Add message to DOM without saving (for loading history)
  function addMessageToHistory(content, type, shouldSave = true) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `flex gap-3 ${type === 'user' ? 'justify-end' : ''}`;

    if (type === 'user') {
      messageDiv.innerHTML = `
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-2xl rounded-tr-md px-4 py-3 max-w-[80%]">
          <p>${content}</p>
        </div>
        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
          <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
      `;
    } else {
      messageDiv.innerHTML = `
        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden">
          <img src="atierexlogo.png" alt="ATIEREx AI" class="w-full h-full object-cover">
        </div>
        <div class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-2xl rounded-tl-md px-4 py-3 max-w-[80%]">
          <div class="prose prose-sm dark:prose-invert max-w-none">${content}</div>
        </div>
      `;
    }

    messagesContainer.appendChild(messageDiv);

    // Save to history if it's a new message
    if (shouldSave) {
      chatHistory.push({
        content: content,
        type: type,
        timestamp: new Date().toISOString()
      });
      saveChatHistory();
    }
  }

  // Show welcome message for new users
  function showWelcomeMessage() {
    const welcomeMessage = `
      🤖 <strong>ATIEREx Assistant</strong><br>
      Hello! I'm your centralized AI financial companion. I can help you with:
      <ul class="mt-2 ml-4 list-disc">
        <li>Financial reports and analysis</li>
        <li>System navigation and features</li>
        <li>Accounting questions</li>
        <li>Hotel & restaurant operations</li>
      </ul>
      <p class="mt-2">What would you like to know about your financial system?</p>
    `;
    addMessageToHistory(welcomeMessage, 'bot', false);
  }

  // Enhanced addMessage function with persistence
  function addMessage(content, type) {
    // Add to DOM and save to history
    addMessageToHistory(content, type, true);

    // Scroll to bottom
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    // Hide quick actions after first interaction
    if (quickActions.style.display !== 'none') {
      quickActions.style.display = 'none';
    }

    // Add voice output to bot messages
    if (type === 'bot') {
      setTimeout(() => {
        const messages = messagesContainer.querySelectorAll('.flex:not(.justify-end)');
        const lastBotMessage = messages[messages.length - 1];
        if (lastBotMessage) {
          addVoiceOutputToMessage(lastBotMessage, content);
        }
      }, 100);
    }
  }

  // Clear chat history
  function clearChatHistory() {
    if (confirm('Are you sure you want to clear all chat history? This action cannot be undone.')) {
      chatHistory = [];
      localStorage.removeItem(CHAT_STORAGE_KEY);

      // Clear messages container and show welcome message
      messagesContainer.innerHTML = '';
      showWelcomeMessage();

      // Show quick actions again
      quickActions.style.display = 'block';
    }
  }

  // Add clear history button to header
  function addClearHistoryButton() {
    const header = document.querySelector('#aiAssistantPanel .flex.items-center.justify-between');
    if (header) {
      const clearBtn = document.createElement('button');
      clearBtn.id = 'clearChatHistory';
      clearBtn.className = 'p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors';
      clearBtn.title = 'Clear chat history';
      clearBtn.innerHTML = `
        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
      `;
      clearBtn.addEventListener('click', clearChatHistory);

      // Insert before the close button
      header.insertBefore(clearBtn, closeBtn);
    }
  }

  // Initialize chat persistence when modal opens
  aiBtn.addEventListener('click', function() {
    // Load chat history if not already loaded
    if (chatHistory.length === 0) {
      loadChatHistory();
    }

    // Add clear history button if not already added
    if (!document.getElementById('clearChatHistory')) {
      setTimeout(addClearHistoryButton, 100);
    }
  });

  // Update typing indicator text
  function updateTypingIndicator() {
    const typingText = document.querySelector('#aiTypingIndicator .text-sm');
    if (typingText) {
      typingText.textContent = 'ATIEREx is thinking...';
    }
  }

  // Update typing indicator when showing
  const originalShowTypingIndicator = showTypingIndicator;
  showTypingIndicator = function() {
    originalShowTypingIndicator();
    updateTypingIndicator();
  };
});
</script>

<style>
/* AI Assistant specific styles */
#aiAssistantBtn {
  box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4);
}

#aiAssistantBtn:hover {
  box-shadow: 0 20px 40px -10px rgba(59, 130, 246, 0.6);
}

#aiAssistantPanel {
  max-height: 80vh;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  #aiAssistantBtn {
    bottom: 1rem;
    right: 1rem;
    width: 3rem;
    height: 3rem;
  }

  #aiAssistantBtn svg {
    width: 1.25rem;
    height: 1.25rem;
  }

  #aiAssistantPanel {
    margin: 1rem;
    max-height: calc(100vh - 2rem);
  }
}

/* Dark mode support */
html.dark #aiAssistantBtn {
  background: linear-gradient(135deg, #1e40af, #7c3aed);
}

html.dark #aiAssistantBtn:hover {
  background: linear-gradient(135deg, #1d4ed8, #6d28d9);
}

/* Animation for modal */
#aiAssistantModal:not(.hidden) #aiAssistantPanel {
  animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(-20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

/* Prose styling for responses */
.prose p {
  margin-bottom: 0.5rem;
}

.prose ul {
  margin-left: 1rem;
  margin-bottom: 0.5rem;
}

.prose li {
  margin-bottom: 0.25rem;
}

.prose strong {
  font-weight: 600;
}
</style>
