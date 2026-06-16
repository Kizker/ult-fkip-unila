/* Disable the legacy PWA worker and clean up its caches. */
self.addEventListener('install', () => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    try {
      const keys = await caches.keys();
      await Promise.all(
        keys
          .filter((key) => key.startsWith('ultfkip-'))
          .map((key) => caches.delete(key))
      );
    } catch (_) {
      // Ignore cache cleanup failures during worker retirement.
    }

    const clients = await self.clients.matchAll({
      type: 'window',
      includeUncontrolled: true,
    });

    await Promise.all(
      clients.map((client) => client.postMessage({ type: 'ULT_SW_DISABLED' }).catch(() => undefined))
    );

    await self.registration.unregister();
  })());
});
