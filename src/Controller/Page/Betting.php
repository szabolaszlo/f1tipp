<?php

namespace App\Controller\Page;

use App\Builder\BetBuilder;
use App\Entity\Event;
use App\Form\BettingType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
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
     * @Route("/betting/{type}", name="betting", methods={"GET", "POST"}, defaults={"type"="new"})
     *
     * @param Request $request
     * @param BetBuilder $betBuilder
     * @param TranslatorInterface $translator
     * @param $type
     * @return Response
     */
    public function indexAction(Request $request, BetBuilder $betBuilder, TranslatorInterface $translator, $type)
    {
        $user = $this->getUser();

        if (!$user) {
            throw new UnauthorizedHttpException('');
        }

        $events = $this->getDoctrine()->getRepository('App:Event')->getActualWeekendEvents();

        $bets = [];

        /** @var Event $event */
        foreach ($events as $event) {
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
        }

        $eventsData = [];
        foreach ($events as $event) {
            $bet = $bets[$event->getType()];
            $eventsData[$this->getTimeDiff($event->getDateTime())] = [
                'event' => $event,
                'form' => $this->get('form.factory')->createNamed(
                    $bet->getEvent()->getType() . 'betting_form',
                    BettingType::class, $bet)
            ];
        }
        ksort($eventsData);

        foreach ($eventsData as &$event) {
            /** @var Form $form */
            $form = $event['form'];
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->get("security.csrf.token_manager")->refreshToken("form_intention");
                $bet = $form->getData();
                $now = new \DateTime();

                // check bet is before deadline and form has no hacked event or user
                if ($now < $bet->getEvent()->getDateTime()
                    && $event['event']->getId() == $bet->getEvent()->getId()
                    && $bet->getUser()->getId() == $user->getId()
                ) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($bet);
                    $entityManager->flush();

                    $this->addFlash($form->getName(), 'betting_success');

                    return $this->redirectToRoute(
                        'betting',
                        ['type' => $bet->getEvent()->getType()]
                    );
                } else {
                    $form->addError(new FormError($translator->trans('betting_time_out')));
                }
            }
            $event['form'] = $form->createView();
        }

        return $this->render('controller/page/betting.html.twig', [
            'events' => $eventsData,
            'type' => $type
        ]);
    }

    protected function getTimeDiff(\DateTime $time)
    {
        return abs(time() - $time->getTimestamp());
    }
}
