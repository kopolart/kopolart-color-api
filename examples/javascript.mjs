// Kopolart Color API example — Node.js 18+ (global fetch).
// Usage: node javascript.mjs [hex]

const BASE_URL = process.env.KOPOLART_API_BASE ?? "https://kopolart.ir";
const hex = process.argv[2] ?? "ff0000";

async function getJson(path) {
  const response = await fetch(`${BASE_URL}${path}`);

  if (!response.ok) {
    const hint =
      response.status === 400
        ? "Invalid HEX value"
        : response.status === 429
          ? "Rate limited; retry in about a minute"
          : "Unexpected error";

    throw new Error(`${hint} (HTTP ${response.status}) for ${path}`);
  }

  return response.json();
}

const color = await getJson(`/api/color/${encodeURIComponent(hex)}`);

console.log(`${color.persianName} / ${color.englishName}`);
console.log("HEX:", color.color);
console.log("RGB:", color.rgb);
console.log("HSL:", color.hsl);
console.log("CMYK:", color.cmyk);
console.log("Wavelength:", color.wavelength, "nm");
console.log("Purity:", color.purity, "%");
console.log("Brightness:", color.brightness, "%");

const [complementary, similar] = await Promise.all([
  getJson(`/api/complementary-color/${encodeURIComponent(hex)}`),
  getJson(`/api/similar-colors/${encodeURIComponent(hex)}`),
]);

console.log("\nComplementary:", complementary.compNameEn, complementary.compHex);
console.log("Similar:", similar.map((item) => item.span).join(", "));
