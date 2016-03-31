<?php

class Model_PartnerTemplate extends CrudModelCaching {

    protected $t_template_partners;
    protected $t_tplp_industries;

    protected $defaultArguments = array (
        'listTemplateNamesAssocForIndustries' => array (
            0 => array ()
        ),
    );


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_template_partners);
    }
    //--------------------------------------------------------------------------


    public function listTemplateNamesAssoc() {
        $sql = "SELECT `partner_id`, `template_name` FROM `$this->tableName` ORDER BY 2";
        return $this->db->getArrayAssoc($sql);
    }
    //--------------------------------------------------------------------------


    public function listTemplateNamesAssocForIndustries(array $industries = array()) {
        $sql = "
        SELECT `tp`.`partner_id`, `tp`.`template_name` AS `name`, GROUP_CONCAT(`ti`.`industry_id` ORDER BY `ti`.`industry_id`) AS `ind`
        FROM `$this->tableName` AS `tp` LEFT JOIN `$this->t_tplp_industries` AS `ti` ON `ti`.`tp_id` = `tp`.`id` GROUP BY `tp`.`id` ORDER BY 2";

        $result = array();
        foreach ($this->db->getArray($sql) as $row) {

            // If no industry is assigned to this template, or if some are and there is a match with $industries array
            if (!$row[2] || array_intersect(explode(',', $row[2]), $industries)) {
                $result[$row[0]] = $row[1];
            }
        }
        return $result;
    }
    //--------------------------------------------------------------------------


    public function getTemplateForPartnerId($partnerId) {
        return $this->db->getTopArray("SELECT * FROM `$this->tableName` WHERE `partner_id` = ?", array((int)$partnerId), MYSQL_ASSOC);
    }
    //--------------------------------------------------------------------------


    public function listTemplateIndustriesAssoc(array $partnerIds) {
        if (!$partnerIds) {
            return array();
        }

        $sql = "SELECT `partner_id`, `industry_id` FROM `$this->tableName` AS `t`, `$this->t_tplp_industries` AS `ti`
        WHERE `t`.`id` = `ti`.`tp_id` AND `t`.`partner_id` IN (" . self::implodeInts($partnerIds) . ')';
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


    public function markAsTemplate($partnerId, $name, array $industries = array()) {
        $name = trim($name);
        if (!$name) {
            throw new Exception('Template name cannot be empty');
        }

        $existingData = array('partner_id' => (int)$partnerId, 'template_name' => trim($name));
        $conflicts = $this->getExistingFieldValuesForKey(0, $existingData);
        if (isset($conflicts['partner_id'])) {
            throw new EExplainableError("Partner is already marked as template");
        }
        if (isset($conflicts['template_name'])) {
            throw new EExplainableError("Name '$name' already exists");
        }

        $sql = "INSERT IGNORE INTO `$this->tableName` (`partner_id`, `template_name`) VALUES (?, ?)";
        $success = $this->db->query($sql, array((int)$partnerId, $name));
        $templateId = $this->db->lastInsertId();
        if ($success && $templateId) {
            $this->saveTemplateIndustries($templateId, $industries);
        }
        return $success;
    }
    //--------------------------------------------------------------------------


    private function saveTemplateIndustries($templateId, array $industries = array()) {
        $this->db->query("DELETE FROM `$this->t_tplp_industries` WHERE `tp_id` = ?", array((int)$templateId));
        if ($industries) {
            $sql = "INSERT INTO `$this->t_tplp_industries` (`tp_id`, `industry_id`) VALUES\n";
            $sql .= implode(",\n", array_fill(0, sizeof($industries), "(" . (int)$templateId . ', ?)'));
            $this->db->query($sql, $industries);
        }
    }
    //--------------------------------------------------------------------------


    public function removeTemplate($partnerId) {
        return $this->db->query("DELETE FROM `$this->tableName` WHERE `partner_id` = ?", array((int)$partnerId));
    }
    //--------------------------------------------------------------------------


    public function renameTemplate($partnerId, $name, array $industries = array()) {
        $name = trim($name);
        if (!$name) {
            throw new Exception('Template name cannot be empty');
        }

        $sql   = "SELECT COUNT(*) FROM `$this->tableName` WHERE `template_name` = ? AND `partner_id` <> ?";
        $count = $this->db->getTopLeftInt($sql, array($name, (int)$partnerId));
        if ($count) {
            throw new EExplainableError("Name '$name' already exists");
        }

        $success = $this->db->query("UPDATE `$this->tableName` SET `template_name` = ? WHERE `partner_id` = ?", array($name, (int)$partnerId));
        if ($success) {
            $templateId = $this->db->getTopLeftInt("SELECT `id` FROM `$this->tableName` WHERE `partner_id` = ?", array((int)$partnerId));
            $this->saveTemplateIndustries($templateId, $industries);
        }
        return $success;
    }
    //--------------------------------------------------------------------------
}
