<?php

namespace Difra\Users;

use Difra\Ajaxer;
use Difra\Events\Event;
use Difra\Libs\Cookies;
use Difra\Locales;
use Difra\Users;
use Difra\View;

/**
 * Class Handlers
 * @package Difra\Users
 */
class Handlers
{
    /**
     * Register default event handlers
     */
    public static function register()
    {
        // login
        Event::getInstance(Users::EVENT_LOGIN_FORM_AJAX)->registerDefaultHandler(function () {
            Ajaxer::close();
            Ajaxer::display(View::simpleTemplate('auth-ajax', 'login'), 'overlay-login');
        });
        Event::getInstance(Users::EVENT_LOGIN_DONE_AJAX)->registerDefaultHandler(function () {
            Ajaxer::reload();
        });

        // register
        Event::getInstance(Users::EVENT_REGISTER_FORM_AJAX)->registerDefaultHandler(function () {
            Ajaxer::close();
            Ajaxer::display(View::simpleTemplate('auth-ajax', 'register'), 'overlay-registration');
        });
        Event::getInstance(Users::EVENT_REGISTER_DONE_AJAX)->registerDefaultHandler(function () {
            Ajaxer::close();
            Ajaxer::notify(
                Locales::get('auth/register/complete-' . Users::getActivationMethod())
            );
        });

        // password change
        Event::getInstance(Users::EVENT_PASSWORD_CHANGE_FORM_AJAX)->registerDefaultHandler(function () {
            $xml = new \DOMDocument();
            $xml->appendChild($xml->createElement('passwordChange'));
            $view = new View();
            $view->setTemplateInstance('auth-ajax');
            $view->setFillXML(View::FILL_XML_LOCALE);
            Ajaxer::close();
            Ajaxer::display($view->process($xml), 'overlay-password-change');
        });
        Event::getInstance(Users::EVENT_PASSWORD_CHANGE_DONE_AJAX)->registerDefaultHandler(function () {
            Ajaxer::close();
            Ajaxer::notify(Locales::get('auth/password/changed'));
            Ajaxer::reset();
        });

        // password recovery
        Event::getInstance(Users::EVENT_RECOVER_FORM_AJAX)->registerDefaultHandler(function () {
            Ajaxer::close();
            Ajaxer::display(View::simpleTemplate('auth-ajax', 'recover'), 'overlay-password-recovery');
        });
        Event::getInstance(Users::EVENT_RECOVER_DONE_AJAX)->registerDefaultHandler(function () {
            Ajaxer::close();
            Ajaxer::notify(Locales::get('auth/login/recovered'));
        });

        // logout
        Event::getInstance(Users::EVENT_LOGOUT_DONE_AJAX)->registerDefaultHandler(function () {
            // TODO: redirect to / if page requires auth
            Ajaxer::reload();
        });
    }
}
