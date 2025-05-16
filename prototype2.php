<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $userQuestion = strtolower(trim($_POST['question']));
    $lang = isset($_POST['lang']) ? $_POST['lang'] : 'en';

    $responses_en = [
        ['keywords' => ['hi'], 'response' => "Hello budy! I'm always here to chat. What's on your mind?"],
        ['keywords' => ['sad'], 'response' => "I'm really sorry you're feeling sad. I'm here with you."],
        ['keywords' => ['happy'], 'response' => "That's wonderful to hear! Keep embracing that joy."]
    ];

    $responses_tl = [
        ['keywords' => ['kamusta', 'hello'], 'response' => "Kumusta! Ano ang nararamdaman mo ngayon?"],
        ['keywords' => ['lungkot', 'malungkot'], 'response' => "Pasensya ka na at ganito ang nararamdaman mo. Nandito ako para makinig."],
        ['keywords' => ['masaya', 'tuwang-tuwa'], 'response' => "Ayos! Ano ang nagpapasaya sa'yo?"]
    ];

    $responses_hil = [
        ['keywords' => ['kamusta', 'hello'], 'response' => "Kamusta! Ano ang ginabatyagan mo subong?"],
        ['keywords' => ['kasubo', 'subo'], 'response' => "Pasensya, kabudlay subong sa imo. Ari lang ko, pamatian ta ka."],
        ['keywords' => ['kalipay', 'lipay'], 'response' => "Ayos gid! Ano ang gina-palipay sa imo subong?"]
    ];

    $responses_ceb = [
        ['keywords' => ['kamusta', 'hello'], 'response' => "Kamusta! Unsa'y gibati nimo karon?"],
        ['keywords' => ['kasubo', 'subo'], 'response' => "Pasensya, murag bug-at imong gibati. Naa ra ko diri, andam maminaw."],
        ['keywords' => ['kalipay', 'lipay'], 'response' => "Ayos kaayo! Unsa'y nakapalipay nimo karon?"]
    ];

    $defaultResponses = [
        'en' => "Sorry, I don't understand that question.",
        'tl' => "Pasensya, hindi ko naintindihan ang tanong mo.",
        'hil' => "Pasensya, wala ko ka inchindi sa imo pamangkot.",
        'ceb' => "Pasensya, wa ko kasabot sa imong pangutana."
    ];

    $responses = $responses_en;
    switch ($lang) {
        case 'tl':
            $responses = $responses_tl;
            break;
        case 'hil':
            $responses = $responses_hil;
            break;
        case 'ceb':
            $responses = $responses_ceb;
            break;
    }

    $response = $defaultResponses[$lang] ?? $defaultResponses['en'];

    foreach ($responses as $item) {
        foreach ($item['keywords'] as $keyword) {
            if (strpos($userQuestion, $keyword) !== false) {
                $response = $item['response'];
                break 2;
            }
        }
    }

    // Save conversation to file
    $filename = 'conversations/' . date('Y-m-d_H-i-s') . '.txt';
    $logEntry = "User: $userQuestion\nBot: $response\n";
    file_put_contents($filename, $logEntry, FILE_APPEND);

    echo json_encode(['response' => $response]);
    exit;
}

// List saved conversations
if (isset($_GET['action']) && $_GET['action'] === 'list_convos') {
    $files = glob('conversations/*.txt');
    $convoList = [];
    foreach ($files as $file) {
        $convoList[] = basename($file);
    }
    echo json_encode(['convos' => $convoList]);
    exit;
}

// Show content of a conversation file
if (isset($_GET['action']) && $_GET['action'] === 'view_convo' && isset($_GET['file'])) {
    $filePath = 'conversations/' . basename($_GET['file']);
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        echo json_encode(['content' => $content]);
    } else {
        echo json_encode(['error' => 'File not found']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mental Health Listener</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            position: relative;
            width: 90%;
            max-width: 900px;
            display: flex;
            height: 80vh;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
            border-radius: 8px;
            background: white;
        }
        .chat-box {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            padding: 20px;
            overflow: hidden;
        }
        .messages {
            flex-grow: 1;
            overflow-y: auto;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background: #fff;
        }
        .message {
            max-width: 80%;
            margin: 6px 0;
            padding: 10px 15px;
            border-radius: 15px;
            word-wrap: break-word;
        }
        .user { background-color: #d0e6ff; align-self: flex-end;}
        .bot { background-color: #d9f7d9; align-self: flex-start;}
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .header select {
            margin-left: 10px;
        }
        /* Sidebar styling */
        #sidebar {
            width: 250px;
            background: #f0f0f0;
            border-left: 1px solid #ccc;
            padding: 15px;
            overflow-y: auto;
            display: none; /* Hidden by default */
            flex-direction: column;
        }
        #sidebar h3 {
            margin-top: 0;
        }
        #sidebar ul {
            list-style: none;
            padding-left: 0;
        }
        #sidebar ul li {
            margin: 8px 0;
            cursor: pointer;
            color: #007BFF;
            text-decoration: underline;
        }
        #sidebar pre {
            white-space: pre-wrap;
            background: #fff;
            padding: 10px;
            border: 1px solid #ccc;
            height: 200px;
            overflow-y: auto;
            margin-top: 10px;
            border-radius: 5px;
        }
        /* Menu button */
        #menuBtn {
            background-color: #007BFF;
            border: none;
            color: white;
            padding: 7px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        #menuBtn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="chat-box" id="chatBox">
        <div class="header">
            <div>
                <button id="menuBtn" title="Show/Hide Conversations">☰ Menu</button>
                <strong>Ask the AI Bot</strong>
            </div>
            <select name="lang" id="lang">
                <option value="en">English</option>
                <option value="tl">Tagalog</option>
                <option value="hil">Hiligaynon</option>
                <option value="ceb">Cebuano</option>
            </select>
        </div>

        <div class="messages" id="chat"></div>

        <form id="chatForm" method="POST">
            <input type="text" name="question" id="questionInput" placeholder="Type your question..." required autocomplete="off" />
            <input type="submit" value="Send" />
        </form>
    </div>

    <div id="sidebar">
        <h3>Saved Conversations</h3>
        <ul id="convoList"></ul>
        <pre id="convoContent"></pre>
    </div>
</div>

<script>
    const chatForm = document.getElementById('chatForm');
    const chat = document.getElementById('chat');
    const langSelect = document.getElementById('lang');
    const questionInput = document.getElementById('questionInput');
    const menuBtn = document.getElementById('menuBtn');
    const sidebar = document.getElementById('sidebar');
    const convoList = document.getElementById('convoList');
    const convoContent = document.getElementById('convoContent');

    // Toggle sidebar visibility on menu button click
    menuBtn.addEventListener('click', () => {
        if (sidebar.style.display === 'flex') {
            sidebar.style.display = 'none';
        } else {
            sidebar.style.display = 'flex';
            loadConversations();
        }
    });

    // Load list of saved conversations
    function loadConversations() {
        fetch('?action=list_convos')
            .then(res => res.json())
            .then(data => {
                convoList.innerHTML = '';
                convoContent.textContent = '';
                if (data.convos && data.convos.length > 0) {
                    data.convos.forEach(file => {
                        const li = document.createElement('li');
                        li.textContent = file;
                        li.onclick = () => loadConversation(file);
                        convoList.appendChild(li);
                    });
                } else {
                    convoList.innerHTML = '<li>No conversations found.</li>';
                }
            });
    }

    // Load conversation content
    function loadConversation(filename) {
        fetch(`?action=view_convo&file=${encodeURIComponent(filename)}`)
            .then(res => res.json())
            .then(data => {
                if (data.content) {
                    convoContent.textContent = data.content;
                } else {
                    convoContent.textContent = data.error || 'Unable to load conversation.';
                }
            });
    }

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const question = questionInput.value.trim();
        const lang = langSelect.value;
        if (!question) return;

        const userMsg = document.createElement('div');
        userMsg.className = 'message user';
        userMsg.textContent = question;
        chat.appendChild(userMsg);
        chat.scrollTop = chat.scrollHeight;

        fetch("", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "ajax=1&question=" + encodeURIComponent(question) + "&lang=" + encodeURIComponent(lang)
        })
        .then(res => res.json())
        .then(data => {
            const botMsg = document.createElement('div');
            botMsg.className = 'message bot';
            botMsg.textContent = data.response;
            chat.appendChild(botMsg);
            chat.scrollTop = chat.scrollHeight;
        });

        questionInput.value = '';
        questionInput.focus();
    });
</script>

</body>
</html>
