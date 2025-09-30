const API_BASE_URL = '/Web/controller/';

async function apiFetch(url, options = {}) {
  options.method = options.method || 'GET';

  // attach CSRF for object bodies
  if (options.body && !(options.body instanceof FormData)) {
    if (!options.body.csrf_token) {
      const csrf = localStorage.getItem('csrf_token');
      if (csrf) options.body.csrf_token = csrf;
    }
    options.body = JSON.stringify(options.body);
    options.headers = {
      'Content-Type': 'application/json',
      ...options.headers,
    };
  } else if (options.body instanceof FormData) {
    if (!options.body.has('csrf_token')) {
      const csrf = localStorage.getItem('csrf_token');
      if (csrf) options.body.append('csrf_token', csrf);
    }
  }

  try {
    let endpoint = `${url}.php`;
    if (url.includes('?')) {
      const [path, query] = url.split('?');
      endpoint = `${path}.php?${query}`;
    }

    const res = await fetch(`${API_BASE_URL}${endpoint}`, options);

    // read body as text (some errors are non-json)
    const text = await res.text();
    let data = null;
    try {
      data = text ? JSON.parse(text) : null;
    } catch (e) {
      data = null;
    }

    if (!res.ok) {
      // log server response for easier debugging
      console.error('API non-OK response', res.status, text);
      // if server returned JSON, return it; otherwise return text fallback
      return data ?? { success: false, message: `Server error ${res.status}: ${text}` };
    }

    return data ?? { success: false, message: 'Invalid JSON response from server' };
  } catch (err) {
    console.error('Fetch failed', err);
    return { success: false, message: `Network/API Error: ${err.message}` };
  }
}
