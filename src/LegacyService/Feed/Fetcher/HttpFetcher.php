<?php

namespace App\LegacyService\Feed\Fetcher;

class HttpFetcher implements IFetcher
{
    private const TIMEOUT_SECONDS = 15;

    // a forrás WAF-ja bizonyos UA-kat (pl. „collector”) 403-mal tilt
    private const USER_AGENT = 'Mozilla/5.0 (compatible; F1Tipp/1.0; +https://www.f1tipp.nhely.hu)';

    public function fetch(string $url): ?string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => self::TIMEOUT_SECONDS,
                'user_agent' => self::USER_AGENT,
            ],
        ]);

        $content = @file_get_contents($url, false, $context);

        return $content === false || $content === '' ? null : $content;
    }
}
