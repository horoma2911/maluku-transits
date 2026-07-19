/**
 * Shared API client for the MALUKU LOGISTICS frontend.
 * Talks to the PHP backend at /api (same-origin when served by the backend).
 * Reads the auth token from localStorage/sessionStorage (set by login.html).
 */
const API = (function () {
  function base() {
    if (window.API_BASE) return window.API_BASE.replace(/\/$/, '');
    return window.location.origin + window.location.pathname
      .substring(0, window.location.pathname.lastIndexOf('/') + 1)
      .replace(/\/pages\/?$/, '/') + 'api';
  }

  function token() {
    return localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
  }

  function authHeaders(json) {
    const h = {};
    if (json) h['Content-Type'] = 'application/json';
    const t = token();
    if (t) h['Authorization'] = 'Bearer ' + t;
    return h;
  }

  async function request(method, path, body) {
    const opts = {
      method: method,
      headers: authHeaders(body !== undefined),
    };
    if (body !== undefined) {
      opts.body = typeof body === 'string' ? body : JSON.stringify(body);
    }
    const res = await fetch(base() + path, opts);
    let data = null;
    try { data = await res.json(); } catch (e) { data = null; }
    if (!res.ok) {
      const err = new Error((data && data.error) || ('Request failed (' + res.status + ')'));
      err.status = res.status;
      err.data = data;
      throw err;
    }
    return data;
  }

  return {
    base,
    token,
    isAuthed: function () { return !!token(); },
    get: (p) => request('GET', p),
    post: (p, b) => request('POST', p, b),
    put: (p, b) => request('PUT', p, b),
    del: (p) => request('DELETE', p),
    login: (email, password) => request('POST', '/auth/login', { email, password }),
  };
})();

if (typeof module !== 'undefined' && module.exports) {
  module.exports = API;
}
