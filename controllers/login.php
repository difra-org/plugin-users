<?php

use Difra\Ajaxer;
use Difra\Locales;
use Difra\Param;
use Difra\Users\Register;
use Difra\Users\User;
use Difra\Users\UsersException;
use Difra\Controller;

/**
 * Class LoginController
 */
class LoginController extends Controller
{
    /**
     * Login form
     * @return void
     */
    public function indexAjaxAction()
    {
        if (\Difra\Auth::getInstance()->isAuthorized()) {
            Ajaxer::reload();
            return;
        }
        \Difra\Events\Event::getInstance(\Difra\Users::EVENT_LOGIN_FORM_AJAX)->trigger();
    }

    /**
     * User login
     * @param Difra\Param\AjaxString $login
     * @param Difra\Param\AjaxString $password
     * @param Difra\Param\AjaxCheckbox $rememberMe
     */
    public function authAjaxAction(Param\AjaxString $login, Param\AjaxString $password, Param\AjaxCheckbox $rememberMe)
    {
        try {
            User::loginByPassword($login->val(), $password->val(), ($rememberMe->val() == 1) ? true : false);
            \Difra\Events\Event::getInstance(\Difra\Users::EVENT_LOGIN_DONE_AJAX)->trigger();
        } catch (UsersException $ex) {
            switch ($error = $ex->getMessage()) {
                case UsersException::LOGIN_BADPASS:
                    Ajaxer::status('password', Locales::get('auth/login/' . $error), 'problem');
                    break;
//                case UsersException::LOGIN_INACTIVE:
//                    Ajaxer::close();
//                    Ajaxer::display('test');
//                    break;
                default:
                    Ajaxer::status('login', Locales::get('auth/login/' . $error), 'problem');
            }
        } catch (\Difra\Exception $ex) {
            $ex->notify();
//            Ajaxer::status('login', Locales::get('auth/login/' . $ex->getMessage()), 'problem');
        }
    }

    /**
     * User login (stub for logged in users)
     * @param Difra\Param\AjaxString $login
     * @param Difra\Param\AjaxString $password
     * @param Difra\Param\AjaxCheckbox $rememberMe
     */
    public function authAjaxActionAuth(
        /** @noinspection PhpUnusedParameterInspection */
        Param\AjaxString $login,
        Param\AjaxString $password,
        Param\AjaxCheckbox $rememberMe
    ) {
        Ajaxer::reload();
    }

    /**
     * Change password
     * @param Difra\Param\AjaxString $oldpassword
     * @param Difra\Param\AjaxString $password1
     * @param Difra\Param\AjaxString $password2
     */
    public function passwordAjaxActionAuth(
        Param\AjaxString $oldpassword,
        Param\AjaxString $password1,
        Param\AjaxString $password2
    ) {
        $user = User::getCurrent();
        if (!$user->verifyPassword($oldpassword)) {
            Ajaxer::status('oldpassword', Locales::get('auth/password/bad_old'), 'problem');
            $ok = false;
        } else {
            $ok = true;
        }
        $reg = new Register();
        $reg->setPassword1($password1->val());
        $reg->setPassword2($password2->val());
        if (!$reg->validatePasswords()) {
            if ($ok) {
                Ajaxer::status('oldpassword', Locales::get('auth/password/old_ok'), 'ok');
            }
            $reg->callAjaxerEvents();
            return;
        }
        if (!$ok) {
            return;
        }
        $user->setPassword($password1->val());
        \Difra\Events\Event::getInstance(\Difra\Users::EVENT_PASSWORD_CHANGED_AJAX)->trigger();
    }
}
