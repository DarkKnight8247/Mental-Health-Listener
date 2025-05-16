<?php
// Folder to store conversation files
$convo_dir = __DIR__ . '/conversations';

// Make sure folder exists
if (!is_dir($convo_dir)) {
    mkdir($convo_dir, 0777, true);
}

// Handle AJAX actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    $ajax_type = $_POST['ajax'];

    if ($ajax_type === '1') {
        // Chat response generation
        $userQuestion = strtolower(trim($_POST['question'] ?? ''));
        $lang = $_POST['lang'] ?? 'en';

        $responses_en = [
            ['keywords' => ['hi', 'hello', 'hey'], 'response' => "Hello buddy! I'm always here to chat. What's on your mind?"],
            ['keywords' => ['sad', 'depressed', 'unhappy'], 'response' => "I'm really sorry you're feeling sad. I'm here with you."],
            ['keywords' => ['happy', 'joy', 'glad'], 'response' => "That's wonderful to hear! Keep embracing that joy."],
            ['keywords' => ['help', 'support'], 'response' => "I'm here to help. Tell me what's bothering you."],
            ['keywords' => ['anxious', 'nervous', 'worried'], 'response' => "It's okay to feel that way. Would you like to talk more about it?"],
        ];
        $responses_tl = [
            ['keywords' => ['kumusta', 'hello', 'hi'], 'response' => "Kamusta! Nandito ako para makinig. Ano ang nais mong pag-usapan?"],
            ['keywords' => ['malungkot', 'lungkot'], 'response' => "Pasensya na sa iyong nararamdaman. Nandito ako para sa'yo."],
            ['keywords' => ['masaya', 'tuwa', 'alak'], 'response' => "Salamat sa pagbabahagi ng iyong kasiyahan!"],
            ['keywords' => ['tulong', 'sanggunian'], 'response' => "Handa akong tumulong. Ano ang problema mo?"],
            ['keywords' => ['balisa', 'kinabahan', 'nerbyos'], 'response' => "Normal lamang ang ganitong pakiramdam. Gusto mo bang pag-usapan pa ito?"],
        ];
        $responses_hil = [
            ['keywords' => ['kamusta', 'hello', 'hi'], 'response' => "Kamusta! Ari ako diri para pamati. Ano gusto mo istoryahan?"],
            ['keywords' => ['maluoy', 'masubo', 'kalain'], 'response' => "Pasensya gid sa pagbatyag mo subong. Ari ko para sa imo."],
            ['keywords' => ['lipay', 'kalipay', 'masadya'], 'response' => "Nalipay ako nga ginshare mo ang imo kalipay!"],
            ['keywords' => ['bulig', 'tabang'], 'response' => "Handa gid ako magbulig. Ano ang imo problema?"],
            ['keywords' => ['kabala', 'kulba', 'nerbyos'], 'response' => "Normal lang ang pagbati sini. Gusto mo pa istoryahan?"],
        ];

        // Select language responses
        switch($lang) {
            case 'tl': $responses = $responses_tl; break;
            case 'hil': $responses = $responses_hil; break;
            default: $responses = $responses_en; break;
        }

        $response = "Sorry, I don't understand that. Could you please rephrase?";

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
    } elseif ($ajax_type === '2') {
        // Save conversation
        // Receive conversation array JSON string
        $conversation_json = $_POST['conversation'] ?? '';
        if (!$conversation_json) {
            echo json_encode(['success' => false, 'error' => 'No conversation data received']);
            exit;
        }

        // Sanitize and generate unique filename by timestamp
        $timestamp = date('YmdHis');
        $filename = "conversation_{$timestamp}.txt";
        $filepath = $convo_dir . DIRECTORY_SEPARATOR . $filename;

        // Decode JSON conversation array client side sent as [{sender,text},...]
        $conv_array = json_decode($conversation_json, true);
        if (!is_array($conv_array)) {
            echo json_encode(['success' => false, 'error' => 'Invalid conversation JSON']);
            exit;
        }

        // Prepare text content for saving
        $content_lines = [];
        foreach($conv_array as $line) {
            $sender = ($line['sender'] === 'user') ? 'You' : 'Bot';
            // Replace newlines in text with spaces to prevent breaking line structure
            $text = str_replace(["\r","\n"], ' ', $line['text']);
            $content_lines[] = $sender . ': ' . $text;
        }
        $content_str = implode("\n", $content_lines);

        // Save to file
        if (file_put_contents($filepath, $content_str) !== false) {
            echo json_encode(['success' => true, 'filename' => $filename]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to write file']);
        }
        exit;
    } elseif ($ajax_type === '3') {
        // List existing conversation files
        $files = scandir($convo_dir);
        $convos = [];
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            if (pathinfo($file, PATHINFO_EXTENSION) === 'txt') {
                $convos[] = $file;
            }
        }
        // Sort descending by filename (timestamp)
        rsort($convos, SORT_STRING);
        echo json_encode(['success' => true, 'files' => $convos]);
        exit;
    } elseif ($ajax_type === '4') {
        // Load content of a conversation file
        $file = $_POST['filename'] ?? '';
        // For security only allow filenames that are alphanumeric + underscore + dot + txt extension
        if (!preg_match('/^conversation_\d{14}\.txt$/', $file)) {
            echo json_encode(['success' => false, 'error' => 'Invalid filename']);
            exit;
        }
        $filepath = $convo_dir . DIRECTORY_SEPARATOR . $file;
        if (!file_exists($filepath)) {
            echo json_encode(['success' => false, 'error' => 'File not found']);
            exit;
        }
        $content = file_get_contents($filepath);
        // Parse content lines back into conversation array [{sender,text}]
        $lines = explode("\n", $content);
        $conversation = [];
        foreach($lines as $line) {
            $line = trim($line);
            if (!$line) continue;
            // Expected format: "You: text" or "Bot: text"
            if (strpos($line, 'You: ') === 0) {
                $conversation[] = ['sender' => 'user', 'text' => substr($line, 5)];
            } elseif (strpos($line, 'Bot: ') === 0) {
                $conversation[] = ['sender' => 'bot', 'text' => substr($line, 5)];
            }
        }
        echo json_encode(['success' => true, 'conversation' => $conversation]);
        exit;
    }
    // Unknown ajax type
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
<style>
    * {
        box-sizing: border-box;
    }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #e8f0fe;
        margin: 0;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 15px;
        overflow: hidden;
    }
    .chat-container {
        background: #fff;
        width: 100%;
        max-width: 900px;
        height: 80vh;
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        border-radius: 12px;
        display: flex;
        position: relative;
        overflow: hidden;
        font-size: 16px;
        color: #222;
    }
    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    nav {
        background: #4a86e8;
        padding: 15px 25px;
        color: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }
    nav h2 {
        margin: 0;
        font-size: 1.25rem;
        flex-grow: 1;
        min-width: 160px;
    }
    nav select, nav button {
        background: white;
        color: #4a86e8;
        border: none;
        border-radius: 4px;
        padding: 6px 12px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.3s ease;
        flex-shrink: 0;
    }
    nav select:hover, nav button:hover {
        background: #d0dffd;
    }
    .messages {
        flex: 1;
        padding: 15px 20px;
        overflow-y: auto;
        background: #f0f5ff;
        display: flex;
        flex-direction: column;
        gap: 10px;
        scrollbar-width: thin;
        scrollbar-color: #4a86e8 #d0dffd;
        min-height: 0;
        min-width: 0;
    }
    .messages::-webkit-scrollbar {
        width: 8px;
    }
    .messages::-webkit-scrollbar-track {
        background: #d0dffd;
    }
    .messages::-webkit-scrollbar-thumb {
        background-color: #4a86e8;
        border-radius: 4px;
    }
    .message {
        max-width: 75%;
        padding: 12px 18px;
        border-radius: 25px;
        white-space: pre-wrap;
        word-wrap: break-word;
        line-height: 1.3;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        flex-shrink: 0;
    }
    .user {
        background-color: #cce0ff;
        color: #1a3c75;
        align-self: flex-end;
        border-bottom-right-radius: 4px;
    }
    .bot {
        background-color: #d5efd5;
        color: #1f4d1f;
        align-self: flex-start;
        border-bottom-left-radius: 4px;
    }
    form {
        display: flex;
        padding: 12px 20px;
        background: #e6ecff;
        gap: 10px;
        border-top: 1px solid #b3c0ff;
    }
    #questionInput {
        flex-grow: 1;
        padding: 10px 15px;
        font-size: 1rem;
        border-radius: 25px;
        border: 1px solid #a2acf7;
        outline-offset: 2px;
        transition: border-color 0.25s ease;
    }
    #questionInput:focus {
        border-color: #4a86e8;
    }
    form button[type="submit"] {
        background: #4a86e8;
        color: white;
        border: none;
        border-radius: 25px;
        padding: 10px 20px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    form button[type="submit"]:hover {
        background: #3a6fd4;
    }
    .history-panel {
        width: 300px;
        background: #fafafa;
        border-left: 1px solid #cfd8fd;
        display: flex;
        flex-direction: column;
        position: relative;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        min-width: 0;
        max-width: 300px;
        overflow: hidden;
        z-index: 10;
    }
    .history-panel.visible {
        transform: translateX(0);
    }
    .history-header {
        background: #4a86e8;
        color: white;
        font-weight: 700;
        padding: 15px 20px;
        text-align: center;
        letter-spacing: 0.05em;
        flex-shrink: 0;
    }
    .history-items {
        flex: 1;
        overflow-y: auto;
        min-height: 0;
    }
    .history-item {
        padding: 12px 20px;
        border-bottom: 1px solid #d6defb;
        cursor: pointer;
        font-size: 0.95rem;
        color: #2a3a8a;
        transition: background-color 0.2s ease;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        user-select: none;
    }
    .history-item:hover {
        background-color: #d0d7ff;
    }
    .history-item.active {
        background-color: #a3b1ff;
        color: #1e2a6a;
        font-weight: bold;
    }
</style>
</head>
<body>
<div class="chat-container" role="main">
    <div class="chat-main" aria-live="polite" aria-atomic="false">
        <nav aria-label="Chat navigation">
            <h2 id="chatTitle">Ask the AI Bot</h2>
            <select id="langSelect" aria-label="Select language">
                <option value="en" selected>English</option>
                <option value="tl">Tagalog</option>
                <option value="hil">Hiligaynon</option>
            </select>
            <button id="menuButton" aria-label="Toggle conversation history menu" title="Toggle History">&#9776;</button>
            <button id="saveButton" aria-label="Save conversation" title="Save Conversation">Save</button>
        </nav>

        <div class="messages" id="chat" role="log" aria-live="polite" aria-relevant="additions"></div>

        <form id="chatForm" aria-label="Send new message">
            <input type="text" id="questionInput" name="question" placeholder="Type your question..." aria-required="true" autocomplete="off" />
            <button type="submit">Send</button>
        </form>
    </div>

    <aside class="history-panel" id="historyPanel" aria-label="Conversation history" aria-hidden="true">
        <div class="history-header">Chat History</div>
        <div class="history-items" id="historyItems"></div>
    </aside>
</div>

<script>
(() => {
    const chat = document.getElementById('chat');
    const chatForm = document.getElementById('chatForm');
    const questionInput = document.getElementById('questionInput');
    const langSelect = document.getElementById('langSelect');
    const saveButton = document.getElementById('saveButton');
    const menuButton = document.getElementById('menuButton');
    const historyPanel = document.getElementById('historyPanel');
    const historyItems = document.getElementById('historyItems');

    // Full conversation array of {text, sender}
    let conversationHistory = [];
    // Selected index of loaded (past) conversation, null if current chat
    let selectedHistoryIndex = null;

    // Append a message to chat window
    function appendMessage(text, sender) {
        if (!text) return;
        const msgEl = document.createElement('div');
        msgEl.className = 'message ' + sender;
        msgEl.textContent = text;
        chat.appendChild(msgEl);
        chat.scrollTop = chat.scrollHeight;
    }

    // Render entire conversation in chat window
    function renderConversation(conversation) {
        chat.innerHTML = '';
        conversation.forEach(({text, sender}) => appendMessage(text, sender));
    }

    // Update conversation history panel with saved conversation filenames or current conversation snippets
    // Here we fetch the files on server instead of local conversationHistory for listing
    async function updateHistoryPanel() {
        // Fetch saved conversations list from server (ajax=3)
        try {
            const res = await fetch(window.location.href, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ajax:'3'}).toString()
            });
            if (!res.ok) throw new Error('Network error');
            const data = await res.json();

            if (!data.success) {
                historyItems.innerHTML = '<div style="padding:12px;">Failed to load history.</div>';
                return;
            }

            if (data.files.length === 0) {
                historyItems.innerHTML = '<div style="padding:12px;">No saved conversations.</div>';
                return;
            }

            historyItems.innerHTML = '';
            data.files.forEach(filename => {
                const item = document.createElement('div');
                item.className = 'history-item';
                item.textContent = filename;
                item.title = filename;
                item.tabIndex = 0;
                item.setAttribute('role', 'button');
                item.setAttribute('aria-pressed', 'false');

                item.addEventListener('click', () => loadConversationFromFile(filename, item));
                item.addEventListener('keydown', e => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        item.click();
                    }
                });

                historyItems.appendChild(item);
            });
        } catch (err) {
            historyItems.innerHTML = '<div style="padding:12px;">Error loading history.</div>';
        }
    }

    // Load conversation from saved file by AJAX (ajax=4)
    async function loadConversationFromFile(filename, clickedItem) {
        try {
            const res = await fetch(window.location.href, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ajax:'4', filename}).toString()
            });
            if (!res.ok) throw new Error('Network error');
            const data = await res.json();
            if (!data.success) {
                alert('Failed to load conversation: ' + (data.error || 'Unknown error'));
                return;
            }
            if (!Array.isArray(data.conversation) || data.conversation.length === 0) {
                alert('Conversation file is empty.');
                return;
            }

            // Set conversationHistory to loaded conversation
            conversationHistory = data.conversation;
            selectedHistoryIndex = null;

            renderConversation(conversationHistory);
            highlightSelectedFile(clickedItem);
        } catch (err) {
            alert('Error loading conversation.');
        }
    }

    // Highlight selected conversation file
    function highlightSelectedFile(selectedElem) {
        Array.from(historyItems.children).forEach(child => {
            if (child === selectedElem) {
                child.classList.add('active');
                child.setAttribute('aria-pressed', 'true');
            } else {
                child.classList.remove('active');
                child.setAttribute('aria-pressed', 'false');
            }
        });
    }

    // Save current conversationHistory to server (ajax=2)
    async function saveConversation() {
        if (conversationHistory.length === 0) {
            alert('No conversation to save.');
            return;
        }
        try {
            const res = await fetch(window.location.href, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    ajax:'2',
                    conversation: JSON.stringify(conversationHistory)
                }).toString()
            });
            if (!res.ok) throw new Error('Network error');
            const data = await res.json();
            if (data.success) {
                alert(`Conversation saved as \${data.filename}`);
                updateHistoryPanel();
            } else {
                alert('Failed to save conversation: ' + (data.error || 'Unknown error'));
            }
        } catch (err) {
            alert('Error saving conversation.');
        }
    }

    // Toggle history panel
    function toggleHistoryPanel() {
        const visible = historyPanel.classList.toggle('visible');
        historyPanel.setAttribute('aria-hidden', visible ? 'false' : 'true');
        if (visible) {
            updateHistoryPanel();
        }
    }

    // Send user question and get bot response (ajax=1)
    async function sendQuestion(question, lang) {
        appendMessage(question, 'user');
        if (selectedHistoryIndex !== null) {
            // If user was viewing past convo, discard future messages after selectedHistoryIndex,
            // so new conversation continues from that point
            conversationHistory = conversationHistory.slice(0, selectedHistoryIndex + 1);
            selectedHistoryIndex = null;
        }
        conversationHistory.push({text: question, sender: 'user'});

        try {
            const res = await fetch(window.location.href, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    ajax: '1',
                    question: question,
                    lang: lang
                }).toString()
            });
            if (!res.ok) throw new Error('Network error');
            const data = await res.json();
            const botResponse = data.response || "Sorry, I didn't understand that.";

            appendMessage(botResponse, 'bot');
            conversationHistory.push({text: botResponse, sender: 'bot'});
            updateHistoryPanel();
        } catch (err) {
            appendMessage("Error: Unable to reach server.", 'bot');
            conversationHistory.push({text: "Error: Unable to reach server.", sender: 'bot'});
            updateHistoryPanel();
        }
    }

    // Highlight none in history panel
    function clearHighlight() {
        Array.from(historyItems.children).forEach(child => {
            child.classList.remove('active');
            child.setAttribute('aria-pressed', 'false');
        });
    }

    // Event listeners:
    chatForm.addEventListener('submit', async e => {
        e.preventDefault();
        const question = questionInput.value.trim();
        if (!question) return;
        questionInput.value = '';
        questionInput.focus();
        await sendQuestion(question, langSelect.value);
        clearHighlight();
    });

    saveButton.addEventListener('click', saveConversation);
    menuButton.addEventListener('click', toggleHistoryPanel);

    // On initial load, set focus
    questionInput.focus();
})();
</script>
</body>
</html>
