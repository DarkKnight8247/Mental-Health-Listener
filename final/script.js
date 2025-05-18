  (() => {
      const chat = document.getElementById('chat');
      const chatForm = document.getElementById('chatForm');
      const questionInput = document.getElementById('questionInput');
      const langSelect = document.getElementById('langSelect');
      const saveButton = document.getElementById('saveButton');
      const historyItems = document.getElementById('historyItems');

      let conversationHistory = [];
      let firstQuestion = '';

      function appendMessage(text, sender) {
          if (!text) return;
          const msgEl = document.createElement('div');
          msgEl.className = 'message ' + sender;
          chat.appendChild(msgEl);
          chat.scrollTop = chat.scrollHeight;

          if (sender === 'user') {
            
              msgEl.textContent = text;
          } else {
              msgEl.textContent = '';
              let i = 0;
              const typingInterval = setInterval(() => {
                  if (i < text.length) {
                      msgEl.textContent += text.charAt(i);
                      i++;
                      chat.scrollTop = chat.scrollHeight;
                  } else {
                      clearInterval(typingInterval);
                  }
              }, 20);
          }
      }

      function renderConversation(conversation) {
          chat.innerHTML = '';
          conversation.forEach(({ text, sender }) => {
              const msgEl = document.createElement('div');
              msgEl.className = 'message ' + sender;
              msgEl.textContent = text; 
              chat.appendChild(msgEl);
          });
          chat.scrollTop = chat.scrollHeight;
      }

      async function updateHistoryPanel() {
          try {
              const res = await fetch(window.location.href, {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                  body: new URLSearchParams({ ajax: '3' }).toString()
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

                  item.addEventListener('click', () => loadConversationFromFile(filename));

                  historyItems.appendChild(item);
              });
          } catch (err) {
              historyItems.innerHTML = '<div style="padding:12px;">Error loading history.</div>';
          }
      }

      async function loadConversationFromFile(filename) {
          try {
              const res = await fetch(window.location.href, {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                  body: new URLSearchParams({ ajax: '4', filename }).toString()
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

              conversationHistory = data.conversation;
              renderConversation(conversationHistory);
          } catch (err) {
              alert('Error loading conversation.');
          }
      }

      async function saveConversation() {
          if (conversationHistory.length === 0) {
              alert('No conversation to save.');
              return;
          }
          const filename = firstQuestion;
          try {
              const res = await fetch(window.location.href, {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                  body: new URLSearchParams({
                      ajax: '2',
                      conversation: JSON.stringify(conversationHistory),
                      filename: filename
                  }).toString()
              });
              if (!res.ok) throw new Error('Network error');
              const data = await res.json();
              if (data.success) {
                  updateHistoryPanel();
                  conversationHistory = [];
                  firstQuestion = '';
                  chat.innerHTML = '';
              } else {
                  alert('Failed to save conversation: ' + (data.error || 'Unknown error'));
              }
          } catch (err) {
              alert('Error saving conversation.');
          }
      }

      async function sendQuestion(question, lang) {
          appendMessage(question, 'user');
          if (!firstQuestion) firstQuestion = question;
          conversationHistory.push({ text: question, sender: 'user' });

          try {
              const res = await fetch(window.location.href, {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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
              conversationHistory.push({ text: botResponse, sender: 'bot' });
              updateHistoryPanel();
          } catch (err) {
              appendMessage("Error: Unable to reach server.", 'bot');
              conversationHistory.push({ text: "Error: Unable to reach server.", sender: 'bot' });
              updateHistoryPanel();
          }
      }

      chatForm.addEventListener('submit', async e => {
          e.preventDefault();
          const question = questionInput.value.trim();
          if (!question) return;
          questionInput.value = '';
          questionInput.focus();
          await sendQuestion(question, langSelect.value);
      });

      saveButton.addEventListener('click', saveConversation);

      questionInput.focus();
      updateHistoryPanel();
  })();
