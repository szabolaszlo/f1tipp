<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 01. 07.
 * Time: 13:09
 */

namespace App\Controller\Module\MessageWall;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Message;

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
        $this->data['token'] = md5(time());
        $this->session->set('token', $this->data['token']);

        $this->data['messages'] = $this->entityManager
            ->getRepository('App\Entity\Message')
            ->findBy(array(), array('id' => 'DESC'), self::MESSAGE_COUNT);

        $this->data['userToken'] = $this->registry->getUserAuth()->getActualToken();

        return $this->render();
    }

    /**
     * @return string
     */
    public function formAction()
    {
        $this->data['token'] = md5(time());
        $this->session->set('token', $this->data['token']);

        $this->data['placeholder'] = $this->session->get('messages_placeholder');
        $this->session->remove('messages_placeholder');

        $this->data['userToken'] = $this->registry->getUserAuth()->getActualToken();

        $this->setTemplate('controller/module/message_wall/form.tpl');

        return $this->render();
    }

    /**
     * @return string
     */
    public function messagesAction()
    {
        $this->data['messages'] = $this->entityManager
            ->getRepository('App\Entity\Message')
            ->findBy(array(), array('id' => 'DESC'), self::MESSAGE_COUNT);

        $this->setTemplate('controller/module/message_wall/messages.tpl');

        return $this->render();
    }

    public function saveAction()
    {
        $user = $this->registry->getUserAuth()->getUserByToken($this->request->getPost('user-token'));

        if ($this->request->isPost()
            && $user
            && $this->request->getPost('token', 'notEqual') == $this->session->get('token')
            && $this->request->getPost('message')
        ) {
            $message = new Message();
            $message->setContent($this->turnUrlIntoHyperlink($this->request->getPost('message')));
            $message->setUser($user);
            $message->setDateTime(new \DateTime());

            $this->entityManager->persist($message);
            $this->entityManager->flush();
        } else {
            $placeholder = $user
                ? $this->registry->getLanguage()->get($this->id . '_empty_message')
                : $this->registry->getLanguage()->get($this->id . '_not_logged_in');

            $this->session->set('messages_placeholder', $placeholder);
        }

        $this->session->remove('token');
    }

    protected function turnUrlIntoHyperlink($string){

        //The Regular Expression filter
        $reg_exUrl = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";

        // Check if there is a url in the text
        if(preg_match_all($reg_exUrl, $string, $url)) {

            // Loop through all matches
            foreach($url[0] as $newLinks){
                if(strstr( $newLinks, ":" ) === false){
                    $link = 'http://'.$newLinks;
                }else{
                    $link = $newLinks;
                }

                // Create Search and Replace strings
                $search  = $newLinks;
                $replace = '<a href="'.$link.'" title="'.$newLinks.'" target="_blank">'.$link.'</a>';
                $string = str_replace($search, $replace, $string);
            }
        }

        //Return result
        return $string;
    }
}
