<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Silent Queue</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Segoe UI", sans-serif;
    }

    body {
      background: linear-gradient(to right, #4facfe, #00f2fe);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .container {
      background-color: white;
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
      text-align: center;
      max-width: 400px;
      width: 100%;
      animation: fadeIn 1s ease;
    }

    .container h1 {
      font-size: 2rem;
      margin-bottom: 10px;
      color: #333;
    }

    .container p {
      color: #666;
      font-size: 1rem;
      margin-bottom: 30px;
    }

    .join-btn {
      background-color: #00c9a7;
      color: white;
      border: none;
      padding: 15px 30px;
      border-radius: 50px;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .join-btn:hover {
      background-color: #00a78e;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 480px) {
      .container {
        padding: 30px 20px;
      }

      .container h1 {
        font-size: 1.6rem;
      }

      .join-btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Welcome to Silent Queue</h1>
    <p>No need to shout. Tap the button below to join the queue silently.</p>
    <button class="join-btn" onclick="joinQueue()">Join Queue</button>
  </div>

  <script>
const publicVapidKey = 'BCk3-0-nDCS9RnVOfZRCu57X0R0fIDF27a8cwUAQP_WeX3a4ia4VNB8jaH7zOrIqVBmWkJlhYw11sArOhT73Q7E';

function joinQueue() {
  // Redirect to queue_status.php
  window.location.href = "queue_status.php";
}

// Register service worker and subscribe to push
if ('serviceWorker' in navigator && 'PushManager' in window) {
  registerPush();
} else {
  console.warn("Push notifications are not supported.");
}

async function registerPush() {
  try {
    const register = await navigator.serviceWorker.register('service-worker.js');
    console.log("✅ Service Worker registered");

    const permission = await Notification.requestPermission();
    if (permission !== "granted") {
      alert("Notification permission is required to proceed.");
      return;
    }

    const subscription = await register.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(publicVapidKey)
    });

    await fetch('save_subscription.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(subscription)
    });

    console.log("📬 Subscription sent to server");
  } catch (err) {
    console.error("❌ Push registration failed:", err);
  }
}

// Utility function
function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
  const rawData = atob(base64);
  return Uint8Array.from([...rawData].map(char => char.charCodeAt(0)));
}
</script>
</body>
</html>
