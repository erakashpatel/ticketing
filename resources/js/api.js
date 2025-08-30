export const apiBase = '/api/v1';

export async function apiFetch(endpoint, options = {}) {
  const token = localStorage.getItem('token');

  const defaultHeaders = {
    'Accept': 'application/json',
    ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
    ...(options.headers || {}),
  };

  const url =
    endpoint.startsWith('/login') || endpoint.startsWith('/logout')
      ? `/api${endpoint}`
      : `${apiBase}${endpoint}`;

  const res = await fetch(url, {
    ...options,
    headers: defaultHeaders,
  });

  const data = await res.json();

  if (!res.ok) {
    throw new Error(data.message || 'API request failed');
  }

  return data;
}
