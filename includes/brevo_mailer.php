<?php

function brevoConfig(): array
{
    $cfg = [
        "api_key" => "",
        "from_email" => "noreply@rmclassacademy.com",
        "from_name" => "R.M CLASS ACADEMY",
    ];

    if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "brevo_config.php")) {
        $local = require __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "brevo_config.php";
        if (is_array($local)) $cfg = array_merge($cfg, $local);
    }

    $envKey = getenv("BREVO_API_KEY");
    if (is_string($envKey) && $envKey !== "") $cfg["api_key"] = $envKey;

    $envFrom = getenv("BREVO_FROM_EMAIL");
    if (is_string($envFrom) && $envFrom !== "") $cfg["from_email"] = $envFrom;

    $envName = getenv("BREVO_FROM_NAME");
    if (is_string($envName) && $envName !== "") $cfg["from_name"] = $envName;

    return $cfg;
}

function brevoSendEmail(string $toEmail, string $toName, string $subject, string $textContent): bool
{
    $cfg = brevoConfig();
    if (empty($cfg["api_key"])) return false;

    $payload = json_encode([
        "sender" => ["email" => $cfg["from_email"], "name" => $cfg["from_name"]],
        "to" => [["email" => $toEmail, "name" => $toName]],
        "subject" => $subject,
        "textContent" => $textContent,
    ]);

    if ($payload === false) return false;

    $url = "https://api.brevo.com/v3/smtp/email";

    // Prefer curl when available (InfinityFree usually has it).
    if (function_exists("curl_init")) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "accept: application/json",
            "api-key: " . $cfg["api_key"],
            "content-type: application/json",
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $resp = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $resp !== false && $code >= 200 && $code < 300;
    }

    // Fallback to streams.
    $opts = [
        "http" => [
            "method" => "POST",
            "header" => implode("\r\n", [
                "accept: application/json",
                "api-key: " . $cfg["api_key"],
                "content-type: application/json",
            ]),
            "content" => $payload,
            "timeout" => 15,
        ],
    ];
    $ctx = stream_context_create($opts);
    $resp = @file_get_contents($url, false, $ctx);

    if (!is_array($http_response_header ?? null)) return false;
    foreach ($http_response_header as $h) {
        if (preg_match('/^HTTP\\/(\\d+\\.\\d+)\\s+(\\d+)/', $h, $m)) {
            $code = (int)$m[2];
            return $resp !== false && $code >= 200 && $code < 300;
        }
    }
    return false;
}

