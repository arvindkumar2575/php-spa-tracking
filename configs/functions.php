<?php

function getDeviceType(): string
{
    $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

    // Bots first
    if (preg_match('/bot|crawl|spider|slurp|mediapartners/i', $ua)) {
        return 'BOT';
    }

    // Tablets
    if (preg_match('/ipad|tablet|playbook|silk/i', $ua)) {
        return 'TABLET';
    }

    // Mobiles
    if (preg_match('/mobile|iphone|ipod|android|blackberry|opera mini|windows phone/i', $ua)) {
        return 'MOBILE';
    }

    // Default
    return 'DESKTOP';
}

function getBrowserName(): string
{
    $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

    return match (true) {
        str_contains($ua, 'edg/')        => 'Edge',
        str_contains($ua, 'opr/')        => 'Opera',
        str_contains($ua, 'chrome/')     && !str_contains($ua, 'edg/') => 'Chrome',
        str_contains($ua, 'firefox/')    => 'Firefox',
        str_contains($ua, 'safari/')     && !str_contains($ua, 'chrome/') => 'Safari',
        str_contains($ua, 'msie'),
        str_contains($ua, 'trident/')    => 'Internet Explorer',
        default                          => 'Unknown'
    };
}

function getClientIP(): string
{
    $keys = [
        'HTTP_CF_CONNECTING_IP', // Cloudflare
        'HTTP_X_FORWARDED_FOR',  // Proxy chain
        'HTTP_X_REAL_IP',
        'REMOTE_ADDR',
    ];

    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ipList = explode(',', $_SERVER[$key]);
            $ip = trim($ipList[0]);

            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return 'UNKNOWN';
}

function getIpApiData($ip): array
{
    if($ip) {
        $url = "https://ipapi.co/$ip/json/";
    } else {
        $url = "https://ipapi.co/json/";
    }
    $ch = curl_init($url);

    $headers = [
        'Content-Type: application/json',
        'Connection: keep-alive',
        'User-Agent: PostmanRuntime/7.5.1.1'
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers
    ]);

    $response = curl_exec($ch);

    curl_close($ch);
    if ($response === false) {
        return [];
    }
    // echo '<pre>';print_r($response);die;
    $data = json_decode($response, true);
    return is_array($data) ? $data : [];
}

function uuidv4(): string
{
    $data = random_bytes(16);

    // Set version to 0100
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function base_url(string $uri = ''): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ? 'https'
        : 'http';

    $host = $_SERVER['HTTP_HOST'];

    $base = $scheme . '://' . $host . BASE_PATH;

    if ($uri === '') {
        return rtrim($base, '/');
    }

    return rtrim($base, '/') . '/' . ltrim($uri, '/');
}

function saveClickEvents($data) : bool {
    if ($data) {
        echo "aaaaa";
    }
    return true;
}

function getOrCreateCookie(): string {
    $cookieName = 'daily_visitor';

    if (isset($_COOKIE[$cookieName])) {
        return $_COOKIE[$cookieName];
    } else {
        // Generate a new UUID if no cookie is found
        $uuid = uuidv4();
        // Step 4: Set the cookie only if insertion succeeded and the cookie wasn't already set
        if (!isset($_COOKIE[$cookieName])) {
            $expiry = strtotime('today 23:59:59');

            setcookie(
                $cookieName,
                $uuid,
                [
                    'expires'  => $expiry,
                    'path'     => '/',
                    'secure'   => isset($_SERVER['HTTPS']),
                    'samesite' => 'Lax'
                ]
            );
        }
        return $uuid;
    }
}

function current_uri_without_basepath(string $basePath = ''): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';

    if ($basePath !== '' && str_starts_with($uri, $basePath)) {
        $uri = substr($uri, strlen($basePath));
    }

    return $uri ?: '/';
}

function current_uri(): string
{
    return $_SERVER['REQUEST_URI'] ?? '/';
}