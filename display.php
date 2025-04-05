<?php
// Fetch the latest "called" number from the database
$host = "localhost";
$dbname = "whisprq_db";
$user = "root";
$pass = "Manidu@2005"; // Update if needed

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  $stmt = $pdo->query("SELECT id FROM queue WHERE status = 'called' ORDER BY id DESC LIMIT 1");
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
  $currentNumber = $data ? $data['id'] : 'Not started';

} catch (PDOException $e) {
  $currentNumber = 'Error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Now Serving - WhisprQ</title>
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
      color: #fff;
      text-align: center;
    }

    .display-box {
      background: rgba(255, 255, 255, 0.1);
      padding: 60px;
      border-radius: 30px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
      animation: fadeIn 1s ease;
    }

    .display-box h1 {
      font-size: 2.5rem;
      margin-bottom: 30px;
    }

    .current-number {
      font-size: 6rem;
      font-weight: bold;
      background: #fff;
      color: #00c9a7;
      padding: 20px 60px;
      border-radius: 30px;
      box-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }

    @media (max-width: 600px) {
      .display-box {
        padding: 30px;
      }

      .display-box h1 {
        font-size: 1.8rem;
      }

      .current-number {
        font-size: 4rem;
        padding: 15px 40px;
      }
    }
  </style>
  <script>
    // Auto-refresh every 5 seconds to keep number updated
    setInterval(() => {
      window.location.reload();
    }, 5000);
  </script>
</head>
<body>
  <div class="display-box">
    <h1>Now Serving</h1>
    <div class="current-number"><?= $currentNumber ?></div>
  </div>
</body>
</html>
