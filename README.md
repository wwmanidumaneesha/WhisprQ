
# 🎧 WhisprQ – The Silent Queueing System

> ✨ A smart, modern queue management system that whispers instead of shouting.

WhisprQ is a **browser-based silent queueing platform** designed to enhance user experience in places like clinics, banks, or government offices. It replaces loud number calls with **push notifications and sound alerts**, making queues less stressful and more efficient.

---

## 🚀 Features

- 📲 One-tap join – no app needed  
- 🔄 Real-time queue status with auto-refresh  
- 🔔 Web Push Notifications (even when browser is closed)  
- 🔉 Sound alert when it’s your turn  
- 🌙 Toggle between light and dark mode  
- 🧹 Auto session cleanup and reset detection  

---

## 🧰 Tech Stack

| Frontend      | Backend      | Other Tools              |
|---------------|--------------|---------------------------|
| HTML & CSS    | PHP          | Service Workers           |
| JavaScript    | MySQL        | VAPID (Web Push Protocol) |

---

## 🛠️ Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/WhisprQ.git
   cd WhisprQ
   ```

2. **Import the MySQL database**
   - Use `whisprq_db.sql` file (or export your own from phpMyAdmin)

3. **Generate and configure VAPID keys**
   - Generate using:
     ```bash
     npx web-push generate-vapid-keys
     ```
   - Set public/private keys in `send_push.php`

4. **Run using XAMPP or any PHP server**

5. **Test via HTTPS**
   - Required for push to work properly
   - Use [Ngrok](https://ngrok.com/)
     ```bash
     ngrok http 80
     ```

---

## 🌐 Live Demo

Coming soon...

---

## 📄 License

MIT License © 2025 [Manidu](https://github.com/wwmanidumaneesha)

---

## 💡 Future Enhancements

- 📱 Add PWA/mobile app support  
- 📊 Admin dashboard to monitor & control queues  
- 🔍 QR Code-based queue entry  
- ☁️ Firebase integration for cross-device push  

---

## 🙏 Acknowledgements

- [Minishlink/web-push](https://github.com/web-push-libs/web-push-php)  
- [MDN Docs](https://developer.mozilla.org/) for service workers & push

---

### 🤍 Built with peace and quiet in mind.
