<?php

namespace App\Controller\Admin;

use App\Season\ConfirmationCode;
use App\Season\SeasonFactsProvider;
use App\Season\SeasonResetGuard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Szezonváltás: a szezon végi nullázás oldala (ROLE_SUPER_ADMIN). Az admin kontextusban a
 * /admin?routeName=season_reset címen érhető el (MenuItem::linkToRoute).
 */
class SeasonResetController extends AbstractController
{
    #[Route(path: '/admin/maintenance/season_reset', name: 'season_reset', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function indexAction(
        SeasonFactsProvider $factsProvider,
        SeasonResetGuard $guard,
        ConfirmationCode $confirmationCode,
    ): Response {
        $now = new \DateTimeImmutable();
        $facts = $factsProvider->load($now);
        $blocks = $guard->blockReasons($facts, $now);

        return $this->render('admin/season_reset.html.twig', [
            'facts' => $facts,
            'blocks' => $blocks,
            'warnings' => $guard->warnings($facts),
            // kód csak akkor készül, ha a nullázás engedélyezett
            'code' => $blocks === [] ? $confirmationCode->issue() : null,
        ]);
    }

    /** Átmeneti: a tényleges nullázás a következő feladatban készül; most csak visszairányít az oldalra. */
    #[Route(path: '/admin/maintenance/season_reset/execute', name: 'season_reset_execute', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function executeAction(AdminUrlGenerator $adminUrlGenerator): RedirectResponse
    {
        return $this->redirect(
            $adminUrlGenerator->setDashboard(DashboardController::class)->setRoute('season_reset')->generateUrl()
        );
    }
}
