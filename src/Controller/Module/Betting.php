<?php

namespace App\Controller\Module;

use App\Builder\BetBuilder;
use App\Entity\Event;
use App\Form\BettingType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
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
     * @Route("module/betting/form/{eventId}", name="betting_one_form", methods={"GET", "POST"})
     *
     * @param Request $request
     * @param BetBuilder $betBuilder
     * @param TranslatorInterface $translator
     * @param $eventId
     * @return Response
     */
    public function indexAction(Request $request, BetBuilder $betBuilder, TranslatorInterface $translator, $eventId)
    {
        sleep(1);
        $user = $this->getUser();

        if (!$user) {
            throw new UnauthorizedHttpException('');
        }

        $event = $this->getDoctrine()->getRepository('App:Event')->find($eventId);

        $bets = [];

        $eventType = $event->getType();
        $bets[$eventType] = $this
            ->getDoctrine()
            ->getRepository('App:Bet')
            ->getBetByUserAndEventNonCache(
                $user,
                $event
            );

        $bets[$eventType] = $bets[$eventType] ?? $betBuilder->buildForEvent($event);
        $bet = $bets[$eventType];
        $bet->setUser($this->getUser());

        $bet = $bets[$event->getType()];

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamed(
            'betting_form',
            BettingType::class, $bet,
        ['action' => '/module/betting/form/' . $eventId]);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("form_intention");
            $bet = $form->getData();
            $now = new \DateTime();

            // check bet is before deadline and form has no hacked event or user
            if ($now < $bet->getEvent()->getDateTime()
                && $event->getId() == $bet->getEvent()->getId()
                && $bet->getUser()->getId() == $user->getId()
            ) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($bet);
                $entityManager->flush();

                $this->addFlash($form->getName(), 'betting_success');

                return $this->redirectToRoute(
                    'betting_one_form',
                    ['eventId' => $eventId]
                );
            } else {
                $form->addError(new FormError($translator->trans('betting_time_out')));
            }
        }
        $form = $form->createView();

        return $this->render('controller/page/betting.html.twig', [
            'event' => $event,
            'form' => $form,
            'type' => $eventType
        ]);
    }

    /**
     * @Route("/module/betting/weekend-events", name="betting-weekend-events", methods={"GET"})
     */
    public function weekendEventsAction(): JsonResponse
    {
        sleep(1);

        $user = $this->getUser();

        if (!$user) {
            throw new UnauthorizedHttpException('');
        }

        $events = $this->getDoctrine()->getRepository('App:Event')->getActualWeekendEvents();

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
