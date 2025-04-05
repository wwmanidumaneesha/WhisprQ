<?php
require __DIR__ . '/vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

// ✅ Your valid VAPID keys
$publicKey = 'BCk3-0-nDCS9RnVOfZRCu57X0R0fIDF27a8cwUAQP_WeX3a4ia4VNB8jaH7zOrIqVBmWkJlhYw11sArOhT73Q7E';
$privateKey = 'pLgbqElfpEoEylsTn6qIUMvytbqRZSZmH0Ya6NtcvBw';

// ✅ Setup DB
$pdo = new PDO("mysql:host=localhost;dbname=whisprq_db", "root", "Manidu@2005");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ✅ Get all push subscriptions
$stmt = $pdo->query("SELECT * FROM push_subscriptions");
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Configure WebPush with VAPID
$auth = [
    'VAPID' => [
        'subject' => 'mailto:manidumaneeshaww@gmail.com',
        'publicKey' => $publicKey,
        'privateKey' => $privateKey,
    ],
];
$webPush = new WebPush($auth);

// ✅ Queue notifications
foreach ($subscriptions as $sub) {
    $subscription = Subscription::create([
        'endpoint' => $sub['endpoint'],
        'publicKey' => $sub['public_key'],           // ✅ match DB column
        'authToken' => $sub['auth_token'],           // ✅ match DB column
        'contentEncoding' => $sub['content_encoding'] // ✅ match DB column
    ]);

    $payload = json_encode([
        'title' => '🎉 Your Turn!',
        'body' => 'Please proceed to the counter.',
        'icon' => 'notify.png'
    ]);

    $webPush->queueNotification($subscription, $payload);
}

// ✅ Send all notifications
foreach ($webPush->flush() as $report) {
    $endpoint = $report->getRequest()->getUri()->__toString();
    if ($report->isSuccess()) {
        echo "✅ Notification sent to: {$endpoint}\n";
    } else {
        echo "❌ Failed to send to {$endpoint}: {$report->getReason()}\n";
    }
}
?>
