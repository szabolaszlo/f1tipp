<?php

namespace App\Twig;

use App\Calculator\Provider\PointProvider;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

class PointCalculatingInfoRuntimeExtension implements RuntimeExtensionInterface
{
    /**
     * @var Environment
     */
    protected Environment $twig;

    protected PointProvider $pointProvider;

    public function __construct(Environment $twig, PointProvider $pointProvider)
    {
        $this->twig = $twig;
        $this->pointProvider = $pointProvider;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function renderPointCalculatingInfo($isLoggedUser): string
    {
        return $isLoggedUser ? $this->twig->render(
            'extension/point_calculating_info.html.twig',
            [
                'pointProvider' => $this->pointProvider,
                'userLogged' => (bool)$isLoggedUser,
                'id' => 'pointCalculatingInfo'
            ]
        ) : '';
    }
}