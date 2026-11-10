# Quick start

No signup, no SDK and no API key. Send a GET request and parse JSON.

**Base URL:** `https://kopolart.ir`

## cURL

```bash
curl -sS "https://kopolart.ir/api/color/ff0000"
```

Pretty-print it if you have `jq`:

```bash
curl -sS "https://kopolart.ir/api/color/ff0000" | jq
```

## JavaScript (Node.js 18+)

```js
const BASE_URL = "https://kopolart.ir";
const hex = "ff0000";

const response = await fetch(`${BASE_URL}/api/color/${encodeURIComponent(hex)}`);
if (!response.ok) {
  throw new Error(`Kopolart API responded with HTTP ${response.status}`);
}

const color = await response.json();

console.log(`${color.persianName} / ${color.englishName}`);
console.log(`RGB: ${color.rgb.r}, ${color.rgb.g}, ${color.rgb.b}`);
console.log(`Wavelength: ${color.wavelength} nm`);
```

## Python 3

Uses only the standard library:

```python
import json
import urllib.request

BASE_URL = "https://kopolart.ir"
hex_value = "ff0000"

with urllib.request.urlopen(f"{BASE_URL}/api/color/{hex_value}", timeout=15) as response:
    color = json.load(response)

print(color["persianName"], "/", color["englishName"])
print("RGB:", color["rgb"])
print("HSL:", color["hsl"])
```

## PHP

```php
<?php

$url = "https://kopolart.ir/api/color/ff0000";
$json = file_get_contents($url, false, stream_context_create([
    "http" => ["timeout" => 15],
]));

$color = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

echo $color["persianName"] . " / " . $color["englishName"] . PHP_EOL;
echo "Wavelength: " . $color["wavelength"] . " nm" . PHP_EOL;
```

## Browser applications

The API does not currently send CORS headers. A browser `fetch` from a different
origin is blocked by the browser, even though the request works from a server.

Recommended patterns:

1. Call the API from your backend and expose the result to your frontend.
2. Proxy it through a serverless function on your own domain.
3. Cache the result at build time when colors are static.

## Handling errors

Non-2xx responses are not all JSON. `400` returns a small ProblemDetails object;
`429` returns the site's HTML error page. Check the status code:

- `400` — the HEX value is malformed (`zzzzzz`, empty, 4 digits).
- `429` — more than 25 requests in a minute from the same IP on that endpoint.
- `500` — transient server error; retry with backoff.

```js
const response = await fetch("https://kopolart.ir/api/color/not-a-color");

if (response.status === 400) {
  console.error("Invalid HEX value");
} else if (response.status === 429) {
  console.error("Rate limited — retry in about a minute");
} else if (!response.ok) {
  console.error(`Unexpected error: ${response.status}`);
}
```

## Accepted HEX formats

| Input | Accepted | Result |
| :---- | :------- | :----- |
| `ff0000` | Yes | Red |
| `FF0000` | Yes | Red |
| `f00` | Yes | Red (3-digit shorthand) |
| `%23ff0000` | Yes | Red (`#` percent-encoded) |
| `#ff0000` | In a URL, `#` starts the fragment | Use `%23ff0000` or omit `#` |
| `ff00` | No | `400` |
| `ff0000,00ff00` | No | `400` — one color per request |
| `red` | No | `400` |

## Next steps

- [Endpoint reference](./reference.md)
- [Errors, rate limits and caching](./errors-and-limits.md)
- [OpenAPI spec](../openapi.yaml)
