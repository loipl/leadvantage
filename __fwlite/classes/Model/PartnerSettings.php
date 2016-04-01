<?php

class Model_PartnerSettings extends CrudModelCaching {
    protected $t_partner_settings;

    const SETTING_AFF_API_KEY   = 1;

    const SETTING_GROUP_GENERAL = 1;
    const SETTING_GROUP_AFF     = 2;

    private static $customSettings = array (
        self::SETTING_GROUP_GENERAL => array(),
        self::SETTING_GROUP_AFF     => array(
            self::SETTING_AFF_API_KEY => 'AdultFriendFinder API Key',
        )
    );


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_partner_settings);
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listSettings($partnerId) {
        return $this->db->getArrayAssoc("SELECT `key`, `value` FROM `$this->tableName` WHERE `partner_id` = ? ORDER BY 1", array((int)$partnerId));
    }
    //--------------------------------------------------------------------------


    public function attachSettingsForGroup(array & $partners) {
        if (!$partners) {
            return;
        }

        $ids = array(); $map = array();
        foreach ($partners as & $row) {
            $ids[] = (int)$row['id'];
            $row['settings'] = array();
            $map[(int)$row['id']] = & $row['settings'];
        }

        $data = $this->listForAttachSettings($ids);
        foreach ($data as $pid => $settings) {
            $map[(int)$pid] = $settings;
        }
    }
    //--------------------------------------------------------------------------


    public function attachSettingsForPartnerList(array $partners) {
        if (!$partners) {
            return array();
        }

        $ids = array(); $map = array();
        foreach ($partners as & $row) {
            $ids[] = (int)$row['id'];
            $row['settings'] = array();
            $map[(int)$row['id']] = & $row['settings'];
        }

        $data = $this->listForAttachSettings($ids);
        foreach ($data as $pid => $settings) {
            $map[(int)$pid] = $settings;
        }

        return $partners;
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listForAttachSettings(array $ids) {
        return  $this->db->getArrayAssoc("SELECT `partner_id`, `key`, `value` FROM `$this->tableName` WHERE `partner_id` IN (" . self::implodeInts($ids) . ") ORDER BY 1, 2");;
    }
    //--------------------------------------------------------------------------


    public function saveSettings($partnerId, array $settings) {
        $sql = "DELETE FROM `$this->tableName` WHERE `partner_id` = ?";
        if ($settings) {
            $sql .= " AND `key` NOT IN (" . self::implodeInts(array_keys($settings)) . ')';
        }

        $this->db->query($sql, array((int)$partnerId));
        if (empty($settings)) {
            return;
        }

        $sql = "REPLACE INTO `$this->tableName` (`partner_id`, `key`, `value`) VALUES " . implode(', ', array_fill(0, sizeof($settings), "(" . (int)$partnerId . ", ?, ?)"));
        $params = array();
        foreach ($settings as $key => $value) {
            $params[] = $key;
            $params[] = $value;
        }
        $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------


    public static function listCustomSettings() {
        return self::$customSettings;
    }
    //--------------------------------------------------------------------------
}