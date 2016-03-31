<?php

class Model_User extends CrudModelCaching {

    /**
     * @var DB
     */
    protected $db;

    protected $t_users;
    protected $t_user_rights_cache;
    protected $t_config_posts_per_level;

    private $apcKey;
    private $canValidateCache = array();

    /**
     * @desc only used when we are running from CLI, which will happen from cron jobs
     */
    private $userRightsCache = false;


    public function __construct(DB $db = null) {
        $this->apcKey = __FILE__ . '/user_rights_cache';
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_users);

        $this->timestampForInsert = array('last_login_time');
    }
    //--------------------------------------------------------------------------


    public function canValidateFieldTypes(array $userCaps) {
        if (sizeof($userCaps) != 1) {
            return array();
        }
        $s = $userCaps[0];
        if ($s == 'administrator') {
            return array(
                Model_CampaignField::FIELD_TYPE_EMAIL,
                Model_CampaignField::FIELD_TYPE_PHONE
            );
        }
        if (!$this->canValidateCache) {
            $this->fetchCanValidateRights();
        }
        return isset($this->canValidateCache[$s]) ? $this->canValidateCache[$s] : array();
    }
    //--------------------------------------------------------------------------


	private function fetchCanValidateRights () {
        $arr = $this->db->getArray("SELECT `level`, `can_validate_email`, `can_validate_phone` FROM `$this->t_config_posts_per_level` ORDER BY 1", array(), MYSQL_ASSOC);
        foreach ($arr as $row) {
            $fields = array();
            if ($row['can_validate_email']) {
                $fields[] = Model_CampaignField::FIELD_TYPE_EMAIL;
            }
            if ($row['can_validate_phone']) {
                $fields[] = Model_CampaignField::FIELD_TYPE_PHONE;
            }
            $this->canValidateCache[$row['level']] = $fields;
        }
    }
    //--------------------------------------------------------------------------


    public function listUsernamesAssoc($justUsernames = false) {
        $out = array();
        global $wpdb;
        $aUsersID = $wpdb->get_col( $wpdb->prepare("SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY `user_login` ASC", null));
        foreach ( $aUsersID as $iUserID ) {
            $user = get_userdata($iUserID);
            $s = $user->user_login;
            if (!$justUsernames && ($user->first_name . $user->last_name && ($user->first_name . $user->last_name != $s))) {
                $s .= ' (' . trim($user->first_name . ' ' . $user->last_name) . ')';
            }
            $out[$user->ID] = $s;
        };
        return $out;
    }
    //--------------------------------------------------------------------------


    public function listUserCaps($userId) {
        if (Config::$useApc) {
            $arr = apc_fetch($this->apcKey, $success);
            if ($success) {
                return isset($arr[$userId]) ? $arr[$userId] : array();
            }
        }
        if (PHP_SAPI == 'cli') {
            if ($this->userRightsCache === false) {
                $this->userRightsCache = $this->listUserCapsAssoc();
            }
            return isset($this->userRightsCache[$userId]) ? $this->userRightsCache[$userId] : array();
        }
        $sql = "SELECT `capability` FROM `$this->t_user_rights_cache` WHERE `user_id` = ? ORDER BY 1";
        return $this->db->getArray1v($sql, array((int)$userId));
    }
    //--------------------------------------------------------------------------


    public function listUserCapsAssoc() {
        if (Config::$useApc) {
            $arr = apc_fetch($this->apcKey, $success);
            if ($success) {
                return $arr;
            }
        }
        if (PHP_SAPI == 'cli' && ($this->userRightsCache !== false)) {
            return $this->userRightsCache;
        }
        $sql = "SELECT `user_id`, `capability` FROM `$this->t_user_rights_cache` ORDER BY 1, 2";
        $output = array();
        foreach ($this->db->getArray($sql, array(), MYSQL_NUM) as $row) {
            if (!isset($output[$row[0]])) {
                $output[$row[0]] = array();
            }
            $output[$row[0]][] = $row[1];
        }
        if (PHP_SAPI == 'cli') {
            $this->userRightsCache = $output;
        }
        return $output;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc refresh user_rights_cache table with data from WordPress. Also if we do not
     * have user row in our users table, add it.
     */
    public function recreateUserCapCache() {
        try {
            $lock = new MySQLLocker(Config::$lockPrefix . __CLASS__  . '/' . __FUNCTION__, 10);
        } catch (EServerError $e) {
            return;
        }

        try {
            $userNames = $this->listUsernamesAssoc(true);

            $this->startTransaction();
            $this->db->query("DELETE FROM `$this->t_user_rights_cache`");
            $this->makeSureWordpressUsersExistInPingtreeUsersTable(array_keys($userNames));

            $wordpressCaps = array();
            foreach (array_keys($userNames) as $userId) {
                $user = new WP_User((int)$userId);
                if ($user->ID) {
                    $wordpressCaps[$user->ID] = array_values($user->roles);
                }
            }
            if (Config::$useApc) {
                apc_store($this->apcKey, $wordpressCaps, 60);
            }

            if (PHP_SAPI == 'cli') {
                $this->userRightsCache = $wordpressCaps;
            }
            foreach ($wordpressCaps as $userId => $caps) {
                $this->writeUserCaps($userId, $caps);
            }

            $this->commit();
            $lock->release();
        } catch (Exception $e) {
            $this->commit();
            $lock->release();
            throw $e;
        }
    }
    //--------------------------------------------------------------------------


    public function writeUserCaps($userId, array $roles) {
        $sql = "DELETE FROM `$this->t_user_rights_cache` WHERE `user_id` = ? ORDER BY `capability`";
        $this->db->query($sql, array((int)$userId));
        if (!$roles) {
            return;
        }
        sort($roles);
        $sql = "INSERT IGNORE INTO `$this->t_user_rights_cache` (`user_id`, `capability`) VALUES\n\t";
        $sql .= implode(",\n\t", array_fill(0, sizeof($roles), "(" . (int)$userId . ", ?)"));
        $result = $this->db->query($sql, $roles);
        return $result;
    }
    //--------------------------------------------------------------------------


    public function makeSureWordpressUsersExistInPingtreeUsersTable($userIDs = false) {
        if (!is_array($userIDs)) {
            $userIDs = array_keys($this->listUsernamesAssoc(true));
        }
        $existingUserIDs = $this->db->getArray1v("SELECT `id` FROM `$this->tableName` ORDER BY 1");
        foreach (array_diff($userIDs, $existingUserIDs) as $userId) {
            $this->insert(array('id' => (int)$userId), true);
        }
    }
    //--------------------------------------------------------------------------


    public function countOfUserCapCacheEntries() {
        if (Config::$useApc) {
            $arr = apc_fetch($this->apcKey, $success);
            return ($success && is_array($arr)) ? sizeof($arr) : 0;
        }
        if (PHP_SAPI == 'cli') {
            if ($this->userRightsCache === false) {
                $this->userRightsCache = $this->listUserCapsAssoc();
            }
            return sizeof($this->userRightsCache);
        }
        return $this->db->getTopLeftInt("SELECT COUNT(*) FROM `$this->t_user_rights_cache`");
    }
    //--------------------------------------------------------------------------


    public function listUserIDs() {
        return $this->db->getArray1v("SELECT `id` FROM `$this->tableName` ORDER BY 1");
    }
    //--------------------------------------------------------------------------
    
    
    public function listNonAdminUserIDs() {
        $sql = "SELECT `id` FROM `$this->tableName` AS u "
                . "INNER JOIN `user_rights_cache` AS ur ON u.`id` = ur.user_id AND ur.capability != 'administrator'"
                . "ORDER BY 1";
        return $this->db->getArray1v($sql);
    }
    //--------------------------------------------------------------------------
    

    public function listUsersWithRole($role) {
        if (Config::$useApc) {
            $arr = apc_fetch($this->apcKey, $success);
            if ($success) {
                if (!is_array($arr)) {
                    return array();
                }
                $userIDs = array();
                foreach ($arr as $userId => $subArr) {
                    foreach ($subArr as $s) {
                        if (strtolower($s) == strtolower($role)) {
                            $userIDs[] = $userId;
                        }
                    }
                }
                return $userIDs;
            }
        }

        if (PHP_SAPI == 'cli') {
            if ($this->userRightsCache === false) {
                $this->userRightsCache = $this->listUserCapsAssoc();
            }
            $userIDs = array();
            foreach ($this->userRightsCache as $userId => $subArr) {
                foreach ($subArr as $s) {
                    if (strtolower($s) == strtolower($role)) {
                        $userIDs[] = $userId;
                    }
                }
            }
            return $userIDs;
        }

        $sql = "SELECT `user_id` FROM `$this->t_user_rights_cache` WHERE `capability` = ? ORDER BY 1";
        return $this->db->getArray1v($sql, array(strtolower($role)));
    }
    //--------------------------------------------------------------------------


    public function updateLastLoginTime($userId) {
        if (!$userId) {
            return;
        }
        $userId = (int)$userId;
        $sql = "UPDATE `$this->tableName` SET `last_login_time` = NOW() WHERE `id` = $userId";
        $this->db->query($sql);

        $sql = "SELECT `umeta_id` FROM `wp_usermeta` WHERE `user_id` = $userId AND `meta_key` = 'last_activity'";
        $umetaId = DB::$wpDb->getTopLeftInt($sql);

        if ($umetaId) {
            DB::$wpDb->query("UPDATE `wp_usermeta` SET `meta_value` = NOW() WHERE `umeta_id` = " . $umetaId);
        } else {
            DB::$wpDb->query("INSERT INTO `wp_usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES ($userId, 'last_activity', NOW())");
        }
    }
    //--------------------------------------------------------------------------


    public function listUsersInactiveFor($days) {
        $sql = "SELECT `id` FROM `$this->tableName` WHERE (`last_login_time` IS NULL) OR (`last_login_time` IS NOT NULL AND `last_login_time` < DATE_SUB(NOW(), INTERVAL ? DAY))";
        return $this->db->getArray1v($sql, array((int)$days));
    }
    //--------------------------------------------------------------------------


    public function updateTimeZone($userId, $tz) {
        $sql = "UPDATE `$this->t_users` SET `time_zone` = ? WHERE `id` = ?";
        $this->db->query($sql, array($tz, (int)$userId));
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Only needed for unit tests
     */
    public function clearUserCapCache() {
        $this->userRightsCache = false;
    }
    //--------------------------------------------------------------------------


    public function deleteUserCapCache($userId) {
        apc_delete($this->apcKey);
        $sql = "DELETE FROM `$this->t_user_rights_cache` WHERE `user_id` = ?";
        return $this->db->query($sql, array((int)$userId));
    }
    //--------------------------------------------------------------------------
    
    
    public function deleteUserFromPingtreeSystem($userId) {
        $sql = "DELETE FROM `$this->t_users` WHERE `id` = ?";
        $this->db->query($sql, array((int)$userId));
        
        $sql = "DELETE FROM `$this->t_user_rights_cache` WHERE `user_id` = ?";
        $this->db->query($sql, array((int)$userId));
    }
    //--------------------------------------------------------------------------
}
