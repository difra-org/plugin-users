<?php

namespace Difra\Users;

use Difra\Auth;
use Difra\Envi;
use Difra\Events\Event;
use Difra\Exception;
use Difra\DB;
use Difra\Locales;
use Difra\Mailer;
use Difra\Users;

/**
 * Class User
 * @package Difra\Plugins\Users
 */
class User
{
    /** @var int */
    private $id = null;
    /** @var string */
    private $email = null;
    /** @var string */
    private $login = null;
    /** @var string SHA-1 */
    private $password = null;
    /** @var bool */
    private $active = false;
    /** @var bool */
    private $banned = 0;
    /** @var string Datetime */
    private $registered = null;
    /** @var string Datetime */
    private $lastseen = null;
    /** @var mixed[] */
    private $info = [];
    /** @var string|null */
    private $activation = null;
    /** @var bool */
    private $modified = false;
    /** @var string[] */
    private $saveProperties = ['email', 'login', 'password', 'active', 'banned', 'info', 'activation'];
    /** @var self[] */
    private static $cache = [];

    /**
     * Forbid direct creation of user object
     */
    private function __construct()
    {
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->save();
    }

    /**
     * Save changes
     */
    public function save()
    {
        if (!$this->modified) {
            return;
        }
        $set = [];
        $parameters = [];
        foreach ($this->saveProperties as $field) {
            switch ($field) {
                case 'info':
                    $set[] = "`$field` = :$field";
                    $parameters[$field] = serialize($this->{$field});
                    break;
                default:
                    $set[] = "`$field` = :$field";
                    $parameters[$field] = $this->{$field};
            }
        }
        $db = DB::getInstance(Users::DB);
        if ($this->id) {
            $parameters['id'] = $this->id;
            $db->query("\nUPDATE `user` SET " . implode(',', $set) . ' WHERE `id`=:id', $parameters);
        } else {
            $db->query('INSERT INTO `user` SET ' . implode(',', $set), $parameters);
            $this->id = $db->getLastId();
            self::$cache[$this->id] = $this;
        }
        $this->modified = false;
    }

    /**
     * Initialize User object from database row data
     * @param array $data Database row
     * @return User
     */
    private static function load($data)
    {
        $user = new self;
        $user->id = (int)$data['id'];
        $user->email = $data['email'];
        $user->login = $data['login'];
        $user->password = $data['password'];
        $user->active = $data['active'];
        $user->banned = $data['banned'];
        $user->registered = $data['registered'];
        $user->lastseen = $data['lastseen'];
        $user->info = $data['info'] ? @unserialize($data['info']) : '';
        $user->activation = $data['activation'];
        self::$cache[$user->id] = $user;
        return $user;
    }

    /**
     * @param \DOMElement $node
     * @param \Difra\Unify\Paginator $paginator
     * @param bool $createNode
     * @param array $search
     * @throws Exception
     */
    public static function getListXML($node, $paginator, $createNode = false, $search = null)
    {
        $subNode = $createNode ? $node->appendChild($node->ownerDocument->createElement('users')) : $node;
        foreach (self::getList($paginator, $search) as $user) {
            $user->getXML($subNode, true);
        }
        $stats = DB::getInstance(Users::DB)->fetchRow(
            'SELECT count(*) AS `total`, count(*) - count(`activation`) AS `active` FROM `user`'
        );
        $subNode->setAttribute('total', $stats['total']);
        $subNode->setAttribute('active', $stats['active']);
        $paginator->getPaginatorXML($subNode);
    }

    /**
     * @param \Difra\Unify\Paginator $paginator
     * @param array $search
     * @return User[]
     * @throws Exception
     */
    public static function getList($paginator = null, $search = null)
    {
        $db = DB::getInstance(Users::DB);

        $whereArr = [];
        $whereParams = [];
        if (!empty($search)) {
            foreach($search as $k => $v) {
                switch ($k) {
                    case 'name':
                        $whereArr[] = '(`login` LIKE :name OR `email` LIKE :name)';
                        $whereParams['name'] = $v . '%';
                        break;
                    case 'banned':
                        $whereArr[] = '(`banned`=:banned)';
                        $whereParams['banned'] = $v ? '1' : '0';
                        break;
                    case 'custom':
                        if (!is_array($v)) {
                            $v = [$v];
                        }
                        foreach ($v as $customRule) {
                            $whereArr[] = $customRule;
                        }
                        break;
                    default:
                        throw new \Difra\Exception('Unknown user search key: ' . $k);
                }
            }
        }
        $where = empty($whereArr) ? '' : ('WHERE ' . implode(' AND ', $whereArr));

        if ($paginator) {
            $limits = $paginator->getPaginatorLimit();
            $usersData = $db->fetch("SELECT * FROM `user` $where LIMIT {$limits[0]},{$limits[1]}", $whereParams);
            $total = $db->fetchOne("SELECT COUNT(*) FROM `user` $where", $whereParams);
            $paginator->setTotal($total);
        } else {
            $usersData = $db->fetch("SELECT * FROM `user` $where", $whereParams);
        }
        $users = [];
        foreach ($usersData as $data) {
            $user = User::load($data);
            $users[] = $user;
        }
        return $users;
    }

    /**
     * @param \DOMElement $node
     * @param bool|string $addNode
     * @return \DOMElement
     */
    public function getXML($node, $addNode = false)
    {
        if ($addNode === false) {
            $subNode = $node;
        } elseif ($addNode === true) {
            $subNode = $node->appendChild($node->ownerDocument->createElement('user'));
        } else {
            $subNode = $node->appendChild($node->ownerDocument->createElement($addNode));
        }
        $subNode->setAttribute('id', $this->id);
        $subNode->setAttribute('email', $this->email);
        $subNode->setAttribute('login', $this->login);
        $subNode->setAttribute('active', $this->active);
        $subNode->setAttribute('banned', $this->banned);
        $subNode->setAttribute('registered', $this->registered);
        $subNode->setAttribute('lastseen', $this->lastseen);
        if (!empty($this->info)) {
            /** @var \DOMElement $infoNode */
            $infoNode = $subNode->appendChild($subNode->ownerDocument->createElement('info'));
            foreach ($this->info as $k => $v) {
                $infoNode->setAttribute($k, $v);
            }
        }
        return $subNode;
    }

    /**
     * Get user ID
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get user e-mail
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set user e-mail
     * @param string $email
     */
    public function setEmail($email)
    {
        if ($email == $this->email) {
            return;
        }
        $this->email = $email;
        $this->modified = true;
    }

    /**
     * Get user login
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set user login
     * @param string $login
     */
    public function setLogin($login)
    {
        if ($this->login == $login) {
            return;
        }
        $this->login = $login;
        $this->modified = true;
    }

    /**
     * Get user's password hash
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password
     * @param string $password
     */
    public function setPassword($password)
    {
        $hash = md5($password);
        if ($this->password == $hash) {
            return;
        }
        $this->password = $hash;
        $this->modified = true;
    }

    /**
     * Verify password
     * @param $password
     * @return bool
     */
    public function verifyPassword($password)
    {
        return sha1($password) === $this->password or md5($password) === $this->password;
    }

    /**
     * Is user activated?
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Is user banned?
     * @return boolean
     */
    public function isBanned()
    {
        return (bool)$this->banned;
    }

    /**
     * Set user banned flag
     * @param boolean $banned
     */
    public function setBanned($banned)
    {
        if ($this->banned == $banned) {
            return;
        }
        $this->banned = $banned ? 1 : 0;
        $this->modified = true;
    }

    /**
     * Get user registration date and time
     * @return string
     */
    public function getRegistered()
    {
        return $this->registered;
    }

    /**
     * Get user's last seen flag
     * @return string
     */
    public function getLastseen()
    {
        return $this->lastseen;
    }

    /**
     * Get user's additional info array
     * @return \mixed[]
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Update user's additional info array
     * @param \mixed[] $info
     */
    public function setInfo($info)
    {
        if (serialize($this->info) == serialize($info)) {
            return;
        }
        $this->info = $info;
        $this->modified = true;
    }

    /**
     * Activate user by e-mail
     * @param string $key
     * @return int
     * @throws Exception
     */
    public static function activate($key)
    {
        // prevent activation of all users with empty activation key
        if (!$key) {
            return false;
        }
        // find user
        $db = DB::getInstance(Users::DB);
        if (!$userId = $db->fetchOne('SELECT `id` FROM `user` WHERE `active`=0 AND `activation`=? LIMIT 1', [$key])) {
            // not found
            return false;
        }
        // activate
        $db->query('SET `active`=1,`activation`=NULL WHERE `id`=?', [$userId]);
        return $userId;
    }

    /**
     * Get user by id
     * @param int $id
     * @return User
     * @throws UsersException
     * @throws Exception
     */
    public static function getById($id)
    {
        if (isset(self::$cache[$id])) {
            $user = self::$cache[$id];
        } else {
            /** @var bool|array $data */
            $data = DB::getInstance(Users::getDB())->fetchRow('SELECT * FROM `user` WHERE `id`=?', [$id]) ?: false;
            $user = $data ? self::load($data) : false;
        }
        if (!$user) {
            throw new UsersException(UsersException::LOGIN_NOTFOUND);
        }

        return self::$cache[$id] = $user;
    }

    /**
     * Get user by login name
     * @param string $login
     * @return User
     * @throws Exception
     * @throws UsersException
     */
    public static function getByLogin($login)
    {
        $data = DB::getInstance(Users::getDB())->fetchRow('SELECT * FROM `user` WHERE `login`=?', [$login]);
        if (empty($data)) {
            throw new UsersException(UsersException::LOGIN_NOTFOUND);
        }
        $user = self::load($data);
        return $user;
    }

    /**
     * Get user by login name or e-mail
     * @param string $login
     * @return User
     * @throws Exception
     * @throws UsersException
     */
    public static function getByLoginOrMail($login)
    {
        $data = DB::getInstance(Users::getDB())->fetchRow(
            'SELECT * FROM `user` WHERE `login`=:login OR `email`=:login',
            ['login' => $login]
        );
        if (empty($data)) {
            throw new UsersException(UsersException::LOGIN_NOTFOUND);
        }
        $user = self::load($data);
        return $user;
    }

    /**
     * Get current user
     * @return User
     * @throws UsersException
     * @throws Exception
     */
    public static function getCurrent()
    {
        if (!$id = Auth::getInstance()->getUserId()) {
            return null;
        }
        if (isset(self::$cache[$id])) {
            $user = self::$cache[$id];
        } else {
            /** @var bool|array $data */
            $data = DB::getInstance(Users::getDB())->fetchRow('SELECT * FROM `user` WHERE `id`=?', [$id]) ?: false;
            $user = $data ? self::load($data) : false;
        }

        return $user;
    }

    /**
     * Log in current user
     */
    public function login()
    {
        // check if user is banned
        if ($this->banned) {
            throw new UsersException(UsersException::LOGIN_BANNED);
        }
        // check if user is not active
        if (!$this->active) {
            throw new UsersException(UsersException::LOGIN_INACTIVE);
        }
        Auth::getInstance()->login(
            $this->email,
            [
                'id' => $this->id,
                'login' => $this->login,
                'info' => $this->info
            ]
        );
        DB::getInstance(Users::getDB())->query(
            'UPDATE `user` SET `lastseen`=CURRENT_TIMESTAMP WHERE `id`=:id',
            ['id' => $this->id]
        );
    }

    /**
     * Log in user by email/login and password
     * @param string $login Email/login
     * @param string $password Password
     * @param bool $longSession Set long session
     * @return User
     * @throws UsersException
     */
    public static function loginByPassword($login, $password, $longSession)
    {
        $data = DB::getInstance(Users::getDB())->fetchRow(
            "SELECT * FROM `user` WHERE (`email` = :login OR `login` = :login)",
            [
                'login' => $login,
                'password' => $password
            ]
        );
        if (empty($data)) {
            throw new UsersException(Users::isSingleError() ? UsersException::LOGIN_BAD_LOGIN_OR_PASSWORD
                : UsersException::LOGIN_NOTFOUND);
        }
        if ($data['password'] !== sha1($password) and $data['password'] !== md5($password)) {
            throw new UsersException(Users::isSingleError() ? UsersException::LOGIN_BAD_LOGIN_OR_PASSWORD
                : UsersException::LOGIN_BADPASS);
        }
        $user = self::load($data);
        $user->login();
        if ($longSession) {
            Session::save();
        }
        return $user;
    }

    /**
     * Log out current user
     */
    public static function logout()
    {
        Session::remove();
        Auth::getInstance()->logout();
    }

    /**
     * Create new user
     * @return User
     * @throws Exception
     */
    public static function create()
    {
        $user = new self;

        // configure activation
        $activation = Users::getActivationMethod();
        switch ($activation) {
            case 'email':
                do {
                    $user->activation = bin2hex(openssl_random_pseudo_bytes(24));
                } while (DB::getInstance(Users::getDB())
                    ->fetchOne('SELECT count(*) FROM `user` WHERE `activation`=?', [$user->activation]));
                $user->active = 0;
                break;
            case 'moderate':
                $user->activation = null;
                $user->active = 0;
                break;
            case 'none':
                $user->activation = null;
                $user->active = 1;
                break;
            default:
                throw new Exception("Unknown activation type: $activation");
        }

        return $user;
    }

    /**
     * Activation action depending on activation method
     * @throws Exception
     */
    public function autoActivation()
    {
        switch ($method = Users::getActivationMethod()) {
            case 'email':
                $mailer = Mailer::getInstance();
                $mailer->setTo($this->email);
                $mailer->setSubject(
                     Locales::get('auth/mail/registration/subject-activation-1')
                     . Envi::getHost(true)
                     . Locales::get('auth/mail/registration/subject-activation-2')
                );
                $mailer->render('mail_registration', [
                    'username' => $this->login ?: $this->email,
                    'ttl' => Users::ACTIVATE_TTL,
                    'code' => $this->activation,
                    'confirm' => $method,
                    'host' => Envi::getHost(true),
                    'fullhost' => Envi::getURLPrefix()
                ]);
                $mailer->send();
                break;
            default:
                throw new Exception('Unknown activation method: ' . $method);
        }
    }

    /**
     * Activate user (by administrator)
     */
    public function activateManual()
    {
        $this->active = true;
        $this->activation = null;
        $this->modified = true;
    }

    /**
     * Set user's additional field value
     * @param string $name
     * @param string $value
     */
    public function setField($name, $value)
    {
        DB::getInstance(Users::getDB())->query(
            'REPLACE INTO `user_field` SET `user`=:user,`name`=:name,`value`=:value',
            [
                'user' => $this->getId(),
                'name' => $name,
                'value' => $value
            ]
        );
    }

    /**
     * Get activation code
     * @return string|null
     */
    public function getActivationCode()
    {
        return $this->activation;
    }
}
