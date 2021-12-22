<?php

namespace App\LegacyService\ResultTable\Type;

use App\Entity\Event;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Summary extends ATableType
{
    /**
     * @var string
     */
    protected $type = 'summary';

    /**
     * @var string
     */
    protected $template = 'extension/result_table/type/summary.html.twig';

    /**
     * @param Event $event
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderTable(Event $event): string
    {
        $data['result'] = $this->em->getRepository('App:AlternativeChampionship')->findBy(
            ['race' => $event->getId()],
            ['points'=>'DESC']
        );


        return $this->renderer->render($this->template, $data);
    }
}
