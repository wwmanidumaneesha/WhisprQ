// service-worker.js

self.addEventListener('push', function(event) {
    const data = event.data.json();
  
    const options = {
      body: data.body,
      icon: 'notify.png', // Optional icon
      badge: 'notify.png', // Optional badge
    };
  
    event.waitUntil(
      self.registration.showNotification(data.title, options)
    );
  });
  