<?php

namespace App\LegacyService\Feed\Image;

use App\LegacyService\Feed\Fetcher\HttpFetcher;
use App\LegacyService\Feed\Fetcher\IFetcher;

/**
 * Posztok kiemelt képe a WordPress REST API-ból, egy kérésben (`_embed=wp:featuredmedia`).
 *
 * Az f1vilag.hu 2026-11 óta nem teszi a borítóképet az RSS description-be, ezért kell külön lekérni.
 */
class WordPressFeaturedImages
{
    public const API = 'https://f1vilag.hu/wp-json/wp/v2/posts';

    // a hírlista 120 px széles képet mutat: a 768 px-es változat bőven elég, és töredéke az eredetinek
    private const PREFERRED_SIZE = 'medium_large';

    // a WordPress REST per_page felső határa
    private const MAX_PER_PAGE = 100;

    public function __construct(
        private readonly string $api = self::API,
        private readonly IFetcher $fetcher = new HttpFetcher(),
    ) {
    }

    /**
     * @param int[] $postIds
     * @return array<int, string> poszt-id => kép URL; a kép nélküli vagy hiányzó posztok kimaradnak
     */
    public function getImages(array $postIds): array
    {
        $postIds = array_slice(array_values(array_unique($postIds)), 0, self::MAX_PER_PAGE);
        if ($postIds === []) {
            return [];
        }

        $content = $this->fetcher->fetch($this->api . '?' . http_build_query([
            'include' => implode(',', $postIds),
            'per_page' => count($postIds),
            '_embed' => 'wp:featuredmedia',
            '_fields' => 'id,_links,_embedded',
        ]));
        $posts = $content === null ? null : json_decode($content, true);
        if (!is_array($posts) || !array_is_list($posts)) {
            return [];
        }

        $images = [];
        foreach ($posts as $post) {
            $image = self::imageOf($post);
            if ($image !== null) {
                $images[$post['id']] = $image;
            }
        }

        return $images;
    }

    private static function imageOf(mixed $post): ?string
    {
        if (!is_array($post) || !is_int($post['id'] ?? null)) {
            return null;
        }

        $media = $post['_embedded']['wp:featuredmedia'][0] ?? null;
        if (!is_array($media)) {
            return null;
        }

        $url = $media['media_details']['sizes'][self::PREFERRED_SIZE]['source_url'] ?? $media['source_url'] ?? null;

        return is_string($url) && preg_match('#^https?://#i', $url) ? $url : null;
    }
}
