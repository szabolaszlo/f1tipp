<?php

namespace App\Twig;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

class PrizeDrawRuntimeExtension implements RuntimeExtensionInterface
{
    /**
     * @var Environment
     */
    protected Environment $twig;

    /**
     * @param Environment $twig
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function renderPrizeDrawExtension($isLoggedUser): string
    {
        return $isLoggedUser ? $this->twig->render(
            'extension/prize_draw.html.twig',
            [
                'userLogged' => (bool)$isLoggedUser,
                'id' => 'prizeDraw'
            ]
        ) : '';
    }

}