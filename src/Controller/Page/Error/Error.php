<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 07.
 * Time: 21:35
 */

namespace App\Controller\Page\Error;

use App\Controller\Controller;

/**
 * Class Error
 * @package App\Controller\Page
 */
class Error extends Controller
{
    /**
     * @return mixed
     */
    public function notFoundAction()
    {
        return $this->render();
    }
}
