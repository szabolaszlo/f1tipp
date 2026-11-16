<?php

namespace App\Controller\Api;

use App\Entity\Event;
use App\Entity\Feed;
use App\Entity\Information;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Csak olvasó JSON végpontok a Vue SPA-nak (Calendar, News, Rules).
 * Korábban az API Platform 2.7 szolgálta ki őket, a válaszformátum ugyanaz maradt.
 */
#[Route('/api', name: 'api_content_')]
class ContentApiController
{
    private const EVENTS_PER_PAGE = 100;
    private const FEEDS_PER_PAGE = 10;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/events', name: 'events', methods: ['GET'])]
    public function events(Request $request): JsonResponse
    {
        /** @var Event[] $events */
        $events = $this->entityManager->getRepository(Event::class)->findBy(
            [],
            ['id' => 'ASC'],
            self::EVENTS_PER_PAGE,
            $this->offset($request, self::EVENTS_PER_PAGE)
        );

        return new JsonResponse(array_map(static fn (Event $event): array => [
            'id' => $event->getId(),
            'weekendOrder' => $event->getWeekendOrder(),
            'name' => $event->getName(),
            'date_time' => self::formatDate($event->getDateTime()),
            'dateTime' => self::formatDate($event->getDateTime()),
            'type' => $event->getType(),
        ], $events));
    }

    #[Route('/feeds', name: 'feeds', methods: ['GET'])]
    public function feeds(Request $request): JsonResponse
    {
        /** @var Feed[] $feeds */
        $feeds = $this->entityManager->getRepository(Feed::class)->findBy(
            [],
            ['id' => 'DESC'],
            self::FEEDS_PER_PAGE,
            $this->offset($request, self::FEEDS_PER_PAGE)
        );

        return new JsonResponse(array_map(static fn (Feed $feed): array => [
            'id' => $feed->getId(),
            'title' => $feed->getTitle(),
            'description' => $feed->getDescription(),
            'link' => $feed->getLink(),
            'publishDate' => self::formatDate($feed->getPublishDate()),
            'image' => $feed->getImage(),
        ], $feeds));
    }

    #[Route('/information/{id}', name: 'information', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function information(int $id): JsonResponse
    {
        $information = $this->entityManager->getRepository(Information::class)->find($id);
        if (!$information instanceof Information) {
            return new JsonResponse(['title' => 'An error occurred', 'detail' => 'Not Found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $information->getId(),
            'title' => $information->getTitle(),
            'content' => $information->getContent(),
            'news' => $information->getNews(),
            'slug' => $information->getSlug(),
        ]);
    }

    private function offset(Request $request, int $perPage): int
    {
        return (max(1, $request->query->getInt('page', 1)) - 1) * $perPage;
    }

    private static function formatDate(?\DateTimeInterface $date): ?string
    {
        return $date?->format(\DateTimeInterface::RFC3339);
    }
}
