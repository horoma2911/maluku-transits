document.addEventListener('DOMContentLoaded', () => {
  ThemeManager.init();
  Layout.init();
  Utils.hideLoader();

  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/pwa/service-worker.js')
      .then(() => console.log('Service Worker registered'))
      .catch((err) => console.warn('Service Worker registration failed:', err));
  }
});

if (typeof window !== 'undefined') {
  window.addEventListener('load', () => {
    const loader = document.querySelector('.loader-overlay');
    if (loader) loader.classList.add('hidden');
  });
}

// Global auth guard: protected pages require a valid session token.
// The token is set by pages/login.html on successful login.
(function guard() {
  var path = window.location.pathname;
  var onLogin = /(^|\/)login\.html$/.test(path) || path.endsWith('/login.html');
  if (onLogin) return;

  var token = sessionStorage.getItem('auth_token') || '';
  if (!token) {
    window.location.href = 'login.html';
  }
})();

