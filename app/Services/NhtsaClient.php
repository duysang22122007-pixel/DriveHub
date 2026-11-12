<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class NhtsaClient
{
    public function __construct(
        private readonly string $baseUrl = 'https://vpic.nhtsa.dot.gov/api/vehicles',
        private readonly int $timeoutSeconds = 20
    ) {
        if ($timeoutSeconds < 1 || $timeoutSeconds > 60) {
            throw new \InvalidArgumentException('NHTSA timeout must be between 1 and 60 seconds.');
        }
    }

    /**
     * @return list<mixed>
     */
    public function getAllMakes(): array
    {
        return $this->results('/GetAllMakes?format=json');
    }

    /**
     * @return list<mixed>
     */
    public function getMakesForVehicleType(string $type): array
    {
        if (!in_array($type, ['car', 'mpv', 'truck'], true)) {
            throw new \InvalidArgumentException('Unsupported NHTSA vehicle type.');
        }

        return $this->results('/GetMakesForVehicleType/' . $type . '?format=json');
    }

    /**
     * @return list<mixed>
     */
    public function getModelsForMake(int $makeId): array
    {
        if ($makeId < 1) {
            throw new \InvalidArgumentException('NHTSA make ID must be positive.');
        }

        return $this->results('/GetModelsForMakeId/' . $makeId . '?format=json');
    }

    /**
     * @return list<mixed>
     */
    public function getModelsForMakeYear(string $make, int $year): array
    {
        $make = trim($make);
        if ($make === '' || $year < 1886 || $year > (int) date('Y') + 1) {
            throw new \InvalidArgumentException('A valid make and model year are required.');
        }

        return $this->results(
            '/GetModelsForMakeYear/make/' . rawurlencode($make) . '/modelyear/' . $year . '?format=json'
        );
    }

    /**
     * @return list<mixed>
     */
    private function results(string $endpoint): array
    {
        $url = rtrim($this->baseUrl, '/') . $endpoint;
        $handle = curl_init($url);
        if ($handle === false) {
            throw new RuntimeException('Unable to initialize the NHTSA API request.');
        }

        $body = '';
        $tooLarge = false;
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_CONNECTTIMEOUT => min(5, $this->timeoutSeconds),
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_USERAGENT => 'AutoCareHub vehicle-catalog importer',
            CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use (&$body, &$tooLarge): int {
                if (strlen($body) + strlen($chunk) > 25 * 1024 * 1024) {
                    $tooLarge = true;

                    return 0;
                }
                $body .= $chunk;

                return strlen($chunk);
            },
        ]);
        $success = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if ($tooLarge) {
            throw new RuntimeException('NHTSA API response exceeded the 25 MB safety limit.');
        }
        if ($success !== true) {
            throw new RuntimeException('NHTSA API request failed: ' . ($error !== '' ? $error : 'no response body'));
        }
        if ($status < 200 || $status >= 300) {
            throw new RuntimeException('NHTSA API returned HTTP ' . $status . '.');
        }

        try {
            $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new RuntimeException('NHTSA API returned invalid JSON.', 0, $exception);
        }

        if (!is_array($payload) || !array_key_exists('Results', $payload)) {
            throw new RuntimeException('NHTSA API response does not contain a Results collection.');
        }
        if ($payload['Results'] === null && (int) ($payload['Count'] ?? 0) === 0) {
            return [];
        }
        if (!is_array($payload['Results'])) {
            throw new RuntimeException('NHTSA API Results must be a list.');
        }

        return array_values($payload['Results']);
    }
}
