<?php

namespace Controller;

use Difra\Events\Event;
use Difra\Users;
use Difra\Users\User;

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
     * @throws \Difra\Exception
     */
    public function indexAjaxAction()
    {
        User::logout();
        Event::getInstance(Users::EVENT_LOGOUT_DONE_AJAX)->trigger();
    }
}
