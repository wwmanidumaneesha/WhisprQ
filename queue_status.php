<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

session_start();

// DB connection
$host = "localhost";
$dbname = "whisprq_db";
$user = "root";
$pass = "Manidu@2005";

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}

// Fetch reset count from cache_settings table
$resetStmt = $pdo->query("SELECT reset_count FROM cache_settings WHERE id = 1");
$currentResetCount = $resetStmt->fetchColumn();

// ✅ Check if queue table is empty
$totalRows = $pdo->query("SELECT COUNT(*) FROM queue")->fetchColumn();
if ($totalRows == 0 && isset($_SESSION['queue_number'])) {
  session_unset();
  session_destroy();

  // Also clear session cookie
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
  }

  header("Location: index.php");
  exit();
}

// ✅ Validate if queue number in session still exists
if (isset($_SESSION['queue_number'])) {
  $check = $pdo->prepare("SELECT id FROM queue WHERE id = ?");
  $check->execute([$_SESSION['queue_number']]);
  if (!$check->fetch()) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
  }
}

// ✅ Assign new queue number only if not already set
if (!isset($_SESSION['queue_number'])) {
  $insert = $pdo->prepare("INSERT INTO queue (status) VALUES ('waiting')");
  $insert->execute();
  $_SESSION['queue_number'] = $pdo->lastInsertId();
}

$userQueue = (int)$_SESSION['queue_number'];

// Get currently called number
$currentStmt = $pdo->query("SELECT id FROM queue WHERE status = 'called' ORDER BY id DESC LIMIT 1");
$currentData = $currentStmt->fetch(PDO::FETCH_ASSOC);
$currentNumber = $currentData ? (int)$currentData['id'] : 0;

// People ahead
$aheadStmt = $pdo->prepare("SELECT COUNT(*) FROM queue WHERE status = 'waiting' AND id < ?");
$aheadStmt->execute([$userQueue]);
$peopleAhead = $aheadStmt->fetchColumn();
$estimatedWait = $peopleAhead * 0.5;

// ✅ Trigger Push Notification if it's user's turn
if ($userQueue === $currentNumber && !isset($_SESSION['push_sent'])) {
  $payload = json_encode([
    'title' => "🎉 Your Turn!",
    'body' => "Please proceed to the counter.",
    'icon' => "notify.png"
  ]);

  // Call the push notification sender
  $ch = curl_init("http://localhost/WhisprQ/send_push.php");
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, ['payload' => $payload]);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_exec($ch);
  curl_close($ch);

  $_SESSION['push_sent'] = true;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Queue Status - Silent Queue</title>
  <style>
    :root {
      --bg: #4facfe;
      --text: #333;
      --card: white;
      --accent: #00c9a7;
    }

    [data-theme="dark"] {
      --bg: #121212;
      --text: #f0f0f0;
      --card: #1e1e1e;
      --accent: #00c9a7;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: "Segoe UI", sans-serif;
      background: linear-gradient(to right, var(--bg), #00f2fe);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
      color: var(--text);
      transition: 0.3s ease;
      position: relative;
    }

    .status-box {
      background-color: var(--card);
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
      text-align: center;
      max-width: 400px;
      width: 100%;
      animation: fadeIn 1s ease;
    }

    .status-box h2 {
      font-size: 1.8rem;
      margin-bottom: 10px;
    }

    .queue-number {
      font-size: 3.5rem;
      font-weight: bold;
      color: var(--accent);
      margin: 20px 0;
    }

    .current-number, .details {
      font-size: 1.1rem;
      margin: 10px 0;
    }

    .spinner {
      border: 4px solid #f3f3f3;
      border-top: 4px solid var(--accent);
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
      margin: 20px auto;
    }

    .theme-icon {
      position: absolute;
      top: 20px;
      right: 20px;
      font-size: 1.5rem;
      background: none;
      border: none;
      color: var(--accent);
      cursor: pointer;
      transition: 0.2s;
    }

    .theme-icon:hover {
      transform: scale(1.2);
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 480px) {
      .status-box {
        padding: 30px 20px;
      }

      .queue-number {
        font-size: 3rem;
      }

      .theme-icon {
        font-size: 1.3rem;
        top: 15px;
        right: 15px;
      }
    }
  </style>
</head>
<body>
  <button onclick="toggleTheme()" class="theme-icon" id="themeToggle">🌙</button>

  <div class="status-box">
    <h2>Your Queue Number</h2>
    <div class="queue-number"><?= $userQueue ?></div>

    <div class="current-number">
      Currently Serving: <strong><?= $currentNumber ?></strong>
    </div>

    <div class="details">
      People Ahead: <strong><?= $peopleAhead ?></strong><br/>
      Estimated Wait: <strong><?= $estimatedWait ?> min</strong>
    </div>

    <div class="spinner"></div>

    <p class="details">
      Please wait silently. This page updates every 10 seconds.
    </p>

    <audio id="ding" src="notify.mp3" preload="auto"></audio>
  </div>

<script>
const currentResetCount = <?= json_encode((int)$currentResetCount) ?>;
const storedResetCount = localStorage.getItem('resetCount');

// 🔁 Reset localStorage if resetCount changed
if (storedResetCount !== currentResetCount.toString()) {
  console.log("🔄 Queue reset detected. Clearing localStorage...");
  localStorage.clear();
  localStorage.setItem('resetCount', currentResetCount);
  location.reload(); // Force reload for fresh session
}

const userNum = <?= json_encode((int)$userQueue) ?>;
const currentNum = <?= json_encode((int)$currentNumber) ?>;
const ding = document.getElementById("ding");
const notifiedKey = "notified_" + userNum;

function toggleTheme() {
  const html = document.documentElement;
  html.dataset.theme = html.dataset.theme === "dark" ? "light" : "dark";
  localStorage.setItem("theme", html.dataset.theme);
  document.getElementById("themeToggle").innerText =
    html.dataset.theme === "dark" ? "☀️" : "🌙";
}

async function tryNotify() {
  console.log("🔔 Checking if it's user's turn...");

  if (userNum === currentNum && userNum !== 0 && !localStorage.getItem(notifiedKey)) {
    console.log("✅ It's your turn!");

    // 🔔 Fallback: show in-page alert + sound
    alert("🎉 It's your turn!");
    ding.play().catch(() => {
      console.warn("🔕 Audio blocked. Tap to enable.");
    });

    // ✅ Trigger actual push notification to this user's subscription
    try {
      await fetch('send_push.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ message: "🎉 It's your turn! Please proceed to the counter." })
      });
      console.log("📤 Push notification sent from browser trigger");
    } catch (err) {
      console.error("❌ Failed to send push:", err);
    }

    // Mark as notified
    localStorage.setItem(notifiedKey, "yes");
  } else {
    console.log("⏳ Not your turn or already notified.");
  }
}

window.onload = () => {
  // 🌙 Theme setup
  const savedTheme = localStorage.getItem("theme") || "light";
  document.documentElement.dataset.theme = savedTheme;
  document.getElementById("themeToggle").innerText =
    savedTheme === "dark" ? "☀️" : "🌙";

  // 🧹 Clean up old notification keys
  for (let key in localStorage) {
    if (key.startsWith("notified_") && key !== notifiedKey) {
      localStorage.removeItem(key);
    }
  }

  // 🔐 Unlock sound & notifications on first user interaction
  document.body.addEventListener("click", () => {
    ding.play().catch(() => {});
    if (Notification.permission === "default") {
      Notification.requestPermission();
    }
  }, { once: true });

  tryNotify();

  // 🔄 Refresh every 5 seconds
  setInterval(() => location.reload(), 5000);
};
</script>
</body>
</html>
