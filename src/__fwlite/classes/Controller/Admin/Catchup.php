<?php

class Controller_Admin_Catchup extends Controller {

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
        $this->out['jobs'] = $this->logIncoming->listCatchupJobs();
        $this->out['usernamesAssoc'] = SingletonRegistry::getModelUser()->listUsernamesAssoc();
    }
    //--------------------------------------------------------------------------


    public function deleteJobAction() {
        $id = isset($_GET['id']) ? max(0, (int)$_GET['id']) : 0;
        if (!$id) {
            throw new EExplainableError("Invalid id parameter");
        }
        $job = $this->logIncoming->getCatchUpJob($id);
        if (!$job) {
            throw new EExplainableError("Invalid id parameter");
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->logIncoming->deleteCatchupJob($id);
            App::getFrontController()->redirectToCP($this);
        }

        $form = new Form_Data();
        $form->add('button', '', 'Cancel');
        $pf = new PageFragment_FormAuto($this, $form, true);
        $pf->title = 'Cancel catchup job?';
    }
    //--------------------------------------------------------------------------
}
