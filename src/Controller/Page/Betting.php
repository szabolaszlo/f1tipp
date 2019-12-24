<?php

namespace App\Controller\Page;

use App\Builder\BetBuilder;
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
     * @Route("/betting", name="betting", methods={"GET", "POST"})
     *
     * @param Request $request
     * @param BetBuilder $betBuilder
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Exception
     */
    public function indexAction(Request $request, BetBuilder $betBuilder, TranslatorInterface $translator)
    {
        $user = $this->getUser();

        if (!$user) {
            throw new UnauthorizedHttpException('');
        }

        $qualify = $this->getDoctrine()->getRepository('App:Qualify')->getNextEvent();
        $race = $this->getDoctrine()->getRepository('App:Race')->getNextEvent();

        $userQualifyBet = $this->getDoctrine()->getRepository('App:Bet')->getBetByUserAndEvent(
            $user,
            $qualify
        );

        $userRaceBet = $this->getDoctrine()->getRepository('App:Bet')->getBetByUserAndEvent(
            $user,
            $race
        );

        $qualifyDefaultBet = $userQualifyBet ?? $betBuilder->buildForEvent($qualify);
        $qualifyDefaultBet->setUser($this->getUser());

        $raceDefaultBet = $userRaceBet ?? $betBuilder->buildForEvent($race);
        $raceDefaultBet->setUser($this->getUser());

        $events = [
            $this->getTimeDiff($qualify->getDateTime()) => [
                'event' => $qualify,
                'form' => $this->get('form.factory')->createNamed(
                    $qualifyDefaultBet->getEvent()->getType() . 'betting_form',
                    BettingType::class, $qualifyDefaultBet),
            ],
            $this->getTimeDiff($race->getDateTime()) => [
                'event' => $race,
                'form' => $this->get('form.factory')->createNamed(
                    $raceDefaultBet->getEvent()->getType() . 'betting_form',
                    BettingType::class, $raceDefaultBet),
            ]
        ];

        ksort($events);

        foreach ($events as &$event) {
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

                    return $this->redirectToRoute('betting');
                } else {
                    $form->addError(new FormError($translator->trans('betting_time_out')));
                }
            }
            $event['form'] = $form->createView();
        }

        return $this->render('controller/page/betting.html.twig', ['events' => $events]);
    }

    protected function getTimeDiff(\DateTime $time)
    {
        return abs(time() - $time->getTimestamp());
    }
}
