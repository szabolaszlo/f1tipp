<?php

namespace App\Controller\Module;

use App\Builder\BetBuilder;
use App\Display\BettingFormPresenter;
use App\Entity\Bet;
use App\Entity\Event;
use App\Form\BettingType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class Betting
 * @package App\Controller\Page\Betting
 */
class Betting extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine)
    {
    }

    /**
     * A tippelő form JSON leírása (GET), illetve a beküldött tipp mentése (POST).
     * A Symfony form végzi a validációt és a CSRF-ellenőrzést, a megjelenítés a Vue-é.
     */
    #[Route(path: 'module/betting/form/{eventId}', name: 'betting_one_form', methods: ['GET', 'POST'])]
    public function indexAction(
        Request $request,
        BetBuilder $betBuilder,
        TranslatorInterface $translator,
        BettingFormPresenter $presenter,
        $eventId
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user) {
            throw new UnauthorizedHttpException('');
        }

        /** @var Event|null $event */
        $event = $this->doctrine->getRepository(\App\Entity\Event::class)->find($eventId);

        if (!$event) {
            throw $this->createNotFoundException();
        }

        $bet = $this->doctrine
            ->getRepository(\App\Entity\Bet::class)
            ->getBetByUserAndEventNonCache($user, $event);

        $bet = $bet ?? $betBuilder->buildForEvent($event);
        $bet->setUser($user);

        $form = $this->createBettingForm($bet, $eventId);

        // a mentett tipp végleges: a zárolt mezők csak a felületen tiltottak, ezért a szerver sem fogad el rá
        // újabb beküldést (különben a többiek – mentés után már látható – tippjei alapján át lehetne írni)
        if ($bet->getId() && $request->isMethod('POST')) {
            $form->addError(new FormError($translator->trans('betting_already_saved')));

            return new JsonResponse($presenter->present($form, $event), Response::HTTP_CONFLICT);
        }

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return new JsonResponse($presenter->present($form, $event));
        }

        if ($form->isValid()) {
            $bet = $form->getData();
            $now = new \DateTime();

            // check bet is before deadline and form has no hacked event or user
            if ($now < $bet->getEvent()->getDateTime()
                && $event->getId() == $bet->getEvent()->getId()
                && $bet->getUser()->getId() == $user->getId()
            ) {
                $this->container->get('security.csrf.token_manager')->refreshToken('form_intention');
                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($bet);
                $entityManager->flush();

                // friss form a mentett tippre: zárolt mezők, nincs beküldő gomb
                return new JsonResponse($presenter->present(
                    $this->createBettingForm($bet, $eventId),
                    $event,
                    'betting_success'
                ));
            }

            $form->addError(new FormError($translator->trans('betting_time_out')));
        }

        return new JsonResponse($presenter->present($form, $event), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function createBettingForm(Bet $bet, $eventId): FormInterface
    {
        return $this->container->get('form.factory')->createNamed(
            'betting_form',
            BettingType::class,
            $bet,
            ['action' => '/module/betting/form/' . $eventId]
        );
    }

    #[Route(path: '/module/betting/weekend-events', name: 'betting-weekend-events', methods: ['GET'])]
    public function weekendEventsAction(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            throw new UnauthorizedHttpException('');
        }

        $events = $this->doctrine->getRepository(\App\Entity\Event::class)->getActualWeekendEvents();

        $eventsData = [];
        foreach ($events as $event) {
            $eventsData[$this->getTimeDiff($event->getDateTime())] = [
                'event' => $event,
            ];
        }
        ksort($eventsData);

        $events = [];

        foreach ($eventsData as $eventData) {
            $events[] = $eventData['event'];
        }

        return new JsonResponse($events);
    }

    protected function getTimeDiff(\DateTime $time)
    {
        return abs(time() - $time->getTimestamp());
    }
}
