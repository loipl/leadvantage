<?php

class Model_PartnerField extends CrudModelCaching {
    /**
     * @var DB
     */
    protected $db;

    protected $t_partner_fields;

    protected $defaultArguments = array (
        'listAllForPartner' => array (
            1 => 'id',
        ),
    );

    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_partner_fields);
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listAllForPartner($partnerId, $orderBy = 'id') {
        $sql = "SELECT * FROM `$this->tableName` WHERE `partner_id` = ?" . $this->order($orderBy);
        return $this->db->getArrayIndexed($sql, array((int)$partnerId), 'id');
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listAssocForPartnerGroup(array $ids) {
        if (!$ids) {
            return array();
        }

        $sql    = "SELECT `partner_id`, `name`, `value`, `modifier` FROM `$this->tableName` WHERE `partner_id` IN (" . self::implodeInts($ids) . ') ORDER BY 1, 2';
        $result = array();
        foreach($this->db->getArray($sql) as $row) {
            $result[$row['partner_id']][$row['name']] = array('value' => $row['value'], 'modifier' => $row['modifier']);
        }
        return $result;
    }
    //--------------------------------------------------------------------------


    public function deleteForPartner($partnerId) {
        $this->db->query("DELETE FROM `$this->tableName` WHERE `partner_id` = ?", array((int)$partnerId));
    }
    //--------------------------------------------------------------------------
}
