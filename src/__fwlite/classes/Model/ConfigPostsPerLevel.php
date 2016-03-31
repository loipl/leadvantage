<?php

class Model_ConfigPostsPerLevel extends CrudModelCaching {


    protected $t_config_posts_per_level;

    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_config_posts_per_level);

        $this->zeroOneFields[] = 'can_validate_email';
        $this->zeroOneFields[] = 'can_validate_phone';
    }
    //--------------------------------------------------------------------------


    public function listPrettyLevelNames() {
        $sql = "SELECT `option_value` FROM `wp_options` WHERE `option_name` = 'ws_plugin__s2member_options'";
        $text = DB::$wpDb->getTopLeft($sql);
        
        $fixedSerializedData = preg_replace_callback ( '!s:(\d+):"(.*?)";!', function($match) {
            return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
        }, $text );
        $arr = unserialize($fixedSerializedData);

        $result = array();
        foreach ($arr as $k => $v) {
            if (preg_match('/^level([\\d]{1,})_label$/', $k)) {
                $result["s2member_" . substr($k, 0, -6)] = $v;
            }
        }

        return $result;
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listLevelsAssoc() {
        $sql = "SELECT `level`, `max_posts` FROM `$this->tableName` ORDER BY 2 DESC";
        return $this->db->getArrayAssoc($sql);
    }
    //--------------------------------------------------------------------------
    
    
    public function getMaxSubAccountLimit($level) {
        $sql = "SELECT `max_sub_accounts` FROM `$this->tableName` WHERE `level` = ? ORDER BY 1";
        return $this->db->getArray1v($sql, array($level));
    }
}
