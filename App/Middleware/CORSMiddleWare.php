<?php

namespace App\Middleware;

use App\Configuration\Config;
use App\Services\LoggerService;

class CORSMiddleware
{
    public function handle($request, $next)
    {
        $method   = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $origin   = trim($_SERVER['HTTP_ORIGIN'] ?? '');
        // if no origin you can skip
        if ($origin === '') {
            //LoggerService::logInfo('CORS-skip', 'No Origin header (same-origin/proxy or navigation)');
            return $next($request); 
        }
        $reqHdrs  = trim($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? '');
        $env      = (string) Config::get('environment', 'remote');
        $allowCreds = (bool) Config::get('cors_allow_credentials', true);

        // Load & normalize accepted origins (array | JSON | CSV)
        $accepted = $this->normalizeOrigins(
            $this->coerceToArray(Config::get('accepted_origins', []))
        );

        // Optional: configurable exposed headers
        $exposeHeaders = $this->headerList(
            Config::get('cors_expose_headers', ['ETag', 'X-Request-Id'])
        );

        // What methods & headers we will allow on preflight
        $allowMethods = 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD';
        $defaultAllowHeaders = 'Content-Type, Authorization, X-Requested-With, X-Profile, X-Site, Accept-Language, X-HL-Api-Key, X-Local-Token';

        // Always advertise Vary so caches/CDNs key correctly
        header('Vary: Origin, Access-Control-Request-Method, Access-Control-Request-Headers', true);

        LoggerService::logInfo('CORS-enter', [
            'method' => $method,
            'origin' => $origin !== '' ? $origin : '(none)',
        ]);

        $isAllowed = $origin !== '' && $this->isOriginAllowed($origin, $accepted, $env);

        // Log decision without dumping full list in prod
        LoggerService::logInfo('CORS-eval', [
            'allowed' => $isAllowed ? 'yes' : 'no',
            'rules'   => count($accepted) . ' rule(s)',
        ]);

        // --- Preflight ---
        if (strcasecmp($method, 'OPTIONS') === 0) {
            if ($isAllowed) {
                header("Access-Control-Allow-Origin: {$origin}", true);
                header('Access-Control-Allow-Methods: ' . $allowMethods, true);
                header('Access-Control-Allow-Headers: ' . ($reqHdrs !== '' ? $reqHdrs : $defaultAllowHeaders), true);
                header('Access-Control-Max-Age: 600', true);
                if ($allowCreds) {
                    header('Access-Control-Allow-Credentials: true', true);
                }
                http_response_code(204); // no body
            } else {
                http_response_code(403); // deny cleanly, no text body
            }
            exit;
        }

        // --- Actual request ---
        if ($isAllowed) {
            header("Access-Control-Allow-Origin: {$origin}", true);
            if ($allowCreds) {
                header('Access-Control-Allow-Credentials: true', true);
            }
            if ($exposeHeaders !== '') {
                header('Access-Control-Expose-Headers: ' . $exposeHeaders, true);
            }
            // Vary already set above
            LoggerService::logInfo('CORS-allowed', $origin);
        } else {
            if ($origin !== '') {
                // No ACAO header => browser blocks for cross-origin JS
                LoggerService::logWarning('CORS-denied', $origin);
            }
        }

        return $next($request);
    }

    /**
     * Allow rules:
     * 1) Exact match from config.
     * 2) Wildcard like "https://*.mylanguage.net.au".
     * 3) Exact host without port allows any port ("https://wsu.mylanguage.net.au").
     * 4) In local/dev, allow loopback any port via rules like "http://localhost:*".
     */
    private function isOriginAllowed(string $origin, array $rules, string $env): bool
    {
        if (in_array($this->normalizeOrigin($origin), $rules, true)) {
            return true;
        }

        $p = parse_url($origin);
        $scheme = strtolower($p['scheme'] ?? '');
        $host   = strtolower($p['host'] ?? '');
        $port   = isset($p['port']) ? (int) $p['port'] : null;

        $isLoopback = in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]'], true);

        foreach ($rules as $rule) {
            $rule = trim((string) $rule);
            if ($rule === '') continue;

            // Wildcard host: "https://*.mylanguage.net.au"
            if (strpos($rule, '//*.') !== false) {
                $rp   = parse_url($rule);
                $rSch = strtolower($rp['scheme'] ?? '');
                $rHost= strtolower($rp['host'] ?? '');
                if ($rSch === $scheme && $this->hostMatchesWildcard($host, $rHost)) {
                    return true;
                }
                continue;
            }

            // Exact without port: match scheme+host only
            if (preg_match('#^https?://[^/:]+$#i', $rule)) {
                $rp   = parse_url($rule);
                $rSch = strtolower($rp['scheme'] ?? '');
                $rHost= strtolower($rp['host'] ?? '');
                if ($rSch === $scheme && $rHost === $host) {
                    return true;
                }
                continue;
            }

            // Exact with port: strict match
            if (preg_match('#^https?://[^/]+:\d+$#i', $rule)) {
                $rp   = parse_url($rule);
                $rSch = strtolower($rp['scheme'] ?? '');
                $rHost= strtolower($rp['host'] ?? '');
                $rPort= isset($rp['port']) ? (int) $rp['port'] : null;
                if ($rSch === $scheme && $rHost === $host && $rPort === $port) {
                    return true;
                }
                continue;
            }

            // Dev-only loopback wildcard ports (e.g. "http://localhost:*")
            if ($env === 'local' || $env === 'dev') {
                if (preg_match('#^https?://(\[::1\]|::1|127\.0\.0\.1|localhost):\*$#i', $rule)) {
                    if ($isLoopback && ($scheme === 'http' || $scheme === 'https')) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function hostMatchesWildcard(string $host, string $ruleHost): bool
    {
        // ruleHost like "*.mylanguage.net.au"
        if (strpos($ruleHost, '*.') !== 0) return false;
        $suffix = substr($ruleHost, 1); // ".mylanguage.net.au"
        return $host !== ltrim($suffix, '.') &&
               substr($host, -strlen($suffix)) === $suffix;
    }

    private function coerceToArray($val): array
    {
        if (is_array($val)) return $val;
        $s = trim((string) $val);
        if ($s === '') return [];
        if ($s[0] === '[') { // JSON array
            $j = json_decode($s, true);
            if (is_array($j)) return array_map('trim', $j);
        }
        // CSV
        return array_filter(array_map('trim', explode(',', $s)));
    }

    private function normalizeOrigins(array $origins): array
    {
        $out = [];
        foreach ($origins as $o) {
            $n = $this->normalizeOrigin($o);
            if ($n !== '') $out[] = $n;
        }
        return array_values(array_unique($out));
    }

    private function normalizeOrigin(string $origin): string
    {
        $origin = trim($origin);
        if ($origin === '') return '';
        // strip trailing slash, lower-case scheme + host
        $origin = rtrim($origin, '/');
        $p = parse_url($origin);
        if (!$p || empty($p['scheme']) || empty($p['host'])) {
            return ''; // ignore malformed entries
        }
        $scheme = strtolower($p['scheme']);
        $host   = strtolower($p['host']);
        $port   = isset($p['port']) ? ':' . (int) $p['port'] : '';
        return "{$scheme}://{$host}{$port}";
    }

    private function headerList($val): string
    {
        $arr = $this->coerceToArray($val);
        $arr = array_filter(array_map(function ($s) {
            return trim((string) $s);
        }, $arr), fn($s) => $s !== '');
        return implode(', ', $arr);
    }
}
