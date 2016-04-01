<?php

class Controller_Admin_Subaccounts extends CrudControllerWithCM {

    /**
     * @var Model_ConfigPostsPerLevel
     */
    protected $model;

    /**
     * @var Model_User
     */
    protected $userModel;
    
    /**
     * @var Model_ConfigPostsPerLevel
     */
    protected $configPerLevel;
    
    public function __construct() {

        $this->model = SingletonRegistry::getSingleInstance('Model_SubAccount');
        $this->userModel = SingletonRegistry::getSingleInstance('Model_User');
        $this->configPerLevel = SingletonRegistry::getSingleInstance('Model_ConfigPostsPerLevel');
        
        parent::__construct();
        $this->defaultOrderBy = 'id DESC';
        $this->itemName       = 'Sub-account';
    }
    //--------------------------------------------------------------------------


    public function indexAction() {
        $this->out['subAccounts'] = $this->model->listAllSubAccounts();
        parent::indexAction();
    }
    //--------------------------------------------------------------------------


    protected function addFormFields($action = '') {
        $userIds = $this->userModel->listUserIDs();
        $nonAdminUserIds = $this->userModel->listNonAdminUserIDs();
        
        // main accounts select
        $mainAccounts = array();
        foreach ($userIds as $id) {
            $userData = get_userdata($id);
            if (!empty($userData->data->user_login)) {
                $mainAccounts[$id] = $userData->data->user_login . "(" . $userData->data->display_name . ")";
            }
        }
        
        // sub accounts select
        $subAccounts = array();
        foreach ($nonAdminUserIds as $id) {
            $userData = get_userdata($id);
            if (!empty($userData->data->user_login)) {
                $subAccounts[$id] = $userData->data->user_login . "(" . $userData->data->display_name . ")";
            }
        }
        
        $this->form->add('select',   'user_id', 'Main account',      array('* items' => $mainAccounts));
        $this->form->add('select',   'sub_account_id', 'Sub account',      array('* items' => $subAccounts));
        $this->form->add('checkbox', 'revoke_reporting_access', 'Revoke reporting access');
        
        $this->pageFragmentForm->tableAttributes['width'] = 650;
    }
    //--------------------------------------------------------------------------


    public function addAction($insert = true, $redirect = true) {
        $this->form($this->titleForAddForm(), $this->buttonTextForAddForm(), 'add');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->form->importFromArray($_POST, true);
            if ($this->form->hasErrors()) {
                return;
            }
            $this->validateFormImport('add');
            if ($this->form->hasErrors()) {
                return;
            }
            
            $this->data = $this->getFormValuesForWritingToDb('add');
            
            // check sub-account existing
            if ($this->model->checkSubAccountExist($this->data['user_id'], $this->data['sub_account_id'])) {
                $this->form->addError('sub_account_id', "Sub-account was already assigned!");
                return;
            }
                
            // get user meta data
            $userData = get_userdata($this->data['user_id']);
            $this->data['username'] = $userData->data->user_login . "(" . $userData->data->display_name . ")";
            
            // revoke reporting access ?
            $this->data['revoke_reporting_access'] = $this->data['revoke_reporting_access'] ? '1' : '0';
            
            // check if exceeds limit of sub-accounts
            $userId = $this->data['user_id'];
            $userCap = $this->userModel->listUserCaps($userId);
            $subAccountLimit = $this->configPerLevel->getMaxSubAccountLimit($userCap[0]);
            $subAccounts = $this->model->listAllSubAccounts($userId);
            if (isset($subAccountLimit[0]) && count($subAccounts) >= intval($subAccountLimit[0])) {
                $this->form->addError('user_id', "Can not add more sub-accounts to user '".$this->data['username']."'!<br/> Max sub-accounts: " . $subAccountLimit[0]);
                return;
            }
            
            // get sub-account meta data
            $subAccountData = get_userdata($this->data['sub_account_id']);
            $this->data['sub_account_username'] = $subAccountData->data->user_login . "(" . $subAccountData->data->display_name . ")";
            
            $this->insertDefaultValues();
            if ($insert) {
                $this->insertRow();
            }
            if ($redirect) {
                App::getFrontController()->redirectToCP($this, $this->paramsForRedirect, $this->getForRedirect);
            }
        } else {
            $this->putInitialValuesToForm();
        }
    }
    //--------------------------------------------------------------------------
    
    
    public function deleteAction($delete = true, $redirect = true) {
        $this->getRowFromDb('delete');
        $this->form = new Form_Data;
        $this->form->add('button', '', 'Delete', array('type' => 'submit'));

        $frm = new PageFragment_FormAuto($this, $this->form, true);
        $frm->title = $this->titleForDeleteForm();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($delete) {
                $deletedRow = $this->model->getWhere(array('id' => $this->id));

                $this->deleteRow();
                
                if (!empty($deletedRow['sub_account_id'])) {
                    $this->userModel->deleteUserFromPingtreeSystem($deletedRow['sub_account_id']);
                    
                    require_once(ABSPATH.'wp-admin/includes/user.php');
                    wp_delete_user($deletedRow['sub_account_id']);
                }
            }
            if ($redirect) {
                App::getFrontController()->redirectToCP($this, $this->paramsForRedirect, $this->getForRedirect);
            }
        }
    }
}
