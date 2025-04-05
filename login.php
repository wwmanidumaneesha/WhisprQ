<?php
session_start();
$error = "";

// ✅ Database connection
$host = "localhost";
$dbname = "whisprq_db";
$user = "root";
$pass = "Manidu@2005"; // Change if your root user has a password

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}

// ✅ Handle login form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = trim($_POST["username"] ?? "");
  $password = trim($_POST["password"] ?? "");

  $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
  $stmt->execute([$username]);
  $admin = $stmt->fetch();

  if ($admin && password_verify($password, $admin['password'])) {
    $_SESSION["admin_logged_in"] = true;
    header("Location: admin.php");
    exit();
  } else {
    $error = "Invalid username or password!";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login - WhisprQ</title>
  <style>
    body {
      background: linear-gradient(to right, #00c9a7, #92fe9d);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      font-family: "Segoe UI", sans-serif;
    }

    .login-box {
      background: white;
      padding: 40px 30px;
      border-radius: 15px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
      text-align: center;
      width: 90%;
      max-width: 400px;
    }

    .login-box h2 {
      margin-bottom: 25px;
      color: #00a78e;
    }

    .login-box input {
      width: 100%;
      padding: 12px 16px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 10px;
      font-size: 1rem;
      outline: none;
    }

    .login-box button {
      width: 100%;
      padding: 12px;
      background-color: #00c9a7;
      border: none;
      border-radius: 10px;
      color: white;
      font-size: 1rem;
      cursor: pointer;
    }

    .login-box button:hover {
      background-color: #009f8a;
    }

    .error {
      color: red;
      margin-bottom: 10px;
      font-size: 0.95rem;
    }
  </style>
</head>
<body>
  <form class="login-box" method="POST">
    <h2>Admin Login</h2>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <input type="text" name="username" placeholder="Username" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">Login</button>
  </form>
</body>
</html>
