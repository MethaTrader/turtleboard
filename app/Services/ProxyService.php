<?php
// app/Services/ProxyService.php

namespace App\Services;

use App\Models\Proxy;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProxyService
{
    /**
     * Parse proxies from text input or file.
     *
     * @param string|null $textInput
     * @param UploadedFile|null $file
     * @return Collection
     */
    public function parseProxies(?string $textInput = null, ?UploadedFile $file = null): Collection
    {
        $proxyLines = collect();

        // Parse from text input
        if ($textInput) {
            $lines = preg_split('/\r\n|\r|\n/', trim($textInput));
            $proxyLines = collect($lines)->filter(fn($line) => !empty(trim($line)));
        }

        // Parse from file
        if ($file) {
            $content = file_get_contents($file->getPathname());
            $lines = preg_split('/\r\n|\r|\n/', trim($content));
            $proxyLines = collect($lines)->filter(fn($line) => !empty(trim($line)));
        }

        // Process each proxy line
        return $proxyLines->map(function ($line) {
            $parts = explode(':', trim($line));

            // Format: IP:PORT:USERNAME:PASSWORD or IP:PORT
            if (count($parts) >= 2) {
                return [
                    'ip_address' => $parts[0],
                    'port' => (int) $parts[1],
                    'username' => $parts[2] ?? null,
                    'password' => $parts[3] ?? null,
                    'validation_status' => 'pending',
                    'user_id' => Auth::id(),
                ];
            }

            return null;
        })->filter();
    }

    /**
     * Validate a proxy and update its status.
     *
     * @param Proxy $proxy
     * @return bool
     */
    public function validateProxy(Proxy $proxy): bool
    {
        try {
            $startTime = microtime(true);

            // Build proxy string for cURL
            $proxyString = $proxy->ip_address . ':' . $proxy->port;
            if ($proxy->username) {
                $proxyString = $proxy->username . ':' . $proxy->password . '@' . $proxyString;
            }

            // Use Laravel's HTTP client with a timeout
            $response = Http::timeout(10)
                ->withOptions([
                    'proxy' => $proxyString,
                    'verify' => false, // Skip SSL verification for testing
                ])
                ->get('https://httpbin.org/ip');

            $endTime = microtime(true);
            $responseTime = (int) (($endTime - $startTime) * 1000); // Convert to milliseconds

            if ($response->successful()) {
                // Get geolocation data with country code
                $geoData = $this->getGeolocation($proxy->ip_address);

                // Mark proxy as valid
                $proxy->markAsValid($responseTime, $geoData['location'], $geoData['country_code']);
                return true;
            } else {
                $proxy->markAsInvalid();
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Proxy validation error: ' . $e->getMessage(), [
                'proxy' => $proxy->ip_address . ':' . $proxy->port,
                'exception' => $e
            ]);

            $proxy->markAsInvalid();
            return false;
        }
    }

    /**
     * Get geolocation information for an IP address.
     *
     * @param string $ipAddress
     * @return array
     */
    protected function getGeolocation(string $ipAddress): array
    {
        try {
            // Use ip-api.com's free service for geolocation
            $response = Http::timeout(5)
                ->get("http://ip-api.com/json/{$ipAddress}");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'success') {
                    return [
                        'location' => $data['country'] . ', ' . $data['city'],
                        'country_code' => strtolower($data['countryCode']) // Convert to lowercase for flag API
                    ];
                }
            }

            return [
                'location' => null,
                'country_code' => null
            ];
        } catch (\Exception $e) {
            Log::error('Geolocation error: ' . $e->getMessage(), [
                'ip_address' => $ipAddress,
                'exception' => $e
            ]);

            return [
                'location' => null,
                'country_code' => null
            ];
        }
    }

    /**
     * Create a new proxy.
     *
     * @param array $data
     * @return Proxy
     */
    public function create(array $data): Proxy
    {
        return Proxy::create($data);
    }

    /**
     * Update an existing proxy.
     *
     * @param Proxy $proxy
     * @param array $data
     * @return Proxy
     */
    public function update(Proxy $proxy, array $data): Proxy
    {
        $proxy->update($data);
        return $proxy;
    }

    /**
     * Delete a proxy.
     *
     * @param Proxy $proxy
     * @return bool|null
     */
    public function delete(Proxy $proxy): ?bool
    {
        // Check if the proxy is associated with an email account
        if ($proxy->emailAccount) {
            throw new \Exception('Cannot delete proxy that is linked to an email account.');
        }

        return $proxy->delete();
    }

    /**
     * Validate multiple proxies at once.
     *
     * @param Collection $proxies
     * @return array
     */
    public function bulkValidate(Collection $proxies): array
    {
        $results = [
            'total' => $proxies->count(),
            'valid' => 0,
            'invalid' => 0
        ];

        foreach ($proxies as $proxy) {
            if ($this->validateProxy($proxy)) {
                $results['valid']++;
            } else {
                $results['invalid']++;
            }
        }

        return $results;
    }
}