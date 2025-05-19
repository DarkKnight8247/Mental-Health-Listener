<?php
// Define the directory for storing conversation files
$convo_dir = __DIR__ . '/conversations';

// Check if the conversations directory exists; if not, create it
if (!is_dir($convo_dir)) {
    mkdir($convo_dir, 0777, true); // Create directory with full permissions
}

// Check if the request method is POST and if the 'ajax' parameter is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json'); // Set response type to JSON
    $ajax_type = $_POST['ajax']; // Get the type of AJAX request

    // Handle AJAX type 1: Respond to user questions
    if ($ajax_type === '1') {
        // Get the user's question and language preference
        $userQuestion = strtolower(trim($_POST['question'] ?? '')); // Normalize the question
        $lang = $_POST['lang'] ?? 'en'; // Default to English if no language is specified

        // Define responses for English, Tagalog, and Hiligaynon
        $responses_en = [
            // ... (responses for English)
        ];
        $responses_tl = [
            // ... (responses for Tagalog)
        ];
        $responses_hil = [
            // ... (responses for Hiligaynon)
        ];

        // Select the appropriate responses based on the language
        switch($lang) {
            case 'tl':
                $responses = $responses_tl; 
                $response = "Pa umanhin, hindi kita na intindihan, pede mo bang ulitin?"; 
                break;
            case 'hil':
                $responses = $responses_hil; 
                $response = "Pansenya, di taka ma inchindihan, pede mo suliton?"; 
                break;
            default:
                $responses = $responses_en; 
                $response = "Sorry, I don't understand that. Could you please rephrase?"; 
                break;
        }

        // Match the user's question with predefined keywords to find a response
        foreach ($responses as $item) {
            foreach ($item['keywords'] as $keyword) {
                if (strpos($userQuestion, $keyword) !== false) {
                    $response = $item['response']; // Set the response if a keyword is found
                    break; // Exit the inner loop
                }
            }
        }

        // Return the response as a JSON object
        echo json_encode(['response' => $response]);
        exit; // Stop further execution

    // Handle AJAX type 2: Save conversation data
    } elseif ($ajax_type === '2') {
        $conversation_json = $_POST['conversation'] ?? ''; // Get conversation data
        if (!$conversation_json) {
            echo json_encode(['success' => false, 'error' => 'No conversation data received']);
            exit; // Exit if no data is received
        }

        // Decode the JSON conversation data into an array
        $conv_array = json_decode($conversation_json, true);
        if (!is_array($conv_array)) {
            echo json_encode(['success' => false, 'error' => 'Invalid conversation JSON']);
            exit; // Exit if the data is not valid
        }

        // Extract the first question from the conversation
        $first_question = '';
        foreach ($conv_array as $line) {
            if ($line['sender'] === 'user') {
                $first_question = $line['text']; // Get the user's first question
                break; // Exit the loop after finding the first question
            }
        }

        // Create a unique filename for the conversation
        $timestamp = date('mdhis'); // Get the current timestamp
        $filename = "conversation_{$timestamp}_{$first_question}.txt"; // Construct the filename
        $filepath = $convo_dir . DIRECTORY_SEPARATOR . $filename; // Full path for the file

        // Prepare the content for the file
        $content_lines = [];
        foreach($conv_array as $line) {
            $sender = ($line['sender'] === 'user') ? 'You' : 'Bot'; // Determine sender
            $text = str_replace(["\r","\n"], ' ', $line['text']); // Normalize text
            $content_lines[] = $sender . ': ' . $text; // Format the line
        }
        $content_str = implode("\n", $content_lines); // Join lines into a single string

        // Attempt to write the content to the file
        if (file_put_contents($filepath, $content_str) !== false) {
            echo json_encode(['success' => true, 'filename' => $filename]); // Success response
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to write file']); // Error response
        }
        exit; // Stop further execution

    // Handle AJAX type 3: Load conversation history
    } elseif ($ajax_type === '3') {
        $files = scandir($convo_dir); // Scan the conversations directory
        $convos = []; // Initialize an array for conversation files
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue; // Skip current and parent directory
            if (pathinfo($file, PATHINFO_EXTENSION) === 'txt') {
                $convos[] = $file; // Add text files to the array
            }
        }
        rsort($convos, SORT_STRING); // Sort files in reverse order
        echo json_encode(['success' => true, 'files' => $convos]); // Return the list of files
        exit; // Stop further execution

    // Handle AJAX type 4: Load a specific conversation
    } elseif ($ajax_type === '4') {
        $file = $_POST['filename'] ?? ''; // Get the filename from POST data
        $filepath = $convo_dir . DIRECTORY_SEPARATOR . $file; // Full path for the file
        if (!file_exists($filepath)) {
            echo json_encode(['success' => false, 'error' => 'File not found']); // Error if file doesn't exist
            exit; // Stop further execution
        }

        // Read the content of the specified file
        $content = file_get_contents($filepath);
        $lines = explode("\n", $content); // Split content into lines
        $conversation = []; // Initialize an array for the conversation
        foreach($lines as $line) {
            $line = trim($line); // Trim whitespace
            if (!$line) continue; // Skip empty lines
            if (strpos($line, 'You: ') === 0) {
                $conversation[] = ['sender' => 'user', 'text' => substr($line, 5)]; // Add user message
            } elseif (strpos($line, 'Bot: ') === 0) {
                $conversation[] = ['sender' => 'bot', 'text' => substr($line, 5)]; // Add bot message
            }
        }
        echo json_encode(['success' => true, 'conversation' => $conversation]); // Return the conversation
        exit; // Stop further execution
    }

    // Default response for unknown AJAX type
    echo json_encode(['success' => false, 'error' => 'Unknown ajax type']);
    exit; // Stop further execution
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mental Health Listener</title>
    <link rel="icon" href="graphics/logo.png" style="filter: invert(100%);">
</head>
<body>
    <div class="history-panel" id="historyPanel" aria-label="Conversation history">
        <div class="history-header">Chat History</div>
        <div class="history-items" id="historyItems"></div>
    </div>

    <div class="chat-container" role="main">
        <div class="chat-main" aria-live="polite" aria-atomic="false">
            <nav aria-label="Chat navigation">
                <h2 id="chatTitle"><img src="graphics/logo.png" class="logo">Mental Health Listener</h2>
                <select id="langSelect" aria-label="Select language">
                    <option value="en" selected>English</option>
                    <option value="tl">Tagalog</option>
                    <option value="hil">Hiligaynon</option>
                </select>
                <button id="saveButton" aria-label="Save conversation and start new chat" title="Save & New Chat">+ New Chat</button>
            </nav>

            <div class="messages" id="chat" role="log" aria-live="polite" aria-relevant="additions"></div>

            <form id="chatForm" aria-label="Send new message">
                <input type="text" id="questionInput" name="question" placeholder="Type your question..." aria-required="true" autocomplete="off" />
                <button type="submit">Send</button>
            </form>
        </div>
    </div>
    <script>
        (() => {
        // Cache frequently accessed DOM elements for efficiency and clarity
        const chat = document.getElementById('chat');               // The container element where chat messages are displayed
        const chatForm = document.getElementById('chatForm');       // The form element that wraps the user input and submit button
        const questionInput = document.getElementById('questionInput'); // The text input where the user types their question
        const langSelect = document.getElementById('langSelect');   // Dropdown element allowing the user to select a language
        const saveButton = document.getElementById('saveButton');   // Button element the user presses to save the conversation
        const historyItems = document.getElementById('historyItems'); // Container that lists previously saved conversations
    
        // Variables for tracking conversation state:
        let conversationHistory = []; // Array storing the messages exchanged: each with 'text' and 'sender' keys
        let firstQuestion = '';       // Stores the very first user question, used for naming saved conversations
    
        /**
         * Append a message to the chat display.
         * - User messages display instantly.
         * - Bot messages are displayed character-by-character as a typing animation for better UX.
         *
         * @param {string} text - The message text to display in chat.
         * @param {string} sender - Who sent the message; expected values: 'user' or 'bot'.
         */
        const appendMessage = (text, sender) => {
            // Do nothing if 'text' is blank or undefined (defensive coding)
            if (!text) return;
    
            // Create a new div element for the message bubble
            const msgEl = document.createElement('div');
    
            // Assign CSS classes based on sender to style differently
            msgEl.className = `message ${sender}`;
    
            // Add this message bubble to the chat container
            chat.appendChild(msgEl);
    
            // Scroll chat container to bottom so newest message is visible
            chat.scrollTop = chat.scrollHeight;
    
            // Display message text:
            if (sender === 'bot') {
                // For bot messages, show introspective, engaging typing animation one character at a time
                msgEl.textContent = ''; // Clear any existing text
                [...text].forEach((char, i) => setTimeout(() => {
                    // Append one character every 20 milliseconds until full message is displayed
                    msgEl.textContent += char;
                    // Keep scrolling to bottom as message length grows
                    chat.scrollTop = chat.scrollHeight;
                }, 20 * i));
            } else {
                // For user messages, display text immediately (no animation)
                msgEl.textContent = text;
            }
        };
    
        /**
         * Render a full conversation of messages into the chat display.
         * Useful for displaying a previously saved conversation loaded from storage.
         *
         * @param {Array} convo - Array of message objects, each having properties {text: string, sender: string}.
         */
        const renderConversation = convo => {
            // Clear existing chat messages from display to start fresh
            chat.innerHTML = '';
    
            // Sequentially add each message to chat display with appropriate styling
            convo.forEach(({ text, sender }) => {
                const msgEl = document.createElement('div');
                msgEl.className = `message ${sender}`;
                msgEl.textContent = text;
                chat.appendChild(msgEl);
            });
    
            // Scroll to bottom so last message is visible
            chat.scrollTop = chat.scrollHeight;
        };
    
        /**
         * Fetch the list of saved conversation filenames from server,
         * then display them as clickable entries in the chat history panel.
         *
         * Clicking an entry triggers loading that conversation.
         */
        const updateHistoryPanel = async () => {
            try {
                // Send POST request to this page/API asking for conversation list (ajax=3)
                const res = await fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ ajax: '3' })
                });
    
                // Parse JSON response from server
                const data = await res.json();
    
                // If server indicates failure, show error message in history panel
                if (!data.success) {
                    historyItems.innerHTML = '<div style="padding:12px;">Failed to load history.</div>';
                    return;
                }
    
                // Clear existing history entries before populating
                historyItems.innerHTML = '';
    
                // If list is empty, inform the user no conversations are saved yet
                if (data.files.length === 0) {
                    historyItems.innerHTML = '<div style="padding:12px;">No saved conversations.</div>';
                    return;
                }
    
                // Iterate over each filename and create a clickable history item for it
                data.files.forEach(filename => {
                    const item = document.createElement('div');
    
                    // Apply CSS class and accessibility attributes for a button
                    item.className = 'history-item';
                    item.tabIndex = 0;
                    item.setAttribute('role', 'button');
                    item.title = filename;     // Tooltip on hover with full filename
                    item.textContent = filename; // Visible filename text
    
                    // Add click event listener so clicking loads this conversation's history
                    item.addEventListener('click', () => loadConversationFromFile(filename));
    
                    // Append item to the history container
                    historyItems.appendChild(item);
                });
            } catch {
                // Generic error message if network or parsing error occurs
                historyItems.innerHTML = '<div style="padding:12px;">Error loading history.</div>';
            }
        };
    
        /**
         * Load the full content of a saved conversation file from the server,
         * parse it into messages, and render in chat display.
         *
         * @param {string} filename - The exact filename to request and load.
         */
        const loadConversationFromFile = async filename => {
            try {
                // Request conversation content (ajax=4)
                const res = await fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ ajax: '4', filename }).toString()
                });
    
                // Parse server JSON response
                const data = await res.json();
    
                // If failure from server: show alert to user with error message
                if (!data.success) throw new Error(data.error || 'Unknown error');
    
                // If conversation empty, alert user accordingly
                if (!Array.isArray(data.conversation) || data.conversation.length === 0)
                    throw new Error('Conversation is empty.');
    
                // Store loaded conversation in memory
                conversationHistory = data.conversation;
    
                // Render loaded conversation to chat display
                renderConversation(conversationHistory);
            } catch (err) {
                // Alert user upon any error during loading process
                alert(err.message);
            }
        };
    
        /**
         * Save the current conversation history to the server.
         * Upon success clears the chat and updates history panel.
         */
        const saveConversation = async () => {
            // Prevent save if no messages present
            if (conversationHistory.length === 0) {
                alert('No conversation to save.');
                return;
            }
    
            try {
                // Send POST request to save conversation (ajax=2)
                const res = await fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        ajax: '2',
                        conversation: JSON.stringify(conversationHistory)
                    }).toString()
                });
    
                // Parse server response
                const data = await res.json();
    
                // Show error alert if save fails
                if (!data.success) throw new Error(data.error || 'Failed to save conversation.');
    
                // Update the conversation history panel with the new saved file listed
                await updateHistoryPanel();
    
                // Reset conversation state and clear chat display for new conversation
                conversationHistory = [];
                firstQuestion = '';
                chat.innerHTML = '';
            } catch (err) {
                // Alert user on unexpected errors during save
                alert(err.message);
            }
        };
    
        /**
         * Sends user's question to the server and handles the chatbot response.
         * Both user question and chatbot answer are stored and displayed.
         *
         * @param {string} question - Text of the user's question
         * @param {string} lang - Language code selected by user
         */
        const sendQuestion = async (question, lang) => {
            // Append user message instantly to chat and store in history
            appendMessage(question, 'user');
            if (!firstQuestion) firstQuestion = question; // Remember first user question for saving filename
            conversationHistory.push({ text: question, sender: 'user' });
    
            try {
                // Send AJAX POST to server with question and language (ajax=1)
                const res = await fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ ajax: '1', question, lang }).toString()
                });
    
                // Parse JSON response
                const data = await res.json();
    
                // Use server response or fallback message
                const botResponse = data.response || "Sorry, I didn't understand that.";
    
                // Append bot's reply with typing animation and store in history
                appendMessage(botResponse, 'bot');
                conversationHistory.push({ text: botResponse, sender: 'bot' });
    
                // Refresh conversation history panel to reflect new interaction
                await updateHistoryPanel();
            } catch {
                // Gracefully handle network errors by informing user in chat
                const errorMsg = "Error: Unable to reach server.";
                appendMessage(errorMsg, 'bot');
                conversationHistory.push({ text: errorMsg, sender: 'bot' });
                await updateHistoryPanel();
            }
        };
    
        // Event listener: On form submission, send the user's question
        chatForm.addEventListener('submit', e => {
            e.preventDefault(); // Prevent form from causing page reload
            const question = questionInput.value.trim(); // Ignore empty inputs
            if (!question) return;
            questionInput.value = '';  // Clear input field for convenience
            questionInput.focus();     // Keep cursor in input field
            sendQuestion(question, langSelect.value); // Initiate sending question to server
        });
    
        // Event listener: When save button clicked, save current conversation
        saveButton.addEventListener('click', saveConversation);
    
        // When page loads, focus input and load saved conversation history
        questionInput.focus();
        updateHistoryPanel();
    })();
    </script>
</body>
</html>
