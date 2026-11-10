# FAQ

### Is the API really free?

Yes. There is no key, account, billing or paid tier. The only constraint is the
published rate limit of 25 requests per minute per IP on color profile
endpoints.

### Do I need an API key?

No. Send a `GET` request and parse the JSON. There is no authentication header.

### Why is `#` in my request causing trouble?

In a URL, `#` starts the fragment and is not sent to the server. Use either:

```text
https://kopolart.ir/api/color/ff0000      ← recommended
https://kopolart.ir/api/color/%23ff0000   ← percent-encoded
```

### Does it accept 3-digit HEX?

Yes. `f00` is expanded to `#FF0000`. Both 3- and 6-digit values are accepted,
case-insensitively.

### Does it support alpha, RGBA or 8-digit HEX?

No. The API accepts only 3 or 6 hexadecimal digits.

### Is the response UTF-8? How do I read the Persian names?

Yes, responses are UTF-8 JSON with Persian text sent unescaped. Use any standard
JSON parser; do not apply a legacy encoding such as Windows-1256.

### Why is `wavelength` sometimes `0`?

Greys, white and black have no dominant wavelength. The API returns `0` instead
of a meaningless or `NaN` value.

### Why does `compTempVal` contain Persian text?

Achromatic colors have no hue, so instead of a degree value the field contains
`خنثی` ("neutral"). Always treat it as a string.

### What is the difference between `compHex` and `compNearHex`?

- `compHex` is the closest **complementary** color — roughly opposite on the
  color wheel.
- `compNearHex` is the closest **analogous alternative** — nearer to the input
  and often easier to combine in a real palette.

### Why are the similar-color `url` values relative?

They are intentionally relative so the same payload works on any hostname.
Prepend `https://kopolart.ir` to build absolute links.

For palettes, the `url` is a slug **without** a leading slash; build the page URL
as `https://kopolart.ir/` + `slug`.

### Why is `dateTime` always `0001-01-01T00:00:00`?

The field is reserved for future use and currently carries the default .NET date
value. Ignore it.

### Why is `persianName` or `englishName` empty?

The catalog is curated; a few colors have no usable name entry. The API still
returns all numeric color data.

### Why do I get `429`?

You exceeded 25 requests in a minute from the same IP on that endpoint. Wait
about a minute, cache results, and avoid retry storms. See
[errors and limits](./errors-and-limits.md).

### Can I call it from a browser?

Not directly from another origin: the API does not send CORS headers. Call it
from your server, a serverless function, or a proxy. See
[errors and limits](./errors-and-limits.md#cors).

### Can I use the data commercially?

Yes. Attribution to [kopolart.ir](https://kopolart.ir) is appreciated but not
required. The documentation in this repository is MIT-licensed.

### Is the server source code available?

No. This repository is documentation-only; the API implementation and database
are private.

### Can I self-host it?

There is no public server implementation to self-host. If you only need
conversions, most languages have color libraries; this API's value is the
bilingual naming, catalog matching and scientific attributes.

### How do I report a bug or request a feature?

Open an issue in this repository. For security vulnerabilities, follow
[SECURITY.md](../SECURITY.md) instead of opening a public issue.
