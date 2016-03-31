<?php

class Controller_Campaigns extends Controller {

    /**
     * @var Model_Campaign
     */
    protected $model;

    /**
     * @var Model_CampaignField
     */
    protected $modelField;

    /**
     * @var Model_CampaignField
     */
    protected $modelPartnerField;

    /**
     * @desc Campaign we are working on, for all pages except indexAction and addAction
     * @var array
     */
    protected $campaign;

    /**
     * @desc How many campaigns will be shown per one page on campaigns listing
     */
    const PER_PAGE = 15;


    public function indexAction() {
        $auth = Auth::getInstance();

        SingletonRegistry::getModelPartner()->updateCampaignsWithExistingWrapperPartner($auth->getUserId());
        
        if ( ! SingletonRegistry::getModelCampaign()->checkExistMasterCampaign(Auth::getInstance()->getUserId())) {
            SingletonRegistry::getModelPartner()->removeUnassignedWrapperPartner(Auth::getInstance()->getUserId());
        }
        
        $this->makeSureAllWrapperPartnersHaveChildIpPartnerField($auth->getUserId());

        App::getFrontController()->extraCss[] = 'campaigns.css';

        $this->model or $this->model = SingletonRegistry::getModelCampaign();

        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        
        $subAccountCampaignIds = SingletonRegistry::getModelSubAccount()->listAllCampaignIds($auth->getUserId());
        $whereUserId = 'user_id = ' . $auth->getUserId();
        if (!empty($subAccountCampaignIds)) {
            $whereUserId = $whereUserId . ' OR `id` IN (' . implode(',', $subAccountCampaignIds) . ')';
        }
        
        if (isset($_GET['all'])) {
            $list = $this->model->listAllWhere(array($whereUserId), 'name');
            $this->out['perPage'] = sizeof($list) + 2;
        } else {
            $list = $this->model->listPageWhere(array($whereUserId),$page, self::PER_PAGE, 'name');
            $this->out['perPage'] = self::PER_PAGE;
        }
        
        $subAccountInfo = SingletonRegistry::getModelSubAccount()->checkIfUserIsSubAccount($auth->getUserId());
        if (!empty($subAccountInfo)) {
            $this->out['revokeCampaignControl'] = SingletonRegistry::getModelSubAccount()->checkIfRevokeCampaignControl($auth->getUserId());
        }
        
        $this->out['list']    = $list;
        $this->out['count']   = $this->model->countWhere(array($whereUserId));
        $this->out['isAdmin'] = $isAdmin = $auth->isAdmin();

        /* @var $mci Model_Industry */
        $mci = SingletonRegistry::getSingleInstance('Model_Industry');
        $this->out['industries'] = $mci->listAssoc();
        $industries = $this->listIndustryIds();

        /* @var $mct Model_CampaignTemplate */
        $mct = SingletonRegistry::getSingleInstance('Model_CampaignTemplate');

        $this->out['templatesAssoc']          = $templatesAssoc = $mct->listTemplateNamesAssoc();
        $this->out['templatesAssocInd']       = $isAdmin ? $templatesAssoc : $mct->listTemplateNamesAssocForIndustries($industries);
        $this->out['templateIndustriesAssoc'] = $mct->listTemplateIndustriesAssoc(array_keys($templatesAssoc));

        if ($auth->isAdmin()) {
            $this->out['usernames']            = SingletonRegistry::getModelUser()->listUsernamesAssoc();
            $this->out['campaignsPerUser']     = $this->model->listCampaigsAssocPerUser();
            $this->out['campaignsWithShadows'] = $this->model->listCampaignsWithShadows();
        }

        if ($auth->isAdminLoggedIn()) {
            $modelPartner = SingletonRegistry::getModelPartner();
            $allPartners  = $modelPartner->listPartnerNamesAssoc($auth->getUserId());
            $pingPartners = $modelPartner->listPingPartnerNames($auth->getUserId());
            $postPartners = $modelPartner->listPostOnlyPartnerNames($auth->getUserId());
            
            $this->out['partnersAssoc']     = $modelPartner->removePingPostSuffix($allPartners);
            $this->out['pingPartners']      = $modelPartner->removePingPostSuffix($pingPartners);
            $this->out['postOnlyPartners']  = $postPartners;
            
            $logInc = SingletonRegistry::getModelLogIncoming();

            // If impersonating we still need list of impersonated user's campaigns
            // since that is the list shown on the screen
            $cj = array();
            foreach ($logInc->listCatchupJobsForUser(Auth::getInstance()->getUserId()) as $row) {
                $cj[] = array((int)$row['campaign_id'], (int)$row['partner_id']);
            }
            $this->out['catchupJobs'] = $cj;
        }
    }
    //--------------------------------------------------------------------------


    public function addAction() {
        $subAccountInfo = SingletonRegistry::getModelSubAccount()->checkIfUserIsSubAccount(Auth::getInstance()->getUserId());
        if (!empty($subAccountInfo) && SingletonRegistry::getModelSubAccount()->checkIfRevokeCampaignControl(Auth::getInstance()->getUserId())) {
            throw new EAccessDenied('Access denied');
        }
        
        $this->pageTitle = 'Add Campaign';
        App::getFrontController()->extraCss[] = '2campaigns.css';

        $form = $this->form();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->filterPostForAdd();
            $form->importPostRequestAndThrowEDoneOnError();
            $this->validateFormAndThrowEDoneOnError($form, 0);

            $data = $form->getValues();
            
            if (empty($data['campaign_type'])) {
                // if campaign_type select is disable, then it is Ping+Post
                $data['campaign_type'] = Model_Campaign::CAMPAIGN_TYPE_PING_POST;
            }
            $data['is_active']          = $data['campaign_type'] != Model_Campaign::CAMPAIGN_TYPE_NOT_ACTIVE;
            $data['is_master']          = $data['campaign_type'] == Model_Campaign::CAMPAIGN_TYPE_MASTER;
            $data['is_pingpost']        = ($data['campaign_type'] == Model_Campaign::CAMPAIGN_TYPE_PING_POST) ? '1' : '0';
            $data['is_ping_and_post']   = ($data['campaign_type'] == Model_Campaign::CAMPAIGN_TYPE_PING_AND_POST) ? '1' : '0';
            $data['round_robin']        = ($data['distribution'] == Model_Campaign::DISTRIBUTION_ROUND_ROBIN) ? '1' : '0';
            $data['auction']            = ($data['distribution'] == Model_Campaign::DISTRIBUTION_AUCTION) ? '1' : '0';
            unset($data['campaign_type']);
            unset($data['distribution']);
            
            $this->filterDataForAdd($form, $data);
            $userId = Auth::getInstance()->getUserId();
            
            if (!empty($subAccountInfo)) {
                $userId = $subAccountInfo['user_id'];
            } 
            $data['user_id'] = $userId;

            $this->model or $this->model = SingletonRegistry::getModelCampaign();
            $campaignId = $this->model->insert($data);
            if ($campaignId) {

                if (!empty($subAccountInfo)) {
                    $subAccountRightData = array(
                        'sub_account_id' => Auth::getInstance()->getUserId(),
                        'sub_account_username' => $subAccountInfo['sub_account_username'],
                        'object_id' => $campaignId,
                        'object_name' => $data['name'],
                        'object_type' => Model_SubAccount::OBJECT_TYPE_CAMPAIGN
                    );
                    
                    SingletonRegistry::getModelSubAccount()->insertRecord($subAccountRightData);
                }
                
                if ($data['is_master'] == 1) {
                    SingletonRegistry::getModelPartner()->createWrapperAroundCampaign($campaignId);
                }
                
                $this->modelField = SingletonRegistry::getModelCampaignField();
                if ($data['is_ping_and_post']) {
                    $this->modelField->insertLeadIdForPingAndPostCampaign($campaignId);
                }

                SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Campaign added');
                $this->smartRedirect(array('fields', 'delivery'), $campaignId, false);
            } else {
                throw new ERequestError("There was an error adding your campaign");
            }
        } else {
            $form->leads_per       = 1;
            $form->repost_hours    = 1;
            $form->repost_max      = 1;
            $form->partner_retries = 2;
            $form->throttle_value  = 0;
            $form->oob_cap_value   = 0;
        }
    }
    //--------------------------------------------------------------------------


    public function editAction() {
        $id = 0;
        $data = array();
        $this->getCampaignFromGetId($id, $data);
        if ($data['shadow_of']) {
            App::getFrontController()->redirectToCP($this, array('action' => 'shadowSettings'), array('id' => $id));
        }

        $this->pageTitle = 'Update Campaign';
        App::getFrontController()->extraCss[] = '2campaigns.css';

        // For information where user should submit his form, displayed at top of edit page
        $this->out['hash'] = $this->model->getCampaignHashCode($data['user_id'], $id);
        $this->out['hasDeliveryPartner'] = SingletonRegistry::getModelCampaign()->hasDeliveryPartner($id);

        $form = $this->form();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->filterPostForEdit();
            $form->importPostRequestAndThrowEDoneOnError();
            $this->validateFormAndThrowEDoneOnError($form, $id);

            $newData = $form->getValues();
            
            $newData['is_active']       = $newData['campaign_type'] != Model_Campaign::CAMPAIGN_TYPE_NOT_ACTIVE;
            $newData['is_master']       = $newData['campaign_type'] == Model_Campaign::CAMPAIGN_TYPE_MASTER;
            
            // if campaign is inactive, we keep is_pingpost setting.
            if ($newData['is_active']) {
                $newData['is_pingpost']     = ($newData['campaign_type'] == Model_Campaign::CAMPAIGN_TYPE_PING_POST) ? '1' : '0';
                $newData['is_ping_and_post']= ($newData['campaign_type'] == Model_Campaign::CAMPAIGN_TYPE_PING_AND_POST) ? '1' : '0';
            }
            
            $newData['round_robin']     = ($newData['distribution'] == Model_Campaign::DISTRIBUTION_ROUND_ROBIN) ? '1' : '0';
            $newData['auction']         = ($newData['distribution'] == Model_Campaign::DISTRIBUTION_AUCTION) ? '1' : '0';
            unset($newData['campaign_type']);
            unset($newData['distribution']);

            $this->filterDataForEdit($form, $data, $newData);

            if (!empty($newData['delivery_type']) && intval($newData['delivery_type']) === Model_Campaign::DELIVERY_TYPE_QUEUED_KEY && ($data['delivery_type'] != $newData['delivery_type'])) {
                $newData['oob_cutoff_time'] = date('Y-m-d H:i:s');
            }
            
            $this->model->updateDiff($id, $newData, $data);
            if (DB::$db->affectedRows()) {
                SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Campaign updated');
            }
            
            $this->modelField = SingletonRegistry::getModelCampaignField();
            if ($data['is_ping_and_post'] && $newData['is_active'] && empty($newData['is_ping_and_post'])) {
                $this->modelField->deleteLeadIdOfPingAndPostCampaign($id);
            } else if (empty($data['is_ping_and_post']) && !empty($newData['is_ping_and_post'])) {
                $this->modelField->insertLeadIdForPingAndPostCampaign($id);
            }
            
            $this->smartRedirect(array('fields', 'delivery', 'filters'), $id);
        } else {
            $form->importFromArray($data, false);
            $form->campaign_type = ($data['is_active'] == 0) ? 0 : ($data['is_master'] ? 2 : ($data['is_pingpost'] ? 3 : ($data['is_ping_and_post'] ? 4 : 1)));
            $form->distribution = ($data['round_robin']) ? 1 : (($data['auction']) ? 2 : 0);
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Modifications done on $_POST before we import it into the $form
     */
    private function filterPostForAdd() {

        //   Form will expect the succcess_url and failure_url either to be empty
        // or to contain valid URLs. There is javascript in the page to make sure
        // there's http:// in front of these two, but sometimes it won't be triggered
        //, ie if user hits Enter while in success_url field.
        //   So we need to check here before the $form imports the data and add http://
        // If necessary
        if (!empty($_POST['success_url']) && (strpos($_POST['success_url'], '://') === false)) {
            $_POST['success_url'] = 'http://' . $_POST['success_url'];
        }
        if (!empty($_POST['failure_url']) && (strpos($_POST['failure_url'], '://') === false)) {
            $_POST['failure_url'] = 'http://' . $_POST['failure_url'];
        }

        
        if ($_POST['response_type'] == Model_Campaign::RESPONSE_TYPE_API_KEY) {
            $_POST['success_url'] = '';
            $_POST['failure_url'] = '';
        }
        
        if ($_POST['campaign_type'] == Model_Campaign::CAMPAIGN_TYPE_PING_AND_POST) {
            $_POST['distribution'] = Model_Campaign::DISTRIBUTION_AUCTION;
        }

    }
    //--------------------------------------------------------------------------


    /**
     * @desc $data is assoc array that will be written to DB as new campaign. We
     * check that the data makes, put default values if they are missing etc.
     */
    private function filterDataForAdd(Form_Data $form, array & $data) {
        // Make sure oob_cap_type is numeric - if not set at all we put in zero
        if (!isset($data['oob_cap_type'])) {
            $data['oob_cap_type'] = 0;
        }

        //   Field for throttle_type is a dropdown that will be disabled by javascript when
        // user enters zero in throttle_value. Disabled field will not send anything in
        // POST data. Here we put 0 for throttle_type in case value is also 0 to prevent DB
        // errors
        if ($data['throttle_value'] == 0) {
            $data['throttle_type'] = 0;
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @desc This function will modify $_POST array before we import it into the form. The
     * data will then be used to do updateDiff so we unset the fields we don't need.
     */
    private function filterPostForEdit() {

        // Make sure success_url and failure_url have http:// prefix if they are entered.
        // Look at filterPostForAdd() for explanation why
        if (!empty($_POST['success_url']) && (strpos($_POST['success_url'], '://') === false)) {
            $_POST['success_url'] = 'http://' . $_POST['success_url'];
        }
        if (!empty($_POST['failure_url']) && (strpos($_POST['failure_url'], '://') === false)) {
            $_POST['failure_url'] = 'http://' . $_POST['failure_url'];
        }

        // if it is not OOB campagin then we don't need throttle type and value
        if (!empty($_POST['delivery_type']) && intval($_POST['delivery_type']) === Model_Campaign::DELIVERY_TYPE_LIVE_KEY) {
            unset($_POST['throttle_type']);
            unset($_POST['throttle_value']);
        }
        
        if ($_POST['response_type'] == Model_Campaign::RESPONSE_TYPE_API_KEY) {
            $_POST['success_url'] = '';
            $_POST['failure_url'] = '';
        }
        
        if ($_POST['campaign_type'] == Model_Campaign::CAMPAIGN_TYPE_PING_AND_POST) {
            $_POST['distribution'] = Model_Campaign::DISTRIBUTION_AUCTION;
        }
        
        if (!Auth::getInstance()->isAdminLoggedIn()) {
            unset($_POST['industry_id']);
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Will be called before updating the DB row with difference between $newData and $data.
     * If we unset a key in both $data and $newData it will not be included in SQL update statement.
     */
    private function filterDataForEdit(Form_Data $form, array & $data, array & $newData) {

        // Make sure this field is in enum 1 or 0 format since from the form we get a boolean
        $newData['append_succ_url'] = $newData['append_succ_url'] ? '1' : '0';

        // Not reposting a campaign, no need for reposting-related settings
        if (empty($newData['should_repost'])) {
            unset($newData['repost_hours']);
            unset($newData['repost_max']);
        }

        // OOB campaign
        if (intval($form->delivery_type) === Model_Campaign::DELIVERY_TYPE_QUEUED_KEY) {
            // And if throttle_value is zero, make no changes to throttle_type
            if ($newData['throttle_value'] == 0) {
                unset($data['throttle_type']);
                unset($newData['throttle_type']);
            }
        }

        if ($newData['oob_cap_value'] == '0') {
            $newData['is_oob_capped'] = '0';
            $newData['oob_cap_type']  = '0';
        } else {
            $newData['is_oob_capped'] = '1';
        }

        // Convert from form's true / false to DB's enum 1 or 0 - has to be a string
        $newData['capalp'] = $newData['capalp'] ? '1' : '0';
    }
    //--------------------------------------------------------------------------


    public function shadowSettingsAction() {
        $id = 0; $data = array();
        $this->getCampaignFromGetId($id, $data);
        if (!$data['shadow_of']) {
            App::getFrontController()->redirectToCP($this, array('action' => 'edit'), array('id' => $id));
        }

        $this->pageTitle = 'Update Shadow Campaign Settings';
        App::getFrontController()->extraCss[] = '2campaigns.css';

        $sourceCampaign = $this->model->get($data['shadow_of']);
        $this->out['sourceCampaign'] = $sourceCampaign;

        $form = $this->shadowForm();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $form->importPostRequestAndThrowEDoneOnError();
            $this->validateFormAndThrowEDoneOnError($form, $id, true);

            $newData = $form->getValues();
            $this->filterDataForShadow($form, $data, $newData);

            $this->model->updateDiff($id, $newData, $data);
            if (DB::$db->affectedRows()) {
                SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Campaign updated');
            }
            $this->smartRedirect(array('fields', 'delivery', 'filters'), $id);
        } else {
            $form->importFromArray($data, false);
            $form->shadow_type = ($data['skim_percentage'] == 0) ? 'parasite' : 'skim';
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @desc This is called when user is editing a shadow campaign, after importing data from $form
     * and before that data is pushed to DB via updateDiff($id, $newData, $data);
     */
    private function filterDataForShadow(Form_Data $form, array & $data, array & $newData) {

        // If campaign does not repost, do not change repost_hours and repost_max fields
        if (empty($newData['should_repost'])) {
            unset($newData['repost_hours']);
            unset($newData['repost_max']);
        }

        // If throttle_value is zero that means that throttle_type field is disabled
        // and won't be present in form data. We unset throttle_type in both old and
        // new data so updateDiff won't include it in SQL for UPDATE
        if ($newData['throttle_value'] == 0) {
            unset($data['throttle_type']);
            unset($newData['throttle_type']);
        }

        // There's no field shadow_type in DB, but there's one in the form. $newData came from the form,
        // we need to unset the index here so it won't get picked up by updateDiff later
        unset($newData['shadow_type']);

        // All shadow campaigns are marked as OOB.
        // TODO Boban - check if this makes sense for skim shadows
        $newData['delivery_type'] = strval(Model_Campaign::DELIVERY_TYPE_QUEUED_KEY);
        $newData['response_type'] = strval(Model_Campaign::RESPONSE_TYPE_API_KEY);

        // skim type is actually kept in DB as value of skim_percentage field.
        // zero - parasite, non-zero - skim by that percentage.
        if ($form->shadow_type == 'skim') {
            $newData['skim_percentage'] = $form->skim_percentage;
        } else {
            $newData['skim_percentage'] = 0;
        }
    }
    //--------------------------------------------------------------------------


    public function apiDetailsAction() {
        $id = 0; $data = array();
        $this->getCampaignFromGetId($id, $data);
        App::getFrontController()->setUsePageTemplate(false);

        $this->modelField = SingletonRegistry::getModelCampaignField();
        $fields = $this->modelField->listAllForCampaign($data['shadow_of'] ? (int)$data['shadow_of'] : $id);
        $emailField = array(
            'name' => $data['email_field_name'],
            'field_type' => Model_CampaignField::FIELD_TYPE_EMAIL,
            'description' => $data['email_field_description'],
            'is_mandatory' => $data['email_field_mandatory']
        );
        array_unshift($fields, $emailField);
        
        usort($fields, array('Model_CampaignField', 'sortFields'));

        $this->out['fieldTypes'] = $this->modelField->listFieldTypesAssocFull();

        // campaign rules data
        $this->modelFilter = SingletonRegistry::getModelCampaignFilter();
        $campaignFilters = $this->modelFilter->listActiveFiltersForCampaign($data['shadow_of'] ? (int)$data['shadow_of'] : $id);
        $filterMatches = $this->modelFilter->listFilterMatches();
        
        // integrate campaign rule into Requirements of campaign fields
        foreach ($fields as $index => $field) {
            $fields[$index]['campaign_rule']  = '';
            
            foreach ($campaignFilters as $filter) {
                if ($field['field_type'] == $filter['field_type_id']) {
                    $fields[$index]['campaign_rule'] .= $filterMatches[-$filter['match_type']] . ' ' . $filter['match_value'] . ', ';
                }
            }
            
            if (!empty($fields[$index]['campaign_rule'])) {
                $fields[$index]['campaign_rule'] = substr($fields[$index]['campaign_rule'], 0, -2);
            }
        }
        
        $this->out['fields'] = $fields;
        if ($data['is_ping_and_post']) {
            $this->out['isPingAndPost'] = true;
        }
        
        if ($data['is_oob']) {
            $s = $_SERVER['HTTP_HOST'];
            if (strpos($s, 'www.') === 0) {
                $s = 'api.' . substr($s, 4);
            } else {
                $s = "api.$s";
            }
            $domain = $s;
        } else {
            $domain = escapeJSVal($_SERVER['HTTP_HOST']);
        }
        $this->out['submitUrl'] = "http://$domain/api/" . $this->model->getCampaignHashCode($data['user_id'], $data['id']) . '/';
    }
    //--------------------------------------------------------------------------


    public function fieldsAction() {
        $id = 0; $data = array();
        $this->getCampaignFromGetId($id, $data);

        $this->pageTitle = 'Campaign Fields';
        App::getFrontController()->extraCss[] = 'fieldcampaings.css';

        $this->modelField = SingletonRegistry::getModelCampaignField();

        $impersonating  = Auth::getInstance()->isAdminLoggedIn() && !Auth::getInstance()->isAdmin();
        $cvFTypes       = $this->getCanValidateFieldTypes($data, $impersonating);

        $campaignFields = $this->modelField->listAllForCampaign($data['shadow_of'] ? (int)$data['shadow_of'] : $id);
        $fieldTypes     = $this->getFieldTypes($data, $campaignFields);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!$data['shadow_of']) {
                $this->updateCampaignFields($id, $data, $campaignFields, $fieldTypes, $cvFTypes, $impersonating);
            } else {
                $this->copySourceEmailValidationSettingsIntoShadowCampaign();
            }
            $this->smartRedirect(array('edit', 'delivery', 'filters'), $id);
        }
    }
    //--------------------------------------------------------------------------
    
    
    public function filtersAction() {
        $id = 0; $data = array();
        $this->getCampaignFromGetId($id, $data);

        $this->pageTitle = 'Campaign Rules';
        App::getFrontController()->extraCss[] = 'fieldcampaings.css';

        $this->modelFilter = SingletonRegistry::getModelCampaignFilter();
        $this->modelField = SingletonRegistry::getModelCampaignField();
        
        $campaignFilters = $this->modelFilter->listAllForCampaign($data['shadow_of'] ? (int)$data['shadow_of'] : $id);
        $campaignFields = $this->modelField->listAllForCampaign($data['shadow_of'] ? (int)$data['shadow_of'] : $id);
        
        $fieldTypes     = $this->getFieldTypes($data, $campaignFields, true);
        
        $this->out['filters'] = $campaignFilters;
        $this->out['filterActions'] = $this->modelFilter->listFilterActions();
        $this->out['filterMatches'] = $this->modelFilter->listFilterMatches();
        
        // system fields
        $this->out['systemFields']  = Controller_Partners::listSystemFieldsForDisplay($data);
        
        // external tokens
        $extTokensAssoc = ExternalLookup_Registry::listTokens2Names();
        $extTokens      = array_keys($extTokensAssoc);
        sort($extTokens);
        $this->out['extTokens']       = $extTokens;
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->updateCampaignFilters($id, $data, $campaignFilters, $fieldTypes);
            $this->smartRedirect(array('edit', 'delivery', 'fields'), $id);
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @desc This function will return field type IDs (from field_types.id field in DB)
     * for field types that this user can validate via external APIs. If impersonating
     * we need to be able to mark validation even for users who are lower level, so
     * if impersonating this function returns all ftypes that can be validated.
     *
     * <p>Also sets output variables for view file.
     */
    private function getCanValidateFieldTypes(array $data, $impersonating) {
        $modelUser        = SingletonRegistry::getModelUser();
        $caps             = $impersonating ? array('administrator') : $modelUser->listUserCaps(Auth::getInstance()->getUserId());
        $cvFTypes         = $modelUser->canValidateFieldTypes($caps);

        $this->out['canVerifyViaAPI'] = !empty($cvFTypes);
        $this->out['cvFTypes']        = $cvFTypes;
        $this->out['impersonating']   = $impersonating;
        if ($data['shadow_of']) {
            $this->out['shadow']          = $this->model->get($data['shadow_of']);
            $this->out['shadowOverrides'] = $this->modelField->listValidationOverrides($data['id']);
        } else {
            $this->out['shadow']          = array();
            $this->out['shadowOverrides'] = array();
        }
        return $cvFTypes;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Gets assoc array of field_type_id => field_type_name, depending on user and
     * campaign industry settings and whether the field is already present in the campaign.
     *
     * <p>Also sets output variables $this->out['fieldTypes'] and $this->out['fields']
     */
    private function getFieldTypes(array $data, array $campaignFields, $filterFlag = null) {
        $this->modelField or $this->modelField = SingletonRegistry::getModelCampaignField();

        $seenFTIds  = array();
        foreach ($campaignFields as $row) {
            if (isset($row['field_type'])) {
                $seenFTIds[] = (int)$row['field_type'];
            } else if (isset($row['field_type_id'])) {
                $seenFTIds[] = (int)$row['field_type_id'];
            }
            
        }

        $fieldTypes = $this->modelField->listFieldTypesAssoc();
        $industryId = $this->getIndustryIdForCampaign($data);

        /* @var $mft Model_FieldType */
        $mft        = SingletonRegistry::getSingleInstance('Model_FieldType');
        foreach ($mft->listAssoc('id') as $ftId => $row) {
            if (empty($row['industries'])) {
                continue;
            }
            if (!in_array($industryId, $row['industries']) && !in_array($ftId, $seenFTIds)) {
                unset($fieldTypes[$ftId]);
            }
        }
        
        if ($data['is_ping_and_post'] == '0') {
            unset($fieldTypes[Model_CampaignField::FIELD_TYPE_LEAD_ID]);
        }
            
        if (isset($filterFlag)) {
            $availableFieldTypes = array(
                Model_CampaignField::FIELD_TYPE_EMAIL => 'Email'
            );
            
            foreach ($campaignFields as $field) {
                $fieldTypeId = $field['field_type'];
                $availableFieldTypes[$fieldTypeId] = $fieldTypes[$fieldTypeId];
            }
            $this->out['fieldTypes'] = $availableFieldTypes;
        } else {
            $this->out['fieldTypes'] = $fieldTypes;
        }
        
        $this->out['fields']     = $campaignFields;

        return $fieldTypes;
    }
    //--------------------------------------------------------------------------

    
    private function updateCampaignFields($id, array $data, array & $campaignFields, array $fieldTypes, array $cvFTypes, $impersonating) {
        $this->processEmailFieldForPingAndPost($data);
        
        $new = $this->normalizeFieldPostValues('new', $id, $data, $fieldTypes, $cvFTypes, $impersonating);
        $old = $this->normalizeFieldPostValues('old', $id, $data, $fieldTypes, $cvFTypes, $impersonating);
        
        $serialized = serialize($campaignFields);
        $origFields = $campaignFields;

        $names = array();
        $ftypes = array();
        foreach ($new as $row) {
            $row['name'] = trim($row['name']);
            if (in_array(strtolower($row['name']), $names)) {
                throw new ERequestError("Cannot have two fields with same name ({$row['name']})");
            }
            $ftypes[] = $row['field_type'];
            $names[]  = $row['name'];
        }
        foreach ($old as $fid => $row) {
            $row['name'] = trim($row['name']);
            if (in_array(strtolower($row['name']), $names)) {
                throw new ERequestError("Cannot have two fields with same name ({$row['name']})");
            }
            $ftypes[] = $row['field_type'];
            $names[]  = $row['name'];
            if (isset($origFields[$fid]) && ($origFields[$fid]['validate'] == '2') && !$impersonating && !Auth::getInstance()->isAdmin()) {
                $old[$fid]['validate'] = '2';
            }
        }
        if (sizeof(array_unique($ftypes)) != sizeof($ftypes)) {
            throw new ERequestError("Cannot have two fields with same fied type");
        }

        foreach ($old as $fid => $requestData) {
            if (!isset($campaignFields[$fid])) {
                $new[] = $requestData;
                unset($old[$fid]);
                continue;
            }
            unset($campaignFields[$fid]);
        }

        //######################################################################
        // START TRANSACTION
        //######################################################################
        $this->modelField->startTransaction();
        $this->modelField->deleteWhere(array('campaign_id' => (int)$id));
        foreach ($old as $row) {
            $this->modelField->insert($row);
        }
        foreach ($new as $row) {
            unset($row['id']);
            $this->modelField->insert($row);
        }

        if (!$impersonating && !Auth::getInstance()->isAdmin() && $data['email_field_verify'] == '2') {
            $emailValidate = '2';
        } else {
            $emailValidate = !empty($_POST['span-verify-checkbox_email']) ? ($impersonating ? '2' : '1') : '';
        }
        $cdata = array(
                'email_field_name'        => isset($_POST['email_field_name'])        ? trim($_POST['email_field_name'])        : '',
                'email_field_description' => isset($_POST['email_field_description']) ? trim($_POST['email_field_description']) : '',
                'email_field_mandatory'   => isset($_POST['email_field_mandatory'])   ? trim($_POST['email_field_mandatory'])   : '0',
                'email_field_verify'      => $emailValidate,
        );
        $this->model->updateDiff($id, $cdata, $data);
        $this->modelField->commit();
        //######################################################################
        // COMMIT
        //######################################################################

        $different = ($cdata['email_field_name']        != $data['email_field_name']) || 
                     ($cdata['email_field_description'] != $data['email_field_description']) ||
                     ($cdata['email_field_mandatory']   != $data['email_field_mandatory']) ;
        if ($different || ($serialized != serialize($this->modelField->listAllForCampaign($id)))) {
            SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Campaign fields updated');
            $this->model->deactivateShadowCampaigns($id);
            $this->model->updateShadowCampaignsEmailFieldSettings($id, $cdata['email_field_name'], $cdata['email_field_description'], $cdata['email_field_mandatory']);
        }
    }
    //--------------------------------------------------------------------------
    
    
    private function updateCampaignFilters($id, array $data, array $campaignFilters, array $fieldTypes) {
        
        if ($data['shadow_of']) {
            return false;
        }
        
        $new = $this->normalizeFilterPostValues('new', $id, $data, $fieldTypes);
        $old = $this->normalizeFilterPostValues('old', $id, $data, $fieldTypes);  
        
        $serialized = serialize($campaignFilters);

        foreach ($old as $fid => $requestData) {
            if (!isset($campaignFilters[$fid])) {
                $new[] = $requestData;
                unset($old[$fid]);
                continue;
            }
            unset($campaignFilters[$fid]);
        }

        //######################################################################
        // START TRANSACTION
        //######################################################################
        $this->modelFilter->startTransaction();
        $this->modelFilter->deleteWhere(array('campaign_id' => (int)$id));
        foreach ($old as $row) {
            $this->modelFilter->insert($row);
        }
        foreach ($new as $row) {
            unset($row['id']);
            $this->modelFilter->insert($row);
        }
        
        $this->modelFilter->commit();
        //######################################################################
        // COMMIT
        //######################################################################

        if ($serialized != serialize($this->modelFilter->listAllForCampaign($id))) {
            SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Campaign rules updated');
        }
    }
    //--------------------------------------------------------------------------
    
    private function processEmailFieldForPingAndPost($campaignData) {
        if ($campaignData['is_ping_and_post']) {

                if (!empty($_POST['email_field_ping_mandatory']) && !empty($_POST['email_field_post_mandatory'])) {
                    $_POST['email_field_mandatory'] = Model_CampaignField::MANDATORY_BOTH;
                } else if (!empty($_POST['email_field_ping_mandatory'])) {
                    $_POST['email_field_mandatory'] = Model_CampaignField::MANDATORY_PING;
                } else if (!empty($_POST['email_field_post_mandatory'])) {
                    $_POST['email_field_mandatory'] = Model_CampaignField::MANDATORY_POST;
                } else {
                    $_POST['email_field_mandatory'] = Model_CampaignField::MANDATORY_NONE;
                }
            }
    }
    //--------------------------------------------------------------------------
    

    private function copySourceEmailValidationSettingsIntoShadowCampaign() {
        $ssourceCampaignId = (int)$this->campaign['shadow_of'];
        if (!$ssourceCampaignId) {
            return;
        }

        $sourceCampaign = $this->model->get($ssourceCampaignId, MYSQL_ASSOC);

        $this->data['email_field_verify'] = $sourceCampaign['email_field_verify'];
    }
    //--------------------------------------------------------------------------


    public function deliveryAction() {
        $id = 0; $data = array();
        $this->getCampaignFromGetId($id, $data);
        
        SingletonRegistry::getModelPartner()->updateCampaignsWithExistingWrapperPartner(Auth::getInstance()->getUserId());
        
        if (SingletonRegistry::getModelCampaign()->checkExistMasterCampaign(Auth::getInstance()->getUserId())) {
            SingletonRegistry::getModelPartner()->makeSureAllCampaignsHaveWrapperPartners(Auth::getInstance()->getUserId());
        }

        $this->makeSureAllWrapperPartnersHaveChildIpPartnerField(Auth::getInstance()->getUserId());

        $this->pageTitle = 'Campaign Delivery';
        App::getFrontController()->extraCss[] = '2campaigns.css';

        $modelPartner  = SingletonRegistry::getModelPartner();
        $subAccountPartnerIds = SingletonRegistry::getModelSubAccount()->listAllPartnerIds(Auth::getInstance()->getUserId());
        
        if ($data['is_pingpost'] || $data['is_ping_and_post']) {
            $this->out['getPingPostPartner'] = true;
            $partnerNames  = $modelPartner->listPingPartnerNames(Auth::getInstance()->getUserId(), $subAccountPartnerIds);
        } else {
            $this->out['getPingPostPartner'] = false;
            $partnerNames  = $modelPartner->listPostOnlyPartnerNames(Auth::getInstance()->getUserId(), $subAccountPartnerIds);
        }
        
        $wrappedCNames = $modelPartner->listWrappedCampaigns(Auth::getInstance()->getUserId());
        $list          = $this->model->listDeliverySettings($id);
        $serialized    = serialize($list);

        $sourceCampaignId = $data['shadow_of'] ? (int)$data['shadow_of'] : $data['id'];
        $this->out['partnerMissingFields'] = array();

        $this->attachOffendingFieldData($list, $sourceCampaignId, $partnerNames, $wrappedCNames);

        $this->out['partnerNames'] = $modelPartner->removePingPostSuffix($partnerNames);
        $this->out['wrapperNames'] = $wrappedCNames;
        $this->out['list']         = $list;
        $this->jsPrepend = array('tableDnD');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $deliveryData = array();
            if (isset($_POST['partner_id'], $_POST['always_send']) && is_array($_POST['partner_id']) && is_array($_POST['always_send'])) {
                foreach ($_POST['partner_id'] as $index => $pid) {
                    if (!isset($partnerNames[$pid]) && !isset($wrappedCNames[$pid])) {
                        continue;
                    }
                    $deliveryData[] = array('partner_id' => $pid, 'always_send' => empty($_POST['always_send'][$index]) ? '0' : '1');
                }
            }
            $this->model->insertDeliverySettings($id, $deliveryData);
            if ($serialized != serialize($this->model->listDeliverySettings($id))) {
                SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Campaign delivery settings saved');
            }
            $this->smartRedirect(array('fields', 'edit', 'filters'), $id);
        }
    }
    //--------------------------------------------------------------------------


    private function attachOffendingFieldData(array & $list, $sourceCampaignId, array $partnerNames, array $wrappedCNames) {
        $this->modelField or $this->modelField = SingletonRegistry::getModelCampaignField();
        $ftypesAssoc = $this->modelField->listFieldTypesAssoc();
        $myFields = array(Model_CampaignField::FIELD_TYPE_EMAIL);
        foreach ($this->modelField->listAllForCampaign($sourceCampaignId) as $row) {
            $myFields[] = (int)$row['field_type'];
        }
        $partnerIds = array();
        foreach ($list as & $row) {
            $partnerIds[] = (int)$row['partner_id'];
        }
        $mp = SingletonRegistry::getModelPartnerField();
        $partnerFields = $mp->listAssocForPartnerGroup($partnerIds);
        foreach ($list as & $row) {
            $pid = (int)$row['partner_id'];
            $fields = isset($partnerFields[$pid]) ? $partnerFields[$pid] : array();
            $error = '';
            $offendingFields = $this->listOffendingFields($fields, $ftypesAssoc, $myFields, $pid, $error);

            $row['offending_fields'] = implode(', ', $offendingFields);
            $row['error'] = $error;
        }
        unset($row);
        if (!$partnerNames) {
            $this->out['partnerMissingFields'] = array();
            return;
        }
        $partnerFields = $mp->listAssocForPartnerGroup(array_keys($partnerNames));
        $partnerMissingFields = array();
        foreach ($partnerNames as $pid => $pname) {
            $fields = isset($partnerFields[$pid]) ? $partnerFields[$pid] : array();
            $error = '';
            $offendingFields = $this->listOffendingFields($fields, $ftypesAssoc, $myFields, $pid, $error);
            if ($offendingFields) {
                $partnerMissingFields[$pid] = implode(', ', $offendingFields);
            }
        }
        $wrapperFields = $mp->listAssocForPartnerGroup(array_keys($wrappedCNames));
        foreach ($wrappedCNames as $pid => $pname) {
            $fields = isset($wrapperFields[$pid]) ? $wrapperFields[$pid] : array();
            $error = '';
            $offendingFields = $this->listOffendingFields($fields, $ftypesAssoc, $myFields, $pid, $error);
            if ($offendingFields) {
                $partnerMissingFields[$pid] = implode(', ', $offendingFields);
            }
        }
        $this->out['partnerMissingFields'] = $partnerMissingFields;
    }
    //--------------------------------------------------------------------------


    private function listOffendingFields(array $fields, array $ftypesAssoc, array $myFields, $pid, & $isError) {
        $isError = false;
        $offendingFields = array();
        foreach ($fields as $arr) {
            preg_match_all('/\\[([\\w]{1,})\\]/', $arr['value'], $matches);
            foreach ($matches[1] as $ftName) {
                $ft = array_search($ftName, $ftypesAssoc);
                if ($ft === false) {
                    $offendingFields[] = $ftName;
                    $isError = true;
                    break;
                }
                if (!in_array($ft, $myFields)) {
                    if (!empty($arr['modifier'])) {
                        $mLines = explode("\n", $arr['modifier']);
                        foreach ($mLines as & $s) {
                            $s = str_replace("\r", '', $s);
                            $s = str_replace("\n", '', $s);
                        }
                        if (sizeof($mLines >= 5)) {
                            if (($mLines[0] == 'dlg-modifier-replacements') && ($mLines[1] == $ftName)) {
                                for($i = 2; $i < sizeof($mLines); $i += 3) {
                                    if (($mLines[$i] == Model_PartnerFilter::FILTER_MATCH_EQUALS) && ($mLines[$i + 1] == '')) {
                                        // replacement rule for empty string
                                        continue 2;
                                    }
                                }
                            }
                        }
                    }
                    $offendingFields[] = $ftName;
                    $isError = true;
                    break;
                }
            }
        }
        return $offendingFields;
    }
    //--------------------------------------------------------------------------


    public function deleteAction() {
        $id = 0; $data = array();
        $this->getCampaignFromGetId($id, $data);
        $this->pageTitle = 'Delete Campaign';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->model->markAsDeleted($id);
            SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Campaign deleted');
            App::getFrontController()->redirectToCP($this);
        }
    }
    //--------------------------------------------------------------------------


    public function ajaxCopyCampaignAction() {
        App::getFrontController()->setUsePageTemplate(false);

        try {
            $_GET['id'] = isset($_POST['id']) ? $_POST['id'] : 0;
            $id = 0; $data = array();
            $this->getCampaignFromGetId($id, $data);
            $this->model->copyCampaign($id, trim($_POST['new_name']), !empty($_POST['fields']), !empty($_POST['delivery']), !empty($_POST['rules']));
            SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Campaign copied');
            die('OK');
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    //--------------------------------------------------------------------------


    public function ajaxNewFromTemplateAction() {
        App::getFrontController()->setUsePageTemplate(false);

        try {
            $id = isset($_POST['id']) ? max(0, (int)$_POST['id']) : 0;
            /* @var $mct Model_CampaignTemplate */
            $mct = SingletonRegistry::getSingleInstance('Model_CampaignTemplate');
            $templatesAssoc = $mct->listTemplateNamesAssoc();

            if (!isset($templatesAssoc[$id])) {
                throw new Exception('Invalid id ' . $id);
            }

            $this->model or $this->model = SingletonRegistry::getModelCampaign();
            if (!$this->model->get($id)) {
                throw new Exception('Invalid id ' . $id);
            }

            $this->model->copyCampaign($id, trim($_POST['new_name']), !empty($_POST['fields']), false, !empty($_POST['rules']), Auth::getInstance()->getUserId());
            SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Created new campaign from template');
            throw new Exception('OK');
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    //--------------------------------------------------------------------------


    public function ajaxMarkCampaignAsTemplateAction() {
        App::getFrontController()->setUsePageTemplate(false);

        try {
            $auth = Auth::getInstance();
            if (!$auth->isAdmin()) {
                throw new Exception('Only admin can do this action');
            }
            $id = isset($_POST['id']) ? max(0, (int)$_POST['id']) : 0;

            $this->model or $this->model = SingletonRegistry::getModelCampaign();
            if (!$this->model->get($id)) {
                throw new Exception('Invalid id ' . $id);
            }
            $industries = array();
            if (isset($_POST['industries']) && is_array($_POST['industries'])) {
                foreach ($_POST['industries'] as $s) {
                    $industries[] = (int)$s;
                }
            }

            /* @var $mct Model_CampaignTemplate */
            $mct  = SingletonRegistry::getSingleInstance('Model_CampaignTemplate');
            SingletonRegistry::getModelNotification()->add($auth->getUserId(), 'Campaign marked as template');
            $mct->markAsTemplate($id, (string)$_POST['name'], $industries);

            die('OK');
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    //--------------------------------------------------------------------------


    public function ajaxDeleteCampaignTemplateAction() {
        App::getFrontController()->setUsePageTemplate(false);

        try {
            $auth = Auth::getInstance();
            if (!$auth->isAdmin()) {
                throw new Exception('Only admin can do this action');
            }
            $id = isset($_POST['id']) ? max(0, (int)$_POST['id']) : 0;

            $this->model or $this->model = SingletonRegistry::getModelCampaign();
            if (!$this->model->get($id)) {
                throw new Exception('Invalid id ' . $id);
            }

            /* @var $mct Model_CampaignTemplate */
            $mct  = SingletonRegistry::getSingleInstance('Model_CampaignTemplate');
            $mct->removeTemplate($id);
            SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Campaign unmarked as template');

            die('OK');
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    //--------------------------------------------------------------------------


    public function ajaxUpdateCampaignTemplateAction() {
        App::getFrontController()->setUsePageTemplate(false);

        try {
            $auth = Auth::getInstance();
            if (!$auth->isAdmin()) {
                throw new Exception('Only admin can do this action');
            }
            $id = isset($_POST['id']) ? max(0, (int)$_POST['id']) : 0;

            $this->model or $this->model = SingletonRegistry::getModelCampaign();
            if (!$this->model->get($id)) {
                throw new Exception('Invalid id ' . $id);
            }
            $industries = array();
            if (isset($_POST['industries']) && is_array($_POST['industries'])) {
                foreach ($_POST['industries'] as $s) {
                    $industries[] = (int)$s;
                }
            }

            /* @var $mct Model_CampaignTemplate */
            $mct  = SingletonRegistry::getSingleInstance('Model_CampaignTemplate');
            $mct->renameTemplate($id, (string)$_POST['name'], $industries);
            SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Campaign template settings updated');

            die('OK');
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    //--------------------------------------------------------------------------


    public function ajaxDeleteAction() {
        $id = 0; $data = array();
        try {
            $this->getCampaignFromGetId($id, $data);
        } catch (Exception $e) {
            die($e->getMessage());
        }
        if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['id']) && ($id == $_POST['id'])) {
            $this->model->markAsDeleted($id);
            
            SingletonRegistry::getModelSubAccount()->deleteRecordFromSubAccountRight($id, Model_SubAccount::OBJECT_TYPE_CAMPAIGN);
            
            SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Campaign deleted');
            die('OK');
        }
        die('Unable to delete');
    }
    //--------------------------------------------------------------------------


    public function ajaxCreateShadowCampaignAction() {
        $id = 0; $data = array();
        try {
            $this->getCampaignFromGetId($id, $data);
            if (empty($_POST['name'])) {
                throw new ERequestError('Please enter campaign name');
            }

            $pre = $this->model->getExistingFieldValuesForKey($id, array('name' => trim((string)$_POST['name'])), array('user_id' => Auth::getInstance()->getUserId()));
            if (!empty($pre['name'])) {
                throw new ERequestError("Name '" . $_POST['name'] . "' already exists");
            }
            $newId = $this->model->createShadowCampaignOf($data, trim($_POST['name']), Auth::getInstance()->getUserId());
            if ($newId) {
                die('OK');
            } else {
                throw new ERequestError('Unable to create a shadow of the campaign');
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    //--------------------------------------------------------------------------


    public function ajaxAddCatchUpToQueueAction() {
        try {
            if (!Auth::getInstance()->isAdminLoggedIn()) {
                // Make sure user has the right to create a catchup job
                throw new EFWException("Error");
            }

            $id = 0; $data = array();
            $this->getCampaignFromGetId($id, $data, true);
            if (empty($_POST['partner_id'])) {
                throw new ERequestError("Invalid partner_id");
            };
            $mp = SingletonRegistry::getModelPartner();
            $partner = $mp->get((int)$_POST['partner_id']);
            if (!$partner) {
                throw new ERequestError("Invalid partner_id");
            };
            if (!isset($_POST['days']) || !is_numeric($_POST['days']) || ((int)$_POST['days'] < 0)) {
                throw new ERequestError("Invalid days value");
            }
            if (!isset($_POST['hours']) || !is_numeric($_POST['hours']) || ((int)$_POST['hours'] < 0)) {
                throw new ERequestError("Invalid hours value");
            }
            if (!isset($_POST['limit']) || !is_numeric($_POST['limit']) || ((int)$_POST['limit'] < 0)) {
                throw new ERequestError("Invalid Max deliveries value");
            }
            if (($_POST['days'] == 0) && ($_POST['hours'] == 0)) {
                throw new ERequestError("Days and hours can't both be 0");
            }
            $rlt = $rl = 0;
            if (isset($_POST['rlt'], $_POST['rl'])) {
                if ($_POST['rlt'] == '0') {
                    $rl = 0;
                } else {
                    $rlts = SingletonRegistry::getModelPartner()->listDeliveryCapTypesAssoc();
                    $rlt = (int)$_POST['rlt'];
                    if (!isset($rlts[$rlt])) {
                        throw new ERequestError("Invalid Rate Limit Type");
                    }
                    if (!is_numeric($_POST['rl']) || ($_POST['rl'] < 1)) {
                        throw new ERequestError('Invalid "Rate Limit" value - please enter a number greater than zero');
                    }
                    $rl = (int)$_POST['rl'];
                }
            }
            $logInc = SingletonRegistry::getModelLogIncoming();
            $list   = $logInc->listCatchupJobsForUser(Auth::getInstance()->getUserId());
            foreach ($list as $row) {
                if (($row['campaign_id'] == $id) && ($row['partner_id'] == $partner['id'])) {
                    throw new EFWException("This partner already has an active catchup job on this campaign");
                }
            }

            $affectedRows = 0;
            $jobId = $logInc->createCatchUpJob($affectedRows, $id, Auth::getInstance()->getUserId(),  (int)$_POST['partner_id'], (int)$_POST['days'], (int)$_POST['hours'], (int)$_POST['limit'],
                            $data['shadow_of'] ? (int)$data['shadow_of'] : false, $rlt, $rl, Auth::getInstance()->getUserId());
            if (!$affectedRows) {
                $logInc->deleteCatchupJob($jobId);
                throw new EFWException("Seleced date range didn't have any rows, no catchup job was created");
            }

            SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Catchup Job Added');
            die('OK');
        } catch (EFWException $e) {
            die($e->getMessage());
        }
    }
    //--------------------------------------------------------------------------


    public function ajaxToggleShadowOverrideAction() {
        if (!Auth::getInstance()->isAdmin()) {
            // Make sure user is an admin
            die('You must be logged in as admin to do this');
        }

        $id = 0; $data = array();
        $this->getCampaignFromGetId($id, $data, true);       
        $sourceCampaign = $this->model->get($data['shadow_of']);
        if (!$sourceCampaign) {
            die('Not a shadow campaign');
        }
        if (!isset($_POST['fid'])) {
            die('Missing field id');
        }
        if ($_POST['fid'] == 'email') {
            $d2 = $data;
            $d2['email_field_verify'] = !empty($_POST['validate']) ? '1' : '';
            $this->model->updateDiff($id, $d2, $data);
            die('OK');
        }
        $fid = max(0, (int)$_POST['fid']);
        if (!$fid) {
            die('Invalid field id');
        }
        $this->modelField or $this->modelField = SingletonRegistry::getModelCampaignField();
        $cf = $this->modelField->get($fid);
        if ($cf['campaign_id'] != $sourceCampaign['id']) {
            die('Invalid field id');
        }
        if (!empty($_POST['validate'])) {
            $this->modelField->addValidationOverride($id, $fid);
        } else {
            $this->modelField->removeValidationOverride($id, $fid);
        }

        die('OK');

    }
    //--------------------------------------------------------------------------


    protected function normalizeFieldPostValues($section, $campaignId, $campaignData, array $fieldTypes, array $cvFTypes, $impersonating) {
        if (!isset($_POST['name'][$section]) || !is_array($_POST['name'][$section])) {
            return array();
        }
        $result = array();
        foreach ($_POST['name'][$section] as $id => $name) {
            if (!is_numeric($id)) {
                throw new ERequestError("Invalid request");
            }
            $ftype = isset($_POST['ftype'][$section][$id]) ? (int)$_POST['ftype'][$section][$id] : 1;
            if (!isset($fieldTypes[$ftype])) {
                throw new ERequestError("Invalid request");
            }

            $id = (int)$id;
            $arr = array(
                    'id' => $id,
                    'name' => trim($name),
                    'campaign_id' => '' . $campaignId,
                    'field_type'  => '' . $ftype
            );
            $arr['description']   = isset($_POST['desc'][$section][$id])  ? trim($_POST['desc'][$section][$id]) : '';
            
            if ($campaignData['is_ping_and_post']) {

                if (!empty($_POST['ping_mandatory'][$section][$id]) && !empty($_POST['post_mandatory'][$section][$id])) {
                    $arr['is_mandatory'] = Model_CampaignField::MANDATORY_BOTH;
                } else if (!empty($_POST['ping_mandatory'][$section][$id])) {
                    $arr['is_mandatory'] = Model_CampaignField::MANDATORY_PING;
                } else if (!empty($_POST['post_mandatory'][$section][$id])) {
                    $arr['is_mandatory'] = Model_CampaignField::MANDATORY_POST;
                } else {
                    $arr['is_mandatory'] = Model_CampaignField::MANDATORY_NONE;
                }
            } else {
                $arr['is_mandatory']  = !empty($_POST['mandatory'][$section][$id]) ? '1' : '0';
            }
            
            $arr['validate']      = (in_array($ftype, $cvFTypes) && !empty($_POST['span-verify-checkbox'][$section][$id])) ? ($impersonating ? '2' : '1') : '';
            if ($arr['field_type'] == Model_CampaignField::FIELD_TYPE_EMAIL) {
                $_POST['email_field_name']          = $arr['name'];
                $_POST['email_field_description']   = $arr['description'];
                $_POST['email_field_mandatory']     = $arr['is_mandatory'];
                $_POST['span-verify-checkbox_email']= !empty($_POST['span-verify-checkbox'][$section][$id]);
            } else {
                $result[$id] = $arr;
            }
        }
        return $result;
    }
    //--------------------------------------------------------------------------
    
    
    protected function normalizeFilterPostValues($section, $campaignId, $campaignData, array $fieldTypes) {
        if (!isset($_POST['action'][$section]) || !is_array($_POST['action'][$section])) {
            return array();
        }
        
        $sysFields = Controller_Partners::listSystemFieldsForDisplay($campaignData);

        $extTokensAssoc = ExternalLookup_Registry::listTokens2Names();
        $extTokens      = array_keys($extTokensAssoc);
        sort($extTokens);
        foreach ($extTokens as $x) {
            $sysFields[] = $x;
        }
        
        $result = array();
        foreach ($_POST['action'][$section] as $id => $action) {
            if (!is_numeric($id)) {
                throw new ERequestError("Invalid request");
            }
            if (!is_numeric($_POST['match'][$section][$id])) {
                throw new ERequestError("Invalid request");
            }
            
            if (is_numeric($_POST['field_type'][$section][$id])) {
                $ftype    = (int)$_POST['field_type'][$section][$id];
                $sysField = '';
            } else {
                $ftype    = null;
                $sysField = $_POST['field_type'][$section][$id];
                if (!in_array($sysField, $sysFields)) {
                    $sysField = '';
                }
            }

            $id = (int)$id;
            $arr = array(
                    'id' => $id,
                    'filter_action' => $action,
                    'campaign_id' => '' . $campaignId,
                    'field_type_id'  => $ftype,
                    'system_field'  => $sysField,
                    'match_type'    => $_POST['match'][$section][$id],
                    'match_value'   => $_POST['value'][$section][$id],
                    'is_active'     => empty($_POST['is_active'][$section][$id]) ? '0' : '1'
            );
            $result[$id]     = $arr;
        }
        return $result;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Validations done on $form object after importing data from $_POST. This function will
     * make the checks and at the end throw EDone if there were any errors.
     *
     * @param int $id - ID of campaign we are checking, or zero if adding new campaign
     */
    protected function validateFormAndThrowEDoneOnError(Form_Data $form, $id, $isShadow = false) {
        $this->model or $this->model = SingletonRegistry::getModelCampaign();

        //   This code will check if there is already a campaign with given name. It needs ID of the
        // campaign we are editing so it can look for same name in all campaigns except that one. If
        // adding new campaign we should pass zero as $id
        if ($form->name) {
            $pre = $this->model->getExistingFieldValuesForKey($id, array('name' => $form->name), array('user_id' => Auth::getInstance()->getUserId()));
            if (!empty($pre['name'])) {
                $form->addError('name', "Name '" . escapeHtml($form->name) . "' already exists");
            }
        }

        // If "Repost" is selected then we make sure that values for repost hours and repost max are withing range
        if ($form->should_repost) {
            $form->checkNumeric('repost_hours', 1, 672);
            $form->checkNumeric('repost_max',   1, 10);
        } else {
            // And if campaign doesn't repost, we set those to 0
            $form->repost_hours = 0;
            $form->repost_max   = 0;
        }

        //   Check numeric will add an error to form element if value is not numeric. This is for
        // "Leads Per" field, which is to how many partners we should deliver the data. Can't be
        // less than one.
        $form->checkNumeric('leads_per', 1);

        // parnter_retries should be numeric. In case user left it empty, we put in 0...
        if ($form->partner_retries == '') {
            $form->partner_retries = '0';
        }
        // ... and then check if it is numeric. That way no error is shown even if user left the field empty.
        $form->checkNumeric('partner_retries', 0, 5);

        if ($isShadow) {
            if ($form->shadow_type == 'skim') {
                // If skimming, skim_percentage has to be numeric and we put 0 to delay hours
                $form->checkNumeric('skim_percentage', 0, 100);
                $form->shadow_delay_hours = 0;
            } else {
                // And if parasite shadow, then delay_hours has to be between 0 and 7 days, and skim_percentage 0
                $form->checkNumeric('shadow_delay_hours', 0, 7 * 24);
                $form->skim_percentage = 0;
            }
        }

        // If campaign is not a shadow campaign and not OOB, then succes_url and failure_url are required fields
        if (!$isShadow && $form->response_type != Model_Campaign::RESPONSE_TYPE_API_KEY) {
            if (empty($form->success_url)) {
                $form->addError('success_url', 'This field is required');
            }
            if (empty($form->failure_url)) {
                $form->addError('failure_url', 'This field is required');
            }
        }
        
        if ($form->campaign_type == Model_Campaign::CAMPAIGN_TYPE_PING_AND_POST && !empty($form->bid_response_value)) {
            if (!is_numeric($form->bid_response_value)) {
                $form->addError('bid_response_value', 'Invalid Value');
            } else if ($form->bid_response_type == Model_Campaign::BID_RESPONSE_PERCENT) {
                if ($form->bid_response_value < 0 || $form->bid_response_value > 100) {
                    $form->addError('bid_response_value', 'Please enter a number between 0 and 100');
                }
            }
        }
        
        // validate campaign_timeout (in range 0 - 180)
        if ( ! empty($form->campaign_timeout)) {
            $form->checkNumeric('campaign_timeout', 0, 180);
        } else {
            $form->campaign_timeout = null;
        }
        
        if (!$isShadow) {
            // This code is used in case user has more than one industry to make sure
            // industry_id is in the list of user's industries
            $industries = $this->listIndustryIds();
            if (sizeof($industries) > 0) {
                if (isset($form->industry_id) && $form->industry_id == '0') {
                    $form->addError('industry_id', 'Please select an industry');
                }
                if (!empty($form->industry_id)) {
                    if (!in_array($form->industry_id, $industries)) {
                        $form->addError('industry_id', 'Invalid Value');
                    }
                }
            }
        }

        // For OOB campaigns throttle_value setting must be numeric
        if (intval($form->delivery_type) === Model_Campaign::DELIVERY_TYPE_QUEUED_KEY) {
            $form->checkNumeric('throttle_value', 0);
        }

        // If any of the checks above have failed, it would have added errors to
        // $form->errors. This code will throw EDone if there are any errors
        $form->throwDoneIfErrors();
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Get the data for campaign that this request is about.<br>
     * This function will modify by-reference field $id and $data. It will
     * find ID of campaign from either GET or POST (controlled by $isPost), will
     * put that id into $id parameter, and data from DB for that id into $data
     * parameter. If there's an error it will throw either ERequestError if ID
     * does not exist or EAccessDenied if user does not have the right to see
     * that campaign.
     *
     * <p>ID needs to be read from POST when it is an ajax request.
     *
     * <p>It will also put $data into $this->campaign and initialize $this->out
     */
    protected function getCampaignFromGetId(& $id, & $data, $isPost = false) {
        if ($isPost) {
            $id = isset($_POST['id']) ? max(0, (int)$_POST['id']) : 0;
        } else {
            $id = isset($_GET['id'])  ? max(0, (int)$_GET['id'])  : 0;
        }
        if (!$id) {
            throw new ERequestError("Invalid 'id' parameter");
        }
        $this->model or $this->model = SingletonRegistry::getModelCampaign();
        $data = $this->model->get($id);
        if (!$data) {
            throw new ERequestError("Invalid 'id' parameter");
        }
        $auth = Auth::getInstance();
        if (!$auth->isAdmin() && ($data['user_id'] != $auth->getUserId() && 
                (!in_array($id, SingletonRegistry::getModelSubAccount()->listAllCampaignIds($auth->getUserId()))))) {
            throw new EAccessDenied('Access denied');
        }
        $this->out['id']   = $id;
        $this->out['data'] = $data;
        $this->campaign    = $data;
    }
    //--------------------------------------------------------------------------


    /**
     * @return Form_Data
     */
    protected function form() {
        $form = new Form_Data();
        $form->add('text',     'name',            '', array('maxlength' => 64, '* required' => true, '* trim' => true));
        $form->add('textarea', 'notes',           '', array('rows' => 7));
        $form->add('select',   'campaign_type',   '', array('* items' => SingletonRegistry::getModelCampaign()->listCampaignType()));
        $form->add('text',     'success_url',     '', array('* trim' => 1, '* required' => false, '* filter' => FILTER_VALIDATE_URL, '* filter_error' => 'Invalid URL'));
        $form->add('text',     'failure_url',     '', array('* trim' => 1, '* required' => false, '* filter' => FILTER_VALIDATE_URL, '* filter_error' => 'Invalid URL'));

        $form->add('select',   'should_repost',   '', array('* items' => array('0' => 'No', '1' => 'Yes'), 'id' => 'should-repost'));
        $form->add('text',     'repost_hours',    '', array('maxlength' => 5, 'id' => 'repost-hours'));
        $form->add('text',     'repost_max',      '', array('maxlength' => 7, 'id' => 'repost-max'));
        $form->add('text',     'leads_per',       '', array('maxlength' => 5));
        $form->add('select',   'distribution',    '', array('* items' => SingletonRegistry::getModelCampaign()->listDistributionType(), 'id' => 'campaign-distribution'));
        $form->add('select',   'delivery_type',   '', array('* items' => SingletonRegistry::getModelCampaign()->listDeliveryType(),     'id' => 'delivery-type'));
        $form->add('select',   'response_type',   '', array('* items' => SingletonRegistry::getModelCampaign()->listResponseType(),     'id' => 'response-type'));
        $form->add('select',   'bid_response_type',    '', array('* items' => SingletonRegistry::getModelCampaign()->listBidResponseType(), 'id' => 'bid-response-type'));
        $form->add('text',     'bid_response_value',   '', array('* trim' => 1, 'id' => 'bid-response-value'));
        $form->add('select',   'campaign_timeout',     '', array('* items' => array('0' => 'Default (60)', '90' => '90 seconds',
                                                                                 '120' => '120 seconds', '150' => '150 seconds',
                                                                                 '180' => '180 seconds'), 'id' => 'campaign-timeout'));
        // These attributes are used for OOB checkbox and for OOB capping checkbox. If user is not admin
        // then we make those checkboxes readonly.
        $oobCheckboxAttributes = array('style' => 'border: 0;');
        if (!Auth::getInstance()->isAdminLoggedIn()) {
            $oobCheckboxAttributes['readonly'] = 'readonly';
        }

        // Advanced Settings
        $form->add('select',   'throttle_type',   '', array('* items' => SingletonRegistry::getModelPartner()->listDeliveryCapTypesAssocWithMinute(), 'id' => 'throttle-type'));
        $form->add('text',     'throttle_value',  '', array('maxlength' => 5, '* trim' => true, 'id' => 'throttle-value'));
        $form->add('checkbox', 'capalp',          '', array('style' => 'border: 0;'));
        $form->add('checkbox', 'append_succ_url', '', array('style' => 'border: 0;'));
        $form->add('text',     'partner_retries', '', array('maxlength' => 1));

        $form->add('checkbox', 'is_oob_capped',   '', $oobCheckboxAttributes);
        $form->add('select',   'oob_cap_type',    '', array('* items' => SingletonRegistry::getModelPartner()->listDeliveryCapTypesAssoc(), 'id' => 'oob-cap-type'));
        $form->add('text',     'oob_cap_value',   '', array('maxlength' => 5, '* trim' => true, 'id' => 'oob-cap-value'));

        $industries = $this->listIndustryIds();
        $this->out['showIndustryId'] = true;
        /* @var $mci Model_Industry */
        $mci = SingletonRegistry::getSingleInstance('Model_Industry');
        $systemIndustries = $mci->listAssoc();
        $userIndustries = $this->listIndustryIds();

        // add blank industry to force user to choose one explicitly
        if (sizeof($industries) > 1) {
            $items[0] = "";
            if (!$this->campaign) {
                $this->out['autoExpandAdvance'] = true;
            }
        }

        foreach($userIndustries AS $industry) {
            $items[$industry] = $systemIndustries[$industry];
        }

        $keys = array_keys($items);
        $this->out['defaultIndustryId'] = $keys[0];
        $form->add('select',   'industry_id',   '', array('* items' => $items, 'id' => 'industry-id'));

        $pf = new PageFragment_FormAuto($this, $form, false);

        $this->out['form'] = $form;
        $this->out['pf']   = $pf;

        return $form;
    }
    //--------------------------------------------------------------------------


    /**
     * @return Form_Data
     */
    protected function shadowForm() {
        $form = new Form_Data();
        $form->add('text',     'name',            '', array('maxlength' => 64, '* required' => true, '* trim' => true));
        $form->add('textarea', 'notes',           '', array('rows' => 7));
        $form->add('select',   'is_active',       '', array('* items' => array('1' => 'Active', '0' => 'Not Active')));

        $form->add('select',   'should_repost',   '', array('* items' => array('0' => 'No', '1' => 'Yes'), 'id' => 'should-repost'));
        $form->add('text',     'repost_hours',    '', array('maxlength' => 5, 'id' => 'repost-hours'));
        $form->add('text',     'repost_max',      '', array('maxlength' => 7, 'id' => 'repost-max'));
        $form->add('text',     'leads_per',       '', array('maxlength' => 5));

        // Advanced Settings
        $form->add('checkbox', 'capalp',          '', array('style' => 'border: 0;'));
        $form->add('text',     'partner_retries', '', array('maxlength' => 1));

        $form->add('select',   'throttle_type',   '', array('* items' => SingletonRegistry::getModelPartner()->listDeliveryCapTypesAssocWithMinute(), 'id' => 'throttle-type'));
        $form->add('text',     'throttle_value',  '', array('maxlength' => 5, '* trim' => true, 'id' => 'throttle-value'));

        //Shadow Settings
        $form->add('text',     'shadow_delay_hours', 'Delay before processing shadow campaign (hours)', array('id' => 'shadow-delay-hours'));

        $form->add('radio',    'shadow_type',        'Shadow Type');
        $form->add('text',     'skim_percentage',    'Skim Percentage', array('id' => 'skim-percentage'));

        $pf = new PageFragment_FormAuto($this, $form, false);

        $this->out['form'] = $form;
        $this->out['pf']   = $pf;

        return $form;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc If $_POST['redir'] is in $allowedActions, redirect user to /campaigns/redir.html?id=$id.
     * Otherwise send him to listing page for the campaign $id. If we were editing campaign that's
     * listed on page 3, then after user clicks save we redirect him to page 3. Parameter $toCorrectPage
     * can be set to false, then we just redirect user to /campaigns/ page.
     *
     * <p>The purpose of this is to redirect user to correct page if he click on a button other than "Save"
     */
    private function smartRedirect(array $allowedActions, $id, $toCorrectPage = true) {
        if (isset($_POST['redir']) && is_string($_POST['redir']) && in_array($_POST['redir'], $allowedActions)) {
            App::getFrontController()->redirectToCP($this, array('action' =>  $_POST['redir']), array('id' => $id));
        }
        if ($toCorrectPage) {
            $pos  = $this->model->indexOfIdSortedByName(Auth::getInstance()->getUserId(), $id);
            $page = (int)((int)$pos / self::PER_PAGE);
            App::getFrontController()->redirectToCP($this, array(), $page ? array('page' => $page + 1) : array());
        } else {
            App::getFrontController()->redirectToCP($this);
        }
    }
    //--------------------------------------------------------------------------


    private function listIndustryIds() {
        /* @var $mci Model_Industry */
        $mci = SingletonRegistry::getSingleInstance('Model_Industry');

        $industries = array();
        $arr = get_user_field('industry', Auth::getInstance()->getUserId());
        if (empty($arr)) {
            $arr = array('DAT');
        }
        if ($arr && is_array($arr)) {
            $indAbbrs = $mci->listAbbreviationsAssoc();
            foreach ($arr as $s) {
                $industryId = array_search($s, $indAbbrs);
                if ($industryId != false) {
                    $industries[] = (int)$industryId;
                }
            }
        }
        return $industries;
    }
    //--------------------------------------------------------------------------


    private function getIndustryIdForCampaign(array $campaign) {
        $iids = $this->listIndustryIds();
        if ((sizeof($iids) == 1) || (empty($campaign['industry_id'])) || !in_array($campaign['industry_id'], $iids)) {
            return isset($iids[0]) ? $iids[0] : 1;
        } else {
            return (int)$campaign['industry_id'];
        }
    }
    //--------------------------------------------------------------------------


    public function preRun() {
        $this->pageTitle = 'Campaigns';
        parent::preRun();
        App::getFrontController()->activeSection = FrontController::SECTION_CAMPAIGN;
    }
    //--------------------------------------------------------------------------


    private function makeSureAllWrapperPartnersHaveChildIpPartnerField($userId) {
        $wrappedPartners = SingletonRegistry::getModelCampaign()->getWrapperPartnerIdsAssignedToMasters($userId);
        
        // add if statement (resolve ticket 575, 579)
        if (is_array($wrappedPartners)) {
            foreach ($wrappedPartners AS $partner) {
                SingletonRegistry::getModelPartner()->createChildIpFieldForWrapperPartner($partner);
                $childCampaignId = SingletonRegistry::getModelPartner()->getCampaignIdFromWrapperPartnerId($partner);
                if (! empty($childCampaignId)) {
                    SingletonRegistry::getModelCampaign()->addCampaignFieldForChildIpField($childCampaignId);
                }
            }
        }
    }
    // -------------------------------------------------------------------------
    
    
    public function ajaxGetFormFromUrlAction() {  
        App::getFrontController()->setUsePageTemplate(false);
        $url = filter_input(INPUT_POST, 'url');
        $result = CurlHelper::request($url, 'POST');
        if ($result['httpCode'] === 200) {
            $html = str_replace(array('<html', '</html>'), array('<html2', '</html2>'), $result['content']);
            echo $html;
        }
        die;
    }
    //--------------------------------------------------------------------------
    
    
    public function ajaxSubmitDataFromCsvAction() {
        App::getFrontController()->setUsePageTemplate(false);
        $response = array('status' => '0');
        $campaignId = filter_input(INPUT_POST, 'campaign_id');
        
        if ( isset($_FILES["file"])) {
            if ($_FILES["file"]["error"] > 0) {
                $response['message'] = "File error, number " . $_FILES["file"]["error"];
            } else {
                $filename   = $_FILES["file"]["tmp_name"];
                $initialName = $_FILES["file"]["name"] . '_' . date('Y-m-d-H-i-s');
                
                $this->changeToCsvStandardFormat($filename);
                $processResult = $this->processCsvFile($filename, $campaignId, $initialName);
                
                $response = $processResult;
            }
        } else {
            $response['message'] = "No file selected";
        }
        
        echo json_encode($response);
        die;
    }
    // -------------------------------------------------------------------------
    
    
    private function processCsvFile($filename, $campaignId, $initialFileName) {
        $file   = fopen($filename,"r");
        $header = fgetcsv($file);
        
        $campaignModel  = SingletonRegistry::getModelCampaign();
        $fieldModel     = SingletonRegistry::getModelCampaignField();
        $incomingModel  = SingletonRegistry::getModelLogIncoming();
        $mft            = SingletonRegistry::getSingleInstance('Model_FieldType');
        $fieldTypesFull = $mft->listAssoc();

        
        $campaign = $campaignModel->get($campaignId);        
        
        $checkHeader = $fieldModel->checkFieldMatch($campaign, $header);
        
        if ($checkHeader === true) {
            $rowNumber      = 1;
            $successCount   = 0;
            $failCount      = 0;
            while(!feof($file))
            {
                $rowNumber ++;
                $row = fgetcsv($file);
                if (!empty($row)) {
                    $incomingId = $incomingModel->addToQueueFromCsv($rowNumber, $campaign, $header, $row, $fieldTypesFull, $initialFileName);
                    if ($incomingId === false) {
                        $failCount ++;
                    } else {
                        $successCount ++;
                    }
                }
            }
            
            fclose($file);
            return array (
                'status' => '1',
                'message'=> 'Success: ' . $successCount . ', Fail: ' . $failCount,
                'errors' => $incomingModel->messages
            );
        }

        fclose($file);
        
        return array ('status' => '0', 'message' => $checkHeader);
    }
    //--------------------------------------------------------------------------
    
    public function changeToCsvStandardFormat($filename) {
        try {
            $content            = file_get_contents($filename);
//            $content            = str_replace(array('"', '\''), array('', ''), $content);
            $content            = preg_replace('/\t/', ',', $content);
            $content            = preg_replace("/\r\n?/", "\r\n", $content);
            file_put_contents($filename, $content);
        } catch (Exception $ex) {
            
        }
        
    }
}