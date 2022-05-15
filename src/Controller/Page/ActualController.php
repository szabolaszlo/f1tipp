<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 30.
 * Time: 23:30
 */

namespace App\Controller\Page;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ActualController
 * @package App\Controller\Page
 */
class ActualController extends AbstractController
{
    /**
     * @Route(path="/", name="home", methods={"GET"})
     * @return Response
     * @throws Exception
     */
    public function indexAction(): Response
    {
        return $this->render('controller/page/actual.html.twig');
    }
}
