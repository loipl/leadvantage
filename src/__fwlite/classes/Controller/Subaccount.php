<?php

class Controller_Subaccount extends Controller {
    
    /**
     * @var Model_SubAccount
     */
    protected $model;
    
    
    public function indexAction() {
        $auth = Auth::getInstance();
        $userId = $auth->getUserId();
        $allSubAccount = $this->model->listAllSubAccounts($userId);
        $this->checkReachSubAccountLimit($userId, $allSubAccount, 'index');
        
        foreach ($allSubAccount as $index => $subAccount) {
            $subAccountId = $subAccount['sub_account_id'];
            $campaignAndPartnerCount = $this->model->countCampaignAndPartnerOfSubAccount($subAccountId);
            $allSubAccount[$index]['campaign_count'] = $campaignAndPartnerCount[0];
            $allSubAccount[$index]['partner_count'] = $campaignAndPartnerCount[1];
        }
        
        $this->out['allSubAccount'] = $allSubAccount;
    }
    //--------------------------------------------------------------------------
    
    
    public function editAction() {
        $id = filter_input(INPUT_GET, 'sub_account_id');
        $subAccountData = $this->model->getWhere(array('sub_account_id' => $id));
        if (empty($subAccountData)) {
            throw new ERequestError("Invalid 'sub_account_id' parameter");
        }
           
        $auth = Auth::getInstance();
        $userId = $auth->getUserId();
        $modelCampaign = SingletonRegistry::getModelCampaign();
        $modelPartner  = SingletonRegistry::getModelPartner();
        $campaigns = $modelCampaign->listAllWhere(array('user_id' => $userId));
        $partners  = $modelPartner->listAllWhere(array('user_id' => $userId, 'wrapped_campaign_id IS NULL', 'partner_type = \'0\' OR ping_post_type = \'1\''));
        $partners  = $modelPartner->removePingPostSuffix($partners);
        
        $this->out['subAccountData'] = $subAccountData;
        $this->out['campaigns'] = $campaigns;
        $this->out['partners']  = $partners;
        $this->out['checkedCampaignIds']  = $this->model->listAllCampaignIds($id);
        $this->out['checkedPartnerIds']  = $this->model->listAllPartnerIds($id);
    }
    //--------------------------------------------------------------------------
    
    
    public function addAction() {
        $auth = Auth::getInstance();
        $userId = $auth->getUserId();
        $allSubAccount = $this->model->listAllSubAccounts($userId);
        $this->checkReachSubAccountLimit($userId, $allSubAccount, 'add');
        
        $this->pageTitle = 'Add Campaign';
        $form = new Form_Data();
        $form->add('text', 'username',  '', array('maxlength' => 64, '* required' => true, '* trim' => true));
        $form->add('text', 'email',  '', array('maxlength' => 64, '* required' => true, '* trim' => true));
        $form->add('password', 'password',  '', array('maxlength' => 64, '* required' => true));
        $form->add('password', 're_password',  '', array('maxlength' => 64, '* required' => true));
        $form->add('checkbox', 'revoke_reporting_access', '', array('id' => 'revoke_reporting_access'));
        $form->add('checkbox', 'revoke_campaign_control', '', array('id' => 'revoke_campaign_control'));
        $form->add('checkbox', 'revoke_partner_control', '', array('id' => 'revoke_partner_control'));
        $form->add('checkbox', 'revoke_success_leads', '', array('id' => 'revoke_success_leads'));
        $form->add('checkbox', 'revoke_failed_leads', '', array('id' => 'revoke_failed_leads'));
        $form->add('checkbox', 'revoke_skipped_leads', '', array('id' => 'revoke_skipped_leads'));
        
        $pf = new PageFragment_FormAuto($this, $form, false);

        $this->out['form'] = $form;
        $this->out['pf']   = $pf;
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $form->importPostRequestAndThrowEDoneOnError();
            $this->validateFormAndThrowEDoneOnError($form);

            $userId = wp_create_user($form->username, $form->password, $form->email);
            if (!empty($userId) && is_numeric($userId)) {
                $modelUser = SingletonRegistry::getModelUser();
                $modelUser->insert(array('id'=>$userId), true);
                $modelUser->writeUserCaps($userId, array('subscriber'));
                
                $modelSubAccount = SingletonRegistry::getModelSubAccount();
                $auth = Auth::getInstance();
                $loginUserId = $auth->getUserId();
                $userData = get_userdata($loginUserId);
                
                $data = array(
                    'user_id' => $loginUserId,
                    'username' => $userData->data->user_login . "(" . $userData->data->display_name . ")",
                    'sub_account_id' => $userId,
                    'sub_account_username' => $form->username,
                    'revoke_reporting_access' => $form->revoke_reporting_access ? '1' : '0',
                    'revoke_campaign_control' => $form->revoke_campaign_control ? '1' : '0',
                    'revoke_partner_control' => $form->revoke_partner_control ? '1' : '0',
                    'revoke_success_leads' => $form->revoke_success_leads ? '1' : '0',
                    'revoke_failed_leads' => $form->revoke_failed_leads ? '1' : '0',
                    'revoke_skipped_leads' => $form->revoke_skipped_leads ? '1' : '0'
                );
                
                $modelSubAccount->insert($data);
                App::getFrontController()->redirectToCP($this);
            } else {
                $form->message = "Cannot create user";
            }
        }
    }
    //--------------------------------------------------------------------------
    
    
    protected function checkReachSubAccountLimit($userId, $allSubAccount, $page) {
        $userModel = SingletonRegistry::getSingleInstance('Model_User');
        $configPerLevelModel = SingletonRegistry::getSingleInstance('Model_ConfigPostsPerLevel');
        $userCap = $userModel->listUserCaps($userId);
        if (isset($userCap[0])) {
            $subAccountLimit = $configPerLevelModel->getMaxSubAccountLimit($userCap[0]);
        }
        
        if (isset($subAccountLimit[0]) && $subAccountLimit[0] > 0) {
            if (count($allSubAccount) >= intval($subAccountLimit[0])) {
                if ($page === 'add') {
                    throw new EAccessDenied('Access denied');
                }
                
                $this->out['reachSubAccountLimit'] = true;
            }
        } else {
            throw new EAccessDenied('Access denied');
        }
    }
    //--------------------------------------------------------------------------
    
    
    public function validateFormAndThrowEDoneOnError($form) {
        if ($form->password !== $form->re_password) {
            $form->password     = '';
            $form->re_password  = '';
            $form->addError('re_password', 'Password mismatch');
        }
        
        if (username_exists($form->username)) {
            $form->addError('username', 'Username existed');
        }
        
        if (email_exists($form->email)) {
            $form->addError('email', 'This email is used');
        }
        $form->throwDoneIfErrors();
    }
    
    
    public function ajaxEditAction() {
        App::getFrontController()->setUsePageTemplate(false);
        $id = filter_input(INPUT_POST, 'id');
        if (!is_numeric($id)) {
            echo "FAIL | Invalid id parameter";
            return false;
        }
        
        $name = filter_input(INPUT_POST, 'name');
        $campaignIds = isset($_POST['campaignIds']) ? explode(',', $_POST['campaignIds']) : array();
        $partnerIds  = isset($_POST['partnerIds']) ? explode(',', $_POST['partnerIds']) : array();
        $revokeReportingAccess = isset($_POST['revoke_reporting_access']) ? strval($_POST['revoke_reporting_access']) : '0';
        $revokeCampaignControl = isset($_POST['revoke_campaign_control']) ? strval($_POST['revoke_campaign_control']) : '0';
        $revokePartnerControl = isset($_POST['revoke_partner_control']) ? strval($_POST['revoke_partner_control']) : '0';
        $revokeSuccessLeads = isset($_POST['revoke_success_leads']) ? strval($_POST['revoke_success_leads']) : '0';
        $revokeFailedLeads = isset($_POST['revoke_failed_leads']) ? strval($_POST['revoke_failed_leads']) : '0';
        $revokeSkippedLeads = isset($_POST['revoke_skipped_leads']) ? strval($_POST['revoke_skipped_leads']) : '0';

        $auth = Auth::getInstance();
        $userId = $auth->getUserId();
        $modelCampaign = SingletonRegistry::getModelCampaign();
        $modelPartner  = SingletonRegistry::getModelPartner();
        $campaigns = $modelCampaign->listAllWhere(array('user_id' => $userId));
        $partners  = $modelPartner->listAllWhere(array('user_id' => $userId, 'wrapped_campaign_id IS NULL'));
        
        
        $this->model->startTransaction();
        $this->model->deleteAllCampaignAndPartner($id);
        $this->model->insertCampaigns($id, $name, $campaigns, $campaignIds);
        $this->model->insertPartners($id, $name, $partners, $partnerIds);
        $this->model->updateSubAccountPermission($id, $revokeReportingAccess, $revokeCampaignControl, 
                $revokePartnerControl, $revokeSuccessLeads, $revokeFailedLeads, $revokeSkippedLeads);
        $this->model->commit();
        echo "SUCCESS";
    }
    //--------------------------------------------------------------------------
    
    
    public function preRun() {
        $this->pageTitle = 'Sub-Account';
        parent::preRun();
        App::getFrontController()->activeSection = FrontController::SECTION_SUB_ACCOUNT;
        $this->model = SingletonRegistry::getModelSubAccount();
        App::getFrontController()->extraCss[] = 'subaccount.css';
    }
    //--------------------------------------------------------------------------
    
    
    public function ajaxDeleteAction() {
        App::getFrontController()->setUsePageTemplate(false);

        $id = filter_input(INPUT_POST, 'id');
        if (!is_numeric($id)) {
            echo "FAIL | Invalid id parameter";
            return false;
        }
        $deletedRow = $this->model->getWhere(array('id' => $id));
        
        // delete from sub_accounts & sub_account_rights
        $this->model->delete($id);
        
        // delete from wordpress
        if (!empty($deletedRow['sub_account_id'])) {
            $userModel = SingletonRegistry::getSingleInstance('Model_User');
            $userModel->deleteUserFromPingtreeSystem($deletedRow['sub_account_id']);
            
            require_once(ABSPATH.'wp-admin/includes/user.php');
            wp_delete_user($deletedRow['sub_account_id']);
        }
        
        echo 'OK';
    }
}