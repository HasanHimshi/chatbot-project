<?php
mysqli_report(MYSQLI_REPORT_OFF);

$host = "127.0.0.1";
$username = "root";
$password = "";
$database = "chatbot_db";
$port = 3307; // change to 3306 if your MySQL uses default port

$conn = mysqli_connect($host, $username, $password, $database, $port);

if (!$conn) {
    http_response_code(500);
    die("Database Connection Failed. Please start MySQL in XAMPP and check db.php port. Details: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

function db_query_safe($sql) {
    global $conn;
    return @mysqli_query($conn, $sql);
}

function ensureColumn($table, $column, $definition) {
    global $conn, $database;
    $tableEsc = mysqli_real_escape_string($conn, $table);
    $columnEsc = mysqli_real_escape_string($conn, $column);
    $dbEsc = mysqli_real_escape_string($conn, $database);
    $check = mysqli_query($conn, "SELECT COUNT(*) AS total FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$dbEsc' AND TABLE_NAME = '$tableEsc' AND COLUMN_NAME = '$columnEsc'");
    $row = $check ? mysqli_fetch_assoc($check) : ['total' => 0];
    if ((int)($row['total'] ?? 0) === 0) {
        @mysqli_query($conn, "ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    }
}

// Core tables
db_query_safe("CREATE TABLE IF NOT EXISTS chatbot_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    keywords VARCHAR(255) NOT NULL,
    response TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

db_query_safe("CREATE TABLE IF NOT EXISTS chatbot_training (
    id INT AUTO_INCREMENT PRIMARY KEY,
    keyword VARCHAR(255) NOT NULL,
    response TEXT NOT NULL,
    category VARCHAR(80) DEFAULT 'general',
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

db_query_safe("CREATE TABLE IF NOT EXISTS chat_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_message TEXT NOT NULL,
    bot_reply TEXT NOT NULL,
    source VARCHAR(40) DEFAULT 'ai',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

db_query_safe("CREATE TABLE IF NOT EXISTS chat_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_key VARCHAR(80) NOT NULL UNIQUE,
    title VARCHAR(180) DEFAULT 'New Chat',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

db_query_safe("CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_key VARCHAR(80) NOT NULL,
    user_message TEXT NOT NULL,
    bot_reply TEXT NOT NULL,
    source VARCHAR(40) DEFAULT 'ai',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(session_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

db_query_safe("CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_key VARCHAR(80) DEFAULT NULL,
    user_message TEXT,
    bot_reply TEXT,
    rating VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

db_query_safe("CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

db_query_safe("CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Upgrade older database tables safely
ensureColumn('chatbot_training', 'category', "VARCHAR(80) DEFAULT 'general'");
ensureColumn('chatbot_training', 'status', "ENUM('active','inactive') DEFAULT 'active'");
ensureColumn('chatbot_training', 'updated_at', "TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP");
ensureColumn('chat_history', 'source', "VARCHAR(40) DEFAULT 'ai'");

// Default admin: username admin, password admin123
$adminCountResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM admins");
$adminCount = $adminCountResult ? (int)(mysqli_fetch_assoc($adminCountResult)['total'] ?? 0) : 0;
if ($adminCount === 0) {
    $defaultHash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO admins (username, password_hash) VALUES ('admin', ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $defaultHash);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

$defaultSettings = [
    'bot_name' => 'Himshi AI EduBot',
    'creator_name' => 'Himshi',
    'welcome_message' => 'Hello 👋 I am your education counseling assistant. Ask me about courses, careers, study paths, universities, or skills.',
    'gemini_model' => 'gemini-2.5-flash-lite',
    'api_limit_message' => 'AI limit reached for now. Please wait a little and try again.',
    'system_prompt' => 'You are a helpful education counseling chatbot for Sri Lankan students. Answer in plain text only. Keep answers simple, friendly, short, and practical.'
];

foreach ($defaultSettings as $key => $value) {
    $stmt = mysqli_prepare($conn, "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $key, $value);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

function getSetting($key, $default = '') {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
    if (!$stmt) return $default;
    mysqli_stmt_bind_param($stmt, "s", $key);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);
    return $row ? (string)$row['setting_value'] : $default;
}

function setSetting($key, $value) {
    global $conn;
    $stmt = mysqli_prepare($conn, "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    if (!$stmt) return false;
    mysqli_stmt_bind_param($stmt, "ss", $key, $value);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
?>
