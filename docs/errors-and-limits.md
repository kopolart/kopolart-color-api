# Errors, rate limits and caching

## Status codes

| Status | Meaning | Body | What to do |
| :----- | :------ | :--- | :--------- |
| `200 OK` | Success | JSON | Parse the payload. |
| `400 Bad Request` | Missing or invalid HEX value. | ProblemDetails JSON | Validate the input: 3 or 6 hex digits, optional `#`. |
| `429 Too Many Requests` | Rate limit exceeded. | Site HTML error page | Back off; retry after roughly a minute. |
| `500 Internal Server Error` | Unexpected server error. | Site error page | Retry with exponential backoff; report it if it persists. |

A `400` response carries a small ProblemDetails object:

```json
{
  "type": "https://tools.ietf.org/html/rfc9110#section-15.5.1",
  "title": "Bad Request",
  "status": 400,
  "traceId": "00-7f1f424232f4f6653689d6c924918dc0-174c508a684e2a67-00"
}
```

`429` and `500` responses are HTML from the website, not part of the JSON API
contract. Always inspect the HTTP status code before calling `response.json()`.

### Examples of invalid input

| Input | Result |
| :---- | :----- |
| `zzzzzz` | `400` |
| `ff00` (4 digits) | `400` |
| `ff0000,00ff00` (sequence) | `400` — one color per request |
| empty path segment | `400` |
| `ff0000ff` (8 digits, RGBA) | `400` |
| `red` (CSS name) | `400` |

## Rate limits

| Endpoint | Limit | Window | Scope |
| :------- | :---- | :----- | :---- |
| `/api/color/{hex}` | 25 requests | 60 seconds | Per IP + endpoint |
| `/api/complementary-color/{hex}` | 25 requests | 60 seconds | Per IP + endpoint |
| `/api-color/{hex}` | 25 requests | 60 seconds | Per IP + endpoint |
| `/api/similar-colors/{hex}` | No dedicated limit | — | — |
| `/api/similar-palettes/{hex}` | No dedicated limit | — | — |

Implementation notes:

- The limiter is a **sliding window**, not a fixed bucket.
- Partitions are keyed by client IP, HTTP method and route, so traffic to one
  endpoint does not consume another endpoint's budget.
- Exceeding the budget returns `429` with the website's HTML error page. No
  rate-limit headers are exposed, so do not attempt to read remaining quota.

### Recommended backoff

```js
async function fetchWithRetry(url, attempts = 3) {
  for (let attempt = 1; attempt <= attempts; attempt++) {
    const response = await fetch(url);

    if (response.status !== 429) return response;

    const delayMs = 60_000 * attempt; // 1 min, 2 min, 3 min
    await new Promise((resolve) => setTimeout(resolve, delayMs));
  }

  throw new Error("Rate limited after repeated attempts");
}
```

Avoid tight retry loops. If you need bulk conversions, convert once and persist
the result — a color's profile never changes.

## Caching

| Endpoint | Server cache | Cache varies by |
| :------- | :----------- | :-------------- |
| `/api/color/{hex}` | 24 hours | HEX value |
| `/api/complementary-color/{hex}` | 24 hours | HEX value |
| `/api-color/{hex}` | 24 hours | HEX value |
| `/api/similar-colors/{hex}` | 7 days | HEX value |
| `/api/similar-palettes/{hex}` | 7 days | HEX value |

Because responses are immutable for a given input, you are encouraged to cache
them aggressively on your side:

- **Static sites:** fetch at build time and ship the JSON with your assets.
- **Backends:** use your existing HTTP cache or a key-value store.
- **Native apps:** persist profiles locally; they do not change.

## CORS

The API does not currently send `Access-Control-Allow-Origin` headers. Browsers
will block direct cross-origin `fetch` calls, even though the endpoint responds
correctly to server-side clients.

Workarounds:

1. Call the API from your backend and return the result to the browser.
2. Use a serverless function or reverse proxy on your own domain.
3. Precompute values at build time.

If your product needs first-class browser access, open an issue in this
repository.

## Etiquette

- Cache aggressively; do not re-request the same color or palette.
- Identify your application with a descriptive `User-Agent` when possible.
- Do not bulk-mirror the catalog or resell the data as a competing API.
- Link back to [kopolart.ir](https://kopolart.ir) when displaying the data publicly.
