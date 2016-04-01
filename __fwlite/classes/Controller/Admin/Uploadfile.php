<?php

class Controller_Admin_Uploadfile extends Controller {

    /**
     * @var Model_LogIncoming
     */
    private $logIncoming;


    public function __construct() {
        parent::__construct();
        $this->logIncoming = SingletonRegistry::getModelLogIncoming();
    }
    //--------------------------------------------------------------------------


    public function indexAction() {
        $this->out['uploadedFiles'] = $this->logIncoming->getUploadedFiles();
        $this->out['usernamesAssoc'] = SingletonRegistry::getModelUser()->listUsernamesAssoc();
    }
    //--------------------------------------------------------------------------
    
    public function deleteFileAction() {
        $filename = isset($_GET['name']) ? $_GET['name'] : null;
        if (!$filename) {
            throw new EExplainableError("Invalid name parameter");
        }
        
        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        if (!$userId) {
            throw new EExplainableError("Invalid user_id parameter");
        }
        
        $campaignId = isset($_GET['campaign_id']) ? intval($_GET['campaign_id']) : null;
        if (!$campaignId) {
            throw new EExplainableError("Invalid campaign_id parameter");
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->logIncoming->cancelUploadedFile($userId, $filename, $campaignId);
            App::getFrontController()->redirectToCP($this);
        }

        $form = new Form_Data();
        $form->add('button', '', 'Cancel');
        $pf = new PageFragment_FormAuto($this, $form, true);
        $pf->title = 'Cancel Uploaded File?';
    }
    //--------------------------------------------------------------------------
}