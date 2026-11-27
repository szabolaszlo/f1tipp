<?php

namespace App\Controller\Admin;

use App\Season\ConfirmationCode;
use App\Season\SeasonFactsProvider;
use App\Season\SeasonReset;
use App\Season\SeasonResetGuard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;

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

    /**
     * A nullázás. Sorrend: a kód beváltása (mindig elsőként: a sessionbeli kód minden POST után törlődik),
     * CSRF, a tiltások újraellenőrzése, majd a nullázás. Az első hibánál hibaüzenettel visszairányít, és semmit nem töröl.
     * A reset() kivételét szándékosan nem kapjuk el: ha a commit után (a cache-ürítésben) dob, az adat már törlődött,
     * ezért a "semmi nem törlődött" üzenet hazugság lenne; a hiba 500-ként jelenik meg.
     */
    #[Route(path: '/admin/maintenance/season_reset/execute', name: 'season_reset_execute', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function executeAction(
        Request $request,
        ConfirmationCode $confirmationCode,
        SeasonFactsProvider $factsProvider,
        SeasonResetGuard $guard,
        SeasonReset $seasonReset,
        AdminUrlGenerator $adminUrlGenerator,
    ): RedirectResponse {
        $back = $this->redirect(
            $adminUrlGenerator->setDashboard(DashboardController::class)->setRoute('season_reset')->generateUrl()
        );

        if (!$confirmationCode->consume($this->postedString($request, 'code'))) {
            $this->addFlash('danger', new TranslatableMessage('admin_season_reset_flash_bad_code'));

            return $back;
        }

        if (!$this->isCsrfTokenValid('season_reset', $this->postedString($request, '_token'))) {
            $this->addFlash('danger', new TranslatableMessage('admin_season_reset_flash_bad_csrf'));

            return $back;
        }

        $now = new \DateTimeImmutable();
        if ($guard->blockReasons($factsProvider->load($now), $now) !== []) {
            $this->addFlash('danger', new TranslatableMessage('admin_season_reset_flash_blocked'));

            return $back;
        }

        $counts = $seasonReset->reset();

        $this->addFlash('success', new TranslatableMessage('admin_season_reset_flash_success', [
            '%bets%' => $counts->bets,
            '%results%' => $counts->results,
            '%trophies%' => $counts->trophies,
            '%alternatives%' => $counts->alternatives,
            '%users%' => $counts->users,
        ]));

        return $back;
    }

    /** a beküldött mező szövegként; hiányzó vagy nem szöveg (pl. code[]=...) mező üres szöveg, nem 400-as hiba */
    private function postedString(Request $request, string $name): string
    {
        $value = $request->request->all()[$name] ?? '';

        return is_string($value) ? $value : '';
    }
}
