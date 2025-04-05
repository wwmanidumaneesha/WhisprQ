<?php
// save_subscription.php

// Allow cross-origin if testing locally
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

// DB connection
$host = "localhost";
$dbname = "whisprq_db";
$user = "root";
$pass = "Manidu@2005";

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(["error" => "Database connection failed"]);
  exit();
}

// Get the raw POST body
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!$data) {
  http_response_code(400);
  echo json_encode(["error" => "Invalid JSON"]);
  exit();
}

$endpoint = $data['endpoint'] ?? '';
$publicKey = $data['keys']['p256dh'] ?? '';
$authToken = $data['keys']['auth'] ?? '';
$contentEncoding = $data['contentEncoding'] ?? 'aes128gcm'; // default fallback

if (!$endpoint || !$publicKey || !$authToken) {
  http_response_code(400);
  echo json_encode(["error" => "Missing subscription fields"]);
  exit();
}

// Check if already exists
$stmt = $pdo->prepare("SELECT id FROM push_subscriptions WHERE endpoint = ?");
$stmt->execute([$endpoint]);

if ($stmt->fetch()) {
  echo json_encode(["message" => "Already subscribed"]);
} else {
  $insert = $pdo->prepare("INSERT INTO push_subscriptions (endpoint, public_key, auth_token, content_encoding) VALUES (?, ?, ?, ?)");
  $insert->execute([$endpoint, $publicKey, $authToken, $contentEncoding]);

  echo json_encode(["message" => "Subscription saved"]);
}
?>
