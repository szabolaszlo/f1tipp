<?php

namespace App\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * Class AlternativeChampionshipRuntimeExtension
 * @package App\Twig
 */
class AlternativeChampionshipRuntimeExtension implements RuntimeExtensionInterface
{
    /**
     * @var Environment
     */
    protected $renderer;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * AlternativeChampionshipRuntimeExtension constructor.
     * @param Environment $renderer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(Environment $renderer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator)
    {
        $this->renderer = $renderer;
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderAlternativeChampionship()
    {
        return $this->renderer->render('extension/alternative_championship.html.twig', [
            'id' => 'alternative_championship_extension',
            'details_link' => $this->urlGenerator->generate('alternative_championship'),
            'users' => $this->em->getRepository('App:User')->getAlternativeChampionshipUsers()
        ]);
    }
}
