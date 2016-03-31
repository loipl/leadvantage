<?php

class Model_LogApiErrors extends CrudModel {

    protected $t_log_api_errors;

    const API_RAPLEAF   = 1;
    const API_BRITE     = 2;
    const API_DV        = 3;
    const API_XVERIFY   = 4;
    const API_LEADSPEND = 5;

    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_log_api_errors);

        $this->timestampForInsert = array('request_time');
        $this->zeroOneFields      = array('is_timeout');
    }
    //--------------------------------------------------------------------------


    public static function log($apiCode, $url, $postText, $isTimeout, $httpCode, $response, $curl_error = '') {
        /* @var $thisModel Model_LogApiErrors */
        $thisModel   = SingletonRegistry::getSingleInstance('Model_LogApiErrors');
        $data = array(
            'api_nr'             => (int)$apiCode,
            'url'                => '' . $url,
            'post'               => '' . $postText,
            'is_timeout'         => $isTimeout,
            'response_http_code' => (int)$httpCode,
            'response_text'      => '' . $response,
            'curl_error'         => '' . $curl_error
        );
        return $thisModel->insert($data);
    }
    //--------------------------------------------------------------------------


    public function lastErrorId() {
        return $this->db->getTopLeftInt("SELECT MAX(`id`) FROM `$this->tableName`");
    }
    //--------------------------------------------------------------------------


    public function listErrorsAfterId($lastErrorId) {
        return $this->db->getArray("SELECT * FROM `$this->tableName` WHERE `id` > ?", array((int)$lastErrorId));
    }
    //--------------------------------------------------------------------------


    public function listErrorsSince($timestamp, $limit = 5) {
        $sql = "SELECT * FROM `$this->tableName` WHERE `request_time` >= ? ORDER BY `request_time` DESC LIMIT ?";
        return $this->db->getArray($sql, array($timestamp, (int)$limit));
    }
    //--------------------------------------------------------------------------
}
