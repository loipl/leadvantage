<?php

class Controller_Partners extends Controller {

    /**
     * @var Model_Partner
     */
    protected $model;

    /**
     * @var Model_PartnerField
     */
    protected $modelField;

    /**
     * @var Model_PartnerFilter
     */
    protected $modelFilter;

    protected $partnerSettings = array();

    const PER_PAGE = 15;


    public function indexAction() {
        $auth = Auth::getInstance();

        SingletonRegistry::getModelPartner()->updateCampaignsWithExistingWrapperPartner($auth->getUserId());

        App::getFrontController()->extraCss[] = 'partners.css';

        $this->model or $this->model = SingletonRegistry::getModelPartner();
        $page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $userId  = $auth->getUserId();
        
        $subAccountPartnerIds = SingletonRegistry::getModelSubAccount()->listAllPartnerIds($userId);
        $whereUserId = 'user_id = ' . $userId;
        if (!empty($subAccountPartnerIds)) {
            $whereUserId = '(' . $whereUserId . ' OR `id` IN (' . implode(',', $subAccountPartnerIds) . '))';
        }

        if (isset($_GET['all'])) {
            $condition = array(
                $whereUserId, 
                'wrapped_campaign_id IS NULL', 
                'partner_type = \'0\' OR ping_post_type = \'1\''
                . " OR NOT EXISTS (
                        SELECT * FROM `partners` p1
                        WHERE $whereUserId
                            AND p1.post_partner_id = partners.id
                    )"
            );
            $partnerList          = $this->model->listAllWhere($condition, 'name');
            $this->out['perPage'] = sizeof($partnerList) + 2;
        } else {
            $partnerList          = $this->model->listPageForUser($whereUserId, $page, self::PER_PAGE);
            $this->out['perPage'] = self::PER_PAGE;
        }
               
        $this->out['list']    = $this->model->removePingPostSuffix($partnerList);
        $this->out['count']   = $this->model->countForUser($whereUserId);
        $this->out['isAdmin'] = $isAdmin = $auth->isAdmin();

        $subAccountInfo = SingletonRegistry::getModelSubAccount()->checkIfUserIsSubAccount($auth->getUserId());
        if (!empty($subAccountInfo)) {
            $this->out['revokePartnerControl'] = SingletonRegistry::getModelSubAccount()->checkIfRevokePartnerControl($auth->getUserId());
        }
        
        /* @var $mci Model_Industry */
        $mci = SingletonRegistry::getSingleInstance('Model_Industry');
        $this->out['industries'] = $mci->listAssoc();

        $industries = array();
        $indAbbrs = $mci->listAbbreviationsAssoc();
        $arr = get_user_field('industry', $userId);
        if ($arr && is_array($arr)) {
            foreach ($arr as $s) {
                $industryId = array_search($s, $indAbbrs);
                if ($industryId != false) {
                    $industries[] = (int)$industryId;
                }
            }
        }

        /* @var $mpt Model_PartnerTemplate */
        $mpt = SingletonRegistry::getSingleInstance('Model_PartnerTemplate');

        $this->out['templatesAssoc']          = $templatesAssoc = $mpt->listTemplateNamesAssoc();
        $this->out['templatesAssocInd']       = $isAdmin ? $templatesAssoc : $mpt->listTemplateNamesAssocForIndustries($industries);
        $this->out['templateIndustriesAssoc'] = $mpt->listTemplateIndustriesAssoc(array_keys($templatesAssoc));
    }
    //--------------------------------------------------------------------------


    public function addAction() {
        $subAccountInfo = SingletonRegistry::getModelSubAccount()->checkIfUserIsSubAccount(Auth::getInstance()->getUserId());
        if (!empty($subAccountInfo) && SingletonRegistry::getModelSubAccount()->checkIfRevokePartnerControl(Auth::getInstance()->getUserId())) {
            throw new EAccessDenied('Access denied');
        }
        
        $form = $this->form();
        App::getFrontController()->extraCss[] = '2partners.css';
        $this->partnerSettings = array();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (!empty($_POST['success_url'])) {
                $responseType = isset($_POST['response_type']) ? $_POST['response_type'] : null;
                $_POST['success_url'] = $this->addHttpToUrlIfNotValid(trim($_POST['success_url']), $responseType);
            }
            
            if ( !empty($_POST['post_partner_id'])) {
                $_POST['name'] = preg_replace('/( ?\(ping\))?$/', ' (ping)', trim($_POST['name']), 1);
            } else if ($_POST['partner_type'] == Model_Partner::PARTNER_TYPE_PING_POST) {
                $_POST['name'] = preg_replace('/( ?\(post\))?$/', ' (post)', trim($_POST['name']), 1);
            }

            $form->importFromArray($_POST, true);
            // ping partner always has parse_response = 1
            if (!empty($_POST['post_partner_id'])) {
                $form->parse_response = '1';
            }
            $this->validateFormAndThrowEDoneOnError($form, 0);
            $data = $form->getValues();
            $userId = Auth::getInstance()->getUserId();
            
            if (!empty($subAccountInfo)) {
                $userId = $subAccountInfo['user_id'];
            } 
            $data['user_id'] = $userId;
            
            if (empty($data['partner_timezone'])) {
                $data['partner_timezone'] = null;
            }
            
            if (empty($data['response_type'])) {
                $data['response_type'] = '0';
            }
            
            if ( !empty($data['post_partner_id'])) {
                $data['partner_type'] = strval(Model_Partner::PARTNER_TYPE_PING_POST);
                $data['ping_post_type'] = strval(Model_Partner::PING_POST_TYPE_PING);
                $data['parse_response'] = '1';
            }
            
            $partnerId = $this->model->insert($data);
            if ($partnerId) {
                if (!empty($subAccountInfo)) {
                    $subAccountRightData = array(
                        'sub_account_id' => Auth::getInstance()->getUserId(),
                        'sub_account_username' => $subAccountInfo['sub_account_username'],
                        'object_id' => $partnerId,
                        'object_name' => $data['name'],
                        'object_type' => Model_SubAccount::OBJECT_TYPE_PARTNER
                    );
                    
                    SingletonRegistry::getModelSubAccount()->insertRecord($subAccountRightData);
                }
                
                /* @var $mps Model_PartnerSettings */
                $mps = SingletonRegistry::getSingleInstance('Model_PartnerSettings');
                $mps->saveSettings($partnerId, $this->partnerSettings);
                
                if ( !empty($data['post_partner_id'])) {
                    $this->model->syncPingPostPartner($partnerId, $data, 'pingToPost');
                }

                $params = $get = array();
                if (isset($_POST['redir'])) {
                    $r = $_POST['redir'];
                    if (in_array($r, array('fields', 'filters', 'template'))) {
                        $params['action'] = $r;
                        $get['id']        = $partnerId;
                    } else if ($r === 'ping') {
                        $params['action'] = 'add';
                        $get['ping_post_type'] = 'ping';
                        $get['post_partner_id'] = $partnerId;   
                    } else if ($r === 'post') {
                        $params['action'] = 'edit';
                        $get['id']        = $data['post_partner_id'];
                    }
                }
                SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Partner added');
                App::getFrontController()->redirectToCP($this, $params, $get);
            } else {
                throw new ERequestError("There was an error adding your partner to DB");
            }
        } else {
            if (!empty($_GET['post_partner_id']) && is_numeric($_GET['post_partner_id'])) {
                $postPartnerData      = $this->model->get($_GET['post_partner_id']);
                $form->name           = preg_replace('/ ?(\(post\))?$/', '', $postPartnerData['name'], 1);
                $form->delivery_ctype = $postPartnerData['delivery_ctype'];
                $form->posting_type   = $postPartnerData['posting_type'];
                $form->xml_field_name = $postPartnerData['xml_field_name'];
            }
            
            $form->delimiter      = '|';
            $form->price_per_lead = '0';
        }
    }
    //--------------------------------------------------------------------------


    public function editAction() {
        $id = 0;
        $data = array();
        $this->getPartnerFromGetId($id, $data);
        App::getFrontController()->extraCss[] = '2partners.css';
        $form = $this->form();
        /* @var $mps Model_PartnerSettings */
        $mps = SingletonRegistry::getSingleInstance('Model_PartnerSettings');

        $this->partnerSettings = $mps->listSettings($id);
        $serialized = serialize($this->partnerSettings);
        $changed = false;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (!empty($_POST['success_url'])) {
                $responseType = isset($_POST['response_type']) ? $_POST['response_type'] : null;
                $_POST['success_url'] = $this->addHttpToUrlIfNotValid(trim($_POST['success_url']), $responseType);
            }
            
            if ( !empty($data['post_partner_id'])) {
                $_POST['name'] = preg_replace('/( ?\(ping\))?$/', ' (ping)', trim($_POST['name']), 1);
            } else if ($_POST['partner_type'] == Model_Partner::PARTNER_TYPE_PING_POST) {
                $_POST['name'] = preg_replace('/( ?\(post\))?$/', ' (post)', trim($_POST['name']), 1);
            }

            $form->importFromArray($_POST, true);
            // ping partner always has parse_response = 1
            if ($data['partner_type'] == Model_Partner::PARTNER_TYPE_PING_POST && $data['ping_post_type'] == Model_Partner::PING_POST_TYPE_PING) {
                $form->parse_response = '1';
            }
            $this->validateFormAndThrowEDoneOnError($form, $id);

            $newData = $form->getValues();
            $newData['should_retry'] = empty($newData['should_retry']) ? '0' : '1';
            if (empty($newData['parse_response'])) {
                unset($newData['delimiter']);
            }
            if (empty($newData['partner_timezone'])) {
                $newData['partner_timezone'] = null;
            }
            if (empty($data['response_type'])) {
                $data['response_type'] = '0';
            }
            if (is_null($newData['partner_type'])) {
                $newData['partner_type'] = $data['partner_type'];
            }
            if (is_null($newData['parse_response'])) {
                unset ($newData['parse_response']);
            }
            
                        
            // update name of ping-post partner and sync
            if ($newData['partner_type'] == Model_Partner::PARTNER_TYPE_PING_POST) {
                if ($data['ping_post_type'] == Model_Partner::PING_POST_TYPE_PING) {
                    $this->model->syncPingPostPartner($id, $newData, 'pingToPost');
                } else {  
                    $this->model->syncPingPostPartner($id, $newData, 'postToPing');
                }
            }
            
            // delete ping partner if user change from ping-post to post-only
            if (
                    $data['partner_type']       == Model_Partner::PARTNER_TYPE_PING_POST &&
                    $newData['partner_type']    == Model_Partner::PARTNER_TYPE_POST
                ) {
                $pingPartnerId = $this->model->getPingPartnerId($id);
                $this->model->markAsDeleted($pingPartnerId);
                $this->model->updatePostPartnerId($pingPartnerId, '0');
                
                // force updating partner name
                $data['name'] = '';
            }
            
            $this->model->updateDiff($id, $newData, $data);
            if (DB::$db->affectedRows()) {
                $changed = true;
            }
            if (serialize($this->partnerSettings) != $serialized) {
                $mps->saveSettings($id, $this->partnerSettings);
                $changed = true;
            }

            $params = $get = array();
            if (isset($_POST['redir'])) {
                $r = $_POST['redir'];
                if (in_array($r, array('fields', 'filters', 'caps', 'template'))) {
                    $params['action'] = $r;
                    $get['id']        = $id;
                } else if ($r === 'ping') {
                    $pingPartnerId = $this->model->getPingPartnerId($id);
                    if (empty($pingPartnerId)) {
                        $params['action'] = 'add';
                        $get['ping_post_type'] = 'ping';
                        $get['post_partner_id'] = $id;   
                    } else {
                        $params['action'] = 'edit';
                        $get['id'] = $pingPartnerId;
                    }
                    
                } else if ($r === 'post') {
                    $params['action'] = 'edit';
                    $get['id']        = $data['post_partner_id'];
                }
            }
            if ($changed) {
                SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Partner updated');
                $this->sendMessageIfThereAreDependentTemplates($id, 'Partner settings changed', 'edit');
            }
            if ($params) {
                App::getFrontController()->redirectToCP($this, $params, $get);
            } else {
                $this->redirectToCorrectIndexPage($id);
            }
        } else {
            $form->importFromArray($data, false);   
        }
        
        if ($form->partner_type == Model_Partner::PARTNER_TYPE_PING_POST) {
            $form->name = trim(preg_replace('/ ?(\(post\)|\(ping\))?$/', '', $form->name, 1));
        }
    }
    //--------------------------------------------------------------------------


    public function fieldsAction() {
        $fields = $origFields = array();
        $id = 0;
        $this->prepareOutputVarsForFieldsAction($fields, $origFields, $id);
        App::getFrontController()->extraCss[] = 'fieldpartners.css';
        $fieldsSerialized = serialize($fields);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $new = $this->normalizeFieldPostValues('new', $id);
            $old = $this->normalizeFieldPostValues('old', $id);

            $names = array();
            foreach ($new as $row) {
                if (in_array(strtolower($row['name']), $names)) {
                    throw new ERequestError("Cannot have two fields with same name ({$row['name']})");
                }
                $names[] = $row['name'];
            }
            foreach ($old as $row) {
                if (in_array(strtolower($row['name']), $names)) {
                    throw new ERequestError("Cannot have two fields with same name ({$row['name']})");
                }
                $names[] = $row['name'];
            }

            foreach ($old as $fid => $requestData) {
                if (!isset($fields[$fid])) {
                    $new[] = $requestData;
                    unset($old[$fid]);
                    continue;
                }
                unset($fields[$fid]);
            }

            //######################################################################
            // START TRANSACTION
            //######################################################################
            $this->modelField->startTransaction();
            $this->modelField->deleteWhere(array('partner_id' => (int)$id));
            foreach ($old as $row) {
                $this->modelField->insert($row);
            }
            foreach ($new as $row) {
                unset($row['id']);
                $this->modelField->insert($row);
            }
            $this->modelField->commit();
            //######################################################################
            // COMMIT
            //######################################################################

            if (serialize($this->modelField->listAllForPartner($id)) != $fieldsSerialized) {
                SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Partner fields settings saved');
                $this->sendMessageIfThereAreDependentTemplates($id, 'Partner fields changed', 'fields');
            }

            $params = $get = array();
            if (isset($_POST['redir'])) {
                $r = $_POST['redir'];
                if (in_array($r, array('edit', 'filters','caps', 'template'))) {
                    $params['action'] = $r;
                    $get['id']        = $id;
                } else if ($r === 'ping') {
                    $pingPartnerId = $this->model->getPingPartnerId($id);
                    if (empty($pingPartnerId)) {
                        $params['action'] = 'add';
                        $get['ping_post_type'] = 'ping';
                        $get['post_partner_id'] = $id;   
                    } else {
                        $params['action'] = 'fields';
                        $get['id'] = $pingPartnerId;
                    }
                    
                } else if ($r === 'post') {
                    $data = $this->model->get($id);
                    $params['action'] = 'fields';
                    $get['id']        = $data['post_partner_id'];
                }
            }
            if ($params) {
                App::getFrontController()->redirectToCP($this, $params, $get);
            } else {
                $this->redirectToCorrectIndexPage($id);
            }
        }
    }
    //--------------------------------------------------------------------------


    public function templateAction() {
        $id = 0;
        $data = array();
        $this->getPartnerFromGetId($id, $data);
        $this->modelField or $this->modelField = SingletonRegistry::getModelPartnerField();
        $fields = $this->modelField->listAllForPartner($id);

        $this->out['fields'] = $fields;

        if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['template'])) {
            $p = $data;
            $p['template'] = $_POST['template'];
            $p['valid_template'] = isset($_POST['valid_template']) ? $_POST['valid_template'] : Model_Partner::TEMPLATE_VALID;
            $this->model->updateDiff($id, $p, $data);
            if ($p['template'] != $data['template']) {
                SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Partner template updated');
                $this->sendMessageIfThereAreDependentTemplates($id, 'Partner XML template changed', 'template');
            }


            $params = $get = array();
            if (isset($_POST['redir'])) {
                $r = $_POST['redir'];
                if (in_array($r, array('edit', 'filters', 'fields', 'caps'))) {
                    $params['action'] = $r;
                    $get['id']        = $id;
                } else if ($r === 'ping') {
                    $pingPartnerId = $this->model->getPingPartnerId($id);
                    if (empty($pingPartnerId)) {
                        $params['action'] = 'add';
                        $get['ping_post_type'] = 'ping';
                        $get['post_partner_id'] = $id;   
                    } else {
                        $params['action'] = 'template';
                        $get['id'] = $pingPartnerId;
                    }
                    
                } else if ($r === 'post') {
                    $params['action'] = 'template';
                    $get['id']        = $data['post_partner_id'];
                }
            }
            if ($params) {
                App::getFrontController()->redirectToCP($this, $params, $get);
            } else {
                $this->redirectToCorrectIndexPage($id);
            }
        } else {
            $this->out['tv'] = $data['template'];
        }
    }
    //--------------------------------------------------------------------------


    protected function prepareOutputVarsForFieldsAction(& $fields, & $origFields, & $id) {
        $data = array();
        $this->getPartnerFromGetId($id, $data);
        $this->modelField or $this->modelField = SingletonRegistry::getModelPartnerField();
        $fields = $origFields = $this->modelField->listAllForPartner($id);

        $this->out['fields']         = $fields;
        $this->out['allFtypes']      = SingletonRegistry::getModelCampaignField()->listFieldTypesAssoc();
        $this->out['systemFields']   = $this->listSystemFieldsForDisplay($data);
        $this->out['systemFieldsEx'] = $this->listSystemFieldsEx();
        $this->out['ftypes']         = $usedFTypes = $this->getFtypes(Auth::getInstance()->getUserId());

        $caps = SingletonRegistry::getModelUser()->listUserCaps(Auth::getInstance()->getUserId());
        $maxCap = Auth::maxCap($caps);

        $extTokensAssoc = ExternalLookup_Registry::listTokens2Names();
        ExternalLookup_Registry::removeTokensBasedOnUserCap($extTokensAssoc, $maxCap, $usedFTypes);
        $extTokens      = array_keys($extTokensAssoc);
        sort($extTokens);

        $this->out['extTokens']       = $extTokens;
        $this->out['extTokensAssoc']  = $extTokensAssoc;
        $this->out['externalEngines'] = ExternalLookup_Registry::getFeatures();

        $this->modelFilter or $this->modelFilter = SingletonRegistry::getModelPartnerFilter();

        /* @var $modelDictionary Model_Dictionary */
        $modelDictionary        = SingletonRegistry::getSingleInstance('Model_Dictionary');
        $dicts = $modelDictionary->listVirtualDictionaries();
        foreach ($modelDictionary->listNamesAssoc() as $k => $v) {
            $dicts[$k] = $v;
        }
        $this->out['dicts']     = $dicts;

        /* @var $modelDictionaryCols Model_DictionaryColumn */
        $modelDictionaryCols    = SingletonRegistry::getSingleInstance('Model_DictionaryColumn');
        $dictCols               = $modelDictionaryCols->listAllColumnsAssoc();
        foreach ($modelDictionary->listVirtualDictionaryColumns() as $vdId => $columns) {
            $dictCols[$vdId] = $columns;
        }
        $this->out['dictCols']  = $dictCols;

        $filterMatches = $this->modelFilter->listFilterMatches();
        $this->out['filterMatches'] = $filterMatches;
        
        if (    
                $data['partner_type'] == Model_Partner::PARTNER_TYPE_PING_POST 
                && $data['ping_post_type'] == Model_Partner::PING_POST_TYPE_POST
            ) {
                $this->out['pingPartnerId'] = $this->model->getPingPartnerId($id);
        }
    }
    //--------------------------------------------------------------------------


    public static function listSystemFieldsForDisplay($data = array()) {
        static $list = false;
        if ($list !== false) {
            return $list;
        }
        $list = SingletonRegistry::getModelCampaignField()->listSystemFields();
        $mc = SingletonRegistry::getModelCampaign();
        $mcf = SingletonRegistry::getModelCampaignField();
        $ft = $mcf->listFieldTypesForUser(Auth::getInstance()->getUserId());
        $ftypeData = array();
        foreach ($ft as $ftId) {
            $ftypeData[$ftId] = 1;
        }
        $arr = DerivedField_Registry::listVisibleFields($ftypeData);
        foreach ($arr as $s) {
            $list[] = $s;
        }
        
        if (isset($data['partner_type']) && isset($data['ping_post_type']) && 
                $data['partner_type'] == Model_Partner::PARTNER_TYPE_PING_POST && $data['ping_post_type'] == Model_Partner::PING_POST_TYPE_POST) {
            $list[] = Model_CampaignField::SYS_TRANSACTION_ID;
        }
        
        $list = array_unique($list);
        
        if ((!empty($data['partner_type']) && (int)$data['partner_type'] !== Model_Partner::PARTNER_TYPE_PING_POST) 
                || (!empty($data['ping_post_type']) && (int)$data['ping_post_type'] !== Model_Partner::PING_POST_TYPE_POST)) {
            unset($list[Model_CampaignField::SYS_FIELD_BID_PRICE_INDEX]);
        }

        sort($list);
        return $list;
    }
    //--------------------------------------------------------------------------


    protected function listSystemFieldsEx() {
        static $list = false;
        if ($list !== false) {
            return $list;
        }
        $list = SingletonRegistry::getModelCampaignField()->listSystemFieldsFull();
        $mc = SingletonRegistry::getModelCampaign();
        $mcf = SingletonRegistry::getModelCampaignField();
        $ft = $mcf->listFieldTypesForUser(Auth::getInstance()->getUserId());
        $ftypeData = array();
        foreach ($ft as $ftId) {
            $ftypeData[$ftId] = 1;
        }
        $arr = DerivedField_Registry::listAvailableTokens();
        foreach ($arr as $s) {
            $list[] = $s;
        }
        $list = array_unique($list);
        sort($list);
        return $list;
    }
    //--------------------------------------------------------------------------


    public function filtersAction() {
        $id = 0;
        $data = array();
        $this->getPartnerFromGetId($id, $data);
        App::getFrontController()->extraCss[] = 'fieldpartners.css';
        $this->modelFilter or $this->modelFilter = SingletonRegistry::getModelPartnerFilter();
        $filters = $this->modelFilter->listAllForPartner($id);
        $serialized = serialize($filters);
        foreach ($filters as & $row) {
            $row['field_type_id'] = is_null($row['field_type_id']) ? null : (int)$row['field_type_id'];
        }

        $filterActions = $this->modelFilter->listFilterActions();
        $filterMatches = $this->modelFilter->listFilterMatches();

        $this->out['allFtypes']     = SingletonRegistry::getModelCampaignField()->listFieldTypesAssoc();
        $this->out['systemFields']  = $this->listSystemFieldsForDisplay($data);
        $this->out['ftypes']        = $this->getFtypes(Auth::getInstance()->getUserId());


        $extTokensAssoc = ExternalLookup_Registry::listTokens2Names();
        $extTokens      = array_keys($extTokensAssoc);
        sort($extTokens);

        $this->out['extTokens']       = $extTokens;
        
        // remove "Change success url to" option if Ping partner
        if ($data['ping_post_type'] == Model_Partner::PING_POST_TYPE_PING) {
            unset ($filterActions[Model_PartnerFilter::FILTER_ACTION_CHANGE_SUCCESS_URL]);
        }
        
        $this->out['filterActions'] = $filterActions;
        $this->out['filterMatches'] = $filterMatches;

        $this->out['filters']       = $origFilters = $filters;
        $this->out['dataList']      = SingletonRegistry::getSingleInstance('Model_DataList')->listAllDataListToSelect();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $old = $this->normalizeFilterPostValues('old', $id, $data);
            $new = $this->normalizeFilterPostValues('new', $id, $data);

            //######################################################################
            // START TRANSACTION
            //######################################################################
            $this->modelFilter->startTransaction();
            foreach ($old as $fid => $existingRow) {
                if (isset($filters[$fid])) {
                    $this->modelFilter->updateDiff($fid, $existingRow, $filters[$fid]);
                    unset($filters[$fid]);
                }
            }
            $this->modelFilter->deleteGroup(array_keys($filters));
            foreach ($new as $newRow) {
                unset($newRow['id']);
                $this->modelFilter->insert($newRow);
            }
            $this->modelFilter->commit();
            //######################################################################
            // COMMIT
            //######################################################################

            if ($serialized != serialize($this->modelFilter->listAllForPartner($id))) {
                SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Partner rules updated');
                $this->sendMessageIfThereAreDependentTemplates($id, 'Partner rules changed', 'filters');
            }

            $params = $get = array();
            if (isset($_POST['redir'])) {
                $r = $_POST['redir'];
                if (in_array($r, array('edit', 'fields', 'caps', 'template'))) {
                    $params['action'] = $r;
                    $get['id']        = $id;
                } else if ($r === 'ping') {
                    $pingPartnerId = $this->model->getPingPartnerId($id);
                    if (empty($pingPartnerId)) {
                        $params['action'] = 'add';
                        $get['ping_post_type'] = 'ping';
                        $get['post_partner_id'] = $id;   
                    } else {
                        $params['action'] = 'filters';
                        $get['id'] = $pingPartnerId;
                    }
                    
                } else if ($r === 'post') {
                    $params['action'] = 'filters';
                    $get['id']        = $data['post_partner_id'];
                }
            }
            if ($params) {
                App::getFrontController()->redirectToCP($this, $params, $get);
            } else {
                $this->redirectToCorrectIndexPage($id);
            }
        }
    }
    //--------------------------------------------------------------------------
    
    
    public function capsAction() {
        $id = 0;
        $data = array();
        $this->getPartnerFromGetId($id, $data);
        
        if ($data['ping_post_type'] == Model_Partner::PING_POST_TYPE_PING) {
            App::getFrontController()->redirectToCP($this, array('action' => 'caps'), array('id' => $data['post_partner_id']));
        }
        
        App::getFrontController()->extraCss[] = 'fieldpartners.css';
        
        $this->modelCap = SingletonRegistry::getModelPartnerCap();
        $this->out['caps'] = $this->modelCap->listAllForPartner($id);
        $this->out['capTypes'] = Model_PartnerCap::getCapTypes();
        $this->out['intervals'] = Model_PartnerCap::getIntervals();
        
        $caps = $this->modelCap->listAllForPartner($id);
        $serialized = serialize($caps);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $old = $this->normalizeCapPostValues('old', $id, $data);
            $new = $this->normalizeCapPostValues('new', $id, $data);

            //######################################################################
            // START TRANSACTION
            //######################################################################
            $this->modelCap->startTransaction();
            foreach ($old as $cid => $existingRow) {
                if (isset($caps[$cid])) {
                    $this->modelCap->updateDiff($cid, $existingRow, $caps[$cid]);
                    unset($caps[$cid]);
                }
            }
            $this->modelCap->deleteGroup(array_keys($caps));
            foreach ($new as $newRow) {
                unset($newRow['id']);
                $this->modelCap->insert($newRow);
            }
            $this->modelCap->commit();
            //######################################################################
            // COMMIT
            //######################################################################

            if ($serialized != serialize($this->modelCap->listAllForPartner($id))) {
                SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Partner caps updated');
            }

            $params = $get = array();
            if (isset($_POST['redir'])) {
                $r = $_POST['redir'];
                if (in_array($r, array('edit', 'fields', 'filters', 'template'))) {
                    $params['action'] = $r;
                    $get['id']        = $id;
                } else if ($r === 'ping') {
                    $pingPartnerId = $this->model->getPingPartnerId($id);
                    if (empty($pingPartnerId)) {
                        $params['action'] = 'add';
                        $get['ping_post_type'] = 'ping';
                        $get['post_partner_id'] = $id;   
                    } else {
                        $params['action'] = 'caps';
                        $get['id'] = $pingPartnerId;
                    }
                    
                } else if ($r === 'post') {
                    $params['action'] = 'caps';
                    $get['id']        = $data['post_partner_id'];
                }
            }
            if ($params) {
                App::getFrontController()->redirectToCP($this, $params, $get);
            } else {
                $this->redirectToCorrectIndexPage($id);
            }
        }
    }


    public function deleteAction() {
        $id = 0;
        $data = array();
        $this->getPartnerFromGetId($id, $data);
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // $this->model->delete($id);
            $this->model->markAsDeleted($id);
            SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Partner deleted');
            App::getFrontController()->redirectToCP($this);
        }
    }
    //--------------------------------------------------------------------------


    public function ajaxCopyPartnerAction () {
        App::getFrontController()->setUsePageTemplate(false);

        try {
            $_GET['id'] = isset($_POST['id']) ? $_POST['id'] : 0;

            $id = 0;
            $data = array();
            $this->getPartnerFromGetId($id, $data);
            
            $pingPostInfo = $this->model->getPingPostInfo($id);
            if (!empty($pingPostInfo['0']['partner_type']) && intval($pingPostInfo['0']['ping_post_type']) === Model_Partner::PING_POST_TYPE_PING) {
                $postPartnerData = array();
                $newName = $_POST['new_name'];
                
                $_POST['new_name'] .= Model_Partner::PING_SUFFIX;
                $postPartnerData['id'] = $pingPostInfo['0']['post_partner_id'];
                $postPartnerData['new_name'] = $newName . Model_Partner::POST_SUFFIX;
            }

            $newPingPartnerId = $this->model->copyPartner($id, $_POST['new_name'], !empty($_POST['fields']), !empty($_POST['rules']), !empty($_POST['caps']));

            if (!empty($newPingPartnerId) && !empty($postPartnerData['id'])) {
                $newPostPartnerId = $this->model->copyPartner($postPartnerData['id'], $postPartnerData['new_name'], !empty($_POST['fields']), !empty($_POST['rules']), !empty($_POST['caps']));
                
                // update post_partner_id of new Ping record
                if ($newPostPartnerId && $newPingPartnerId) {
                    $this->model->updatePostPartnerId($newPingPartnerId, $newPostPartnerId);
                }
            }
            
            SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Partner copied');
            die('OK');
        } catch (Exception $e) {
            die($e->getMessage());
        }

    }
    //--------------------------------------------------------------------------


    public function ajaxMarkPartnerAsTemplateAction() {
        App::getFrontController()->setUsePageTemplate(false);

        try {
            $auth = Auth::getInstance();
            if (!$auth->isAdmin()) {
                throw new Exception('Only admin can do this action');
            }
            $id = isset($_POST['id']) ? max(0, (int)$_POST['id']) : 0;

            $this->model or $this->model = SingletonRegistry::getModelPartner();
            if (!$this->model->get($id)) {
                throw new Exception('Invalid id ' . $id);
            }
            $industries = array();
            if (isset($_POST['industries']) && is_array($_POST['industries'])) {
                foreach ($_POST['industries'] as $s) {
                    $industries[] = (int)$s;
                }
            }

            /* @var $mpt Model_PartnerTemplate */
            $mpt  = SingletonRegistry::getSingleInstance('Model_PartnerTemplate');
            $mpt->markAsTemplate($id, (string)$_POST['name'], $industries);
            SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Partner marked as template');

            throw new Exception('OK');
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    //--------------------------------------------------------------------------


    public function ajaxDeletePartnerTemplateAction() {
        App::getFrontController()->setUsePageTemplate(false);

        try {
            $auth = Auth::getInstance();
            if (!$auth->isAdmin()) {
                throw new Exception('Only admin can do this action');
            }
            $id = isset($_POST['id']) ? max(0, (int)$_POST['id']) : 0;

            $this->model or $this->model = SingletonRegistry::getModelPartner();
            if (!$this->model->get($id)) {
                throw new Exception('Invalid id ' . $id);
            }

            /* @var $mpt Model_PartnerTemplate */
            $mpt  = SingletonRegistry::getSingleInstance('Model_PartnerTemplate');
            SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Partner unmarked as template');
            $mpt->removeTemplate($id);

            throw new Exception('OK');
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    //--------------------------------------------------------------------------


    public function ajaxUpdatePartnerTemplateAction() {
        App::getFrontController()->setUsePageTemplate(false);

        try {
            $auth = Auth::getInstance();
            if (!$auth->isAdmin()) {
                throw new Exception('Only admin can do this action');
            }
            $id = isset($_POST['id']) ? max(0, (int)$_POST['id']) : 0;

            $this->model or $this->model = SingletonRegistry::getModelPartner();
            if (!$this->model->get($id)) {
                throw new Exception('Invalid id ' . $id);
            }
            $industries = array();
            if (isset($_POST['industries']) && is_array($_POST['industries'])) {
                foreach ($_POST['industries'] as $s) {
                    $industries[] = (int)$s;
                }
            }

            /* @var $mpt Model_PartnerTemplate */
            $mpt  = SingletonRegistry::getSingleInstance('Model_PartnerTemplate');
            $mpt->renameTemplate($id, (string)$_POST['name'], $industries);
            SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Partner template settings updated');

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
            /* @var $mpt Model_PartnerTemplate */
            $mpt = SingletonRegistry::getSingleInstance('Model_PartnerTemplate');
            $templatesAssoc = $mpt->listTemplateNamesAssoc();

            if (!isset($templatesAssoc[$id])) {
                throw new Exception('Invalid id ' . $id);
            }

            $this->model or $this->model = SingletonRegistry::getModelPartner();
            if (!$this->model->get($id)) {
                throw new Exception('Invalid id ' . $id);
            }
            
            $pingPostInfo = $this->model->getPingPostInfo($id);
            if (!empty($pingPostInfo['0']['partner_type']) && intval($pingPostInfo['0']['ping_post_type']) === Model_Partner::PING_POST_TYPE_PING) {
                $postPartnerData = array();
                $newName = $_POST['new_name'];
                
                $_POST['new_name'] .= Model_Partner::PING_SUFFIX;
                $postPartnerData['id'] = $pingPostInfo['0']['post_partner_id'];
                $postPartnerData['new_name'] = $newName . Model_Partner::POST_SUFFIX;
            }
            
            $newPingPartnerId = $this->model->copyPartner($id, trim($_POST['new_name']), !empty($_POST['fields']), !empty($_POST['rules']), !empty($_POST['caps']), Auth::getInstance()->getUserId(), $id);
            
            if (!empty($newPingPartnerId) && !empty($postPartnerData['id'])) {
                $newPostPartnerId = $this->model->copyPartner($postPartnerData['id'], trim($postPartnerData['new_name']), !empty($_POST['fields']), !empty($_POST['rules']), !empty($_POST['caps']), Auth::getInstance()->getUserId(), $postPartnerData['id']);
                
                // update post_partner_id of new Ping record
                if ($newPostPartnerId && $newPingPartnerId) {
                    $this->model->updatePostPartnerId($newPingPartnerId, $newPostPartnerId);
                }
            }
            SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Created new partner from template');
            throw new Exception('OK');
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    //--------------------------------------------------------------------------


    public function ajaxDeleteAction() {
        try {
            $id = 0;
            $data = array();
            $this->getPartnerFromGetId($id, $data);
        } catch (Exception $e) {
            die($e->getMessage());
        }
        if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['id']) && ($id == $_POST['id'])) {
            // $this->model->delete($id);
            $this->model->markAsDeleted($id);
            
            // delete post partner if user delete ping.
            if (!empty($data['post_partner_id'])) {
                $this->model->markAsDeleted($data['post_partner_id']);
            }
            // delete ping partner if user delete post partner
            if ($data['partner_type'] == Model_Partner::PARTNER_TYPE_PING_POST && $data['ping_post_type'] == Model_Partner::PING_POST_TYPE_POST) {
                $pingPartnerId = $this->model->getPingPartnerId($id);
                if (!empty($pingPartnerId)) {
                    $this->model->markAsDeleted($pingPartnerId);
                }
            }
            
            SingletonRegistry::getModelSubAccount()->deleteRecordFromSubAccountRight($id, Model_SubAccount::OBJECT_TYPE_PARTNER);
            
            SingletonRegistry::getModelNotification()->add(Auth::getInstance()->getUserId(), 'Partner deleted');
            die('OK');
        }
        die('Unable to delete');
    }
    //--------------------------------------------------------------------------


    protected function getPartnerFromGetId(& $id, & $data) {
        $id = isset($_GET['id']) ? max(0, (int)$_GET['id']) : 0;
        if (!$id) {
            throw new ERequestError("Invalid 'id' parameter");
        }
        $this->model or $this->model = SingletonRegistry::getModelPartner();
        $data = $this->model->get($id);
        if (!$data) {
            throw new ERequestError("Invalid 'id' parameter");
        }
        $auth = Auth::getInstance();
        if (!$auth->isAdmin() && ($data['user_id'] != $auth->getUserId()) &&
                (!in_array($id, SingletonRegistry::getModelSubAccount()->listAllPartnerIds($auth->getUserId())))) {
            throw new EAccessDenied('Access denied');
        }
        if ($data['partner_type'] == Model_Partner::PARTNER_TYPE_PING_POST) {
            $data['name'] = trim(preg_replace('/ ?(\(post\)|\(ping\))$/', '', $data['name'], 1));
        }
        $this->out['id']   = $id;
        $this->out['data'] = $data;
    }
    //--------------------------------------------------------------------------


    protected function getFtypes($userId) {
        return SingletonRegistry::getModelCampaign()->getFTypesAssocUsedByUser($userId);
    }
    //--------------------------------------------------------------------------


    protected function getCampaignFieldDataForPartner($partnerId) {
        $modelCampaign = SingletonRegistry::getModelCampaign();
        $modelCF       = SingletonRegistry::getModelCampaignField();

        $fieldTypes    = $modelCF->listFieldTypesAssoc();
        $fieldData     = $modelCampaign->getCampaignFieldsForPartnerId($partnerId);

        $result['cnames'] = $modelCampaign->getCampaignNamesAssocForPartnerId($partnerId);

        foreach ($fieldData as $cid => $arr) {
            foreach ($arr as $row) {
                $key = $row['name'] . ' (' . $fieldTypes[$row['ft']] . ')';
                $result['data'][$key][] = $cid;
            }
        }
        return $result;
    }
    //--------------------------------------------------------------------------


    protected function normalizeFieldPostValues($section, $partnerId) {
        if (!isset($_POST['name'][$section]) || !is_array($_POST['name'][$section])) {
            return array();
        }
        $result = array();
        foreach ($_POST['name'][$section] as $id => $name) {
            $name = trim($name);
            if (!is_numeric($id)) {
                throw new ERequestError("Invalid request");
            }
            if (!preg_match('/^[\\w]{1,}(?:\\[[\\w]{0,}\\]){0,}$/', $name)) {
                throw new ERequestError("Invalid request");
            }

            $id = (int)$id;
            $arr = array(
                    'id'         => $id,
                    'name'       => $name,
                    'partner_id' => "$partnerId",
            );
            $arr['value']    = isset($_POST['val'][$section][$id])       ? trim($_POST['val'][$section][$id]) : '';
            if (isset($_POST['modifier'][$section][$id])) {
                $arr['modifier'] = str_replace("\r\n", "\n", $_POST['modifier'][$section][$id]);
            } else {
                $arr['modifier'] = '';
            }
            $result[$id]     = $arr;
        }
        return $result;
    }
    //--------------------------------------------------------------------------


    protected function normalizeFilterPostValues($section, $partnerId, $data = array()) {
        if (!isset($_POST['action'][$section]) || !is_array($_POST['action'][$section])) {
            return array();
        }
        $sysFields = $this->listSystemFieldsForDisplay($data);

        $extTokensAssoc = ExternalLookup_Registry::listTokens2Names();
        $extTokens      = array_keys($extTokensAssoc);
        sort($extTokens);
        foreach ($extTokens as $x) {
            $sysFields[] = $x;
        }


        $result = array();
        foreach ($_POST['action'][$section] as $id => $actionId) {
            if (!is_numeric($id)) {
                throw new ERequestError("Invalid request");
            }
            if (!is_numeric($actionId)) {
                throw new ERequestError("Invalid request");
            }
            if (!(isset($_POST['ftype'][$section][$id]) && (is_numeric($_POST['ftype'][$section][$id]) || in_array($_POST['ftype'][$section][$id], $sysFields)))) {
                throw new ERequestError("Invalid request");
            }
            if (!(isset($_POST['match'][$section][$id]) && is_numeric($_POST['match'][$section][$id]))) {
                throw new ERequestError("Invalid request");
            }

            $id = (int)$id;
            if (is_numeric($_POST['ftype'][$section][$id])) {
                $ftype    = (int)$_POST['ftype'][$section][$id];
                $sysField = '';
            } else {
                $ftype    = null;
                $sysField = $_POST['ftype'][$section][$id];
                if (!in_array($sysField, $sysFields)) {
                    $sysField = '';
                }
            }
            $arr = array(
                    'id'            => $id,
                    'partner_id'     => "$partnerId",
                    'filter_action' => "$actionId",
                    'field_type_id' => $ftype,
                    'system_field'  => $sysField,
                    'match_type'    => '' . (int)$_POST['match'][$section][$id],
                    'argument'      => $_POST['argument'][$section][$id],
                    'is_active'     => empty($_POST['is_active'][$section][$id]) ? '0' : '1',
            );
            
            if (abs($arr['match_type']) === 9) {
                $arr['match_value'] = $_POST['select-value'][$section][$id];
            } else {
                $arr['match_value'] = $_POST['value'][$section][$id];
            }
            $result[$id]     = $arr;
        }
        return $result;
    }
    //--------------------------------------------------------------------------
    
    
    protected function normalizeCapPostValues($section, $partnerId, $data = array()) {
        if (!isset($_POST['cap_type'][$section]) || !is_array($_POST['cap_type'][$section])) {
            return array();
        }
        
        $result = array();
        foreach ($_POST['cap_type'][$section] as $id => $capType) {
            if (!is_numeric($id)) {
                throw new ERequestError("Invalid request");
            }
            if (!is_numeric($capType)) {
                throw new ERequestError("Invalid cap type");
            }
            if (!(isset($_POST['interval'][$section][$id]) && is_numeric($_POST['interval'][$section][$id]))) {
                throw new ERequestError("Invalid interval");
            }
            if (!(isset($_POST['cap_value'][$section][$id]) && is_numeric($_POST['cap_value'][$section][$id]))) {
                throw new ERequestError("Invalid cap value");
            }
            
            if ($_POST['interval'][$section][$id] === Model_PartnerCap::INTERVAL_DAY_OF_WEEK) {
                $dayOfWeek = $_POST['day_of_week'][$section][$id];
                $timeOfDay = null;
            } else if ($_POST['interval'][$section][$id] === Model_PartnerCap::INTERVAL_TIME_OF_DAY) {
                if (!preg_match('/^\d{2}:\d{2}$/', $_POST['start_time'][$section][$id])) {
                    throw new ERequestError("Invalid start time");
                }
                if (!preg_match('/^\d{2}:\d{2}$/', $_POST['end_time'][$section][$id])) {
                    throw new ERequestError("Invalid end time");
                }
                
                $dayOfWeek = $_POST['day_of_week'][$section][$id];
                $timeOfDay = $_POST['start_time'][$section][$id] . '-' . $_POST['end_time'][$section][$id];
            } else {
                $dayOfWeek = null;
                $timeOfDay = null;
            }

            $arr = array(
                'id'            => $id,
                'partner_id'    => "$partnerId",
                'is_active'     => empty($_POST['is_active'][$section][$id]) ? '0' : '1', 
                'cap_type'      => $capType,
                'cap_value'     => $_POST['cap_value'][$section][$id], 
                'interval'      => $_POST['interval'][$section][$id], 
                'day_of_week'   => $dayOfWeek,
                'time_of_day'   => $timeOfDay
            );
            $result[$id]     = $arr;
        }
        return $result;
    }
    //--------------------------------------------------------------------------


    protected function validateFormAndThrowEDoneOnError(Form_Data $form, $id) {
        $this->model or $this->model = SingletonRegistry::getModelPartner();
        $dbName = $form->name; // partner name store in db, may be added suffix in comparision with name in view
        if ($form->name) {
            $pre = $this->model->getExistingFieldValuesForKey($id, array('name' => $form->name), array('user_id' => Auth::getInstance()->getUserId()));
            
            if ($form->partner_type == Model_Partner::PARTNER_TYPE_PING_POST || !empty($_POST['post_partner_id'])) {
                $form->name = trim(preg_replace('/ ?(\(post\)|\(ping\))?$/', '', $form->name, 1));
            }
            
            if (!empty($pre['name'])) {
                $form->addError('name', "Name '" . escapeHtml($form->name) . "' already exists"); 
            }
        }

        $postTypes = $this->model->listPostTypesAssoc();
        if ($form->posting_type == Model_Partner::POST_TYPE_EMAIL) {
            if (!filter_var($form->delivery_addr, FILTER_VALIDATE_EMAIL)) {
                $form->addError('delivery_addr', 'Invalid email');
            }
        } else {
            if (!filter_var($form->delivery_addr, FILTER_VALIDATE_URL)) {
                $form->addError('delivery_addr', 'Invalid URL');
            }
        }

        if (
            ! ($form->response_type == Model_Partner::RESPONSE_TYPE_TEXT && preg_match('/^\\$[1-9]{1,1}[\\d]{0,}$/', $form->price_per_lead))
            && ! ($form->response_type == Model_Partner::RESPONSE_TYPE_JSON && Model_Partner::isValidJsonPathExpression($form->price_per_lead))
            && ! ($form->response_type == Model_Partner::RESPONSE_TYPE_XML && Model_Partner::isValidXPathExpression($form->price_per_lead))
      
        ) {
            $form->checkNumeric('price_per_lead', 0);
        } elseif ($form->parse_response === '0') {
            if (!is_numeric($form->price_per_lead)) {
                $form->addError('price_per_lead', 'Cannot use response field if "Parse Response" is turned off');
            }
        }
        
        // validate curl_timeout (in range 0 - 60)
        if ( ! empty($form->curl_timeout)) {
            $form->checkNumeric('curl_timeout', 0, 60);
        } else {
            $form->curl_timeout = null;
        }
        
        if ( ! empty($form->post_partner_id) && ! empty ($form->auction_transaction_id)) {
            if ($form->response_type == Model_Partner::RESPONSE_TYPE_TEXT && !preg_match('/^\\$[1-9]{1,1}[\\d]{0,}$/', $form->auction_transaction_id)) {
                $form->addError('auction_transaction_id', 'Please enter $[number] for text response');
            } else if ($form->response_type == Model_Partner::RESPONSE_TYPE_JSON && !Model_Partner::isValidJsonPathExpression($form->auction_transaction_id)) {
                $form->addError('auction_transaction_id', 'Please enter valid json path');
            } else if ($form->response_type == Model_Partner::RESPONSE_TYPE_XML && !Model_Partner::isValidXPathExpression($form->auction_transaction_id)) {
                $form->addError('auction_transaction_id', 'Please enter valid xpath');
            }
        } 
        
        if ($form->posting_type == Model_Partner::POST_TYPE_XML_FIELD && $form->xml_field_name == '') {
            $form->addError('xml_field_name', 'Please enter XML field name value');
        }
        
        if ($form->parse_response) {
            if ($form->delimiter == '' && $form->response_type == Model_Partner::RESPONSE_TYPE_TEXT) {
                $form->addError('delimiter', 'Please enter delimiter character');
            }
            if ($form->success_url && !filter_var($form->success_url, FILTER_VALIDATE_URL)) {
                if ($form->response_type == Model_Partner::RESPONSE_TYPE_TEXT && !preg_match('/^\\$[1-9]{1,1}[\\d]{0,}/', $form->success_url)) {
                    $form->addError('success_url', 'Enter either a valid URL or $[number]');    
                } else if ($form->response_type == Model_Partner::RESPONSE_TYPE_JSON && !preg_match('/^\[[a-zA-Z0-9\$\.\*@()+_-]+\]/', $form->success_url) && !Model_Partner::isValidJsonPathExpression($form->success_url)) {
                    $form->addError('success_url', 'Enter either a valid URL or a jsonpath'); 
                } else if ($form->response_type == Model_Partner::RESPONSE_TYPE_XML && !preg_match('/^\[[a-zA-Z0-9\/\.\*@()+_-]+\]/', $form->success_url) && !Model_Partner::isValidXPathExpression($form->success_url)) {
                    $successUrlWithoutBracket = preg_replace('/\[[^\]]+\]/', '', $form->success_url);
                    if (!filter_var($successUrlWithoutBracket, FILTER_VALIDATE_URL)) {
                        $form->addError('success_url', 'Enter either a valid URL or a xpath');
                    }                
                }
            }
        } else {
            if ($form->success_url && !filter_var($form->success_url, FILTER_VALIDATE_URL)) {
                $form->addError('success_url', 'Invalid URL');
            }
        }
        $settingIDs = array();
        foreach (Model_PartnerSettings::listCustomSettings() as $k => $arr) {
            $settingIDs = $settingIDs + array_keys($arr);
        }
        $settingIDs = array_unique($settingIDs);
        $this->partnerSettings = array();
        if (isset($_POST['partner_settings']) && is_array($_POST['partner_settings'])) {
            foreach ($_POST['partner_settings'] as $k => $val) {
                if (!is_string($val)) {
                    continue;
                }
                if (!in_array($k, $settingIDs)) {
                    continue;
                }
                $this->partnerSettings[$k] = trim($val);
            }
        }
        
        if (!$form->getAllErrors()) {
            $form->name = $dbName;
        }
        
        $form->throwDoneIfErrors();
    }
    //--------------------------------------------------------------------------


    private function redirectToCorrectIndexPage($id) {
        $pos = $this->model->indexOfIdSortedByName(Auth::getInstance()->getUserId(), $id);
        $page = (int)((int)$pos / self::PER_PAGE);
        App::getFrontController()->redirectToCP($this, array(), $page ? array('page' => $page + 1) : array());
    }
    //--------------------------------------------------------------------------


    /**
     * @return Form_Data
     */
    protected function form() {
        $this->model or $this->model = SingletonRegistry::getModelPartner();

        $form = new Form_Data();
        $form->add('text',     'name',            '', array('maxlength' => 64,  '* required' => true, '* trim' => true));
        $form->add('select',   'posting_type',    '', array('* items' => $this->model->listPostTypesAssoc()));
        $form->add('text',     'delivery_addr',   '', array('maxlength' => 255, '* required' => true, '* trim' => true));
        $form->add('text',     'success_keyword', '', array('maxlength' => 32,  '* required' => true, '* trim' => true));
        $form->add('text',     'failure_keyword', '', array('maxlength' => 32,  '* trim' => true, 'id' => 'failure-keyword'));

        $form->add('select',   'response_type',   '', array('* items' => $this->model->listResponseTypesAssoc(), 'id' => 'response-type'));
        $form->add('select',   'partner_type',   '', array('* items' => Model_Partner::$partnerTypes, 'id' => 'partner-type'));
        $form->add('select',   'parse_response',  '', array('* items' => array('0' => 'No', '1' => 'Yes'), 'id' => 'parse-response'));
        $form->add('text',     'delimiter',       '', array('maxlength' => 32,  'id' => 'delimter'));
        
        $form->add('text',     'xml_field_name',  '', array('maxlength' => 64,  'id' => 'xml-field-name-value'));
        $form->add('text',     'transaction_id_field_name',  '', array('maxlength' => 32,  'id' => 'transaction-id-field-name-value'));
        
        $form->add('text',     'success_url',     '', array('maxlength' => 1024, '* trim' => true));
        $form->add('text',     'price_per_lead',  '', array('maxlength' => 255,   '* required' => true, '* trim' => true));
        $form->add('text',     'post_partner_id',  '', array('maxlength' => 20));
        $form->add('text',     'auction_transaction_id',  '', array('maxlength' => 255, 'id' => 'auction-transaction-id'));
        
        // curl timeout
        $form->add('select',     'curl_timeout',     '', array('* items' => array('0' => 'Default (5)', '10' => '10 seconds',
                                                                                 '15' => '15 seconds', '30' => '30 seconds',
                                                                                 '45' => '45 seconds', '60' => '60 seconds'), 'id' => 'curl-timeout'));

        // Advanced settings
        $form->add('checkbox', 'should_retry',    '');
        $form->add('text',     'username_failed_keyword', '', array('* trim' => true));
        
        $timezoneIdentifiers = Controller_Dashboard::listTimeZones();
        $timezoneArray = array();
        foreach ($timezoneIdentifiers as $timezone){
            if (is_array($timezone)) {
                $timezoneArray[$timezone[0]] = str_replace('&amp;', '&', $timezone[1]);
            } else {
                $timezoneArray['disabled'] = null;
            }
        }
        $form->add('select',    'partner_timezone',     '', array('* items' => $timezoneArray, 'id' => 'partner-timezone'));
        
        $pf = new PageFragment_FormAuto($this, $form, false);

        $this->out['form'] = $form;
        $this->out['pf']   = $pf;

        return $form;
    }
    //--------------------------------------------------------------------------


    protected function sendMessageIfThereAreDependentTemplates($partnerId, $messageStart, $action) {
        /* @var $mpt Model_PartnerTemplate */
        $mpt = SingletonRegistry::getSingleInstance('Model_PartnerTemplate');

        $tplRow = $mpt->getTemplateForPartnerId($partnerId);
        if (!$tplRow) {
            return;
        }
        $arr = $this->model->listAllPartnersCreatedFromTemplate($tplRow['id']);
        if (!$arr) {
            return;
        }
        $userTemplates = array();
        foreach ($arr as $row) {
            if (empty($userTemplates[$row['user_id']])) {
                $userTemplates[$row['user_id']] = array();
            }
            $userTemplates[$row['user_id']][$row['id']]= $row['name'];
        }
        foreach ($userTemplates as $userId => $templates) {
            $data = array();
            foreach ($templates as $id => $name) {
                $data[] = "<a href=\"" . App::getFrontController()->urlFor('Partners', array('action' => $action), array('id' => $id)) . '">' . escapeHtml($name) . '</a>';
            }
            if ($data) {
                $message = $messageStart . " for partner template '$tplRow[template_name]' which was used as template to create your partner" . ((sizeof($data) > 1) ? 's: ' : ' ') . implode(", ", $data);
                $this->sendMessage($userId, $message);
            }
        }
    }
    //--------------------------------------------------------------------------


    protected function sendMessage($userId, $message) {
        /* @var $messages Model_SystemMessage */
        $messages = SingletonRegistry::getSingleInstance('Model_SystemMessage');
        $data = array('user_id' => $userId, 'text' => $message, 'should_escape' => false);
        $messages->insert($data);
    }
    //--------------------------------------------------------------------------


    public function preRun() {
        parent::preRun();
        $this->pageTitle = 'Partners';
        App::getFrontController()->activeSection = FrontController::SECTION_PARTNERS;
    }
    //--------------------------------------------------------------------------
    
    public function addHttpToUrlIfNotValid($url, $responseType) {

        $validSuccessUrl = false;
        if (strpos($url, '://') !== false) {
            $validSuccessUrl = true;
        } else if ($responseType == Model_Partner::RESPONSE_TYPE_TEXT) {
            if (strpos($url, '$') === 0) {
                $validSuccessUrl = true;
            }
        } else if ($responseType == Model_Partner::RESPONSE_TYPE_JSON) {
            if (Model_Partner::isValidJsonPathExpression($url) || preg_match('/^\[[a-zA-Z0-9\$\.\*@()+_-]+\]/', $url)) {
                $validSuccessUrl = true;
            }
        } else if ($responseType == Model_Partner::RESPONSE_TYPE_XML) {
            if (Model_Partner::isValidXPathExpression($url) || preg_match('/^\[[a-zA-Z0-9\/\.\*@()+_-]+\]/', $url)) {
                $validSuccessUrl = true;
            }
        }

        if (!$validSuccessUrl) {
            return 'http://' . $url;
        }
        return $url;
    }
    
    public function ajaxGetPartnerFieldsAction() {
        
        $id = is_numeric($_GET['id']) ? $_GET['id'] : '0';
        $this->modelField = SingletonRegistry::getModelPartnerField();
        $fields = $this->modelField->listAllForPartner($id);

        $result = array_values($fields);
        echo json_encode($result);
        die;
    }
}
