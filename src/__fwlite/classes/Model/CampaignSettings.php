<?php

class Model_CampaignSettings extends CrudModelCaching {

    const SETTING_BOOTSCOUT_KEY = 1;

    protected $t_campaign_settings;

    private static $customSettings = array(
            self::SETTING_BOOTSCOUT_KEY => 'BotScout API Key',
    );


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_campaign_setting);
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listSettings($campaignId) {
        return $this->db->getArrayAssoc("SELECT `key`, `value` FROM `$this->tableName` WHERE `campaign_id` = ? ORDER BY 1", array((int)$campaignId));
    }
    //--------------------------------------------------------------------------


    public function attachSettingsForGroup(array & $campaigns) {
        if (!$campaigns) {
            return;
        }

        $ids = array();
        $map = array();
        foreach ($campaigns as & $row) {
            $ids[] = (int)$row['id'];
            $row['settings'] = array();
            $map[(int)$row['id']] = & $row['settings'];
        }

        $data = $this->listForAttachSettings($ids);
        foreach ($data as $cid => $settings) {
            $map[(int)$cid] = $settings;
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listForAttachSettings(array $ids) {
        return $this->db->getArrayAssoc("SELECT `campaign_id`, `key`, `value` FROM `$this->tableName` WHERE `campaign_id` IN (" . self::implodeInts($ids) . ") ORDER BY 1, 2");
    }
    //--------------------------------------------------------------------------


    public function saveSettings($campaignId, array $settings) {

        // Delete all existing settings from DB that are not in new group
        $sql = "DELETE FROM `$this->tableName` WHERE `campaign_id` = ?";
        if ($settings) {
            $sql .= " AND `key` NOT IN (" . self::implodeInts(array_keys($settings)) . ')';
        }
        $this->db->query($sql, array((int)$campaignId));

        if (empty($settings)) {
            return;
        }

        $sql = "REPLACE INTO `$this->tableName` (`campaign_id`, `key`, `value`) VALUES " . implode(', ', array_fill(0, sizeof($settings), "(" . (int)$campaignId . ", ?, ?)"));
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