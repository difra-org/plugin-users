<?php

namespace Difra\Plugins;

use Difra\Config;

/**
 * Class Users
 * @package Difra\Plugins
 */
class Users
{
    const DB = 'users';
    const RECOVER_TTL = 72; // hours
    const ACTIVATE_TTL = 7 * 24; // hours
    const IP_MASK = '255.255.0.0'; // "long session" ip mask

    /**
     * Get database name for users plugin
     * @return string
     */
    public static function getDB()
    {
        return self::DB;
    }

    /**
     * Are user names enabled?
     * @return bool
     */
    public static function isLoginNamesEnabled()
    {
        return (bool)Config::getInstance()->getValue('auth', 'logins');
    }

    /**
     * Is password2 field enabled?
     * @return bool|mixed
     */
    public static function isPassword2Enabled()
    {
        $en = Config::getInstance()->getValue('auth', 'password2');
        return is_null($en) ? true : $en;
    }

    /**
     * Get minimum login length
     * @return int
     */
    public static function getLoginMinChars()
    {
        $min = Config::getInstance()->getValue('auth', 'login_min');
        return $min ?: 1;
    }

    /**
     * Get maximum login length
     * @return int
     */
    public static function getLoginMaxChars()
    {
        $max = Config::getInstance()->getValue('auth', 'login_max');
        return ($max and $max < 80) ? $max : 80;
    }

    /**
     * Get activation method (email, moderate or none)
     * @return string
     */
    public static function getActivationMethod()
    {
        return Config::getInstance()->getValue('auth', 'confirmation') ?: 'email';
    }

    public static function getRecoverTTL()
    {
        return self::RECOVER_TTL;
    }

    public static function isSingleError()
    {
        return Config::getInstance()->getValue('auth', 'single_error') ?: false;
    }
}
