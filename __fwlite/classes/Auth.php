<?php

/**
 * @desc This class wraps WordPress functions into Auth scheme used by the framework, and the one
 * that this project was using before we added WordPress. Not the way I meant it to work, so some
 * functions are empty just because they are abstract in base class.
 */
class Auth extends AuthBase {
    protected $isLoggedIn = null;
    protected $isAdmin    = false;
    protected $userId     = false;
    protected $email      = '';
    protected $lastMsgId  = 0;

    protected $isAdminLoggedIn = false;

    const L_SUBSCRIBER = 'subscriber';
    const L_ADMIN      = 'administrator';
    const L_S2_LEVEL1  = 's2member_level1';
    const L_S2_LEVEL2  = 's2member_level2';
    const L_S2_LEVEL3  = 's2member_level3';
    const L_S2_LEVEL4  = 's2member_level4';
    const L_S2_LEVEL5  = 's2member_level5';
    const L_S2_LEVEL6  = 's2member_level6';
    const L_S2_LEVEL7  = 's2member_level7';
    const L_S2_LEVEL8  = 's2member_level8';
    const L_S2_LEVEL9  = 's2member_level9';
    const L_S2_LEVEL10 = 's2member_level10';


    private static $levels = array (
        1 => self::L_SUBSCRIBER,
        self::L_S2_LEVEL1,
        self::L_S2_LEVEL2,
        self::L_S2_LEVEL3,
        self::L_S2_LEVEL4,
        self::L_S2_LEVEL5,
        self::L_S2_LEVEL6,
        self::L_S2_LEVEL7,
        self::L_S2_LEVEL8,
        self::L_S2_LEVEL9,
        self::L_S2_LEVEL10,
        1000000 => self::L_ADMIN
    );

    const POSTBACK_HASH_SALT = 'd982kjsdy7o3j4s8dfg7qoli3j4t';


    /**
     * @var WP_User
     */
    protected $wpUser;


    /**
     * @return Auth
     */
    public static function getInstance() {
        return SingletonRegistry::getSingleInstance(__CLASS__);
    }
    //--------------------------------------------------------------------------


    public function __construct() {
        $this->isLoggedIn = is_user_logged_in() ? true : false;
        if ($this->isLoggedIn) {
            if (!Session::getSession()->wasLoggedIn) {
                global $current_user;
                SingletonRegistry::getModelUser()->updateLastLoginTime($current_user->ID);
                Session::getSession()->wasLoggedIn = true;
            }

            if (current_user_can("administrator")) {
                $this->isAdminLoggedIn = true;
                $impersonate = Session::getSession()->impersonate;
                if ($impersonate) {
                    $user = get_user_by('login', $impersonate);
                    if ($user) {
                        $this->wpUser = $user;
                    }
                }
            }
        }

        if (!$this->wpUser) {
            global $current_user;
            $this->wpUser = $current_user;
        }
        $this->initialize();
    }
    //--------------------------------------------------------------------------


    public function isAtLeast($level) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        static $caps = array();
        return self::isUserIdAtLeast($this->userId, $caps, $level);
    }
    //--------------------------------------------------------------------------


    public static function userLevelAsNumber($userLevelStr) {
        return (int)array_search($userLevelStr, self::$levels);
    }
    //--------------------------------------------------------------------------


    public static function isUserIdAtLeast($id, array & $caps, $level) {
        if (!$caps) {
            $caps =  SingletonRegistry::getModelUser()->listUserCaps($id);
        }
        $requestedLevel = (int)array_search($level, self::$levels);
        $myLevel = 0;
        foreach ($caps as $cap) {
            $myLevel = max($myLevel, (int)array_search($cap, self::$levels));
        }
        return $myLevel >= $requestedLevel;
    }
    //--------------------------------------------------------------------------


    public static function maxCap(array $caps) {
        $maxCap = self::L_SUBSCRIBER;
        $myLevel = 1;
        foreach ($caps as $cap) {
            $thisLevel = (int)array_search($cap, self::$levels);
            if ($thisLevel > $myLevel) {
                $maxCap  = $cap;
                $myLevel = $thisLevel;
            };
        }
        return $maxCap;
    }
    //--------------------------------------------------------------------------


    protected function initialize() {
        $this->isAdmin    = $this->isLoggedIn && $this->wpUser->has_cap("administrator");
        $this->userId     = $this->isLoggedIn ? (int)$this->wpUser->ID : 0;

        if ($this->isLoggedIn && $this->userId) {
            $this->email = isset($this->wpUser->data->email) ? $this->wpUser->data->email : '';
            $modelUser = SingletonRegistry::getModelUser();
            $row = $modelUser->get($this->userId);

            if (!$row) {
                /* @var $modelSystemMessage Model_SystemMessage */
                $modelSystemMessage = SingletonRegistry::getSingleInstance('Model_SystemMessage');

                $row = array('id' => $this->userId, 'last_message_id' => $modelSystemMessage->lastMessageId());
                $this->userId = $modelUser->insert($row);
                $modelUser->recreateUserCapCache();
                $row = $modelUser->get($this->userId);
            }

            $this->lastMsgId = (int)$row['last_message_id'];
            Session::getSession()->timeZone = empty($row['time_zone']) ? Config::$timeZone : $row['time_zone'];
        }
    }
    //--------------------------------------------------------------------------


    public function logInAs($username) {
        $user = get_user_by('login', $username);
        if (!$user) {
            throw new EExplainableError("Unknown username");
        }

        Session::getSession()->impersonate = $username;
        $row = SingletonRegistry::getModelUser()->get($this->userId);
        Session::getSession()->timeZone = empty($row['time_zone']) ? Config::$timeZone : $row['time_zone'];
    }
    //--------------------------------------------------------------------------


    public function tryToLogIn($userName, $password) {
        //
    }
    //--------------------------------------------------------------------------


    public function getEmail() {
        return $this->email;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc whether active user is admin. If impersonating a user this will return that user's
     * flag, not flag of 'main' user. See also function below, isAdminLoggedIn()
     */
    public function isAdmin() {
        return $this->isAdmin;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Whether user is an admin or is admin impersonating another user. Function isAdmin()
     * will return is_admin flag impersonated user.
     */
    public function isAdminLoggedIn() {
        return $this->isAdminLoggedIn;
    }
    //--------------------------------------------------------------------------


    public function getUserId() {
        return $this->userId;
    }
    //--------------------------------------------------------------------------


    public function isLoggedIn() {
        return $this->isLoggedIn;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc id of last system message that this user has read, used for figuring out if we need to
     * write out notification on top of screen. Updated when user dismisses it.
     */
    public function lastMessageId() {
        return $this->lastMsgId;
    }
    //--------------------------------------------------------------------------


    public static function userIdToPostbackHash($userId) {
        return $userId . 'e' . sha1($userId . ' ' . self::POSTBACK_HASH_SALT);
    }
    //--------------------------------------------------------------------------


    public static function postbackHashToUserId($hash) {
        $arr = explode('e', $hash, 2);
        if (sizeof($arr) != 2) {
            return 0;
        }
        $id = (int)$arr[0];
        if (self::userIdToPostbackHash($id) == $hash) {
            return $id;
        } else {
            return 0;
        }
    }
    //--------------------------------------------------------------------------
}
