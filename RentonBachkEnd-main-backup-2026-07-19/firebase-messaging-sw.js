importScripts('https://www.gstatic.com/firebasejs/6.3.4/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/6.3.4/firebase-messaging.js');
importScripts('firebase-config.js');

firebase.initializeApp(firebaseConfig);

const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  // Customize notification here
  const notificationTitle = payload.data.title;
  const notificationOptions = {
   body: payload.data.body,
  	icon: payload.data.icon,
  	image:  payload.data.image,
  	click_action: payload.data.link_action, // To handle notification click when notification is moved to notification tray
        data: {
            click_action: payload.data.link_action
        }
  };
  
  self.addEventListener('notificationclick', function(event) {
      console.log(event.notification.data.click_action);
  if (!event.action) {
    // Was a normal notification click
    console.log('Notification Click.');
    self.clients.openWindow(event.notification.data.click_action, '_blank')
    event.notification.close();
    return;
  }else{
      event.notification.close();
  }

});

  return self.registration.showNotification(notificationTitle,
      notificationOptions);
});
// [END background_handler]