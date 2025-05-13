<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $userQuestion = strtolower(trim($_POST['question']));
    $lang = isset($_POST['lang']) ? $_POST['lang'] : 'en';

$responses_en = [
    ['keywords' => ['anxious', 'worried', 'nervous'], 'response' => "I'm here to listen. Want to talk about what's making you feel this way?"],
    ['keywords' => ['sad', 'down', 'depressed'], 'response' => "I'm sorry you're feeling this way. I'm here for you. Want to share more?"],
    ['keywords' => ['lonely', 'alone'], 'response' => "You're not alone. I’m here to listen anytime you need."],
    ['keywords' => ['angry', 'frustrated'], 'response' => "It’s okay to feel that way. Want to talk about what’s causing it?"],
    ['keywords' => ['happy', 'joyful'], 'response' => "That's wonderful! What made you feel this way?"],
    ['keywords' => ['hello', 'hi'], 'response' => "Hello! How are you feeling today?"],
    ['keywords' => ['name'], 'response' => "I'm your friendly mental health listener bot. I'm here to talk anytime."],
];

$responses_tl = [
    ['keywords' => ['balisa', 'kabado', 'nenerbyos'], 'response' => "Nandito ako para makinig. Gusto mo bang pag-usapan kung ano ang nagpapabalisa sa'yo?"],
    ['keywords' => ['malungkot', 'lungkot', 'depressed'], 'response' => "Pasensya ka na at ganito ang nararamdaman mo. Nandito ako para makinig."],
    ['keywords' => ['mag-isa', 'malayo'], 'response' => "Hindi ka nag-iisa. Nandito ako, handang makinig."],
    ['keywords' => ['galit', 'inis'], 'response' => "Okay lang yan. Gusto mo bang pag-usapan kung bakit ka nagagalit?"],
    ['keywords' => ['masaya', 'tuwang-tuwa'], 'response' => "Ayos! Ano ang nagpapasaya sa'yo?"],
    ['keywords' => ['hello', 'hi', 'kumusta', 'kamusta'], 'response' => "Kumusta! Ano ang nararamdaman mo ngayon?"],
    ['keywords' => ['pangalan'], 'response' => "Ako ang iyong mental health listener bot. Pwede tayong mag-usap."],
];

$responses_hil = [
    ['keywords' => ['kulba', 'kabalaka'], 'response' => "Ari ko diri para pamati. Gusto mo ba mag-istorya kung ano ang gina-kabalaka mo?"],
    ['keywords' => ['kasubo', 'subo'], 'response' => "Pasensya, basi kabudlay subong sa imo. Ari lang ko, pamatian ta ka."],
    ['keywords' => ['isa', 'walay upod'], 'response' => "Wala ka nagaisahan. Ari ko diri, mabulig pamatian ka."],
    ['keywords' => ['akig', 'pangakig'], 'response' => "Okay lang ina. Gusto mo ba mag-istorya kung ano ang gina-akigan mo?"],
    ['keywords' => ['lipay', 'kalipay'], 'response' => "Ayos gid! Ano ang gina-palipay sa imo subong?"],
    ['keywords' => ['hello', 'hi', 'kamusta', 'kumusta'], 'response' => "Kamusta! Ano ang na-batyagan mo subong?"],
    ['keywords' => ['ngalan'], 'response' => "Ako ang imo mental health listener bot. Pwede kita mag-istorya."],
];

    $response = "Sorry, I don't understand that question.";

    if ($lang === 'tl') {
        $responses = $responses_tl;
    } elseif ($lang === 'hil') {
        $responses = $responses_hil;
    } else {
        $responses = $responses_en;
    }

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
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mini AI Bot</title>
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
        .chat-box {
            background: white;
            padding: 20px;
            width: 600px;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            display: none;
            flex-direction: column;
            max-height: 80vh;
            overflow: hidden;
            overflow: hidden;
        }
        .messages {
            flex-grow: 1;
            overflow-y: auto;
            margin-bottom: 20px;
            font-size: 16px;
            white-space: pre-wrap;
            display: flex;
            flex-direction: column;
        }
        .message {
            max-width: 80%;
            margin: 6px 0;
            padding: 10px 15px;
            border-radius: 15px;
            word-wrap: break-word;
            line-height: 1.4;
        }
        .user {
            align-self: flex-end;
            background-color: #d0e6ff;
            color: #0a3d8f;
            border-radius: 15px 15px 0 15px;
            text-align: right;
        }
        .bot {
            align-self: flex-start;
            background-color: #d9f7d9;
            color: #2a6b2a;
            border-radius: 15px 15px 15px 0;
            text-align: left;
        }
        input[type="text"] {
            width: 80%;
            padding: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            padding: 8px 12px;
            font-size: 16px;
            cursor: pointer;
            margin-left: 8px;
        }
        /* Modal styles */
        #languageModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        #languageModalContent {
            background: white;
            padding: 30px 40px;
            border-radius: 10px;
            text-align: center;
            width: 320px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }
        #languageModalContent h2 {
            margin-bottom: 20px;
        }
        .lang-button {
            display: block;
            width: 100%;
            margin: 10px 0;
            padding: 12px 0;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            border-radius: 6px;
            background-color: #1976d2;
            color: white;
            transition: background-color 0.3s ease;
        }
        .lang-button:hover {
            background-color: #0d47a1;
        }
        /* Loading animation */
        .loading {
            color: orange;
            font-weight: bold;
            margin: 10px 0;
        }
        @keyframes blink {
            0%, 80%, 100% { opacity: 0; }
            40% { opacity: 1; }
        }
        .loading span {
            animation: blink 1.4s infinite ease-in-out;
            font-weight: 900;
            display: inline-block;
        }
        .loading span:nth-child(1) { animation-delay: 0s; }
        .loading span:nth-child(2) { animation-delay: 0.2s; }
        .loading span:nth-child(3) { animation-delay: 0.4s; }
    </style>
</head>
<body>
    <div id="languageModal">
        <div id="languageModalContent">
            <h2>Select your language</h2>
            <button class="lang-button" data-lang="en">English</button>
            <button class="lang-button" data-lang="tl">Tagalog</button>
            <button class="lang-button" data-lang="hil">Hiligaynon</button>
        </div>
    </div>

    <div class="chat-box" id="chatBox">
        <h2>Ask the AI Bot</h2>
        <div class="messages" id="chat"></div>
        <form id="chatForm" method="POST">
            <input type="text" name="question" id="questionInput" placeholder="Type your question..." required autocomplete="off" />
            <input type="submit" value="Send" />
        </form>
    </div>

    <script>
        const languageModal = document.getElementById('languageModal');
        const chatBox = document.getElementById('chatBox');
        const chat = document.getElementById('chat');
        const chatForm = document.getElementById('chatForm');
        const questionInput = document.getElementById('questionInput');

        let selectedLanguage = null;

        // Language selection buttons
        const langButtons = document.querySelectorAll('.lang-button');
        langButtons.forEach(button => {
            button.addEventListener('click', () => {
                selectedLanguage = button.getAttribute('data-lang');
                languageModal.style.display = 'none';
                chatBox.style.display = 'flex';
                questionInput.focus();
            });
        });

        function appendMessage(text, sender) {
            const msg = document.createElement('div');
            msg.className = 'message ' + sender;
            msg.textContent = text;
            chat.appendChild(msg);
            chat.scrollTop = chat.scrollHeight;
        }

        function createLoading() {
            const loading = document.createElement('div');
            loading.className = 'loading';
            loading.textContent = 'Bot is typing';
            for(let i = 0; i < 3; i++) {
                const dot = document.createElement('span');
                dot.textContent = '.';
                loading.appendChild(dot);
            }
            return loading;
        }

        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!selectedLanguage) {
                alert('Please select a language first.');
                return;
            }
            const question = questionInput.value.trim();
            if (!question) return;

            appendMessage(question, 'user');

            const loading = createLoading();
            chat.appendChild(loading);
            chat.scrollTop = chat.scrollHeight;

            fetch("", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "ajax=1&question=" + encodeURIComponent(question) + "&lang=" + encodeURIComponent(selectedLanguage)
            })
            .then(res => res.json())
            .then(data => {
                loading.remove();
                const response = data.response;
                let i = 0;
                const botMsg = document.createElement('div');
                botMsg.className = 'message bot';
                chat.appendChild(botMsg);
                function typeWriter() {
                    if (i < response.length) {
                        botMsg.textContent += response.charAt(i);
                        i++;
                        chat.scrollTop = chat.scrollHeight;
                        setTimeout(typeWriter, 50);
                    }
                }
                typeWriter();
            });

            questionInput.value = '';
            questionInput.focus();
        });
    </script>
</body>
</html>
