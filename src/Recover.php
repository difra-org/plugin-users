<?php

namespace Difra\Users;

use Difra\Users;
use Difra\DB;
use Difra\Mailer;

/**
 * Class Recover
 * @package Difra\Plugins\Users
 */
class Recover
{
    /**
     * Send password change link
     * @param string $login
     * @return bool|string
     * @throws \Difra\Exception
     */
    public static function send($login)
    {
        if (is_null($login) or $login === '' or $login === false) {
            return UsersException::LOGIN_NOTFOUND;
        }
        $db = DB::getInstance(Users::getDB());
        $data = $db->fetchRow(
            'SELECT `id`,`email`,`active`,`banned` FROM `user` WHERE `email`=:login OR `login`=:login LIMIT 1',
            ['login' => $login]
        );
        if (empty($data)) {
            return UsersException::LOGIN_NOTFOUND;
        }
        if (!$data['active']) {
            return UsersException::LOGIN_INACTIVE;
        }
        if ($data['banned']) {
            return UsersException::LOGIN_BANNED;
        }
        do {
            $key = bin2hex(openssl_random_pseudo_bytes(12));
            $d = $db->fetchOne('SELECT count(*) FROM `user_recover` WHERE `recover`=\'' . $key . "'");
        } while ($d);
        $db->query("INSERT INTO `user_recover` (`recover`,`user`) VALUES (?,?)", [$key, $data['id']]);
        $db->query("DELETE FROM `user_recover` WHERE `date_requested`<DATE_SUB(NOW(),INTERVAL 1 YEAR)");
        Mailer::getInstance()->CreateMail(
            $data['email'],
            'mail_recover',
            ['code' => $key, 'ttl' => Users::getRecoverTTL()]
        );
        return true;
    }

    /** Invalid password change link */
    const RECOVER_INVALID = 'recover_invalid';
    /** Password change link was used previously */
    const RECOVER_USED = 'recover_used';
    /** Password change link is outdated */
    const RECOVER_OUTDATED = 'recover_outdated';

    /**
     * Verify password change link
     * @param $key
     * @param bool|false $returnUser
     * @return bool|User|string
     * @throws UsersException
     */
    public static function verify($key, $returnUser = false)
    {
        $db = DB::getInstance(Users::getDB());
        $data = $db->fetchRow('SELECT * FROM `user_recover` WHERE `recover`=?', [$key]);
        if (empty($data)) {
            throw new UsersException(self::RECOVER_INVALID);
        }
        if ($data['used']) {
            throw new UsersException(self::RECOVER_USED);
        }
        $date = $data['date_requested'];
        $date = explode(' ', $date);
        $day = explode('-', $date[0]);
        $time = explode(':', $date[1]);
        $day1 = mktime($time[0], $time[1], $time[2], $day[1], $day[2], $day[0]);
        if ($day1 and (time() - $day1 > 3600 * Users::getRecoverTTL())) {
            throw new UsersException(self::RECOVER_OUTDATED);
        }
        return $returnUser ? User::getById($data['user']) : true;
    }

    /**
     * Flag password change code as used
     * @param string $key
     * @throws UsersException
     */
    public static function setUsed($key)
    {
        if (!$key) {
            throw new UsersException(self::RECOVER_INVALID);
        }
        DB::getInstance(Users::getDB())->query(
            'UPDATE `user_recover` SET `used`=1 WHERE `recover`=?',
            [(string)$key]
        );
    }

    /**
     * Change password by recovery key
     * @param string $key
     * @param string $password
     * @return User
     */
    public static function recoverSetPassword($key, $password)
    {
        $user = self::verify($key, true);
        $user->setPassword($password);
        self::setUsed($key);
        return $user;
    }
}
