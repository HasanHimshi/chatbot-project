<?php
include "db.php";

header("Content-Type: text/plain; charset=UTF-8");

if (!isset($_POST['message']) || trim($_POST['message']) === "") {
    echo "Please type a message.";
    exit;
}

$userMessage = trim($_POST['message']);
$sessionKey = trim($_POST['session_key'] ?? 'default-session');
$sessionKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $sessionKey);
if ($sessionKey === '') $sessionKey = 'default-session';
$searchMessage = mb_strtolower($userMessage, 'UTF-8');

function cleanBotReply($text) {
    $text = strip_tags((string)$text);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/\*\*(.*?)\*\*/s', '$1', $text);
    $text = preg_replace('/^\s*\*\s+/m', '- ', $text);
    $text = str_replace(['###', '##', '#'], '', $text);
    $text = str_replace('*', '', $text);
    $text = preg_replace("/[ \t]+/", " ", $text);
    $text = preg_replace("/\n{3,}/", "\n\n", $text);
    return trim($text);
}

function ensureSession($conn, $sessionKey, $title) {
    $safeTitle = mb_strimwidth(cleanBotReply($title), 0, 160, '...');
    $stmt = mysqli_prepare($conn, "INSERT INTO chat_sessions (session_key, title) VALUES (?, ?) ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $sessionKey, $safeTitle);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

function saveHistory($conn, $sessionKey, $userMessage, $reply, $source) {
    ensureSession($conn, $sessionKey, $userMessage);

    $stmt = mysqli_prepare($conn, "INSERT INTO chat_history (user_message, bot_reply, source) VALUES (?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $userMessage, $reply, $source);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    $stmt2 = mysqli_prepare($conn, "INSERT INTO chat_messages (session_key, user_message, bot_reply, source) VALUES (?, ?, ?, ?)");
    if ($stmt2) {
        mysqli_stmt_bind_param($stmt2, "ssss", $sessionKey, $userMessage, $reply, $source);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
    }
}

function getRecentChatHistory($conn, $sessionKey, $limit = 10) {
    $limit = max(1, min((int)$limit, 20));
    $history = [];
    $stmt = mysqli_prepare($conn, "SELECT user_message, bot_reply FROM chat_messages WHERE session_key = ? ORDER BY id DESC LIMIT ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $sessionKey, $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $history[] = ["user" => cleanBotReply($row["user_message"] ?? ""), "bot" => cleanBotReply($row["bot_reply"] ?? "")];
            }
        }
        mysqli_stmt_close($stmt);
    }
    $history = array_reverse($history);
    $text = "";
    foreach ($history as $item) {
        if ($item["user"] !== "") $text .= "User: " . $item["user"] . "\n";
        if ($item["bot"] !== "") $text .= "Bot: " . $item["bot"] . "\n";
    }
    return trim($text);
}

function getIdentityReply($messageLower) {
    $identityQuestions = ["who are you", "hwo are you", "what are you", "who r u", "who made you", "who created you", "who built you", "what api", "which api", "api do you use", "what model", "which model", "are you gemini", "are you google", "are you chatgpt", "are you openai", "your creator", "your developer", "your owner"];
    foreach ($identityQuestions as $question) {
        if (mb_strpos($messageLower, $question) !== false) return "I was made by " . getSetting('creator_name', 'Himshi') . ".";
    }
    return null;
}

function findReply($conn, $table, $keywordColumn, $responseColumn, $messageLower) {
    $allowedTables = ['chatbot_responses', 'chatbot_training'];
    $allowedColumns = ['keywords', 'keyword', 'response'];
    if (!in_array($table, $allowedTables, true) || !in_array($keywordColumn, $allowedColumns, true) || !in_array($responseColumn, $allowedColumns, true)) return null;

    $sql = $table === 'chatbot_training'
        ? "SELECT `$keywordColumn`, `$responseColumn` FROM `$table` WHERE status = 'active'"
        : "SELECT `$keywordColumn`, `$responseColumn` FROM `$table`";
    $result = mysqli_query($conn, $sql);
    if (!$result) return null;

    while ($row = mysqli_fetch_assoc($result)) {
        $keyword = trim((string)$row[$keywordColumn]);
        if ($keyword !== '' && mb_strpos($messageLower, mb_strtolower($keyword, 'UTF-8')) !== false) return (string)$row[$responseColumn];
    }
    return null;
}

function askGemini($message, $chatHistory = "") {
    $apiKey = getenv("GEMINI_API_KEY");
    if (!$apiKey) return "Gemini API key is missing. Please set GEMINI_API_KEY on your computer.";

    $model = getSetting('gemini_model', 'gemini-2.5-flash-lite');
    $url = "https://generativelanguage.googleapis.com/v1beta/models/" . urlencode($model) . ":generateContent?key=" . urlencode($apiKey);
    $historySection = $chatHistory !== "" ? "Recent conversation history:\n" . $chatHistory . "\n\n" : "Recent conversation history: No previous messages found.\n\n";
    $creator = getSetting('creator_name', 'Himshi');
    $basePrompt = getSetting('system_prompt', 'You are a helpful education counseling chatbot. Answer in plain text only.');

    $prompt = $basePrompt . "\n\n" .
        "Identity rules:\n" .
        "- If the user asks who you are, who made you, who created you, who built you, what API you use, what model you are, whether you are Gemini, Google, ChatGPT, or OpenAI, say exactly: I was made by " . $creator . ".\n" .
        "- Do not mention Gemini, Google, OpenAI, ChatGPT, API names, or model names to users.\n\n" .
        "Memory rules:\n" .
        "- Use recent conversation history to understand follow-up questions.\n" .
        "- If the user asks what they asked before, answer using recent conversation history.\n\n" .
        "Formatting rules:\n" .
        "- Plain text only. No Markdown. No HTML. No star symbols.\n\n" .
        $historySection .
        "Current user question: " . $message;

    $data = ["contents" => [["parts" => [["text" => $prompt]]]]];
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_HTTPHEADER => ["Content-Type: application/json"], CURLOPT_POSTFIELDS => json_encode($data), CURLOPT_TIMEOUT => 30]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return "AI connection error. Please check internet connection. Details: " . $error;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $json = json_decode($response, true);
    if ($httpCode < 200 || $httpCode >= 300) {
        $errorMessage = $json["error"]["message"] ?? "HTTP code: " . $httpCode;
        $lower = strtolower($errorMessage);
        if (strpos($lower, "quota") !== false || strpos($lower, "rate") !== false) return getSetting('api_limit_message', 'AI limit reached for now. Please wait a little and try again.');
        return "AI error: " . $errorMessage;
    }
    return trim($json["candidates"][0]["content"]["parts"][0]["text"] ?? "Sorry, I could not understand the AI response.");
}

$identityReply = getIdentityReply($searchMessage);
if ($identityReply !== null) {
    saveHistory($conn, $sessionKey, $userMessage, $identityReply, 'identity');
    echo $identityReply;
    exit;
}

$normalReply = findReply($conn, 'chatbot_responses', 'keywords', 'response', $searchMessage);
if ($normalReply !== null) {
    $reply = cleanBotReply($normalReply);
    saveHistory($conn, $sessionKey, $userMessage, $reply, 'default');
    echo $reply;
    exit;
}

$trainedReply = findReply($conn, 'chatbot_training', 'keyword', 'response', $searchMessage);
if ($trainedReply !== null) {
    $reply = "🤖 Trained AI: " . cleanBotReply($trainedReply);
    saveHistory($conn, $sessionKey, $userMessage, $reply, 'trained');
    echo $reply;
    exit;
}

$chatHistory = getRecentChatHistory($conn, $sessionKey, 10);
$reply = cleanBotReply(askGemini($userMessage, $chatHistory));
saveHistory($conn, $sessionKey, $userMessage, $reply, 'ai');
echo $reply;
?>
