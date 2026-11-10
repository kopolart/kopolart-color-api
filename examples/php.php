<?php
// Kopolart Color API example — PHP 8+ with ext-curl.
// Usage: php php.php [hex]

$baseUrl = getenv('KOPOLART_API_BASE') ?: 'https://kopolart.ir';
$hex = $argv[1] ?? 'ff0000';

function getJson(string $url): array
{
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => 'KopolartColorApiExample/1.0',
    ]);

    $body = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    if ($body === false) {
        throw new RuntimeException("Request failed: {$error}");
    }

    if ($status !== 200) {
        $hint = match ($status) {
            400 => 'Invalid HEX value',
            429 => 'Rate limited; retry in about a minute',
            default => 'Unexpected error',
        };

        throw new RuntimeException("{$hint} (HTTP {$status})");
    }

    return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
}

$color = getJson("{$baseUrl}/api/color/{$hex}");

echo $color['persianName'] . ' / ' . $color['englishName'] . PHP_EOL;
echo 'HEX: ' . $color['color'] . PHP_EOL;
echo 'RGB: ' . implode(', ', $color['rgb']) . PHP_EOL;
echo 'Wavelength: ' . $color['wavelength'] . ' nm' . PHP_EOL;
echo 'Purity: ' . $color['purity'] . '%' . PHP_EOL;
echo 'Brightness: ' . $color['brightness'] . '%' . PHP_EOL;

$complementary = getJson("{$baseUrl}/api/complementary-color/{$hex}");
echo PHP_EOL . 'Complementary: ' . $complementary['compNameEn'] . ' ' . $complementary['compHex'] . PHP_EOL;
