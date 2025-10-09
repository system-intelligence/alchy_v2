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
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
        wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
        wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
        wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    // Attach X-Socket-Id so server-side broadcast()->toOthers() works and add simple diagnostics
    try {
        const setSocketHeader = () => {
            const id = typeof window.Echo.socketId === 'function' ? window.Echo.socketId() : null;
            if (id && window.axios) {
                window.axios.defaults.headers.common['X-Socket-Id'] = id;
            }
        };

        // Pusher connection lifecycle
        const pusher = window.Echo?.connector?.pusher;
        if (pusher?.connection) {
            pusher.connection.bind('connected', () => {
                setSocketHeader();
                if (console && console.info) console.info('[Echo] connected', window.Echo.socketId());
            });
            pusher.connection.bind('state_change', (states) => {
                if (console && console.debug) console.debug('[Echo] state', states?.current, '=>', states?.previous);
            });
            pusher.connection.bind('error', (err) => {
                if (console && console.error) console.error('[Echo] error', err);
            });
            // If already connected when this runs
            setSocketHeader();
        }
    } catch (_) {
        // no-op
    }
}
