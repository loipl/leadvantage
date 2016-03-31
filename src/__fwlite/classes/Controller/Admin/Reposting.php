<?php

class Controller_Admin_Reposting extends Controller {

    /**
     * @var Model_LogIncomingRepost
     */
    private $modelRepost;


    public function __construct() {
        parent::__construct();
        $this->modelRepost = SingletonRegistry::getModelLogIncomingRepost();
    }
    //--------------------------------------------------------------------------


    public function indexAction() {
        //
    }
    //--------------------------------------------------------------------------


    public function hrtestAction() {
        error_reporting(E_ERROR || E_WARNING);
        ini_set('display_errors', 1);
        Config::$repostThreadLogging = false;

        $hr = new Helper_Repost();
        $data = $hr->testGetRepostData(100);
        $this->content = Lib::var_export($data, true) . "\n<hr><br>\n" . implode("<br>\n", $hr->processed);
    }
    //--------------------------------------------------------------------------
}
