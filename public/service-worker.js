/* Alchy Web Push Service Worker
 * Scope: /
 * Handles background push notifications on Windows/macOS/Linux.
 */

self.addEventListener('install', (event) => {
  // Activate worker immediately on install
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  // Become the active service worker for all clients as soon as possible
  event.waitUntil(self.clients.claim());
});

const focusOrOpen = async (url) => {
  try {
    const allClients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
    const normalized = (url || '/').replace(/\/+$/, '') || '/';

    // Try to focus an existing tab with same origin (and same path if possible)
    for (const client of allClients) {
      try {
        const clientUrl = new URL(client.url);
        if (clientUrl.origin === self.location.origin) {
          // If no URL given, just focus first matching origin. If given, prefer same path.
          if (!url || clientUrl.pathname.replace(/\/+$/, '') === normalized) {
            await client.focus();
            return client;
          }
        }
      } catch (_) {
        // ignore parse errors
      }
    }

    // Open new window if none focused
    return await self.clients.openWindow(url || '/');
  } catch (_) {
    return null;
  }
};

self.addEventListener('push', (event) => {
  if (!event.data) {
    return;
  }

  let payload = {};
  try {
    payload = event.data.json();
  } catch (err) {
    // Some user agents may send text payloads
    try {
      payload = JSON.parse(event.data.text());
    } catch (_) {
      payload = {};
    }
  }

  // Laravel WebPush default payload shape
  // {
  //   "title": "New message from ...",
  //   "body": "Message text",
  //   "icon": "https://.../icon.png",
  //   "badge": "https://.../badge.png",
  //   "tag": "chat-123",
  //   "data": {
  //     "url": "https://.../dashboard",
  //     "sender_id": 1,
  //     "recipient_id": 2,
  //     "message_id": 123
  //   }
  // }

  const title = payload.title || 'Notification';
  const options = {
    body: payload.body || '',
    icon: payload.icon || '/images/logos/alchy_logo.png',
    badge: payload.badge || undefined,
    image: payload.image || undefined,
    tag: payload.tag || 'alchy-chat',
    renotify: payload.renotify ?? true,
    silent: payload.silent ?? false,
    data: payload.data || {},
    actions: payload.actions || [
      { action: 'open', title: 'Open', icon: payload.icon || '/images/logos/alchy_logo.png' }
    ]
  };

  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const data = event.notification?.data || {};
  const targetUrl = data.url || '/';

  // Handle custom actions
  if (event.action === 'open') {
    event.waitUntil(focusOrOpen(targetUrl));
    return;
  }

  // Default click behavior: focus existing or open new
  event.waitUntil(focusOrOpen(targetUrl));
});

self.addEventListener('notificationclose', (_event) => {
  // noop; place for analytics if needed
});

// Optional: handle pushsubscriptionchange (when browser rotates keys)
self.addEventListener('pushsubscriptionchange', async (event) => {
  try {
    const registration = await self.registration.pushManager.subscribe(event.oldSubscription.options);
    // Typically you would POST registration.toJSON() to your server here to update the subscription
    // This requires authenticated API; leaving as a no-op to avoid unauthenticated calls.
  } catch (_) {
    // ignore
  }
});