<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: login.php");
  exit();
}

// DB connection
$host = "localhost";
$dbname = "whisprq_db";
$user = "root";
$pass = "Manidu@2005"; // update if needed

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("DB connection failed: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (isset($_POST['next'])) {
    // Mark next 'waiting' person as 'called'
    $stmt = $pdo->prepare("SELECT id FROM queue WHERE status = 'waiting' ORDER BY id ASC LIMIT 1");
    $stmt->execute();
    $next = $stmt->fetch();

    if ($next) {
      $update = $pdo->prepare("UPDATE queue SET status = 'called' WHERE id = ?");
      $update->execute([$next['id']]);
    }
  } elseif (isset($_POST['reset'])) {
    // Truncate queue and increment reset count
    $pdo->exec("TRUNCATE TABLE queue");
    $pdo->exec("UPDATE cache_settings SET reset_count = reset_count + 1 WHERE id = 1");

    // Redirect to prevent form resubmission
    header("Location: admin.php");
    exit();
  }
}

// Get the current number being served
$currentStmt = $pdo->query("SELECT id FROM queue WHERE status = 'called' ORDER BY id DESC LIMIT 1");
$currentData = $currentStmt->fetch(PDO::FETCH_ASSOC);
$currentNumber = $currentData ? $currentData['id'] : 0;

// Get current reset count
$resetCount = $pdo->query("SELECT reset_count FROM cache_settings WHERE id = 1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Panel - WhisprQ</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    * {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      box-sizing: border-box;
    }

    body {
      background: linear-gradient(to right, #00c9a7, #92fe9d);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .admin-box {
      background: white;
      padding: 50px 40px 40px 40px;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
      text-align: center;
      width: 90%;
      max-width: 400px;
      position: relative;
      animation: fadeIn 0.6s ease;
    }

    .logout-btn {
      position: absolute;
      top: 15px;
      right: 15px;
      background-color: transparent;
      border: none;
      font-size: 1.2rem;
      color: #00c9a7;
      cursor: pointer;
      transition: color 0.3s ease;
    }

    .logout-btn:hover {
      color: #ff4d4d;
    }

    .admin-box h1 {
      font-size: 2rem;
      margin-bottom: 20px;
      color: #00a78e;
    }

    .current-number {
      font-size: 4rem;
      color: #00c9a7;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .reset-count {
      font-size: 0.95rem;
      color: #777;
      margin-bottom: 25px;
    }

    form button {
      background-color: #00c9a7;
      color: white;
      border: none;
      padding: 15px 30px;
      font-size: 1rem;
      border-radius: 10px;
      cursor: pointer;
      margin: 10px;
      transition: background-color 0.3s ease;
    }

    form button:hover {
      background-color: #009f8a;
    }

    .reset-btn {
      background-color: #ff4d4d;
    }

    .reset-btn:hover {
      background-color: #e03c3c;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
    }

    @media (max-width: 480px) {
      .admin-box {
        padding: 40px 20px;
      }

      form button {
        width: 100%;
        margin: 10px 0;
      }

      .logout-btn {
        top: 10px;
        right: 10px;
        font-size: 1rem;
      }
    }
  </style>
</head>
<body>
  <div class="admin-box">
    <form method="POST" style="position: absolute; top: 15px; right: 15px;">
      <a href="logout.php" class="logout-btn" title="Logout">
        <i class="fas fa-sign-out-alt"></i>
      </a>
    </form>

    <h1>Admin Panel</h1>
    <div class="current-number"><?= $currentNumber ?></div>
    <div class="reset-count">Reset Count: <?= $resetCount ?></div>

    <form method="POST">
      <button type="submit" name="next">Call Next</button>
      <button type="submit" name="reset" class="reset-btn">Reset Queue</button>
    </form>
  </div>
</body>
</html>
