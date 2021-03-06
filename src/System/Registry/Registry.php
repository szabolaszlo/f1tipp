<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 17.
 * Time: 19:25
 */

namespace System\Registry;

use Application\HttpProtocol\ICookie;
use Application\HttpProtocol\IRequest;
use Application\HttpProtocol\ISession;
use Application\HttpProtocol\Server;
use Doctrine\ORM\EntityManagerInterface;
use System\Cache\Cache;
use System\Cache\Strategy\File;
use System\Calculator\Calculator;
use System\Calculator\ICalculator;
use System\FormHelper\FormHelper;
use System\FormHelper\SelectOption\Driver;
use System\FormHelper\SelectOption\Question;
use System\Language\Language;
use System\ResultTable\Decorator\BetDecorator;
use System\ResultTable\ResultTable;
use System\ResultTable\Type\Full;
use System\ResultTable\Type\OnlyBets;
use System\ResultTable\Type\OnlyUsers;
use System\Rule\Rule;
use System\Rule\RuleType\Qualify as QualifyRule;
use System\Rule\RuleType\Race as RaceRule;
use System\TrophyHandler\TrophyHandler;
use System\UserAuthentication\Authentication;

/**
 * Class Registry
 * @package System\Registry
 */
class Registry implements IRegistry
{
    /**
     * @var IRequest
     */
    protected $request;

    /**
     * @var ISession
     */
    protected $session;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ICookie
     */
    protected $cookie;

    /**
     * @var Authentication
     */
    protected $userAuth;

    /**
     * @var Server
     */
    protected $server;

    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var FormHelper
     */
    protected $formHelper;

    /**
     * @var \Twig_Environment
     */
    protected $renderer;

    /**
     * @var Language
     */
    protected $language;

    /**
     * @var ICalculator
     */
    protected $calculator;

    /**
     * @var ResultTable
     */
    protected $resultTable;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var TrophyHandler
     */
    protected $trophyHandler;

    /**
     * Registry constructor.
     * @param IRequest $request
     * @param ISession $session
     * @param $entityManager
     * @param ICookie $cookie
     * @param $renderer
     */
    public function __construct(IRequest $request, ISession $session, $entityManager, ICookie $cookie, $renderer)
    {
        $this->request = $request;
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->cookie = $cookie;
        $this->renderer = $renderer;
    }

    /**
     * @return IRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return ISession
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return \Twig_Environment
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * @return ICookie
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * @return Authentication
     */
    public function getUserAuth()
    {
        if (!$this->userAuth) {
            $this->userAuth = new Authentication($this->entityManager, $this->cookie);
        }
        return $this->userAuth;
    }

    /**
     * @return Server
     */
    public function getServer()
    {
        if (!$this->server) {
            $this->server = new Server();
        }
        return $this->server;
    }

    /**
     * @return Rule
     */
    public function getRule()
    {
        if (!$this->rule) {
            $ruleTypes = array(
                'qualify' => new QualifyRule(),
                'race' => new RaceRule()
            );

            $this->rule = new Rule($ruleTypes);
        }

        return $this->rule;
    }

    /**
     * @return FormHelper
     */
    public function getFormHelper()
    {
        if (!$this->formHelper) {
            $optionTypes = array(
                'driver' => new Driver($this->entityManager, $this->renderer),
                'question' => new Question($this->entityManager, $this->renderer)
            );

            $this->formHelper = new FormHelper($optionTypes);
        }

        return $this->formHelper;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        if (!$this->language) {
            $this->language = new Language('Hungarian');
        }

        return $this->language;
    }

    /**
     * @return ICalculator
     */
    public function getCalculator()
    {
        if (!$this->calculator) {
            $this->calculator = new Calculator($this);
        }

        return $this->calculator;
    }

    /**
     * @return ResultTable
     */
    public function getResultTable()
    {
        if (!$this->resultTable) {
            $decorator = new BetDecorator($this);

            $tableTypes = array(
                'full' => new Full($this, $this->getCalculator(), $decorator),
                'only_users' => new OnlyUsers($this),
                'only_bets' => new OnlyBets($this)
            );

            $this->resultTable = new ResultTable($this, $tableTypes);
        }

        return $this->resultTable;
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        if (!$this->cache) {
            $fileStrategy = new File();
            $this->cache = new Cache($fileStrategy);
        }

        return $this->cache;
    }

    /**
     * @return TrophyHandler
     */
    public function getTrophyHandler()
    {
        if (!$this->trophyHandler) {
            $this->trophyHandler = new TrophyHandler($this);
        }

        return $this->trophyHandler;
    }
}
