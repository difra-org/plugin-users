<?php

declare(strict_types=1);

namespace Difra\Users;

use Difra\Events\Event;

/**
 * Class Plugin
 * @package Difra\Plugins\Users
 */
class Plugin extends \Difra\Plugin
{
    /**
     * @inheritdoc
     */
    protected function init(): void
    {
        Event::getInstance(Event::EVENT_PLUGIN_INIT)->registerHandler([\Difra\Users\Session::class, 'load']);
        Handlers::register();
    }
}

Plugin::enable();
