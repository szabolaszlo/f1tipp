<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 01. 07.
 * Time: 13:09
 */

namespace App\Controller\Module;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class MessageWall
 * @package App\Controller\Module\MessageWall
 */
class MessageWall extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine)
    {
    }

    /**
     * @return string
     */
    #[Route(path: '/module/messages/list', name: 'message_list', methods: ['GET'])]
    public function messagesAction()
    {
        // a fal csak belépett usereknek jelenik meg (MessageWallRuntimeExtension), a lista se legyen publikus
        if (!$this->getUser()) {
            throw new UnauthorizedHttpException('');
        }

        return $this->render('controller/module/message_wall/messages.html.twig', [
            'messages' => $this->doctrine->getRepository(\App\Entity\Message::class)->getMessages()
        ]);
    }

    /**
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    #[Route(path: '/module/messages/save', name: 'message_save', methods: ['POST'])]
    public function saveAction(Request $request)
    {
        if ($request->get('message') && $this->getUser()) {
            $message = new Message();
            // a fal |raw-val jeleníti meg a tartalmat (a linkek miatt): a felhasználó szövegét előbb escape-eljük,
            // csak a mi általunk generált <a> tag maradhat HTML
            $text = htmlspecialchars((string) $request->get('message'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $message->setContent($this->turnUrlIntoHyperlink($text));
            $message->setUser($this->getUser());
            $message->setDateTime(new \DateTime());

            $this->doctrine->getManager()->persist($message);
            $this->doctrine->getManager()->flush();
        }

        return new Response();
    }

    protected function turnUrlIntoHyperlink($string)
    {

        //The Regular Expression filter
        $reg_exUrl = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";

        // egyetlen menetben cserélünk: a korábbi str_replace-es ciklus a már legenerált <a> tagekben is
        // lecserélte az ismétlődő / egymás elejét adó URL-eket, ami kitört a href attribútumból (XSS)
        return preg_replace_callback($reg_exUrl, function (array $match) {
            $url = $match[0];
            $link = strstr($url, ':') === false ? 'http://' . $url : $url;

            return '<a href="' . $link . '" title="' . $url . '" target="_blank">' . $link . '</a>';
        }, $string);
    }
}
