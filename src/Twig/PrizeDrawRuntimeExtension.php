<?php

namespace App\Twig;

use App\Repository\PrizeDrawRepository;
use App\Repository\UserRepository;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * Nyeremény modul (oldalsáv, csak belépve): az admin "Nyeremény" beállításából (PrizeDraw).
 * Nyitott nevezésnél csak a bajnokság tagjainak élő száma látszik. Hiányzó beállításnál nem renderel semmit.
 */
class PrizeDrawRuntimeExtension implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly PrizeDrawRepository $prizeDraws,
        private readonly UserRepository $users
    ) {
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function renderPrizeDrawExtension($isLoggedUser): string
    {
        if (!$isLoggedUser) {
            return '';
        }

        $prizeDraw = $this->prizeDraws->get();
        if ($prizeDraw === null) {
            return '';
        }

        return $this->twig->render('extension/prize_draw.html.twig', [
            'prizeDraw' => $prizeDraw,
            'participantCount' => $prizeDraw->isEntryOpen() ? $this->users->countAlternativeChampionshipUsers() : null,
        ]);
    }
}
