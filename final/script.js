(() => {
    const chat = document.getElementById('chat');
    const chatForm = document.getElementById('chatForm');
    const questionInput = document.getElementById('questionInput');
    const langSelect = document.getElementById('langSelect');
    const saveButton = document.getElementById('saveButton'); 
    const historyItems = document.getElementById('historyItems'); 

    let conversationHistory = []; 
    let firstQuestion = '';

    const appendMessage = (text, sender) => {
    if (!text) return;
    const msgEl = document.createElement('div');
    msgEl.className = `message ${sender}`;
    chat.appendChild(msgEl);
    chat.scrollTop = chat.scrollHeight;

    if (sender === 'bot') {
msgEl.textContent = '';
[...text].forEach((char, i) => {
setTimeout(() => msgEl.textContent += char, 20 * i);
});
    } else {
msgEl.textContent = text;
    }
    };

    const renderConversation = (conversation) => {
    chat.innerHTML = '';
    conversation.forEach(({ text, sender }) => {
const msgEl = document.createElement('div');
msgEl.className = `message ${sender}`;
msgEl.textContent = text;
chat.appendChild(msgEl);
    });
    chat.scrollTop = chat.scrollHeight;
    };

    const updateHistoryPanel = async () => {
    try {
const res = await fetch(window.location.href, {
method: 'POST',
headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
body: new URLSearchParams({ ajax: '3' }).toString()
});
const data = await res.json();

if (!data.success) {
historyItems.innerHTML = '<div style="padding:12px;">Failed to load history.</div>';
return;
}

historyItems.innerHTML = data.files.length ? 
data.files.map(filename => `<div class="history-item" tabindex="0" role="button">${filename}</div>`).join('') :
'<div style="padding:12px;">No saved conversations.</div>';

data.files.forEach(filename => {
const item = historyItems.querySelector(`:contains(${filename})`);
item.addEventListener('click', () => loadConversationFromFile(filename));
});
    } catch {
historyItems.innerHTML = '<div style="padding:12px;">Error loading history.</div>';
    }
    };

    const loadConversationFromFile = async (filename) => {
    try {
const res = await fetch(window.location.href, {
method: 'POST',
headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
body: new URLSearchParams({ ajax: '4', filename }).toString()
});
const data = await res.json();
if (!data.success) throw new Error(data.error || 'Unknown error');
if (!data.conversation.length) throw new Error('Conversation file is empty.');

conversationHistory = data.conversation;
renderConversation(conversationHistory);
    } catch (err) {
alert(err.message);
    }
    };

    const saveConversation = async () => {
    if (!conversationHistory.length) return alert('No conversation to save.');
    const filename = firstQuestion;
    try {
const res = await fetch(window.location.href, {
method: 'POST',
headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
body: new URLSearchParams({
    ajax: '2',
    conversation: JSON.stringify(conversationHistory),
    filename
}).toString()
});
const data = await res.json();
if (!data.success) throw new Error(data.error || 'Failed to save conversation.');
updateHistoryPanel();
conversationHistory = [];
firstQuestion = '';
chat.innerHTML = '';
    } catch (err) {
alert(err.message);
    }
    };

    const sendQuestion = async (question, lang) => {
    appendMessage(question, 'user');
    if (!firstQuestion) firstQuestion = question;
    conversationHistory.push({ text: question, sender: 'user' });

    try {
const res = await fetch(window.location.href, {
method: 'POST',
headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
body: new URLSearchParams({
    ajax: '1',
    question,
    lang
}).toString()
});
const data = await res.json();
const botResponse = data.response || "Sorry, I didn't understand that.";
appendMessage(botResponse, 'bot');
conversationHistory.push({ text: botResponse, sender: 'bot' });
updateHistoryPanel();
    } catch {
appendMessage("Error: Unable to reach server.", 'bot');
    }
    };

    chatForm.addEventListener('submit', e => {
    e.preventDefault();
    const question = questionInput.value.trim();
    if (!question) return;
    questionInput.value = '';
    questionInput.focus();
    sendQuestion(question, langSelect.value);
    });

    saveButton.addEventListener('click', saveConversation);

    questionInput.focus();
    updateHistoryPanel();
})();
