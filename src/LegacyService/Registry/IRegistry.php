<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 17.
 * Time: 19:37
 */

namespace App\LegacyService\Registry;

use Application\HttpProtocol\ICookie;
use Application\HttpProtocol\IRequest;
use Application\HttpProtocol\ISession;
use Application\HttpProtocol\Server;
use Doctrine\ORM\EntityManagerInterface;
use App\LegacyService\Cache\Cache;
use App\LegacyService\Calculator\ICalculator;
use App\LegacyService\FormHelper\FormHelper;
use App\LegacyService\Language\Language;
use App\LegacyService\ResultTable\ResultTable;
use App\LegacyService\Rule\Rule;
use App\LegacyService\TrophyHandler\TrophyHandler;
use App\LegacyService\UserAuthentication\Authentication;

/**
 * Interface IRegistry
 * @package App\LegacyService\Registry
 */
interface IRegistry
{
    /**
     * @return IRequest
     */
    public function getRequest();

    /**
     * @return ISession
     */
    public function getSession();
    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager();

    /**
     * @return \Twig_Environment
     */
    public function getRenderer();

    /**
     * @return ICookie
     */
    public function getCookie();

    /**
     * @return Authentication
     */
    public function getUserAuth();

    /**
     * @return Server
     */
    public function getServer();

    /**
     * @return Rule
     */
    public function getRule();

    /**
     * @return FormHelper
     */
    public function getFormHelper();

    /**
     * @return Language
     */
    public function getLanguage();
    
    /**
     * @return ICalculator
     */
    public function getCalculator();

    /**
     * @return ResultTable
     */
    public function getResultTable();

    /**
     * @return Cache
     */
    public function getCache();

    /**
     * @return TrophyHandler
     */
    public function getTrophyHandler();
}
