import Echo from 'laravel-echo';
window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    wsHost: '192.168.4.1', // Raspberry Pi LAN IP
    wsPort: 6001,
    forceTLS: false,
    encrypted: false,
    disableStats: true,
});