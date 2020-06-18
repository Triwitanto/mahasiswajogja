self.addEventListener('install', (e) => {
    // console.log('👷', 'install', e);
    if (e.request.cache === 'only-if-cached' && e.request.mode !== 'same-origin') {
        return;
    }
    self.skipWaiting();
});

self.addEventListener('activate', (e) => {
    // console.log('👷', 'activate', e);
    return self.clients.claim();
});

self.addEventListener('fetch', function(e) {
    // console.log('👷', 'fetch', e);
    if (e.request.cache === 'only-if-cached' && e.request.mode !== 'same-origin') {
        return;
    }
    e.respondWith(fetch(e.request));
});