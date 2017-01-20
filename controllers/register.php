<?php

use Difra\Ajaxer;
use Difra\Libs\Cookies;
use Difra\Locales;
use Difra\Param\AjaxCheckbox;
use Difra\Param\AjaxString;
use Difra\Param\AnyString;
use Difra\Plugins\Users;
use Difra\Param;
use Difra\Plugins\Users\Register;
use Difra\Plugins\Users\UsersException;
use Difra\View;

/**
 * Class RegisterController
 */
class RegisterController extends \Difra\Controller
{
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
        $this->root->appendChild($this->xml->createElement('register'));
        Ajaxer::display(\Difra\View::render($this->xml, 'auth-ajax', true));
    }

    /**
     * Authorized user (error)
     */
    public function indexAjaxActionAuth()
    {
        // TODO: message
        Ajaxer::reload();
        // TODO: log
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
        $this->afterSuccess();
    }

    /**
     * After success actions
     * @param $redirect
     */
    protected function afterSuccess($redirect = false)
    {
        if ($redirect) {
            Cookies::getInstance()->notify(
                Locales::get('auth/register/complete-' . Users::getActivationMethod())
            );
            View::redirect('/');
        } else {
            Ajaxer::notify(
                Locales::get('auth/register/complete-' . Users::getActivationMethod())
            );
            Ajaxer::close();
        }
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
