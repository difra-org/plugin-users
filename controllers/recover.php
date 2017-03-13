<?php

use Difra\Ajaxer;
use Difra\Exception;
use Difra\Libs\Cookies;
use Difra\Locales;
use Difra\Param\AjaxString;
use Difra\Param\AnyString;
use Difra\Users\Recover;
use Difra\View;

/**
 * Class RecoverController
 */
class RecoverController extends \Difra\Controller
{
    /**
     * Recover password (ajax)
     * @param AjaxString $login Login or e-mail
     * @param AjaxString $captcha
     * @throws Exception
     */
    public function indexAjaxAction(AjaxString $submit = null, AjaxString $login = null, AjaxString $captcha = null)
    {
        // show recover form
        if (is_null($submit)) {
            \Difra\Events\Event::getInstance(\Difra\Users::EVENT_RECOVER_FORM_AJAX)->trigger();
            return;
        }
        $error = false;
        // login's empty
        if (is_null($login) or $login->val() === '') {
            Ajaxer::required('login');
            $error = true;
        }
        if (is_null($captcha) or $captcha->val() === '') {
            Ajaxer::required('captcha');
            $error = true;
        } elseif (!\Difra\Capcha::getInstance()->verifyKey($captcha->val())) {
            Ajaxer::invalid('captcha');
            $error = true;
        }
        if ($error) {
            return;
        }
        // recover
        try {
            Recover::send($login->val());
            \Difra\Events\Event::getInstance(\Difra\Users::EVENT_RECOVER_DONE_AJAX)->trigger();
        } catch (Exception $ex) {
//            Ajaxer::status('email', Locales::get('auth/login/' . $ex->getMessage()), 'problem');
            Ajaxer::error(Locales::get('auth/login/' . $ex->getMessage()));
        }
    }

    /**
     * Recover password (stub)
     */
    public function indexActionAuth()
    {
        View::redirect('/');
    }

    /**
     * Recover password (ajax, stub)
     */
    public function indexAjaxActionAuth()
    {
        Ajaxer::reload();
    }

    /**
     * Password recovery link
     * @param AnyString $code
     */
    public function codeAction(AnyString $code)
    {
        try {
            Recover::verify($code->val());
        } catch (Exception $ex) {
            Cookies::getInstance()->notify(Locales::get('auth/recover/' . $ex->getMessage()), true);
            View::redirect('/');
        }
        /** @var \DOMElement $recoverNode */
        $recoverNode = $this->root->appendChild($this->xml->createElement('recover2'));
        $recoverNode->setAttribute('code', $code->val());

//        Ajaxer::display(View::render($this->xml, 'auth-ajax', true));
    }

    /**
     * Password recovery link (stub)
     * @param AnyString $code
     */
    public function codeActionAuth(
        /** @noinspection PhpUnusedParameterInspection */
        AnyString $code
    ) {
        Cookies::getInstance()->notify(Locales::get('auth/recover/already_logged'), true);
        View::redirect('/');
    }

    /**
     * Change password using recovery link
     * @param AnyString $code
     * @param AjaxString $password1
     * @param AjaxString $password2
     * @throws Exception
     */
    public function submitAjaxAction(AnyString $code, AjaxString $password1, AjaxString $password2)
    {
        try {
            Recover::verify($code->val());
        } catch (\Difra\Users\UsersException $ex) {
            Ajaxer::notify(Locales::get('auth/recover/' . $ex->getMessage()));
            return;
        }
        // verify passwords
        $register = new \Difra\Users\Register();
        $register->setPassword1($password1->val());
        $register->setPassword2($password2->val());
        if (!$register->validatePasswords()) {
            $register->callAjaxerEvents();
            return;
        }
        Recover::recoverSetPassword($code->val(), $password1->val());
        Cookies::getInstance()->notify(Locales::get('auth/recover/done'));
        Ajaxer::redirect('/');
    }

    /**
     * Change password using recovery link (already logged in stub)
     * @param AnyString $code
     */
    public function submitAjaxActionAuth(
        /** @noinspection PhpUnusedParameterInspection */
        AnyString $code
    ) {
        Ajaxer::error(Locales::get('auth/recover/already_logged'));
    }
}
