<?php

declare(strict_types=1);

use Difra\Ajaxer;
use Difra\Libs\Cookies;
use Difra\Locales;
use Difra\Param\AjaxCheckbox;
use Difra\Param\AjaxString;
use Difra\Param\AnyString;
use Difra\Users;
use Difra\Users\Register;
use Difra\Users\UsersException;
use Difra\View;
use Difra\View\HttpError;

/**
 * Class RegisterController
 */
class RegisterController extends \Difra\Controller
{
    /**
     * Dispatcher
     */
    public function dispatch(): void
    {
        $enabled = Difra\Config::getInstance()->getValue('auth', 'registration');
        if ($enabled === false) {
            throw new HttpError(404);
        }
    }

    /**
     * Registration form (page)
     */
    public function indexAction()
    {
        $this->root->appendChild($this->xml->createElement('register'));
    }

    /**
     * Authorized user (already registered)
     */
    public function indexActionAuth()
    {
        // TODO: log
        View::redirect('/');
    }

    /**
     * Registration form (ajax)
     */
    public function indexAjaxAction()
    {
        \Difra\Events\Event::getInstance(Users::EVENT_REGISTER_FORM_AJAX)->trigger();
    }

    /**
     * Authorized user (error)
     */
    public function indexAjaxActionAuth()
    {
        Ajaxer::reload();
    }

    /**
     * Registration form submit (registration page version)
     * @param AjaxCheckbox $accept
     * @param AjaxCheckbox $redirect
     * @param AjaxString|null $email
     * @param AjaxString|null $password1
     * @param AjaxString|null $password2
     * @param AjaxString|null $login
     * @param AjaxString|null $capcha
     * @throws Exception
     */
    public function submitAjaxAction(
        AjaxCheckbox $accept,
        AjaxCheckbox $redirect,
        AjaxString $email = null,
        AjaxString $password1 = null,
        AjaxString $password2 = null,
        AjaxString $login = null,
        AjaxString $capcha = null
    ) {
        $register = new Users\Register();
        $register->setEmail($email);
        $register->setLogin($login);
        $register->setPassword1($password1);
        $register->setPassword2($password2);
        $register->setCaptcha($capcha);

        if (!$register->validate()) {
            $register->callAjaxerEvents();
            return;
        }

        // EULA
        if (!$accept->val() and \Difra\Config::getInstance()->getValue('auth', 'eula')) {
            $this->root->appendChild($this->xml->createElement('eula'));
            Ajaxer::display(View::render($this->xml, 'auth-ajax', true));
            return;
        }

        $register->register();
        \Difra\Events\Event::getInstance(Users::EVENT_REGISTER_DONE_AJAX)->trigger();
    }

    /**
     * Authorized user (error)
     */
    public function submitAjaxActionAuth()
    {
        // TODO: handle registered user
        // TODO: log
    }

    /**
     * Activation link
     * @param AnyString $code
     * TODO: move to event
     * @throws \Difra\Exception
     */
    public function activateAction(AnyString $code)
    {
        try {
            Register::activate($code->val());
            $this->afterActivate();
        } catch (UsersException $error) {
            Cookies::getInstance()->notify(
                Locales::get('auth/activate/' . $error->getMessage()),
                true
            );
            \Difra\View::redirect('/');
        }
    }

    /**
     * Redefine this method if you want custom actions after activation
     */
    protected function afterActivate()
    {
        Cookies::getInstance()->notify(Locales::get('auth/activate/done'));
        \Difra\View::redirect('/');
    }
}
