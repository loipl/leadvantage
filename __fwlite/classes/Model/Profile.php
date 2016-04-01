<?php

class Model_Profile extends CrudModel {

    protected $t_profiles;

    protected $t_profiles_data;

    protected $t_profiles_inferred;

    const FT_EMAIL = Model_CampaignField::FIELD_TYPE_EMAIL;


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_profiles);

        $this->timestampForInsert = array('created_at', 'last_change_time');
    }
    //--------------------------------------------------------------------------


    public function getProfileData($profileId) {
        $email = $this->db->getTopLeft("SELECT `email` FROM `$this->tableName` WHERE `id` = ?", array((int)$profileId));
        if ($email) {
            $data = $this->db->getArrayAssoc("SELECT `field_type_id`, `value` FROM `$this->t_profiles_data` WHERE `profile_id` = ? ORDER BY 1", array((int)$profileId));
            $data[self::FT_EMAIL] = $email;
            return $data;
        } else {
            return array();
        }
    }
    //--------------------------------------------------------------------------


    public function getProfileDataForEmail($email) {
        if ($email) {
            $sql = "
          SELECT
            `pd`.`field_type_id`,
            `pd`.`value`
          FROM
            `$this->tableName`       AS `p`,
            `$this->t_profiles_data` AS `pd`
          WHERE
            `p`.`email` = ? AND
            `p`.`id`    = `pd`.`profile_id`
          ORDER BY 1";
            $data = $this->db->getArrayAssoc($sql, array($email));
            $data[self::FT_EMAIL] = $email;
            return $data;
        } else {
            return array();
        }
    }
    //--------------------------------------------------------------------------


    public function getProfileInferredDataForEmail($email) {
        if ($email) {
            $sql = "
          SELECT
            `pi`.`token`,
            `pi`.`value`
          FROM
            `$this->tableName`           AS `p`,
            `$this->t_profiles_inferred` AS `pi`
          WHERE
            `p`.`email` = ? AND
            `p`.`id`    = `pi`.`profile_id`
          ORDER BY 1";
            $data = $this->db->getArrayAssoc($sql, array($email));
            return $data;
        } else {
            return array();
        }
    }
    //--------------------------------------------------------------------------


    public function writeProfileInferredData($userId, array $data, array $inferred, & $profileId) {
        if (!$profileId && !$this->ensureProfileExists($data, $userId, $profileId)) {
            return;
        }

        $params = array();
        foreach ($inferred as $token => $value) {
            if ($value) {
                if (substr($token, 0, 1) == ':') {
                    $token = substr($token, 1);
                }
                if (substr($token, -1) == ':') {
                    $token = substr($token, 0, -1);
                }
                $token = strtolower($token);
                $params[] = $token;
                $params[] = $value;
            }
        }
        if ($params) {
            $sql = "REPLACE INTO `$this->t_profiles_inferred` (`profile_id`, `token`, `value`) VALUES\n\t";
            $sql .= implode(",\n\t", array_fill(0, sizeof($params) / 2, "($profileId, ?, ?)"));
            $this->db->query($sql, $params);
        }
    }
    //--------------------------------------------------------------------------


    public function writeProfileData($userId, array $data, & $profileId) {
        if (!$profileId && !$this->ensureProfileExists($data, $userId, $profileId)) {
            return;
        }
        $params = array();
        foreach ($data as $ftId => $value) {
            if ($value) {
                $params[] = $ftId;
                $params[] = $value;
            }
        }
        if ($params) {
            $sql = "REPLACE INTO `$this->t_profiles_data` (`profile_id`, `field_type_id`, `value`) VALUES\n\t"
            . implode(",\n\t", array_fill(0, sizeof($params) / 2, "($profileId, ?, ?)"));
            $this->db->query($sql, $params);
        }
    }
    //--------------------------------------------------------------------------


    private function ensureProfileExists(array & $data, $userId, & $profileId) {
        $profileId = 0;
        if (empty($data[self::FT_EMAIL])) {
            return false;
        }
        $email = $data[self::FT_EMAIL];
        $ip    = sprintf('%u', ip2long($_SERVER['REMOTE_ADDR']));

        $sql = "INSERT INTO `$this->tableName` (`email`, `created_by_user_id`, `last_ip`, `created_at`, `last_change_time`)
        VALUES (?, ?, $ip, NOW(), NOW())
        ON DUPLICATE KEY UPDATE `last_change_time` = NOW()";
        if (!in_array($_SERVER['REMOTE_ADDR'], Config::$serverIPAddresses)) {
            $sql .= ", `last_ip` = $ip";
        }

        $this->db->query($sql, array($email, (int)$userId));
        $profileId = $this->db->getTopLeftInt("SELECT `id` FROM `$this->tableName` WHERE `email` = ?", array($email));

        unset($data[self::FT_EMAIL]);
        if ($data) {
            return true;
        } else {
            return false;
        }
    }
    //--------------------------------------------------------------------------
}
