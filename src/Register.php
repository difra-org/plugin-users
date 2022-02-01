<?php

declare(strict_types=1);

namespace Difra\Users;

use Difra\Ajaxer;
use Difra\Capcha;
use Difra\Config;
use Difra\Exception;
use Difra\Locales;
use Difra\DB;
use Difra\Wordforms;
use Difra\Users;
use Difra\Security\Filter\Email;

/**
 * Class Register
 * Object used for registration data validation and User::create call.
 * @package Difra\Plugins\Users
 */
class Register
{
    // error codes, should match language strings auth/register/*
    const REGISTER_EMAIL_EMPTY = 'email_empty';
    const REGISTER_EMAIL_INVALID = 'email_invalid';
    const REGISTER_EMAIL_EXISTS = 'email_dupe';
    const REGISTER_EMAIL_OK = 'email_ok';
    const REGISTER_PASSWORD1_EMPTY = 'password1_empty';
    const REGISTER_PASSWORD1_OK = 'password1_ok';
    const REGISTER_PASSWORD2_EMPTY = 'password2_empty';
    const REGISTER_PASSWORD2_OK = 'password2_ok';
    const REGISTER_PASSWORD_SHORT = 'password1_short';
    const REGISTER_PASSWORDS_DIFF = 'passwords_diff';
    const REGISTER_CAPTCHA_EMPTY = 'captcha_empty';
    const REGISTER_CAPTCHA_INVALID = 'captcha_invalid';
    const REGISTER_CAPTCHA_OK = 'captcha_ok';
    const REGISTER_LOGIN_EMPTY = 'login_empty';
    const REGISTER_LOGIN_INVALID = 'login_invalid';
    const REGISTER_LOGIN_EXISTS = 'login_dupe';
    const REGISTER_LOGIN_SHORT = 'login_short';
    const REGISTER_LOGIN_LONG = 'login_long';
    const REGISTER_LOGIN_OK = 'login_ok';
    const LOGIN_REGEX = '/^[a-zA-Z0-9]([a-zA-Z0-9._-]*)$/';
    const MIN_PASSWORD_LENGTH = 6;
    /** @var array Failures list */
    private array $failures = [];
    /** @var array Successful fields list */
    private array $successful = [];
    /** @var string|null E-Mail */
    private ?string $email = null;
    /** @var string|null Login name */
    private ?string $login = null;
    /** @var string|null Password (1) */
    private ?string $password1 = null;
    /** @var string|null Password (2) */
    private ?string $password2 = null;
    /** @var string|null Captcha value */
    private ?string $captcha = null;
    /** @var bool Ignore empty fields on validation */
    private bool $ignoreEmpty = false;
    /** @var bool Fast validation */
    private bool $fast = false;
    /** @var bool Valid flag */
    private bool $valid = false;

    /**
     * @param bool $ignoreEmpty Report only invalid fields (skip empty or fine fields reporting)
     * @param bool|null $fast true = skip database queries, false = query database, null = depending on captcha
     */
    public function __construct(bool $ignoreEmpty = false, bool $fast = null)
    {
        $this->ignoreEmpty = $ignoreEmpty;
        $this->fast = $fast;
    }

    /**
     * Set e-mail
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
        $this->valid = false;
    }

    /**
     * Verify e-mail
     * @param bool|false $fast
     * @return null|string
     * @throws \Difra\Exception
     */
    private function verifyEmail(bool $fast = false): ?string
    {
        // check e-mail
        if (!$this->ignoreEmpty) {
            if ($this->email === '') {
                return $this->failures['email'] = self::REGISTER_EMAIL_EMPTY;
            } elseif (!Email::validate($this->email)) {
                return $this->failures['email'] = self::REGISTER_EMAIL_INVALID;
            } elseif (!$fast and !User::isEmailAvailable($this->email)) {
                return $this->failures['email'] = self::REGISTER_EMAIL_EXISTS;
            } else {
                return $this->successful['email'] = self::REGISTER_EMAIL_OK;
            }
        } elseif ($this->email !== '') {
            if (!Email::validate($this->email)) {
                return $this->failures['email'] = self::REGISTER_EMAIL_INVALID;
            } elseif (!$fast and !User::isEmailAvailable($this->email)) {
                return $this->failures['email'] = self::REGISTER_EMAIL_EXISTS;
            }
        }
        return null;
    }


    /**
     * Set username
     * @param string|null $login
     * @throws \Difra\Exception
     */
    public function setLogin(?string $login)
    {
        if (!Users::isLoginNamesEnabled()) {
            if (!(string)$login) {
                return;
            }
            throw new Exception('User names are disabled');
        }
        $this->login = (string)$login;
        $this->valid = false;
    }

    /**
     * Verify user name
     * @param bool $fast Skip database checks
     * @return null|string
     * @throws \Difra\Exception
     */
    public function verifyLogin(bool $fast = false)
    {
        if (!Users::isLoginNamesEnabled()) {
            return null;
        }
        // check e-mail
        if (!$this->ignoreEmpty) {
            if ($this->login === '') {
                return $this->failures['login'] = self::REGISTER_LOGIN_EMPTY;
            } elseif (!self::isLoginValid($this->login)) {
                return $this->failures['login'] = self::REGISTER_LOGIN_INVALID;
            } elseif (strlen($this->login) < Users::getLoginMinChars()) {
                return $this->failures['login'] = self::REGISTER_LOGIN_SHORT;
            } elseif (strlen($this->login) > Users::getLoginMaxChars()) {
                return $this->failures['login'] = self::REGISTER_LOGIN_LONG;
            } elseif (!$fast and !self::isLoginAvailable($this->login)) {
                return $this->failures['login'] = self::REGISTER_LOGIN_EXISTS;
            } else {
                return $this->successful['login'] = self::REGISTER_LOGIN_OK;
            }
        } elseif ($this->login !== '') {
            if (!self::isLoginValid($this->login)) {
                return $this->failures['login'] = self::REGISTER_LOGIN_INVALID;
            } elseif (strlen($this->login) < Users::getLoginMinChars()) {
                return $this->failures['login'] = self::REGISTER_LOGIN_SHORT;
            } elseif (strlen($this->login) > Users::getLoginMaxChars()) {
                return $this->failures['login'] = self::REGISTER_LOGIN_LONG;
            } elseif (!$fast and !self::isLoginAvailable($this->login)) {
                return $this->failures['login'] = self::REGISTER_LOGIN_EXISTS;
            }
        }
        return null;
    }

    /**
     * Verify if user name string is valid
     * @param string $login
     * @return bool
     */
    public static function isLoginValid(string $login): bool
    {
        return (bool)preg_match(self::LOGIN_REGEX, $login);
    }

    /**
     * Verify if login does not exist yet
     * @param string $login
     * @return bool
     * @throws \Difra\DB\Exception
     */
    public static function isLoginAvailable(string $login): bool
    {
        return !DB::getInstance(Users::getDB())->fetchOne(
            'SELECT `id` FROM `user` WHERE `login`=?',
            [$login]
        );
    }

    /**
     * Set password
     * @param string $password1
     */
    public function setPassword1(string $password1)
    {
        $this->password1 = $password1;
        $this->valid = false;
    }

    /**
     * Validate password
     * @return string|null
     */
    private function verifyPassword1(): ?string
    {
        if (!$this->ignoreEmpty) {
            if ($this->password1 === '') {
                return $this->failures['password1'] = self::REGISTER_PASSWORD1_EMPTY;
            } elseif (strlen($this->password1) < self::MIN_PASSWORD_LENGTH) {
                return $this->failures['password1'] = self::REGISTER_PASSWORD_SHORT;
            } else {
                return $this->successful['password1'] = self::REGISTER_PASSWORD1_OK;
            }
        } elseif ($this->password1 !== '') {
            if (strlen($this->password1) < self::MIN_PASSWORD_LENGTH) {
                return $this->failures['password1'] = self::REGISTER_PASSWORD_SHORT;
            }
        }
        return null;
    }

    /**
     * Set password (repeat)
     * @param string $password2
     */
    public function setPassword2(string $password2): void
    {
        $this->password2 = $password2;
        $this->valid = false;
    }

    /**
     * Validate password (repeat)
     * @return string|null
     */
    private function verifyPassword2(): ?string
    {
        if (!$this->ignoreEmpty) {
            if ($this->password2 === '') {
                return $this->failures['password2'] = self::REGISTER_PASSWORD2_EMPTY;
            } elseif ($this->password1 !== $this->password2) {
                return $this->failures['password2'] = self::REGISTER_PASSWORDS_DIFF;
            } else {
                return $this->successful['password2'] = self::REGISTER_PASSWORD2_OK;
            }
        } elseif ($this->password2 !== '') {
            if ($this->password1 !== '' and $this->password1 != $this->password2) {
                return $this->failures['password2'] = self::REGISTER_PASSWORDS_DIFF;
            }
        }
        return null;
    }

    /**
     * Set captcha
     * @param string $captcha
     */
    public function setCaptcha(string $captcha): void
    {
        $this->captcha = $captcha;
        $this->valid = false;
    }

    /**
     * Validate captcha
     * @return string
     */
    private function verifyCaptcha(): ?string
    {
        if (!$this->ignoreEmpty) {
            if (!$this->captcha) {
                return $this->failures['capcha'] = self::REGISTER_CAPTCHA_EMPTY;
            } elseif (!Capcha::getInstance()->verifyKey($this->captcha)) {
                return $this->failures['capcha'] = self::REGISTER_CAPTCHA_INVALID;
            } else {
                return $this->successful['capcha'] = self::REGISTER_CAPTCHA_OK;
            }
        } elseif ($this->captcha !== '') {
            if (!Capcha::getInstance()->verifyKey($this->captcha)) {
                return $this->failures['capcha'] = self::REGISTER_CAPTCHA_INVALID;
            }
        }
        return null;
    }

    /**
     * Validate registration form fields
     * @return bool
     * @throws \Difra\Exception
     */
    public function validate(): bool
    {
        $this->successful = [];
        $this->failures = [];
        $fast = !is_null($this->fast)
            ? $this->fast
            : ($this->verifyCaptcha() != self::REGISTER_CAPTCHA_OK);
        $this->verifyEmail($fast);
        $this->verifyLogin($fast);
        $this->verifyPassword1();
        if (Users::isPassword2Enabled()) {
            $this->verifyPassword2();
        }

        return $this->valid = empty($this->failures);
    }

    /**
     * Passwords validation
     * @return bool
     */
    public function validatePasswords(): bool
    {
        $this->verifyPassword1();
        $this->verifyPassword2();
        return empty($this->failures);
    }

    /**
     * Add ajaxer events to highlight wrong or correct fields
     * @return bool
     * @throws \Difra\Exception
     */
    public function callAjaxerEvents(): bool
    {
        if (!empty($this->successful)) {
            foreach ($this->successful as $field => $result) {
                Ajaxer::status($field, Locales::get('auth/register/' . $result), 'ok');
            }
        }
        if (!empty($this->failures)) {
            foreach ($this->failures as $field => $result) {
                switch ($result) {
                    case self::REGISTER_LOGIN_SHORT:
                        $loginMinChars = Users::getLoginMinChars();
                        Ajaxer::status(
                            $field,
                            Locales::get('auth/register/login_short/part1')
                            . $loginMinChars
                            . Wordforms::getInstance()->getQuantityForm(
                                Locales::get('auth/register/login_long/part2'),
                                Wordforms::GENDER_MALE | Wordforms::CASE_GENITIVE,
                                $loginMinChars
                            )
                            . Locales::get('auth/register/login_short/part3'),
                            'problem'
                        );
                        break;
                    case self::REGISTER_LOGIN_LONG:
                        $loginMaxChars = Users::getLoginMaxChars();
                        Ajaxer::status(
                            $field,
                            Locales::get('auth/register/login_long/part1')
                            . $loginMaxChars
                            . Wordforms::getInstance()->getQuantityForm(
                                Locales::get('auth/register/login_long/part2'),
                                Wordforms::GENDER_MALE | Wordforms::CASE_GENITIVE,
                                $loginMaxChars
                            )
                            . Locales::get('auth/register/login_long/part3'),
                            'problem'
                        );
                        break;
                    default:
                        Ajaxer::status($field, Locales::get('auth/register/' . $result), 'problem');
                }
            }
            return false;
        }
        return true;
    }

    /**
     * Process registration
     * @throws Exception
     */
    public function register()
    {
        if (!$this->valid) {
            $this->validate();
            if (!$this->valid) {
                throw new Exception('Registration aborted: invalid data');
            }
        }
        $user = User::create();
        $user->setEmail($this->email);
        $user->setPassword($this->password1);
        $user->setLogin($this->login);
        $user->save();
        $user->autoActivation();
        // todo: move to event
        if (function_exists('postRegister')) {
            postRegister($user);
        }
    }

    /** Activation code not found */
    const ACTIVATE_NOTFOUND = 'activate_notfound';
    /** Activation code already used */
    const ACTIVATE_USED = 'activate_used';
//    const ACTIVATE_TIMEOUT = 'activate_timeout'; // think about it. warning: no language string for this.

    /**
     * Activate user
     * @param $key
     * @return bool
     * @throws Exception
     */
    public static function activate($key): bool
    {
        $key = trim((string)$key);
        if (!$key) {
            throw new UsersException(self::ACTIVATE_NOTFOUND);
        }
        $db = DB::getInstance(Users::getDB());
        $data = $db->fetchRow('SELECT * FROM `user` WHERE `activation`=? LIMIT 1', [(string)$key]);
        if (empty($data)) {
            throw new UsersException(self::ACTIVATE_NOTFOUND);
        }
        if ($data['active']) {
            throw new UsersException(self::ACTIVATE_USED);
        }
//        if ($data['registered'] < date('Y-m-d H:i:s', time() - Users::ACTIVATE_TTL)) {
//            throw new UsersException(self::ACTIVATE_TIMEOUT);
//        }
        $db->query("UPDATE `user` SET `active`='1',`activation`=NULL WHERE `activation`=?", [$key]);
        if (Config::getInstance()->getValue('auth', 'login_on_activate')) {
            $user = User::getById($data['id']);
            $user->login();
        }
        return true;
    }
}
