<?php

namespace Difra\Users;

use Difra\Exception;

/**
 * Class UsersException
 * @package Difra\Plugins\Users
 */
class UsersException extends Exception
{
    /** Login: user not found */
    const LOGIN_NOTFOUND = 'not_found';
    /** Login: user is banned */
    const LOGIN_BANNED = 'banned';
    /** Login: bad login or password */
    const LOGIN_BAD_LOGIN_OR_PASSWORD = 'bad_login_or_password';
    /** Login: user is inactive */
    const LOGIN_INACTIVE = 'inactive';
    /** Login: bad password */
    const LOGIN_BADPASS = 'bad_password';
    /** Login is already active (for activation mail request) */
    const LOGIN_ACTIVE = 'already_active';
}
