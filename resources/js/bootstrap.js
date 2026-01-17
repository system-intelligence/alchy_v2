import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Only initialize Echo if Pusher credentials are available
if (import.meta.env.VITE_PUSHER_APP_KEY) {
    try {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'ap1',
            wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'ap1'}.pusher.com`,
            wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
            wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
            forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
            disableStats: false,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            }
        });


        // Attach X-Socket-Id so server-side broadcast()->toOthers() works
        const setSocketHeader = () => {
            const id = typeof window.Echo.socketId === 'function' ? window.Echo.socketId() : null;
            if (id && window.axios) {
                window.axios.defaults.headers.common['X-Socket-Id'] = id;
            }
        };

        // Pusher connection lifecycle events
        const pusher = window.Echo?.connector?.pusher;
        if (pusher?.connection) {
            pusher.connection.bind('connected', () => {
                setSocketHeader();
            });

            pusher.connection.bind('connecting', () => {
            });

            pusher.connection.bind('disconnected', () => {
            });

            pusher.connection.bind('unavailable', () => {
            });

            pusher.connection.bind('failed', () => {
            });

            pusher.connection.bind('state_change', (states) => {
            });

            pusher.connection.bind('error', (err) => {
            });

            // If already connected when this runs
            if (pusher.connection.state === 'connected') {
                setSocketHeader();
            }
        }

        // Global Echo error handler
        window.Echo.connector.pusher.bind('pusher:error', (err) => {
        });

        // Log successful subscription to channels
        if (window.Echo.connector.pusher.channels) {
            window.Echo.connector.pusher.bind('pusher:subscription_succeeded', (data) => {
            });

            window.Echo.connector.pusher.bind('pusher:subscription_error', (err) => {
            });
        }

    } catch (error) {
    }
} else {
}
