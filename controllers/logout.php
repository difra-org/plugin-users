<?php

use Difra\Ajaxer;
use Difra\Plugins\Users\User;

/**
 * Class LogoutController
 */
class LogoutController extends \Difra\Controller
{
    /**
     * Log out
     */
    public function indexAction()
    {
        User::logout();
        \Difra\View::redirect('/');
    }

    /**
     * Log out (ajax)
     */
    public function indexAjaxAction()
    {
        User::logout();
        $this->afterLogoutAjax();
    }

    /**
     * After ajax logout stuff
     */
    protected function afterLogoutAjax()
    {
        // TODO: redirect to / if page requires auth
        Ajaxer::reload();
    }
}
