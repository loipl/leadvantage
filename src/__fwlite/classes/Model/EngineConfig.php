<?php

class Model_EngineConfig extends CrudModelCaching {

    // This was originally meant only for repost config, now we're just keeping table name
    protected $t_repost_config;

    protected $t_nodes;

    const CRON_TIMEOUT       = 'cron_timeout';
    const DELIVERY_TIMEOUT   = 'delivery_timeout';
    const SUBMISSION_TIMEOUT = 'submission_timeout';

    const REPOST_QUEUE_MEM_SIZE = 'repost_queue_mem_size';
    const ONE_REPOST_BATCH_SIZE = 'one_repost_batch_size';


    public static $perDomainSettings = array(
        self::CRON_TIMEOUT,
        self::DELIVERY_TIMEOUT,
        self::SUBMISSION_TIMEOUT,
    );

    public static $universalSettings = array (
        self::REPOST_QUEUE_MEM_SIZE,
        self::ONE_REPOST_BATCH_SIZE
    );


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_repost_config, array('ip', 'key'));
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listSettings($ipAddress) {
        $sql = "SELECT `ip`, `key`, `value` FROM `$this->tableName` WHERE `ip` = '' OR `ip` = ? ORDER BY `key`, `ip`";
        $arr = $this->db->getArray($sql, array($ipAddress), MYSQL_ASSOC);

        $settings = array();
        foreach ($arr as $row) {
            if (($row['ip'] == $ipAddress) || !isset($settings[$row['key']])) {
                $settings[$row['key']] = $row['value'];
            }
        }

        return $settings;
    }
    //--------------------------------------------------------------------------


    public function listAllSettings() {
        $sql = "SELECT `key`, `ip`, `value` FROM `$this->tableName` ORDER BY `key`, `ip`";
        return $this->db->getArrayAssoc($sql);
    }
    //--------------------------------------------------------------------------


    public function writeConfigSettings(array $settings) {
        $this->startTransaction();

        foreach (self::$perDomainSettings as $key) {
            $this->db->query("DELETE FROM `$this->tableName` WHERE `key` = ?", array($key));
            foreach (explode("\n", $settings[$key]) as $line) {
                $arr = explode('=', $line);
                if (sizeof($arr) == 2) {
                    $ip    = $arr[0];
                    $value = $arr[1];
                } else {
                    $ip    = '';
                    $value = $arr[0];
                }
                $sql = "INSERT IGNORE INTO `$this->tableName` (`ip`, `key`, `value`) VALUES (?, ?, ?)";
                $value = trim($value);
                if ($value != '') {
                    $this->db->query($sql, array($ip, $key, $value));
                }
            }
        }

        foreach (self::$universalSettings as $key) {
            $this->db->query("DELETE FROM `$this->tableName` WHERE `key` = ?", array($key));
            $sql = "INSERT INTO `$this->tableName` (`ip`, `key`, `value`) VALUES (?, ?, ?)";
            $value = isset($settings[$key]) ? trim($settings[$key]) : '';
            if ($value != '') {
                $this->db->query($sql, array('', $key, $value));
            }
        }

        $this->commit();
    }
    //--------------------------------------------------------------------------


    public function initializeEngineConfigObject(Engine_Settings $config, $ipAddress) {
        $settings = $this->listSettings($ipAddress);

        if (isset($settings[self::CRON_TIMEOUT]) && ($settings[self::CRON_TIMEOUT] != '')) {
            $config->cronTimeout = max(0, (int)$settings[self::CRON_TIMEOUT]);
        }

        if (isset($settings[self::DELIVERY_TIMEOUT]) && ($settings[self::DELIVERY_TIMEOUT] != '')) {
            $config->deliveryTimeout = max(0, (int)$settings[self::DELIVERY_TIMEOUT]);
        }

        if (isset($settings[self::SUBMISSION_TIMEOUT]) && ($settings[self::SUBMISSION_TIMEOUT] != '')) {
            $config->processingTimeout = max(0, (int)$settings[self::SUBMISSION_TIMEOUT]);
        }

        if (isset($settings[self::REPOST_QUEUE_MEM_SIZE]) && ($settings[self::REPOST_QUEUE_MEM_SIZE] != '')) {
            $config->repostQueueMemSize = max(0, (int)$settings[self::REPOST_QUEUE_MEM_SIZE]);
        }

        if (isset($settings[self::ONE_REPOST_BATCH_SIZE]) && ($settings[self::ONE_REPOST_BATCH_SIZE] != '')) {
            $config->oneRepostBatchSize = max(0, (int)$settings[self::ONE_REPOST_BATCH_SIZE]);
        }
    }
    //--------------------------------------------------------------------------


    public function listNodes() {
        return $this->db->getArray1v("SELECT `ip_address` FROM `$this->t_nodes` WHERE `ip_address` <> '' ORDER BY 1");
    }
    //--------------------------------------------------------------------------


    public function listNodesAssoc() {
        return $this->db->getArrayAssoc("SELECT `ip_address`, `name` FROM `$this->t_nodes` ORDER BY 1");
    }
    //--------------------------------------------------------------------------


    public function writeNodes(array $nodes) {
        if (!$nodes) {
            $this->db->query("DELETE FROM `$this->t_nodes` WHERE `ip_address` <> ''");
            return;
        }

        $this->startTransaction();

        $qms = implode(', ', array_fill(0, sizeof($nodes), '?'));
        $values = $this->db->processParams($qms, array_keys($nodes));
        $this->db->query("DELETE FROM `$this->t_nodes` WHERE `ip_address` <> '' AND `ip_address` NOT IN ($values)");

        foreach ($nodes as $ip => $name) {
            if (!$ip) {
                continue;
            }
            $params = array($ip, $name, $name);
            $this->db->query("INSERT INTO `$this->t_nodes` (`ip_address`, `name`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `name` = ?", $params);
        }

        $this->commit();
    }
    //--------------------------------------------------------------------------
}
