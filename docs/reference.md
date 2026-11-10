# Endpoint reference

All endpoints are `GET`, return UTF-8 JSON and require no authentication.

**Base URL:** `https://kopolart.ir`

| Endpoint | Purpose | Rate limit | Server cache |
| :------- | :------ | :--------- | :----------- |
| `/api/color/{hex}` | Full color profile | 25 req/min | 24 hours |
| `/api/complementary-color/{hex}` | Complement + analogous alternative | 25 req/min | 24 hours |
| `/api/similar-colors/{hex}` | Up to 3 nearest colors | — | 7 days |
| `/api/similar-palettes/{hex}` | Up to 3 nearest palettes | — | 7 days |
| `/api-color/{hex}` | Compact legacy profile | 25 req/min | 24 hours |

## Path parameter: `hex`

| Rule | Detail |
| :--- | :----- |
| Length | 3 or 6 hexadecimal digits |
| Case | Case-insensitive |
| Prefix | Optional `#`; in a URL encode it as `%23` |
| Example | `ff0000`, `FF0000`, `f00`, `%23ff0000` |
| Invalid | `400` with a ProblemDetails JSON body |

> The rate limit is a sliding window of **25 requests per minute per IP**,
> partitioned per endpoint. Discovery endpoints have no dedicated limiter and are
> cached for a week.

---

## 1. `GET /api/color/{hex}`

Returns the complete color profile.

### Example

```bash
curl -sS "https://kopolart.ir/api/color/ff0000"
```

```json
{
  "color": "#FF0000",
  "persianName": "قرمز",
  "englishName": "Red",
  "rgb": { "r": 255, "g": 0, "b": 0 },
  "hsl": { "h": 0, "s": 100, "l": 50 },
  "cmyk": { "c": 0, "m": 100, "y": 100, "k": 0 },
  "wavelength": 611.3,
  "purity": 91.7,
  "brightness": 21.3
}
```

### Fields

| Field | Type | Range / format | Description |
| :---- | :--- | :------------- | :---------- |
| `color` | string | `#RRGGBB`, uppercase | Normalized input color. |
| `persianName` | string | Free text | Persian name; may be empty if no catalog match exists. |
| `englishName` | string | Free text | English name; may be empty if no catalog match exists. |
| `rgb.r` / `rgb.g` / `rgb.b` | integer | `0–255` | RGB channels. |
| `hsl.h` | number | `0–360` | Hue in degrees. |
| `hsl.s` / `hsl.l` | number | `0–100` | Saturation and lightness in percent. |
| `cmyk.c` / `m` / `y` / `k` | number | `0–100` | CMYK channels in percent. |
| `wavelength` | number | `0–780`, one decimal | Dominant wavelength in nm; `0` for achromatic colors. |
| `purity` | number | `0–100`, one decimal | Excitation purity in percent. |
| `brightness` | number | `0–100`, one decimal | Relative luminance in percent. |

### Naming behavior

Names are matched against a curated catalog. An exact HEX match wins. Otherwise
the perceptually nearest catalog color is used; if the distance is large, a
descriptive adjective is appended:

| Persian | English | Typical condition |
| :------ | :------ | :---------------- |
| کم‌رنگ | Pale | Very light, low saturation |
| پاستلی | Pastel | Light, medium saturation |
| سیر | Deep | Dark and saturated |
| مات | Muted | Low saturation |
| زنده | Vivid | High saturation |
| درخشان | Bright | Noticeably more saturated than the match |
| تیره | Dark | Noticeably darker than the match |
| روشن | Light | Noticeably lighter than the match |

### Edge cases

- **Greys, white, black:** `wavelength` is `0` because there is no dominant wavelength.
- **Purple-line colors:** the wavelength magnitude is reported as a positive number.
- **Empty names:** possible only when the catalog has no usable entry.

---

## 2. `GET /api/complementary-color/{hex}`

Returns the complementary color from the catalog plus a nearest analogous
alternative, with comparison metrics for both.

### Example

```bash
curl -sS "https://kopolart.ir/api/complementary-color/3366cc"
```

```json
{
  "compHex": "f8b500",
  "compName": "زرد طلایی",
  "compNameEn": "Golden Yellow",
  "compNearHex": "40a2e3",
  "compNearName": "آبی فیروزه‌ای",
  "compNearNameEn": "Turquoise Blue",
  "compDistance": "80.9",
  "compContrast": "14.1",
  "compMix": "#958E66",
  "compTemp": "گرم",
  "compTempVal": "44",
  "nearDistance": "20.6",
  "nearContrast": "9.1",
  "nearMix": "#3A84D8",
  "nearTemp": "سرد",
  "nearTempVal": "204"
}
```

### Fields

| Field | Type | Description |
| :---- | :--- | :---------- |
| `compHex` | string | Complementary HEX, 6 digits, no `#`. |
| `compName` / `compNameEn` | string | Persian / English name. |
| `compNearHex` | string | Analogous alternative HEX, 6 digits, no `#`. |
| `compNearName` / `compNearNameEn` | string | Names of the alternative. |
| `compDistance` | string | Perceptual distance from the input, normalized to `0–100`. |
| `compContrast` | string | Contrast score, normalized to `0–100`. |
| `compMix` | string | 50/50 RGB mix of input and complement, with `#`. |
| `compTemp` | string | `گرم` (warm), `سرد` (cool), `میانه` (mid) or `خنثی` (achromatic). |
| `compTempVal` | string | Hue angle in degrees as a string, or `خنثی`. |
| `nearDistance` | string | Distance for the analogous alternative. |
| `nearContrast` | string | Contrast for the analogous alternative. |
| `nearMix` | string | Mix of input and analogous alternative. |
| `nearTemp` | string | Temperature label for the analogous alternative. |
| `nearTempVal` | string | Hue angle for the analogous alternative, or `خنثی`. |

### Reading the pair

- `comp*` is the **true complement** — roughly opposite on the color wheel.
- `compNear*` / `near*` is an **analogous alternative** — closer to the input and
  often more usable in real palettes.
- Distance and contrast are strings intentionally; parse them as floats.

---

## 3. `GET /api/similar-colors/{hex}`

Returns up to three catalog colors nearest to the input, ordered by perceptual
closeness.

### Example

```bash
curl -sS "https://kopolart.ir/api/similar-colors/ff0000"
```

```json
[
  {
    "span": "FF1818",
    "title": "رنگ قرمز (Red)",
    "image": "/images/color-swatches/thumbnails/red-color-ff1818.jpg",
    "url": "/colors/red-color-ff1818",
    "captionImage": "رنگ قرمز با کد FF1818",
    "dateTime": "0001-01-01T00:00:00"
  }
]
```

### Fields

| Field | Type | Description |
| :---- | :--- | :---------- |
| `span` | string | Uppercase HEX of the suggested color. |
| `title` | string | Persian title, usually with the English name in parentheses. |
| `image` | string | Relative path; prepend `https://kopolart.ir`. |
| `url` | string | Relative page path; prepend `https://kopolart.ir`. |
| `captionImage` | string | Persian caption / alt text. |
| `dateTime` | string | Reserved; currently the default .NET value. |

The array can contain fewer than three items if the catalog cannot supply more.

---

## 4. `GET /api/similar-palettes/{hex}`

Returns up to three palettes nearest to the supplied color or colors.

### Input

A single HEX value: `/api/similar-palettes/ff0000`.

Comma-separated sequences are rejected with `400`; send one color per request.

If no palette is close enough, a set of popular palettes is returned instead.

### Example

```bash
curl -sS "https://kopolart.ir/api/similar-palettes/ff0000"
```

```json
[
  {
    "span": "3,326",
    "title": "پالت رنگ نسکافه ای، قهوه‌ای، کرم و آبی نفتی",
    "image": "/images/palettes/thumbnails/brown-petrol-blue-palette-865131.jpg",
    "url": "brown-petrol-blue-palette-865131",
    "captionImage": "پالت رنگ نسکافه ای، قهوه‌ای، کرمی و آبی نفتی",
    "dateTime": "0001-01-01T00:00:00"
  }
]
```

### Fields

| Field | Type | Description |
| :---- | :--- | :---------- |
| `span` | string | Palette copy count, formatted (e.g. `3,326`). |
| `title` | string | Persian palette title. |
| `image` | string | Relative path; prepend `https://kopolart.ir`. |
| `url` | string | Palette slug **without** a leading slash. Prepend `https://kopolart.ir/`. |
| `captionImage` | string | Persian caption / alt text. |
| `dateTime` | string | Reserved; currently the default .NET value. |

> Build the page URL as `https://kopolart.ir/` + `url`. Do not add a second slash.

---

## 5. `GET /api-color/{hex}`

A compact, string-only profile kept for backward compatibility. Note the
different prefix: `/api-color`, with a hyphen.

### Example

```bash
curl -sS "https://kopolart.ir/api-color/f00"
```

```json
{
  "message1": "Pure Vibrant Red",
  "message2": "611",
  "message3": "91.7"
}
```

### Fields

| Field | Type | Description |
| :---- | :--- | :---------- |
| `message1` | string | English descriptive name of the nearest catalog color. |
| `message2` | string | Dominant wavelength in nm, as a string. |
| `message3` | string | Excitation purity in percent, as a string. |

For structured numbers, prefer [`/api/color/{hex}`](#1-get-apicolorhex).

---

## Response headers

Successful responses use:

```http
Content-Type: application/json; charset=utf-8
```

The API does not expose rate-limit counters in headers. Treat `429` as the only
signal that you are over the limit.

Error payloads are not uniform: `400` returns ProblemDetails JSON, while `429`
and `500` return the website's HTML error page.

## Related

- [Errors, rate limits and caching](./errors-and-limits.md)
- [FAQ](./faq.md)
- [OpenAPI spec](../openapi.yaml)
