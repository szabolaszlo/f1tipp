<?php

namespace App\E2e;

use App\Entity\Feed;
use Doctrine\ORM\EntityManagerInterface;

/**
 * E2E feed-szenáriók: az f1vilag.hu feed (2026-11 óta érvényes formátum) és a WordPress REST API válasza fájlba,
 * plusz a már tárolt hírek. Négy hír, a futás pillanatához relatív dátummal (a cron a 15 napnál régebbieket törli):
 * - A: új formátum (nincs <img>, magyar lábléc), van kiemelt képe;
 * - B: új formátum, van kiemelt képe (az öngyógyítás és a „meglévő kép nem vész el” alanya);
 * - C: új formátum, nincs kiemelt képe;
 * - D: régi formátum (beágyazott <img>, angol lábléc), a REST nem ismeri.
 */
class E2eFeed
{
    public const NAMES = ['feed', 'feed-heal', 'feed-api-down', 'feed-unreachable'];

    /** kulcs => [poszt-id, cím, bevezető, órával ezelőtt, kiemelt kép (REST), beágyazott kép (régi formátum)] */
    public const ITEMS = [
        'A' => [9001, 'E2E feed: új formátumú hír', 'A Mercedes diktálta a tempót a pénteki edzésen.', 1,
            'https://f1vilag.hu/wp-content/uploads/e2e-a-768x512.jpg', null],
        'B' => [9002, 'E2E feed: utólag képet kapó hír', 'Antonelli nyerte a második szabadedzést.', 2,
            'https://f1vilag.hu/wp-content/uploads/e2e-b-768x512.jpg', null],
        'C' => [9003, 'E2E feed: kiemelt kép nélküli hír', 'Rövid hír kép nélkül.', 3, null, null],
        'D' => [9004, 'E2E feed: régi formátumú hír', 'Régi formátumú bevezető.', 4,
            null, 'https://f1vilag.hu/wp-content/uploads/e2e-d.jpg'],
    ];

    /** feed-api-down: B már tárolva van ezzel a képpel */
    public const STORED_IMAGE = 'https://f1vilag.hu/wp-content/uploads/e2e-b-stored.jpg';

    public function __construct(private readonly EntityManagerInterface $em, private readonly E2eFeedFetcher $fetcher)
    {
    }

    public function build(string $name, \DateTimeImmutable $now): void
    {
        $feed = $name === 'feed-unreachable' ? null : $this->feedXml($now);
        $posts = in_array($name, ['feed', 'feed-heal'], true) ? $this->postsJson() : null;
        $this->fetcher->write($feed, $posts);

        if ($name === 'feed-heal') {
            // ahogy a 2026-11-es feedváltás után a régi parser mentette: kép nélkül, a magyar lábléccel
            [, $title, $lead] = self::ITEMS['B'];
            $this->storeB($now, '', sprintf('%s A %s bejegyzés először F1VILÁG.HU-én jelent meg.', $lead, $title));
        }
        if ($name === 'feed-api-down') {
            $this->storeB($now, self::STORED_IMAGE, self::ITEMS['B'][2]);
        }
    }

    private function storeB(\DateTimeImmutable $now, string $image, string $description): void
    {
        [, $title, , $hoursAgo] = self::ITEMS['B'];
        $publishedAt = self::publishedAt($now, $hoursAgo);

        $feed = new Feed();
        $feed->setId($publishedAt->getTimestamp())
            ->setTitle($title)
            ->setDescription($description)
            ->setLink('https://f1vilag.hu/hirek/e2e-b/')
            ->setPublishDate(new \DateTime('@' . $publishedAt->getTimestamp()))
            ->setImage($image);
        $feed->getPublishDate()->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        $this->em->persist($feed);
        $this->em->flush();
    }

    private function feedXml(\DateTimeImmutable $now): string
    {
        $items = '';
        foreach (self::ITEMS as $key => [$postId, $title, $lead, $hoursAgo, , $embeddedImage]) {
            $link = 'https://f1vilag.hu/hirek/e2e-' . strtolower($key) . '/';
            $titleHtml = htmlspecialchars($title, ENT_QUOTES | ENT_XML1);
            $description = $embeddedImage === null
                ? sprintf('<p>%s</p>' . "\n" . '<p>A <a href="%s">%s</a> bejegyzés először <a href="https://f1vilag.hu">F1VILÁG.HU</a>-én jelent meg.</p>', $lead, $link, $title)
                : sprintf('<p><img src="%s" alt="" /></p><p>%s</p>' . "\n" . '<p>The post <a href="%s">%s</a> appeared first on <a href="https://f1vilag.hu">F1VILÁG.HU</a>.</p>', $embeddedImage, $lead, $link, $title);

            $items .= sprintf(
                "\t<item>\n\t\t<title>%s</title>\n\t\t<link>%s</link>\n\t\t<pubDate>%s</pubDate>\n"
                . "\t\t<guid isPermaLink=\"false\">https://f1vilag.hu/?p=%d</guid>\n"
                . "\t\t<description><![CDATA[%s]]></description>\n\t</item>\n",
                $titleHtml,
                $link,
                self::publishedAt($now, $hoursAgo)->format(\DATE_RSS),
                $postId,
                $description
            );
        }

        return '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0">' . "\n<channel>\n\t<title>F1VILÁG.HU</title>\n"
            . "\t<link>https://f1vilag.hu/</link>\n" . $items . "</channel>\n</rss>\n";
    }

    private function postsJson(): string
    {
        $posts = [];
        foreach (self::ITEMS as [$postId, , , , $featuredImage, $embeddedImage]) {
            if ($embeddedImage !== null) {
                continue; // a régi formátumú posztot a REST nem ismeri
            }
            $posts[] = $featuredImage === null
                ? ['id' => $postId, 'featured_media' => 0, '_links' => new \stdClass()]
                : ['id' => $postId, 'featured_media' => $postId + 100, '_links' => new \stdClass(), '_embedded' => [
                    'wp:featuredmedia' => [[
                        'id' => $postId + 100,
                        'source_url' => str_replace('-768x512', '', $featuredImage),
                        'media_details' => ['sizes' => ['medium_large' => ['source_url' => $featuredImage]]],
                    ]],
                ]];
        }

        return json_encode($posts, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private static function publishedAt(\DateTimeImmutable $now, int $hoursAgo): \DateTimeImmutable
    {
        // egész másodpercre: a hír id-ja a pubDate unix timestampje
        return $now->setTimestamp($now->getTimestamp() - $hoursAgo * 3600);
    }
}
