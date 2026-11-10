"""Kopolart Color API example — Python 3, standard library only.

Usage: python python.py [hex]
"""

import json
import os
import sys
import urllib.error
import urllib.request

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8")

BASE_URL = os.environ.get("KOPOLART_API_BASE", "https://kopolart.ir")
hex_value = sys.argv[1] if len(sys.argv) > 1 else "ff0000"


def get_json(path: str):
    url = f"{BASE_URL}{path}"
    try:
        with urllib.request.urlopen(url, timeout=15) as response:
            return json.load(response)
    except urllib.error.HTTPError as error:
        hints = {
            400: "Invalid HEX value",
            429: "Rate limited; retry in about a minute",
            500: "Unexpected server error",
        }
        raise SystemExit(f"{hints.get(error.code, 'Request failed')} (HTTP {error.code})") from error


color = get_json(f"/api/color/{hex_value}")

print(color["persianName"], "/", color["englishName"])
print("HEX:", color["color"])
print("RGB:", color["rgb"])
print("HSL:", color["hsl"])
print("CMYK:", color["cmyk"])
print("Wavelength:", color["wavelength"], "nm")
print("Purity:", color["purity"], "%")
print("Brightness:", color["brightness"], "%")

complementary = get_json(f"/api/complementary-color/{hex_value}")
similar = get_json(f"/api/similar-colors/{hex_value}")

print("\nComplementary:", complementary["compNameEn"], complementary["compHex"])
print("Similar:", ", ".join(item["span"] for item in similar))
