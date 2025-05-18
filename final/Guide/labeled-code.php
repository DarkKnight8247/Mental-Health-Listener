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
            const chat = document.getElementById('chat'); // Get the chat display area
            const chatForm = document.getElementById('chatForm'); // Get the chat form
            const questionInput = document.getElementById('questionInput'); // Get the input field for questions
            const langSelect = document.getElementById('langSelect'); // Get the language selection dropdown
            const saveButton = document.getElementById('saveButton'); // Get the save button
            const historyItems = document.getElementById('historyItems'); // Get the history items display area

            let conversationHistory = []; // Initialize an array to store the conversation history
            let firstQuestion = ''; // Variable to store the first question asked by the user

            // Function to append a message to the chat display
            function appendMessage(text, sender) {
                if (!text) return; // Exit if there's no text to display
                const msgEl = document.createElement('div'); // Create a new message element
                msgEl.className = 'message ' + sender; // Set the class based on the sender (user or bot)
                chat.appendChild(msgEl); // Add the message element to the chat display
                chat.scrollTop = chat.scrollHeight; // Scroll to the bottom of the chat

                // If the sender is the bot, simulate typing effect
                if (sender === 'bot') {
                    msgEl.textContent = ''; // Clear the message text
                    let i = 0; // Initialize a counter for typing effect
                    const typingInterval = setInterval(() => {
                        if (i < text.length) {
                            msgEl.textContent += text.charAt(i); // Add one character at a time
                            i++;
                            chat.scrollTop = chat.scrollHeight; // Keep scrolling to the bottom
                        } else {
                            clearInterval(typingInterval); // Stop the typing effect
                        }
                    }, 20); // Typing speed
                } else {
                    msgEl.textContent = text; // Directly set the text for user messages
                }
            }

            // Function to render the entire conversation
            function renderConversation(conversation) {
                chat.innerHTML = ''; // Clear the chat display
                conversation.forEach(({ text, sender }) => {
                    const msgEl = document.createElement('div'); // Create a new message element
                    msgEl.className = 'message ' + sender; // Set the class based on the sender
                    msgEl.textContent = text; // Set the message text
                    chat.appendChild(msgEl); // Add the message to the chat display
                });
                chat.scrollTop = chat.scrollHeight; // Scroll to the bottom of the chat
            }

            // Function to update the conversation history panel
            async function updateHistoryPanel() {
                try {
                    const res = await fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ ajax: '3' }).toString() // Request conversation history
                    });
                    if (!res.ok) throw new Error('Network error'); // Check for network errors
                    const data = await res.json(); // Parse the JSON response

                    if (!data.success) {
                        historyItems.innerHTML = '<div style="padding:12px;">Failed to load history.</div>';
                        return; // Exit if loading history fails
                    }

                    if (data.files.length === 0) {
                        historyItems.innerHTML = '<div style="padding:12px;">No saved conversations.</div>';
                        return; // Exit if there are no saved conversations
                    }

                    historyItems.innerHTML = ''; // Clear the history items display
                    data.files.forEach(filename => {
                        const item = document.createElement('div'); // Create a new history item element
                        item.className = 'history-item'; // Set the class for styling
                        item.textContent = filename; // Set the filename as the text
                        item.title = filename; // Set the title for tooltip
                        item.tabIndex = 0; // Make it focusable
                        item.setAttribute('role', 'button'); // Set role for accessibility

                        // Add click event to load the selected conversation
                        item.addEventListener('click', () => loadConversationFromFile(filename));

                        historyItems.appendChild(item); // Add the history item to the display
                    });
                } catch (err) {
                    historyItems.innerHTML = '<div style="padding:12px;">Error loading history.</div>'; // Display error message
                }
            }

            // Function to load a conversation from a file
            async function loadConversationFromFile(filename) {
                try {
                    const res = await fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ ajax: '4', filename }).toString() // Request specific conversation
                    });
                    if (!res.ok) throw new Error('Network error'); // Check for network errors
                    const data = await res.json(); // Parse the JSON response
                    if (!data.success) {
                        alert('Failed to load conversation: ' + (data.error || 'Unknown error'));
                        return; // Exit if loading conversation fails
                    }
                    if (!Array.isArray(data.conversation) || data.conversation.length === 0) {
                        alert('Conversation file is empty.'); // Alert if the conversation is empty
                        return; // Exit if the conversation is empty
                    }

                    conversationHistory = data.conversation; // Store the loaded conversation
                    renderConversation(conversationHistory); // Render the loaded conversation
                } catch (err) {
                    alert('Error loading conversation.'); // Alert on error
                }
            }

            // Function to save the current conversation
            async function saveConversation() {
                if (conversationHistory.length === 0) {
                    alert('No conversation to save.'); // Alert if there's nothing to save
                    return; // Exit if there's no conversation
                }
                const filename = firstQuestion; // Use the first question as the filename
                try {
                    const res = await fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            ajax: '2',
                            conversation: JSON.stringify(conversationHistory), // Send the conversation as JSON
                            filename: filename
                        }).toString()
                    });
                    if (!res.ok) throw new Error('Network error'); // Check for network errors
                    const data = await res.json(); // Parse the JSON response
                    if (data.success) {
                        updateHistoryPanel(); // Update the history panel after saving
                        conversationHistory = []; // Clear the conversation history
                        firstQuestion = ''; // Reset the first question
                        chat.innerHTML = ''; // Clear the chat display
                    } else {
                        alert('Failed to save conversation: ' + (data.error || 'Unknown error')); // Alert on failure
                    }
                } catch (err) {
                    alert('Error saving conversation.'); // Alert on error
                }
            }

            // Function to send a question to the server
            async function sendQuestion(question, lang) {
                appendMessage(question, 'user'); // Display the user's question
                if (!firstQuestion) firstQuestion = question; // Store the first question
                conversationHistory.push({ text: question, sender: 'user' }); // Add to conversation history

                try {
                    const res = await fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            ajax: '1',
                            question: question, // Send the user's question
                            lang: lang // Send the selected language
                        }).toString()
                    });
                    if (!res.ok) throw new Error('Network error'); // Check for network errors
                    const data = await res.json(); // Parse the JSON response
                    const botResponse = data.response || "Sorry, I didn't understand that."; // Get the bot's response

                    appendMessage(botResponse, 'bot'); // Display the bot's response
                    conversationHistory.push({ text: botResponse, sender: 'bot' }); // Add to conversation history
                    updateHistoryPanel(); // Update the history panel
                } catch (err) {
                    appendMessage("Error: Unable to reach server.", 'bot'); // Display error message
                    conversationHistory.push({ text: "Error: Unable to reach server.", sender: 'bot' }); // Add to conversation history
                    updateHistoryPanel(); // Update the history panel
                }
            }

            // Event listener for the chat form submission
            chatForm.addEventListener('submit', async e => {
                e.preventDefault(); // Prevent default form submission
                const question = questionInput.value.trim(); // Get the trimmed question
                if (!question) return; // Exit if the question is empty
                questionInput.value = ''; // Clear the input field
                questionInput.focus(); // Focus back on the input field
                await sendQuestion(question, langSelect.value); // Send the question to the server
            });

            // Event listener for the save button
            saveButton.addEventListener('click', saveConversation); // Save the conversation when clicked

            questionInput.focus(); // Focus on the input field when the page loads
            updateHistoryPanel(); // Load the conversation history on page load
        })();
    </script>
</body>
</html>
