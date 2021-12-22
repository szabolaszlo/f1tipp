<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 01. 07.
 * Time: 13:09
 */

namespace App\Controller\Module;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MessageWall
 * @package App\Controller\Module\MessageWall
 */
class MessageWall extends AbstractController
{
    const MESSAGE_COUNT = 100;

    /**
     * @return mixed
     */
    public function indexAction()
    {
        if ($this->getUser()) {
            return $this->render('controller/module/message_wall/message_wall.html.twig', [
                'id' => 'messageWall',
                'messages' => $this->getDoctrine()->getRepository('App:Message')->getMessages()
            ]);
        } else {
            return new Response();
        }
    }

    /**
     * @Route("/module/messages/list", name="message_list", methods={"GET"})
     * @return string
     */
    public function messagesAction()
    {
        return $this->render('controller/module/message_wall/messages.html.twig', [
            'messages' => $this->getDoctrine()->getRepository('App:Message')->getMessages()
        ]);
    }

    /**
     * @Route("/module/messages/save", name="message_save", methods={"POST"})
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function saveAction(Request $request)
    {
        if ($request->get('message') && $this->getUser()) {
            $message = new Message();
            $message->setContent($this->turnUrlIntoHyperlink($request->get('message')));
            $message->setUser($this->getUser());
            $message->setDateTime(new \DateTime());

            $this->getDoctrine()->getManager()->persist($message);
            $this->getDoctrine()->getManager()->flush();
        }

        return new Response();
    }

    protected function turnUrlIntoHyperlink($string)
    {

        //The Regular Expression filter
        $reg_exUrl = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";

        // Check if there is a url in the text
        if (preg_match_all($reg_exUrl, $string, $url)) {

            // Loop through all matches
            foreach ($url[0] as $newLinks) {
                if (strstr($newLinks, ":") === false) {
                    $link = 'http://' . $newLinks;
                } else {
                    $link = $newLinks;
                }

                // Create Search and Replace strings
                $search = $newLinks;
                $replace = '<a href="' . $link . '" title="' . $newLinks . '" target="_blank">' . $link . '</a>';
                $string = str_replace($search, $replace, $string);
            }
        }

        //Return result
        return $string;
    }
}
