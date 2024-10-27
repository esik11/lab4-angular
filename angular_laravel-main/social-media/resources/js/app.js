import './bootstrap';
import Alpine from 'alpinejs';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Alpine.js initialization
window.Alpine = Alpine;
Alpine.start();

// Laravel Echo and Pusher configuration
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true
});

// Listening to the 'notifications' channel for the 'notification.sent' event
window.Echo.channel('notifications')
    .listen('.notification.sent', (event) => {
        console.log('Notification received: ', event.notification);
        // Here you can implement the logic to display notifications in real-time
    });
