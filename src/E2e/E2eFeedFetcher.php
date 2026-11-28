<?php

namespace App\E2e;

use App\LegacyService\Feed\Fetcher\IFetcher;
use App\LegacyService\Feed\Image\WordPressFeaturedImages;
use App\LegacyService\Feed\Repository\F1VilagRepository;

/**
 * E2E: az f1vilag.hu feed és WordPress REST API helyett a feed-szenáriók által írt fájlokat adja
 * (config/services_e2e.yaml köti be az IFetcher helyére). Hiányzó fájl = elérhetetlen forrás.
 */
class E2eFeedFetcher implements IFetcher
{
    public const FEED_FILE = 'feed.xml';
    public const POSTS_FILE = 'posts.json';

    public function __construct(private readonly string $dir)
    {
    }

    public function fetch(string $url): ?string
    {
        $file = match (true) {
            str_starts_with($url, F1VilagRepository::SOURCE) => self::FEED_FILE,
            str_starts_with($url, WordPressFeaturedImages::API) => self::POSTS_FILE,
            default => throw new \LogicException(sprintf('E2E: unexpected external request: %s', $url)),
        };

        $path = $this->dir . '/' . $file;
        if (!is_file($path)) {
            return null;
        }

        $content = file_get_contents($path);

        return $content === false || $content === '' ? null : $content;
    }

    /**
     * A fixture-fájlok írása; a null törli az adott fájlt (elérhetetlen forrás).
     */
    public function write(?string $feed, ?string $posts): void
    {
        if (!is_dir($this->dir) && !mkdir($this->dir, 0777, true) && !is_dir($this->dir)) {
            throw new \RuntimeException(sprintf('E2E: cannot create %s', $this->dir));
        }

        foreach ([self::FEED_FILE => $feed, self::POSTS_FILE => $posts] as $file => $content) {
            $path = $this->dir . '/' . $file;
            if ($content === null) {
                if (is_file($path)) {
                    unlink($path);
                }
                continue;
            }
            file_put_contents($path, $content);
        }
    }
}
