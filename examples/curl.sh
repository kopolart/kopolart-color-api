#!/usr/bin/env bash
# Call every Kopolart Color API endpoint for one color.
# Usage: ./curl.sh [hex]
set -euo pipefail

BASE_URL="${KOPOLART_API_BASE:-https://kopolart.ir}"
HEX="${1:-ff0000}"

request() {
  local path="$1"
  echo "GET ${BASE_URL}${path}"
  curl -sS -w "\nHTTP %{http_code}\n\n" "${BASE_URL}${path}"
}

request "/api/color/${HEX}"
request "/api/complementary-color/${HEX}"
request "/api/similar-colors/${HEX}"
request "/api/similar-palettes/${HEX}"
request "/api-color/${HEX}"
