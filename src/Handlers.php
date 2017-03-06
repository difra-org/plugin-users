<?php

namespace Difra\Users;

use Difra\Ajaxer;
use Difra\Events\Event;
use Difra\Locales;
use Difra\Users;

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
        Event::getInstance(Users::EVENT_LOGIN_FORM_AJAX)->registerDefaultHandler(function () {
            $xml = new \DOMDocument();
            $xml->appendChild($xml->createElement('login'));
            $view = new \Difra\View();
            $view->setTemplateInstance('auth-ajax');
            Ajaxer::display($view->process($xml));
        });
        Event::getInstance(Users::EVENT_LOGIN_DONE_AJAX)->registerDefaultHandler(function () {
            Ajaxer::reload();
        });

        Event::getInstance(Users::EVENT_REGISTER_FORM_AJAX)->registerDefaultHandler(function () {
            Ajaxer::close();
            $xml = new \DOMDocument();
            $xml->appendChild($xml->createElement('register'));
            Ajaxer::display(\Difra\View::render($xml, 'auth-ajax', true));
        });
        Event::getInstance(Users::EVENT_REGISTER_DONE_AJAX)->registerDefaultHandler(function () {
            Ajaxer::close();
            Ajaxer::notify(
                Locales::get('auth/register/complete-' . Users::getActivationMethod())
            );
        });

        Event::getInstance(Users::EVENT_PASSWORD_CHANGED_AJAX)->registerDefaultHandler(function () {
            Ajaxer::notify(Locales::get('auth/password/changed'));
            Ajaxer::reset();
        });

        Event::getInstance(Users::EVENT_LOGOUT_DONE_AJAX)->registerDefaultHandler(function () {
            // TODO: redirect to / if page requires auth
            Ajaxer::reload();
        });
    }
}
