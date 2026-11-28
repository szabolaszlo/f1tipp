<?php

namespace App\Tests\Unit\Feed;

use App\LegacyService\Feed\Fetcher\IFetcher;

/**
 * Teszt-fetcher: URL-előtag → válasz (null = elérhetetlen), és megjegyzi a kért URL-eket.
 */
class RecordingFetcher implements IFetcher
{
    /** @var string[] */
    public array $requested = [];

    /**
     * @param array<string, string|null> $responses URL-előtag => válasz
     */
    public function __construct(private readonly array $responses = [])
    {
    }

    public function fetch(string $url): ?string
    {
        $this->requested[] = $url;
        foreach ($this->responses as $prefix => $response) {
            if (str_starts_with($url, $prefix)) {
                return $response;
            }
        }

        return null;
    }
}
