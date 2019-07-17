<?php

namespace Controller;

use Difra\Ajaxer;
use Difra\Locales;
use Difra\Param;
use Difra\Users\Register;
use Difra\Users\User;
use Difra\Users\UsersException;

class Login extends \Difra\Controller
{
    /**
     * Login form
     * @throws \Difra\Exception
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
     * @param Param\AjaxString $login
     * @param Param\AjaxString $password
     * @param Param\AjaxCheckbox $rememberMe
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
     * @param Param\AjaxString $login
     * @param Param\AjaxString $password
     * @param Param\AjaxCheckbox $rememberMe
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
     * @param Param\AjaxCheckbox $submit
     * @param Param\AjaxString $oldpassword
     * @param Param\AjaxString $password1
     * @param Param\AjaxString $password2
     * @throws UsersException
     * @throws \Difra\Exception
     */
    public function passwordAjaxActionAuth(
        Param\AjaxCheckbox $submit,
        Param\AjaxString $oldpassword = null,
        Param\AjaxString $password1 = null,
        Param\AjaxString $password2 = null
    ) {
        if (!$submit->val()) {
            \Difra\Events\Event::getInstance(\Difra\Users::EVENT_PASSWORD_CHANGE_FORM_AJAX)->trigger();
            return;
        }
        if (is_null($oldpassword) or $oldpassword->val() === '') {
            Ajaxer::required('oldpassword');
        }
        if (is_null($password1) or $password1->val() === '') {
            Ajaxer::required('password1');
        }
        if (is_null($password2) or $password2->val() === '') {
            Ajaxer::required('password2');
        }
        if (Ajaxer::hasProblem()) {
            return;
        }
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
        \Difra\Events\Event::getInstance(\Difra\Users::EVENT_PASSWORD_CHANGE_DONE_AJAX)->trigger();
    }
}
