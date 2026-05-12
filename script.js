function getChatbox() { return document.getElementById("chatbox"); }
function getTime() { return new Date().toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" }); }

let lastUserQuestion = "";
let lastBotReply = "";
let isSendingMessage = false;

function getSessionKey() {
    let key = localStorage.getItem("himshiChatSessionKey");
    if (!key) {
        key = "session_" + Date.now() + "_" + Math.random().toString(36).slice(2, 10);
        localStorage.setItem("himshiChatSessionKey", key);
    }
    return key;
}

function resetSessionKey() {
    const key = "session_" + Date.now() + "_" + Math.random().toString(36).slice(2, 10);
    localStorage.setItem("himshiChatSessionKey", key);
    return key;
}

function setStatus(text) {
    const status = document.getElementById("connectionStatus");
    if (status) status.textContent = text;
}

function updateCharCount() {
    const input = document.getElementById("userInput");
    const counter = document.getElementById("charCount");
    if (input && counter) counter.textContent = input.value.length;
}

function autoResizeInput() {
    const input = document.getElementById("userInput");
    if (!input) return;
    input.style.height = "auto";
    input.style.height = Math.min(input.scrollHeight, 118) + "px";
}

function scrollToBottom() {
    const chatbox = getChatbox();
    if (!chatbox) return;
    chatbox.scrollTo({ top: chatbox.scrollHeight, behavior: "smooth" });
}

function appendMessage(type, text) {
    const chatbox = getChatbox();
    if (!chatbox) return;

    const row = document.createElement("div");
    row.className = type === "user" ? "user-row message-row" : "bot-row message-row";

    const avatar = document.createElement("div");
    avatar.className = type === "user" ? "avatar user-avatar" : "avatar bot-avatar";
    avatar.textContent = type === "user" ? "👨" : "🤖";

    const bubble = document.createElement("div");
    bubble.className = type === "user" ? "user-message message-bubble" : "bot-message message-bubble";

    const textBox = document.createElement("div");
    textBox.className = "message-text";
    textBox.textContent = text;
    bubble.appendChild(textBox);

    if (type === "bot") {
        lastBotReply = text;
        const actions = document.createElement("div");
        actions.className = "message-actions";

        const copy = makeActionButton("Copy", function () { copyText(copy, text); });
        const speakBtn = makeActionButton("Speak", function () { if (typeof speak === "function") speak(text); });
        const like = makeActionButton("👍", function () { sendFeedback(like, "like", text); });
        const dislike = makeActionButton("👎", function () { sendFeedback(dislike, "dislike", text); });
        const train = makeActionButton("Save to Training", function () { window.location.href = "training.php?keyword=" + encodeURIComponent(lastUserQuestion) + "&response=" + encodeURIComponent(text); });

        actions.appendChild(copy);
        actions.appendChild(speakBtn);
        actions.appendChild(like);
        actions.appendChild(dislike);
        actions.appendChild(train);
        bubble.appendChild(actions);
    }

    const time = document.createElement("div");
    time.className = "time";
    time.textContent = getTime();
    bubble.appendChild(time);

    if (type === "user") { row.appendChild(bubble); row.appendChild(avatar); }
    else { row.appendChild(avatar); row.appendChild(bubble); }

    chatbox.appendChild(row);
    scrollToBottom();
}

function makeActionButton(text, clickHandler) {
    const button = document.createElement("button");
    button.type = "button";
    button.className = "copy-btn";
    button.textContent = text;
    button.onclick = clickHandler;
    return button;
}

function showTyping() {
    removeTyping();
    const chatbox = getChatbox();
    if (!chatbox) return;
    const row = document.createElement("div");
    row.className = "bot-row message-row";
    row.id = "typing";
    row.innerHTML = '<div class="avatar bot-avatar">🤖</div><div class="bot-message typing-bubble"><span>Thinking</span><div class="typing"><i></i><i></i><i></i></div></div>';
    chatbox.appendChild(row);
    scrollToBottom();
}

function removeTyping() {
    const typing = document.getElementById("typing");
    if (typing) typing.remove();
}

function setSendingState(state) {
    isSendingMessage = state;
    const button = document.getElementById("sendBtn");
    const input = document.getElementById("userInput");
    if (button) { button.disabled = state; button.textContent = state ? "Sending..." : "Send"; }
    if (input) input.disabled = state;
    setStatus(state ? "Thinking..." : "Ready");
}

function sendMessage() {
    const input = document.getElementById("userInput");
    if (!input || isSendingMessage) return;
    const message = input.value.trim();
    if (message === "") return;

    lastUserQuestion = message;
    appendMessage("user", message);
    input.value = "";
    updateCharCount();
    autoResizeInput();
    showTyping();
    setSendingState(true);

    fetch("chatbot.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" },
        body: new URLSearchParams({ message: message, session_key: getSessionKey() })
    })
    .then(function (response) {
        if (!response.ok) throw new Error("Server error " + response.status);
        return response.text();
    })
    .then(function (reply) {
        removeTyping();
        const cleanReply = reply.trim() || "Sorry, I could not create a reply.";
        appendMessage("bot", cleanReply);
        if (typeof speechEnabled !== "undefined" && speechEnabled && typeof speak === "function") speak(cleanReply);
    })
    .catch(function (error) {
        removeTyping();
        appendMessage("bot", "Error: chatbot server is not responding. Check Apache, MySQL, db.php, and chatbot.php.");
        console.error(error);
    })
    .finally(function () {
        setSendingState(false);
        if (input) input.focus();
    });
}

function quickAsk(text) {
    const input = document.getElementById("userInput");
    if (!input) { window.location.href = "chat.php?q=" + encodeURIComponent(text); return; }
    input.value = text;
    updateCharCount();
    autoResizeInput();
    sendMessage();
}

function insertPrompt(text) {
    const input = document.getElementById("userInput");
    if (!input) return;
    input.value = text;
    updateCharCount();
    autoResizeInput();
    input.focus();
}

function clearChat() {
    const chatbox = getChatbox();
    if (!chatbox) return;
    chatbox.innerHTML = "";
    appendMessage("bot", "Chat screen cleared. Your saved database history is not deleted.");
}

function newChatScreen() {
    const chatbox = getChatbox();
    if (!chatbox) return;
    resetSessionKey();
    lastUserQuestion = "";
    lastBotReply = "";
    chatbox.innerHTML = "";
    appendMessage("bot", "New chat session started. What education question can I help you with?");
}

function regenerateLastAnswer() {
    if (!lastUserQuestion) { appendMessage("bot", "Ask a question first, then I can regenerate the last answer."); return; }
    insertPrompt(lastUserQuestion);
    sendMessage();
}

function copyText(button, text) {
    function copied() {
        if (!button) return;
        const old = button.textContent;
        button.textContent = "Copied";
        setTimeout(function () { button.textContent = old; }, 1200);
    }
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(copied).catch(function () { fallbackCopy(text); copied(); });
    } else { fallbackCopy(text); copied(); }
}

function fallbackCopy(text) {
    const temp = document.createElement("textarea");
    temp.value = text;
    document.body.appendChild(temp);
    temp.select();
    document.execCommand("copy");
    temp.remove();
}

function sendFeedback(button, rating, botReply) {
    if (!button) return;
    const old = button.textContent;
    button.textContent = "Saving";
    fetch("feedback.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" },
        body: new URLSearchParams({ rating: rating, session_key: getSessionKey(), user_message: lastUserQuestion, bot_reply: botReply })
    })
    .then(function () { button.textContent = rating === "like" ? "Saved 👍" : "Saved 👎"; })
    .catch(function () { button.textContent = "Failed"; })
    .finally(function () { setTimeout(function () { button.textContent = old; }, 1400); });
}

function exportChatText() {
    const chatbox = getChatbox();
    if (!chatbox) return;
    const text = chatbox.innerText.trim();
    if (!text) return;
    const blob = new Blob([text], { type: "text/plain;charset=utf-8" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = "himshi-ai-chat.txt";
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
}

function toggleTheme() {
    document.body.classList.toggle("light-theme");
    const isLight = document.body.classList.contains("light-theme");
    localStorage.setItem("himshiTheme", isLight ? "light" : "dark");
    const button = document.getElementById("themeBtn");
    if (button) button.textContent = isLight ? "☀️ Theme" : "🌙 Theme";
}

function restoreTheme() {
    const saved = localStorage.getItem("himshiTheme");
    if (saved === "light") document.body.classList.add("light-theme");
    const button = document.getElementById("themeBtn");
    if (button) button.textContent = saved === "light" ? "☀️ Theme" : "🌙 Theme";
}

function filterTraining() {
    const input = document.getElementById("trainingSearch");
    const list = document.getElementById("trainingList");
    if (!input || !list) return;
    const q = input.value.toLowerCase();
    list.querySelectorAll(".training-item").forEach(function (item) { item.style.display = item.innerText.toLowerCase().includes(q) ? "grid" : "none"; });
}

function exportTrainingText() {
    const list = document.getElementById("trainingList");
    if (!list) return;
    const text = Array.from(list.querySelectorAll(".training-item")).map(function (item) { return item.innerText.trim(); }).join("\n\n--------------------\n\n");
    if (!text) return;
    downloadText("himshi-training.txt", text);
}

function filterHistory() {
    const input = document.getElementById("historySearch");
    const list = document.getElementById("historyList");
    if (!input || !list) return;
    const q = input.value.toLowerCase();
    list.querySelectorAll(".history-card").forEach(function (item) { item.style.display = item.innerText.toLowerCase().includes(q) ? "grid" : "none"; });
}

function exportHistoryText() {
    const list = document.getElementById("historyList");
    if (!list) return;
    const visible = Array.from(list.querySelectorAll(".history-card")).filter(function (item) { return item.style.display !== "none"; });
    const text = visible.map(function (item) { return item.innerText.trim(); }).join("\n\n--------------------\n\n");
    if (!text) return;
    downloadText("himshi-chat-history.txt", text);
}

function downloadText(filename, text) {
    const blob = new Blob([text], { type: "text/plain;charset=utf-8" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
}

document.addEventListener("DOMContentLoaded", function () {
    restoreTheme();
    getSessionKey();

    const input = document.getElementById("userInput");
    if (input) {
        input.focus();
        updateCharCount();
        autoResizeInput();
        input.addEventListener("input", function () { updateCharCount(); autoResizeInput(); });
        input.addEventListener("keydown", function (event) {
            if (event.key === "Enter" && !event.shiftKey) { event.preventDefault(); sendMessage(); }
        });
        const params = new URLSearchParams(window.location.search);
        const question = params.get("q");
        if (question) { input.value = question; updateCharCount(); autoResizeInput(); setTimeout(sendMessage, 250); }
    }

    const trainingKeyword = new URLSearchParams(window.location.search).get("keyword");
    const trainingResponse = new URLSearchParams(window.location.search).get("response");
    if (trainingKeyword) {
        const keywordInput = document.querySelector('input[name="keyword"]');
        if (keywordInput) keywordInput.value = trainingKeyword;
    }
    if (trainingResponse) {
        const responseInput = document.querySelector('textarea[name="response"]');
        if (responseInput) responseInput.value = trainingResponse;
    }
});
