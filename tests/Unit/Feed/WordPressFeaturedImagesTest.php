<?php

namespace App\Tests\Unit\Feed;

use App\LegacyService\Feed\Image\WordPressFeaturedImages;
use PHPUnit\Framework\TestCase;

/**
 * A WordPress REST API kiemelt képei (`_embed=wp:featuredmedia`): poszt-id → kép URL, a lista méretéhez illő
 * `medium_large` mérettel, ha van.
 */
class WordPressFeaturedImagesTest extends TestCase
{
    private const API = 'https://wp.example/wp-json/wp/v2/posts';

    public function test_requests_only_the_given_posts_with_embedded_featured_media(): void
    {
        $fetcher = new RecordingFetcher([self::API => '[]']);

        (new WordPressFeaturedImages(self::API, $fetcher))->getImages([1000577, 1000540]);

        self::assertCount(1, $fetcher->requested);
        $url = parse_url($fetcher->requested[0]);
        self::assertSame('https://wp.example/wp-json/wp/v2/posts', $url['scheme'] . '://' . $url['host'] . $url['path']);
        parse_str($url['query'], $query);
        self::assertSame([
            'include' => '1000577,1000540',
            'per_page' => '2',
            '_embed' => 'wp:featuredmedia',
            '_fields' => 'id,_links,_embedded',
        ], $query);
    }

    public function test_no_post_ids_means_no_request(): void
    {
        $fetcher = new RecordingFetcher();

        self::assertSame([], (new WordPressFeaturedImages(self::API, $fetcher))->getImages([]));
        self::assertSame([], $fetcher->requested);
    }

    public function test_medium_large_size_is_preferred_then_the_original(): void
    {
        $json = json_encode([
            self::post(1, [
                'source_url' => 'https://wp.example/a.jpg',
                'media_details' => ['sizes' => [
                    'thumbnail' => ['source_url' => 'https://wp.example/a-150x150.jpg'],
                    'medium_large' => ['source_url' => 'https://wp.example/a-768x512.jpg'],
                ]],
            ]),
            self::post(2, ['source_url' => 'https://wp.example/b.jpg', 'media_details' => ['sizes' => []]]),
            self::post(3, ['source_url' => 'https://wp.example/c.jpg']),
        ]);

        $images = (new WordPressFeaturedImages(self::API, new RecordingFetcher([self::API => $json])))->getImages([1, 2, 3]);

        self::assertSame([
            1 => 'https://wp.example/a-768x512.jpg',
            2 => 'https://wp.example/b.jpg',
            3 => 'https://wp.example/c.jpg',
        ], $images);
    }

    public function test_posts_without_usable_featured_media_are_skipped(): void
    {
        $json = json_encode([
            ['id' => 1, 'featured_media' => 0, '_links' => []],
            self::post(2, ['code' => 'rest_forbidden', 'message' => 'Nincs jogosultság.', 'data' => ['status' => 401]]),
            self::post(3, ['source_url' => 'javascript:alert(1)']),
            self::post(4, ['source_url' => '//wp.example/protocol-relative.jpg']),
            ['featured_media' => 5, '_embedded' => ['wp:featuredmedia' => [['source_url' => 'https://wp.example/no-id.jpg']]]],
            self::post(6, ['source_url' => 'http://wp.example/plain-http.jpg']),
        ]);

        $images = (new WordPressFeaturedImages(self::API, new RecordingFetcher([self::API => $json])))->getImages([1, 2, 3, 4, 6]);

        self::assertSame([6 => 'http://wp.example/plain-http.jpg'], $images);
    }

    public function test_unreachable_api_or_invalid_json_yields_no_images(): void
    {
        foreach ([null, '', '<html>Hiba</html>', '{"code":"rest_no_route"}', '"string"'] as $response) {
            $images = (new WordPressFeaturedImages(self::API, new RecordingFetcher([self::API => $response])))->getImages([1]);

            self::assertSame([], $images, var_export($response, true));
        }
    }

    private static function post(int $id, array $media): array
    {
        return ['id' => $id, 'featured_media' => $id + 100, '_embedded' => ['wp:featuredmedia' => [$media]]];
    }
}
