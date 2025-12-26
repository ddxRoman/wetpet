<?php

namespace App\Services\Geo;

class IpApiGeoService
{
    public function detect(string $ip): ?array
    {
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return null;
        }

        

        $url = "http://ip-api.com/json/{$ip}?fields=status,country,regionName,city,lat,lon";

        $json = @file_get_contents($url);

        if (! $json) {
            return null;
        }

        $data = json_decode($json, true);

        if (($data['status'] ?? null) !== 'success') {
            return null;
        }

        return [
            'country' => $data['country'] ?? null,
            'region'  => $data['regionName'] ?? null,
            'city'    => $data['city'] ?? null,
            'lat'     => $data['lat'] ?? null,
            'lon'     => $data['lon'] ?? null,
        ];
    }
}
