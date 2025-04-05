<?php
$hash = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $password = $_POST["password"] ?? "";

  if (!empty($password)) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>BCrypt Password Generator</title>
</head>
<body>
  <h2>Generate BCrypt Password Hash</h2>
  <form method="POST">
    <label>Enter Password:</label><br>
    <input type="text" name="password" required><br><br>
    <input type="submit" value="Generate Hash">
  </form>

  <?php if ($hash): ?>
    <p><strong>Hashed Password:</strong></p>
    <textarea rows="3" cols="80" readonly><?= htmlspecialchars($hash) ?></textarea>
  <?php endif; ?>
</body>
</html>
