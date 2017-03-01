<?php

namespace Difra\Users;

use Difra\Ajaxer;
use Difra\Events\Event;
use Difra\Locales;
use Difra\Users;

/**
 * Class Plugin
 * @package Difra\Plugins\Users
 */
class Plugin extends \Difra\Plugin
{
    /**
     * @inheritdoc
     */
    protected function init()
    {
        Event::getInstance(Event::EVENT_PLUGIN_INIT)->registerHandler([\Difra\Users\Session::class, 'load']);
        Event::getInstance(Users::EVENT_LOGIN_FORM_AJAX)->registerDefaultHandler(function () {
            $xml = new \DOMDocument();
            $xml->appendChild($xml->createElement('login'));
            $view = new \Difra\View();
            $view->setTemplateInstance('auth-ajax');
            Ajaxer::display($view->process($xml));
        });
        Event::getInstance(Users::EVENT_PASSWORD_CHANGED_AJAX)->registerDefaultHandler(function () {
            Ajaxer::notify(Locales::get('auth/password/changed'));
            Ajaxer::reset();
        });
        Event::getInstance(Users::EVENT_LOGOUT_DONE_AJAX)->registerDefaultHandler(function () {
            // TODO: redirect to / if page requires auth
            Ajaxer::reload();
        });
        Event::getInstance(Users::EVENT_LOGIN_DONE_AJAX)->registerDefaultHandler(function () {
            Ajaxer::reload();
        });
    }
}

Plugin::enable();
