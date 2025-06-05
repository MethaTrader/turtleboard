<?php
// app/Services/ProxyIPV4Service.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ProxyIPV4Service
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.proxy_ipv4.api_key');
        $this->baseUrl = config('services.proxy_ipv4.base_url', 'https://proxy-ipv4.com/api');
    }

    /**
     * Get all purchased proxies from ProxyIPV4 service
     *
     * @return array
     */
    public function getPurchasedProxies()
    {
        try {
            // Cache the results for 5 minutes to avoid excessive API calls
            return Cache::remember('proxy_ipv4_purchased', 300, function () {
                $response = Http::timeout(30)
                    ->get($this->baseUrl . '/' . $this->apiKey . '/get/proxies');

                if ($response->successful()) {
                    $data = $response->json();

                    // Process the response and format it for our application
                    return $this->formatProxyData($data);
                } else {
                    Log::error('ProxyIPV4 API Error: ' . $response->status() . ' - ' . $response->body());
                    return [
                        'success' => false,
                        'message' => 'Failed to fetch proxies from ProxyIPV4',
                        'proxies' => []
                    ];
                }
            });
        } catch (\Exception $e) {
            Log::error('ProxyIPV4 Service Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Service error: ' . $e->getMessage(),
                'proxies' => []
            ];
        }
    }

    /**
     * Get proxy details by ID
     *
     * @param string $proxyId
     * @return array
     */
    public function getProxyDetails($proxyId)
    {
        try {
            $response = Http::timeout(30)
                ->get($this->baseUrl . '/' . $this->apiKey . '/get/proxies');

            if ($response->successful()) {
                $data = $response->json();
                $allProxies = $this->formatProxyData($data);

                if ($allProxies['success']) {
                    $proxy = collect($allProxies['proxies'])->firstWhere('id', $proxyId);

                    if ($proxy) {
                        return [
                            'success' => true,
                            'proxy' => $proxy
                        ];
                    }
                }

                return [
                    'success' => false,
                    'message' => 'Proxy not found'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Proxy not found or API error'
                ];
            }
        } catch (\Exception $e) {
            Log::error('ProxyIPV4 Service Error getting proxy details: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Service error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check proxy usage statistics
     *
     * @param string $proxyId
     * @return array
     */
    public function getProxyUsage($proxyId)
    {
        // ProxyIPV4 doesn't seem to have a separate usage endpoint
        // We'll track usage locally instead
        return [
            'success' => true,
            'usage' => [
                'message' => 'Usage tracked locally in application'
            ]
        ];
    }

    /**
     * Format proxy data from API response
     *
     * @param array $apiData
     * @return array
     */
    protected function formatProxyData($apiData)
    {
        if (!isset($apiData['success']) || !$apiData['success']) {
            return [
                'success' => false,
                'message' => 'API returned error response',
                'proxies' => []
            ];
        }

        $formattedProxies = [];

        // Process IPv4 proxies
        if (isset($apiData['ipv4']) && is_array($apiData['ipv4'])) {
            foreach ($apiData['ipv4'] as $proxy) {
                $formattedProxies[] = $this->formatSingleProxy($proxy, 'ipv4');
            }
        }

        // Process IPv6 proxies
        if (isset($apiData['ipv6']) && is_array($apiData['ipv6'])) {
            foreach ($apiData['ipv6'] as $order) {
                if (isset($order['ips']) && is_array($order['ips'])) {
                    foreach ($order['ips'] as $proxy) {
                        $proxy['orderId'] = $order['orderId']; // Add order ID for IPv6
                        $formattedProxies[] = $this->formatSingleProxy($proxy, 'ipv6');
                    }
                }
            }
        }

        // Process ISP proxies
        if (isset($apiData['isp']) && is_array($apiData['isp'])) {
            foreach ($apiData['isp'] as $proxy) {
                $formattedProxies[] = $this->formatSingleProxy($proxy, 'isp');
            }
        }

        // Process Mobile proxies
        if (isset($apiData['mobile']) && is_array($apiData['mobile'])) {
            foreach ($apiData['mobile'] as $proxy) {
                $formattedProxies[] = $this->formatSingleProxy($proxy, 'mobile');
            }
        }

        return [
            'success' => true,
            'message' => 'Proxies fetched successfully',
            'proxies' => $formattedProxies,
            'total' => count($formattedProxies),
            'user_info' => [
                'email' => $apiData['user'] ?? null,
                'balance' => $apiData['balance'] ?? null,
                'currency' => $apiData['currency'] ?? null,
                'count_of_proxy' => $apiData['countOfProxy'] ?? null,
            ]
        ];
    }

    /**
     * Format a single proxy from API response
     *
     * @param array $proxy
     * @param string $type
     * @return array
     */
    protected function formatSingleProxy($proxy, $type)
    {
        // Extract IP and port from different formats
        $ipAddress = $proxy['ip'] ?? null;
        $port = null;

        // For IPv6, the IP might include port (like "140.82.53.23:10002")
        if ($type === 'ipv6' && strpos($ipAddress, ':') !== false) {
            $parts = explode(':', $ipAddress);
            if (count($parts) >= 2) {
                $port = array_pop($parts);
                $ipAddress = implode(':', $parts);
            }
        } else {
            // For other types, use httpsPort or socks5Port
            $port = $proxy['httpsPort'] ?? $proxy['socks5Port'] ?? 8080;
        }

        // Get authentication info
        $authInfo = $proxy['authInfo'] ?? [];
        $username = $authInfo['login'] ?? null;
        $password = $authInfo['password'] ?? null;

        // Parse dates
        $purchaseDate = isset($proxy['dateStart']) ? Carbon::parse($proxy['dateStart']) : null;
        $expiryDate = isset($proxy['dateEnd']) ? Carbon::parse($proxy['dateEnd']) : null;

        // Get country information and fix country code
        $countryCode = $proxy['country'] ?? null;
        $country = $this->getCountryName($countryCode);

        // Convert 3-letter country codes to 2-letter codes for flags
        $flagCountryCode = $this->convertCountryCodeToTwoLetter($countryCode);

        return [
            'id' => $proxy['id'] ?? null,
            'order_id' => $proxy['orderId'] ?? null, // For IPv6 proxies
            'ip_address' => $ipAddress,
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'country' => $country,
            'country_code' => $flagCountryCode, // Use 2-letter code for compatibility
            'city' => null, // Not provided in API
            'purchase_date' => $purchaseDate,
            'expiry_date' => $expiryDate,
            'status' => $expiryDate && $expiryDate->isFuture() ? 'active' : 'expired',
            'protocol' => $proxy['protocol'] ?? ($type === 'ipv6' ? 'SOCKS5' : 'HTTP/HTTPS'),
            'proxy_type' => $type,
            'is_active' => $expiryDate ? $expiryDate->isFuture() : false,
            'days_remaining' => $this->calculateDaysRemaining($expiryDate),
            'is_used' => false, // Will be determined by checking local database
            'mobile_operator' => $proxy['mobileOperator'] ?? null,
            'rotation_time' => $proxy['rotationTime'] ?? null,
            'reboot_link' => $proxy['rebootLink'] ?? null,
            'raw_data' => $proxy // Keep original data for reference
        ];
    }

    /**
     * Convert 3-letter country codes to 2-letter codes for flag compatibility
     *
     * @param string|null $countryCode
     * @return string|null
     */
    protected function convertCountryCodeToTwoLetter($countryCode)
    {
        if (!$countryCode) {
            return null;
        }

        $mapping = [
            'CZE' => 'cz',
            'GBR' => 'gb',
            'USA' => 'us',
            'FRA' => 'fr',
            'DEU' => 'de',
            'RUS' => 'ru',
            'CHN' => 'cn',
            'JPN' => 'jp',
            'CAN' => 'ca',
            'AUS' => 'au',
            'BRA' => 'br',
            'IND' => 'in',
            'ITA' => 'it',
            'ESP' => 'es',
            'NLD' => 'nl',
            'BEL' => 'be',
            'CHE' => 'ch',
            'AUT' => 'at',
            'POL' => 'pl',
            'SWE' => 'se',
            'NOR' => 'no',
            'DNK' => 'dk',
            'FIN' => 'fi',
            'UKR' => 'ua',
            'HUN' => 'hu',
            'ROU' => 'ro',
            'BGR' => 'bg',
            'HRV' => 'hr',
            'SRB' => 'rs',
            'SVN' => 'si',
            'SVK' => 'sk',
            'EST' => 'ee',
            'LVA' => 'lv',
            'LTU' => 'lt',
        ];

        return $mapping[strtoupper($countryCode)] ?? strtolower(substr($countryCode, 0, 2));
    }

    // In ProxyIPV4Service, add this method
    protected function convertCountryCode($code) {
        $mapping = [
            'cze' => 'cz',
            'gbr' => 'gb',
            'usa' => 'us',
            'fra' => 'fr',
            // Add more as needed
        ];

        return $mapping[strtolower($code)] ?? strtolower($code);
    }

    /**
     * Get country name from country code
     *
     * @param string|null $countryCode
     * @return string|null
     */
    protected function getCountryName($countryCode)
    {
        if (!$countryCode) {
            return null;
        }

        // Map of common country codes to names
        $countries = [
            'GBR' => 'United Kingdom',
            'USA' => 'United States',
            'FRA' => 'France',
            'DEU' => 'Germany',
            'RUS' => 'Russia',
            'CHN' => 'China',
            'JPN' => 'Japan',
            'CAN' => 'Canada',
            'AUS' => 'Australia',
            'BRA' => 'Brazil',
            'IND' => 'India',
            'ITA' => 'Italy',
            'ESP' => 'Spain',
            'NLD' => 'Netherlands',
            'BEL' => 'Belgium',
            'CHE' => 'Switzerland',
            'AUT' => 'Austria',
            'POL' => 'Poland',
            'SWE' => 'Sweden',
            'NOR' => 'Norway',
            'DNK' => 'Denmark',
            'FIN' => 'Finland',
            'UKR' => 'Ukraine',
            'CZE' => 'Czech Republic',
            'HUN' => 'Hungary',
            'ROU' => 'Romania',
            'BGR' => 'Bulgaria',
            'HRV' => 'Croatia',
            'SRB' => 'Serbia',
            'SVN' => 'Slovenia',
            'SVK' => 'Slovakia',
            'EST' => 'Estonia',
            'LVA' => 'Latvia',
            'LTU' => 'Lithuania',
        ];

        return $countries[strtoupper($countryCode)] ?? $countryCode;
    }

    /**
     * Calculate days remaining until expiry
     *
     * @param \Carbon\Carbon|string|null $expiryDate
     * @return int|null
     */
    protected function calculateDaysRemaining($expiryDate)
    {
        if (!$expiryDate) {
            return null;
        }

        try {
            if (is_string($expiryDate)) {
                $expiry = Carbon::parse($expiryDate);
            } else {
                $expiry = $expiryDate;
            }

            $now = Carbon::now();

            if ($expiry->isFuture()) {
                return $now->diffInDays($expiry);
            } else {
                return 0; // Expired
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Test API connection
     *
     * @return array
     */
    public function testConnection()
    {
        try {
            $response = Http::timeout(10)
                ->get($this->baseUrl . '/' . $this->apiKey . '/get/proxies');

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success']) {
                    return [
                        'success' => true,
                        'message' => 'Connection successful',
                        'account_info' => [
                            'user' => $data['user'] ?? 'Unknown',
                            'balance' => $data['balance'] ?? 0,
                            'currency' => $data['currency'] ?? 'USD',
                            'proxy_count' => $data['countOfProxy'] ?? 0,
                        ]
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'API returned error response'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Connection failed: ' . $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Import a ProxyIPV4 proxy to local database
     *
     * @param array $proxyData
     * @param int $userId
     * @return array
     */
    public function importProxy($proxyData, $userId)
    {
        try {
            // Check if proxy already exists (including soft deleted)
            $existingProxy = \App\Models\Proxy::withTrashed()
                ->where('ip_address', $proxyData['ip_address'])
                ->where('port', $proxyData['port'])
                ->first();

            if ($existingProxy && !$existingProxy->trashed()) {
                return [
                    'success' => false,
                    'message' => 'Proxy already exists in local database'
                ];
            }

            if ($existingProxy && $existingProxy->trashed()) {
                // Restore and update the soft deleted proxy
                $existingProxy->restore();
                $existingProxy->update([
                    'source' => 'proxy_ipv4',
                    'proxy_ipv4_id' => $proxyData['id'],
                    'username' => $proxyData['username'],
                    'password' => $proxyData['password'],
                    'purchase_date' => $proxyData['purchase_date'],
                    'expiry_date' => $proxyData['expiry_date'],
                    'protocol' => $proxyData['protocol'],
                    'geolocation' => $proxyData['country'],
                    'country_code' => $proxyData['country_code'],
                    'validation_status' => 'pending',
                    'user_id' => $userId,
                ]);

                return [
                    'success' => true,
                    'message' => 'Proxy restored and imported successfully',
                    'proxy' => $existingProxy
                ];
            }

            // Create new proxy record
            $proxy = \App\Models\Proxy::create([
                'ip_address' => $proxyData['ip_address'],
                'port' => $proxyData['port'],
                'username' => $proxyData['username'],
                'password' => $proxyData['password'],
                'source' => 'proxy_ipv4',
                'proxy_ipv4_id' => $proxyData['id'],
                'purchase_date' => $proxyData['purchase_date'],
                'expiry_date' => $proxyData['expiry_date'],
                'protocol' => $proxyData['protocol'],
                'geolocation' => $proxyData['country'],
                'country_code' => $proxyData['country_code'],
                'validation_status' => 'pending',
                'user_id' => $userId,
            ]);

            return [
                'success' => true,
                'message' => 'Proxy imported successfully',
                'proxy' => $proxy
            ];
        } catch (\Exception $e) {
            Log::error('Error importing ProxyIPV4 proxy: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Import error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Clear cached proxy data
     *
     * @return void
     */
    public function clearCache()
    {
        Cache::forget('proxy_ipv4_purchased');
    }
}