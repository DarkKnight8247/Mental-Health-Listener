<?php
// Define the directory where conversation files will be stored
$convo_dir = __DIR__ . '/conversations';

// Create the directory if it doesn't exist, with full permissions
if (!is_dir($convo_dir)) {
    mkdir($convo_dir, 0777, true);
}

// Handle AJAX requests sent via POST with an 'ajax' parameter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json'); // Respond with JSON content
    $ajax_type = $_POST['ajax'];

    // AJAX type 1: Respond to user questions
    if ($ajax_type === '1') {
        $userQuestion = strtolower(trim($_POST['question'] ?? '')); // Normalize question
        $lang = $_POST['lang'] ?? 'en'; // Default language English

        // Define response patterns for English
        $responses_en = [
            ['keywords' => ['hello', 'hi'], 'response' => 'Hello! How can I help you?'],
            ['keywords' => ['help', 'support'], 'response' => 'I am here to listen. Tell me what\'s on your mind.'],
            ['keywords' => ['stress', 'anxiety'], 'response' => 'I\'m sorry to hear that you\'re feeling stressed. Want to talk about it?'],
            ['keywords' => ['thank'], 'response' => 'You\'re welcome! I\'m here whenever you need to chat.'],
            // Add more keyword-response pairs as needed
        ];
        // Define response patterns for Tagalog
        $responses_tl = [
            ['keywords' => ['hello', 'hi'], 'response' => 'Kumusta! Paano kita matutulungan?'],
            ['keywords' => ['help', 'support'], 'response' => 'Narito ako para makinig. Sabihin mo lang.'],
            ['keywords' => ['stress', 'anxiety'], 'response' => 'Pasensya na kung nakakaramdam ka ng stress. Gusto mo bang magkwento?'],
            ['keywords' => ['thank'], 'response' => 'Walang anuman! Nandito ako kung kailangan mo ng kausap.'],
            // Add more keyword-response pairs as needed
        ];
        // Define response patterns for Hiligaynon
        $responses_hil = [
            ['keywords' => ['hello', 'hi'], 'response' => 'Kamusta! Paano ko ikaw mabuligan?'],
            ['keywords' => ['help', 'support'], 'response' => 'Ari ako para pamati. Sadiin ko anay ang problema?'],
            ['keywords' => ['stress', 'anxiety'], 'response' => 'Pasensya, ginakabudlayan ka? Puwede mo ako istoryahon.'],
            ['keywords' => ['thank'], 'response' => 'Wala sapayan! Ari ako pirmi kung kinahanglan mo sang kaupod.'],
            // Add more keyword-response pairs as needed
        ];

        // Select response set based on language with default fallback
        switch ($lang) {
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

        // Search for matching keyword in user input, use first matched response
        foreach ($responses as $item) {
            foreach ($item['keywords'] as $keyword) {
                if (strpos($userQuestion, $keyword) !== false) {
                    $response = $item['response'];
                    break 2;
                }
            }
        }

        echo json_encode(['response' => $response]);
        exit;

    // AJAX type 2: Save conversation data passed as JSON
    } elseif ($ajax_type === '2') {
        $conversation_json = $_POST['conversation'] ?? '';
        if (!$conversation_json) {
            echo json_encode(['success' => false, 'error' => 'No conversation data received']);
            exit;
        }

        $conv_array = json_decode($conversation_json, true);
        if (!is_array($conv_array)) {
            echo json_encode(['success' => false, 'error' => 'Invalid conversation JSON']);
            exit;
        }

        // Extract first user question for filename
        $first_question = '';
        foreach ($conv_array as $line) {
            if ($line['sender'] === 'user') {
                $first_question = $line['text'];
                break;
            }
        }

        // Sanitize and shorten filename base
        $safe_question = substr(preg_replace('/[^a-zA-Z0-9-_ ]/', '', $first_question), 0, 30);
        $timestamp = date('mdHis');
        $filename = "conversation_{$timestamp}_{$safe_question}.txt";
        $filepath = $convo_dir . DIRECTORY_SEPARATOR . $filename;

        // Format conversation lines for saving
        $content_lines = [];
        foreach ($conv_array as $line) {
            $sender = ($line['sender'] === 'user') ? 'You' : 'Bot';
            $text = str_replace(["\r", "\n"], ' ', $line['text']);
            $content_lines[] = $sender . ': ' . $text;
        }
        $content_str = implode("\n", $content_lines);

        // Write to file and respond accordingly
        if (file_put_contents($filepath, $content_str) !== false) {
            echo json_encode(['success' => true, 'filename' => $filename]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to write file']);
        }
        exit;

    // AJAX type 3: List saved conversation files for chat history
    } elseif ($ajax_type === '3') {
        $files = scandir($convo_dir);
        $convos = [];
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            if (pathinfo($file, PATHINFO_EXTENSION) === 'txt') {
                $convos[] = $file;
            }
        }
        rsort($convos, SORT_STRING);
        echo json_encode(['success' => true, 'files' => $convos]);
        exit;

    // AJAX type 4: Load specific conversation from file
    } elseif ($ajax_type === '4') {
        $file = $_POST['filename'] ?? '';
        $filepath = $convo_dir . DIRECTORY_SEPARATOR . $file;
        if (!file_exists($filepath)) {
            echo json_encode(['success' => false, 'error' => 'File not found']);
            exit;
        }

        $content = file_get_contents($filepath);
        $lines = explode("\n", $content);
        $conversation = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) continue;
            if (strpos($line, 'You: ') === 0) {
                $conversation[] = ['sender' => 'user', 'text' => substr($line, 5)];
            } elseif (strpos($line, 'Bot: ') === 0) {
                $conversation[] = ['sender' => 'bot', 'text' => substr($line, 5)];
            }
        }
        echo json_encode(['success' => true, 'conversation' => $conversation]);
        exit;
    }

    // Unknown AJAX type response
    echo json_encode(['success' => false, 'error' => 'Unknown ajax type']);
    exit;
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
            // Cache frequently used DOM elements
            const chat = document.getElementById('chat');
            const chatForm = document.getElementById('chatForm');
            const questionInput = document.getElementById('questionInput');
            const langSelect = document.getElementById('langSelect');
            const saveButton = document.getElementById('saveButton');
            const historyItems = document.getElementById('historyItems');

            let conversationHistory = [];
            let firstQuestion = '';

            /**
             * Add a message to chat display with typing effect for bot
             * @param {string} text Message text
             * @param {string} sender 'user' or 'bot'
             */
            const appendMessage = (text, sender) => {
                if (!text) return;
                const msgEl = document.createElement('div');
                msgEl.className = `message ${sender}`;
                chat.appendChild(msgEl);
                chat.scrollTop = chat.scrollHeight;
                if (sender === 'bot') {
                    msgEl.textContent = '';
                    [...text].forEach((char, i) =>
                        setTimeout(() => {
                            msgEl.textContent += char;
                            chat.scrollTop = chat.scrollHeight;
                        }, 20 * i)
                    );
                } else {
                    msgEl.textContent = text;
                }
            };

            /**
             * Render entire conversation into chat display
             * @param {Array} convo Array of {text, sender}
             */
            const renderConversation = convo => {
                chat.innerHTML = '';
                convo.forEach(({ text, sender }) => {
                    const msgEl = document.createElement('div');
                    msgEl.className = `message ${sender}`;
                    msgEl.textContent = text;
                    chat.appendChild(msgEl);
                });
                chat.scrollTop = chat.scrollHeight;
            };

            /**
             * Request saved conversation file list and display them interactively
             */
            const updateHistoryPanel = async () => {
                try {
                    const res = await fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ ajax: '3' })
                    });
                    const data = await res.json();
                    if (!data.success) {
                        historyItems.innerHTML = '<div style="padding:12px;">Failed to load history.</div>';
                        return;
                    }
                    historyItems.innerHTML = '';
                    if (data.files.length === 0) {
                        historyItems.innerHTML = '<div style="padding:12px;">No saved conversations.</div>';
                        return;
                    }
                    data.files.forEach(filename => {
                        const item = document.createElement('div');
                        item.className = 'history-item';
                        item.tabIndex = 0;
                        item.setAttribute('role', 'button');
                        item.title = filename;
                        item.textContent = filename;
                        item.addEventListener('click', () => loadConversationFromFile(filename));
                        historyItems.appendChild(item);
                    });
                } catch {
                    historyItems.innerHTML = '<div style="padding:12px;">Error loading history.</div>';
                }
            };

            /**
             * Load conversation from file and render it
             * @param {string} filename File name to load
             */
            const loadConversationFromFile = async filename => {
                try {
                    const res = await fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ ajax: '4', filename }).toString()
                    });
                    const data = await res.json();
                    if (!data.success) throw new Error(data.error || 'Unknown error');
                    if (!Array.isArray(data.conversation) || data.conversation.length === 0) throw new Error('Conversation is empty.');
                    conversationHistory = data.conversation;
                    renderConversation(conversationHistory);
                } catch (err) {
                    alert(err.message);
                }
            };

            /**
             * Save the current conversation to server
             */
            const saveConversation = async () => {
                if (conversationHistory.length === 0) {
                    alert('No conversation to save.');
                    return;
                }
                try {
                    const res = await fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            ajax: '2',
                            conversation: JSON.stringify(conversationHistory)
                        }).toString()
                    });
                    const data = await res.json();
                    if (!data.success) throw new Error(data.error || 'Failed to save conversation.');
                    await updateHistoryPanel();
                    conversationHistory = [];
                    firstQuestion = '';
                    chat.innerHTML = '';
                } catch (err) {
                    alert(err.message);
                }
            };

            /**
             * Send user question to server and handle response
             * @param {string} question User question
             * @param {string} lang Language code
             */
            const sendQuestion = async (question, lang) => {
                appendMessage(question, 'user');
                if (!firstQuestion) firstQuestion = question;
                conversationHistory.push({ text: question, sender: 'user' });

                try {
                    const res = await fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ ajax: '1', question, lang }).toString()
                    });
                    const data = await res.json();
                    const botResponse = data.response || "Sorry, I didn't understand that.";
                    appendMessage(botResponse, 'bot');
                    conversationHistory.push({ text: botResponse, sender: 'bot' });
                    await updateHistoryPanel();
                } catch {
                    const errorMsg = "Error: Unable to reach server.";
                    appendMessage(errorMsg, 'bot');
                    conversationHistory.push({ text: errorMsg, sender: 'bot' });
                    await updateHistoryPanel();
                }
            };

            // Event: User submits question form
            chatForm.addEventListener('submit', e => {
                e.preventDefault();
                const question = questionInput.value.trim();
                if (!question) return;
                questionInput.value = '';
                questionInput.focus();
                sendQuestion(question, langSelect.value);
            });

            // Event: User clicks save conversation button
            saveButton.addEventListener('click', saveConversation);

            // Initialize on page load
            questionInput.focus();
            updateHistoryPanel();
        })();
    </script>
</body>
</html>

