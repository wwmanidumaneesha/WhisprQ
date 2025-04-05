<?php
session_start();
if (!isset($_SESSION['current'])) {
  $_SESSION['current'] = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Notification Debug</title>
  <style>
    body {
      font-family: sans-serif;
      background: #f0f0f0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    h1 {
      font-size: 2.5rem;
    }

    .number {
      font-size: 6rem;
      margin: 20px 0;
      color: #00c9a7;
    }

    button {
      padding: 10px 20px;
      font-size: 1rem;
      background: #00c9a7;
      border: none;
      color: white;
      border-radius: 8px;
      cursor: pointer;
    }

    button:hover {
      background: #009f8a;
    }
  </style>
</head>
<body>
  <h1>Simulated Queue</h1>
  <div class="number" id="counter">0</div>
  <button onclick="manualPlay()">▶ Play Sound Test</button>
  <audio id="ding" src="notify.mp3" preload="auto"></audio>

  <script>
    let count = 0;
    const counter = document.getElementById("counter");
    const ding = document.getElementById("ding");
    const notifiedKey = "debug_notified";

    // Ask notification permission on load
    window.onload = () => {
      if (Notification.permission !== "granted") {
        Notification.requestPermission();
      }
    };

    // Simulate counter increase every 5s
    setInterval(() => {
      count++;
      counter.innerText = count;

      if (count === 5 && !localStorage.getItem(notifiedKey)) {
        tryNotify();
      }
    }, 5000);

    function tryNotify() {
      console.log("🔔 Trying to notify at count 5...");

      // 1. Sound
      ding.play().then(() => {
        console.log("✅ Sound played.");
      }).catch(err => {
        console.warn("⚠️ Sound blocked, waiting for user interaction:", err);
        document.addEventListener("click", () => {
          ding.play();
          console.log("🔊 Played after click.");
        }, { once: true });
      });

      // 2. Browser Notification
      if (Notification.permission === "granted") {
        new Notification("🎉 It's your turn!", {
          body: "Now serving number 5",
          icon: "notify.png" // Optional icon
        });
      }

      // 3. Alert fallback
      alert("🎉 It's your turn!");

      localStorage.setItem(notifiedKey, "yes");
    }

    // Manual test
    function manualPlay() {
      ding.play();
    }
  </script>
</body>
</html>
