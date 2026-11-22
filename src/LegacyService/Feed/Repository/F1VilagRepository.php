<?php

namespace App\LegacyService\Feed\Repository;

use App\LegacyService\Feed\Collector\ICollector;
use App\LegacyService\Feed\Collector\XMLCollector;
use App\LegacyService\Feed\Item\Item;
use SimpleXMLElement;

/**
 * Az f1vilag.hu (WordPress) RSS feedje.
 *
 * Az elemekben nincs enclosure: a borítókép a description elején lévő <img>-ben jön,
 * a description végén pedig a WordPress „The post … appeared first on …” lábléce áll.
 * Ebből egy kép URL-t és egy sima szöveges (HTML-escape-elt) bevezetőt készítünk.
 */
class F1VilagRepository implements IRepository
{
    public const SOURCE = 'https://f1vilag.hu/feed/';

    /**
     * @var Item[]|null
     */
    private ?array $items = null;

    public function __construct(
        private readonly string $source = self::SOURCE,
        private readonly ICollector $collector = new XMLCollector(),
    ) {
    }

    /**
     * @return Item[] az id (a megjelenés unix timestampje) szerint kulcsolva, a feed sorrendjében (legújabb elöl)
     */
    public function getItems()
    {
        if ($this->items === null) {
            $this->items = $this->generateItems();
        }

        return $this->items;
    }

    /**
     * @return Item[]
     */
    private function generateItems(): array
    {
        $data = $this->collector->getData($this->source);
        if (!$data instanceof SimpleXMLElement || !isset($data->channel->item)) {
            return [];
        }

        $items = [];
        foreach ($data->channel->item as $xmlItem) {
            $publishDate = trim((string) $xmlItem->pubDate);
            $timestamp = strtotime($publishDate);
            if ($timestamp === false) {
                continue;
            }

            $description = (string) $xmlItem->description;

            $item = new Item();
            $item->setId($timestamp);
            $item->setTitle(self::decode((string) $xmlItem->title));
            $item->setDescription(self::extractLead($description));
            $item->setLink(trim((string) $xmlItem->link));
            $item->setPublishDate($publishDate);
            $item->setImage(self::extractImage($description));

            $items[$item->getId()] = $item;
        }

        return $items;
    }

    private static function extractImage(string $html): string
    {
        if (preg_match('/<img\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1/i', $html, $match)) {
            return self::decode($match[2]);
        }

        return '';
    }

    /**
     * A WordPress lábléc és a kép nélküli, tagektől megtisztított szöveg.
     * A News.vue v-html-lel jeleníti meg, ezért HTML-escape-elve tároljuk.
     */
    private static function extractLead(string $html): string
    {
        $html = preg_replace('#<p>\s*The post .*?appeared first on.*?</p>#is', '', $html) ?? $html;
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $text = self::decode(strip_tags($html));
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);

        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private static function decode(string $text): string
    {
        return trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
