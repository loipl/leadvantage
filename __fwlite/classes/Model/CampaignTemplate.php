<?php

class Model_CampaignTemplate extends CrudModelCaching {

    protected $t_template_campaigns;
    protected $t_tplc_industries;

    protected $defaultArguments = array (
        'listTemplateNamesAssocForIndustries' => array (
            0 => array (),
        ),
    );


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_template_campaigns);
    }
    //--------------------------------------------------------------------------


    public function listTemplateNamesAssoc() {
        $sql = "SELECT `campaign_id`, `template_name` FROM `$this->tableName` ORDER BY 2";
        return $this->db->getArrayAssoc($sql);
    }
    //--------------------------------------------------------------------------


    public function listTemplateNamesAssocForIndustries(array $industries = array()) {
        $sql = "
        SELECT `tc`.`campaign_id`, `tc`.`template_name` AS `name`, GROUP_CONCAT(`ti`.`industry_id` ORDER BY `ti`.`industry_id`) AS `ind`
        FROM `$this->tableName` AS `tc` LEFT JOIN `$this->t_tplc_industries` AS `ti` ON `ti`.`tc_id` = `tc`.`id` GROUP BY `tc`.`id` ORDER BY 2";

        $result = array();
        foreach ($this->db->getArray($sql) as $row) {
            $templateIndustries = $row[2];

            // array_intersect below means if any of campaigns for the template matches the $industries param.
            // Template industries will be empty if it can be used for all industries
            if (!$templateIndustries || array_intersect(explode(',', $templateIndustries), $industries)) {
                $result[$row[0]] = $row[1];
            }
        }
        return $result;
    }
    //--------------------------------------------------------------------------


    public function listTemplateIndustriesAssoc(array $campaignIds) {
        if (!$campaignIds) {
            return array();
        }

        $sql = "SELECT `campaign_id`, `industry_id` FROM `$this->tableName` AS `t`, `$this->t_tplc_industries` AS `ti`
        WHERE `t`.`id` = `ti`.`tc_id` AND `t`.`campaign_id` IN (" . self::implodeInts($campaignIds) . ')';
        $result = array();
        foreach ($this->db->getArray($sql) as $row) {
            if (!isset($result[$row[0]])) {
                $result[$row[0]] = array();
            }
            $result[$row[0]][] = (int)$row[1];
        }
        return $result;
    }
    //--------------------------------------------------------------------------


    public function markAsTemplate($campaignId, $name, array $industries = array()) {
        $name = trim($name);
        if (!$name) {
            throw new Exception('Template name cannot be empty');
        }

        $checkExistingData = array('campaign_id' => (int)$campaignId, 'template_name' => $name);
        $conflicts = $this->getExistingFieldValuesForKey(0, $checkExistingData);
        if (isset($conflicts['campaign_id'])) {
            throw new EExplainableError("Campaign is already marked as template");
        }
        if (isset($conflicts['template_name'])) {
            throw new EExplainableError("Name '$name' already exists");
        }

        $sql = "INSERT IGNORE INTO `$this->tableName` (`campaign_id`, `template_name`) VALUES (?, ?)";
        $success = $this->db->query($sql, array((int)$campaignId, $name));
        $templateId = $this->db->lastInsertId();
        if ($success && $templateId) {
            $this->saveTemplateIndustries($templateId, $industries);
        }
        return $success;
    }
    //--------------------------------------------------------------------------


    private function saveTemplateIndustries($templateId, array $industries = array()) {
        $this->db->query("DELETE FROM `$this->t_tplc_industries` WHERE `tc_id` = ?", array((int)$templateId));
        if ($industries) {
            $sql = "INSERT INTO `$this->t_tplc_industries` (`tc_id`, `industry_id`) VALUES\n";
            $sql .= implode(",\n", array_fill(0, sizeof($industries), "(" . (int)$templateId . ', ?)'));
            $this->db->query($sql, $industries);
        }
    }
    //--------------------------------------------------------------------------


    public function removeTemplate($campaignId) {
        return $this->db->query("DELETE FROM `$this->tableName` WHERE `campaign_id` = ?", array((int)$campaignId));
    }
    //--------------------------------------------------------------------------


    public function renameTemplate($campaignId, $name, array $industries = array()) {
        $name = trim($name);
        if (!$name) {
            throw new Exception('Template name cannot be empty');
        }

        $sql   = "SELECT COUNT(*) FROM `$this->tableName` WHERE `template_name` = ? AND `campaign_id` <> ?";
        $count = $this->db->getTopLeftInt($sql, array($name, (int)$campaignId));
        if ($count) {
            throw new EExplainableError("Name '$name' already exists");
        }

        $success = $this->db->query("UPDATE `$this->tableName` SET `template_name` = ? WHERE `campaign_id` = ?", array($name, (int)$campaignId));
        if ($success) {
            $templateId = $this->db->getTopLeftInt("SELECT `id` FROM `$this->tableName` WHERE `campaign_id` = ?", array((int)$campaignId));
            $this->saveTemplateIndustries($templateId, $industries);
        }
        return $success;
    }
    //--------------------------------------------------------------------------
}