<?php

namespace App\Controller\Module;

use App\Builder\BetBuilder;
use App\Display\BettingFormPresenter;
use App\Entity\Bet;
use App\Entity\Event;
use App\Form\BettingType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class Betting
 * @package App\Controller\Page\Betting
 */
class Betting extends AbstractController
{
    /**
     * A tippelő form JSON leírása (GET), illetve a beküldött tipp mentése (POST).
     * A Symfony form végzi a validációt és a CSRF-ellenőrzést, a megjelenítés a Vue-é.
     *
     * @Route("module/betting/form/{eventId}", name="betting_one_form", methods={"GET", "POST"})
     */
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
        $event = $this->getDoctrine()->getRepository(\App\Entity\Event::class)->find($eventId);

        if (!$event) {
            throw $this->createNotFoundException();
        }

        $bet = $this
            ->getDoctrine()
            ->getRepository(\App\Entity\Bet::class)
            ->getBetByUserAndEventNonCache($user, $event);

        $bet = $bet ?? $betBuilder->buildForEvent($event);
        $bet->setUser($user);

        $form = $this->createBettingForm($bet, $eventId);
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
                $this->get("security.csrf.token_manager")->refreshToken("form_intention");
                $entityManager = $this->getDoctrine()->getManager();
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
        return $this->get('form.factory')->createNamed(
            'betting_form',
            BettingType::class,
            $bet,
            ['action' => '/module/betting/form/' . $eventId]
        );
    }

    /**
     * @Route("/module/betting/weekend-events", name="betting-weekend-events", methods={"GET"})
     */
    public function weekendEventsAction(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            throw new UnauthorizedHttpException('');
        }

        $events = $this->getDoctrine()->getRepository(\App\Entity\Event::class)->getActualWeekendEvents();

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
