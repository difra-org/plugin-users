<?php

namespace Difra\Users;

use Difra\Events;

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
        Events::register(Events::EVENT_PLUGIN_INIT, '\Difra\Users\Session::load');
    }
}

Plugin::enable();
