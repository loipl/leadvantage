<?php

/**
 * @desc This class is used for caching the results of function calls for CrudModel classes.
 */
class DbCache_Wrapper {
    private $model;
    private $name = '';

    /**
     * @desc This array has data for functions whose results need to be cached. Function is marked
     * for caching if it has the word @Cached in phpDoc. There's a unit test that will check if this
     * array is consistent with markings in code.
     */
    public static $cachedFunctions = array (
        'Model_Campaign' => array (
            'count' => 1,
            'countWhere' => 1,
            'get' => 1,
            'getCampaignFieldsForPartnerId' => 1,
            'getCampaignFromHash' => 1,
            'getCampaignNamesAssocForPartnerId' => 1,
            'getFTypesAssocUsedByUser' => 1,
            'getWhere' => 1,
            'listAll' => 1,
            'listAllWhere' => 1,
            'listCampaigsAssocPerUser' => 1,
            'listDeliverySettings' => 1,
            'listGroup' => 1,
            'listPage' => 1,
            'listPageWhere' => 1,
            'listShadowsOfCampaign' => 1,
        ),
        'Model_CampaignField' => array (
            'count' => 1,
            'countWhere' => 1,
            'get' => 1,
            'getWhere' => 1,
            'listAll' => 1,
            'listAllForCampaign' => 1,
            'listAllWhere' => 1,
            'listFieldTypesAssoc' => 1,
            'listFieldTypesAssocFull' => 1,
            'listFieldTypesForUser' => 1,
            'listGroup' => 1,
            'listPage' => 1,
            'listPageWhere' => 1,
            'listValidationOverrides' => 1,
        ),
        'Model_CampaignSettings' => array (
            'count' => 1,
            'countWhere' => 1,
            'get' => 1,
            'getWhere' => 1,
            'listAll' => 1,
            'listAllWhere' => 1,
            'listForAttachSettings' => 1,
            'listGroup' => 1,
            'listPage' => 1,
            'listPageWhere' => 1,
            'listSettings' => 1,
        ),
        'Model_CampaignTemplate' => array (
            'count' => 1,
            'countWhere' => 1,
            'get' => 1,
            'getWhere' => 1,
            'listAll' => 1,
            'listAllWhere' => 1,
            'listGroup' => 1,
            'listPage' => 1,
            'listPageWhere' => 1,
        ),
        'Model_ConfigPostsPerLevel' => array (
            'count' => 1,
            'countWhere' => 1,
            'get' => 1,
            'getWhere' => 1,
            'listAll' => 1,
            'listAllWhere' => 1,
            'listGroup' => 1,
            'listLevelsAssoc' => 1,
            'listPage' => 1,
            'listPageWhere' => 1,
        ),
        'Model_Country' => array (
            'count' => 1,
            'countWhere' => 1,
            'get' => 1,
            'getWhere' => 1,
            'listAll' => 1,
            'listAllWhere' => 1,
            'listCountriesAssoc' => 1,
            'listGroup' => 1,
            'listPage' => 1,
            'listPageWhere' => 1,
        ),
        'Model_EngineConfig' => array (
            'count' => 1,
            'countWhere' => 1,
            'get' => 1,
            'getWhere' => 1,
            'listAll' => 1,
            'listAllWhere' => 1,
            'listGroup' => 1,
            'listPage' => 1,
            'listPageWhere' => 1,
            'listSettings' => 1,
        ),
        'Model_FieldType' => array (
            'count' => 1,
            'countWhere' => 1,
            'get' => 1,
            'getWhere' => 1,
            'listAll' => 1,
            'listAllWhere' => 1,
            'listAssoc' => 1,
            'listFieldTypesAssoc' => 1,
            'listGroup' => 1,
            'listIndustries' => 1,
            'listIndustriesGroup' => 1,
            'listPage' => 1,
            'listPageWhere' => 1,
        ),
        'Model_Industry' => array (
            'count' => 1,
            'countWhere' => 1,
            'get' => 1,
            'getWhere' => 1,
            'listAbbreviationsAssoc' => 1,
            'listAll' => 1,
            'listAllWhere' => 1,
            'listAssoc' => 1,
            'listGroup' => 1,
            'listPage' => 1,
            'listPageWhere' => 1,
        ),
        'Model_Partner' => array (
            'count' => 1,
            'countWhere' => 1,
            'get' => 1,
            'getWhere' => 1,
            'listAll' => 1,
            'listAllWhere' => 1,
            'listGroup' => 1,
            'listPage' => 1,
            'listPageWhere' => 1,
        ),
        'Model_PartnerField' => array (
            'count' => 1,
            'countWhere' => 1,
            'get' => 1,
            'getWhere' => 1,
            'listAll' => 1,
            'listAllForPartner' => 1,
            'listAllWhere' => 1,
            'listAssocForPartnerGroup' => 1,
            'listGroup' => 1,
            'listPage' => 1,
            'listPageWhere' => 1,
        ),
        'Model_PartnerFilter' => array (
            'count' => 1,
            'countWhere' => 1,
            'get' => 1,
            'getWhere' => 1,
            'listAll' => 1,
            'listAllForPartner' => 1,
            'listAllWhere' => 1,
            'listAssocForPartnerGroup' => 1,
            'listGroup' => 1,
            'listPage' => 1,
            'listPageWhere' => 1,
        ),
        'Model_PartnerSettings' => array (
            'count' => 1,
            'countWhere' => 1,
            'get' => 1,
            'getWhere' => 1,
            'listAll' => 1,
            'listAllWhere' => 1,
            'listForAttachSettings' => 1,
            'listGroup' => 1,
            'listPage' => 1,
            'listPageWhere' => 1,
            'listSettings' => 1,
        ),
        'Model_PartnerTemplate' => array (
            'count' => 1,
            'countWhere' => 1,
            'get' => 1,
            'getWhere' => 1,
            'listAll' => 1,
            'listAllWhere' => 1,
            'listGroup' => 1,
            'listPage' => 1,
            'listPageWhere' => 1,
        ),
        'Model_User' => array (
            'count' => 1,
            'countWhere' => 1,
            'get' => 1,
            'getWhere' => 1,
            'listAll' => 1,
            'listAllWhere' => 1,
            'listGroup' => 1,
            'listPage' => 1,
            'listPageWhere' => 1,
        ),
    );

    public static $defaultArguments = array (
        'Model_Campaign' => array (
            'copyCampaign' => array (
                4 => false,
            ),
            'get' => array (
                1 => 3,
            ),
            'getWhere' => array (
                1 => 3,
            ),
            'listAll' => array (
                0 => '',
            ),
            'listAllWhere' => array (
                1 => '',
            ),
            'listGroup' => array (
                1 => '',
                2 => true,
            ),
            'listPage' => array (
                2 => '',
            ),
            'listPageWhere' => array (
                3 => '',
            ),
            'listShadowsOfCampaign' => array (
                1 => false,
                2 => false,
            ),
            'updateDiff' => array (
                3 => false,
            ),
        ),
        'Model_CampaignField' => array (
            'get' => array (
                1 => 3,
            ),
            'getWhere' => array (
                1 => 3,
            ),
            'listAll' => array (
                0 => '',
            ),
            'listAllForCampaign' => array (
                1 => 'id',
            ),
            'listAllWhere' => array (
                1 => '',
            ),
            'listGroup' => array (
                1 => '',
                2 => true,
            ),
            'listPage' => array (
                2 => '',
            ),
            'listPageWhere' => array (
                3 => '',
            ),
        ),
        'Model_CampaignSettings' => array (
            'get' => array (
                1 => 3,
            ),
            'getWhere' => array (
                1 => 3,
            ),
            'listAll' => array (
                0 => '',
            ),
            'listAllWhere' => array (
                1 => '',
            ),
            'listGroup' => array (
                1 => '',
                2 => true,
            ),
            'listPage' => array (
                2 => '',
            ),
            'listPageWhere' => array (
                3 => '',
            ),
        ),
        'Model_CampaignTemplate' => array (
            'get' => array (
                1 => 3,
            ),
            'getWhere' => array (
                1 => 3,
            ),
            'listAll' => array (
                0 => '',
            ),
            'listAllWhere' => array (
                1 => '',
            ),
            'listGroup' => array (
                1 => '',
                2 => true,
            ),
            'listPage' => array (
                2 => '',
            ),
            'listPageWhere' => array (
                3 => '',
            ),
            'listTemplateNamesAssocForIndustries' => array (
                0 =>  array(),
            ),
            'markAsTemplate' => array (
                2 =>  array(),
            ),
            'renameTemplate' => array (
                2 =>  array(),
            ),
        ),
        'Model_ConfigPostsPerLevel' => array (
            'get' => array (
                1 => 3,
            ),
            'getWhere' => array (
                1 => 3,
            ),
            'listAll' => array (
                0 => '',
            ),
            'listAllWhere' => array (
                1 => '',
            ),
            'listGroup' => array (
                1 => '',
                2 => true,
            ),
            'listPage' => array (
                2 => '',
            ),
            'listPageWhere' => array (
                3 => '',
            ),
        ),
        'Model_Country' => array (
            'get' => array (
                1 => 3,
            ),
            'getWhere' => array (
                1 => 3,
            ),
            'listAll' => array (
                0 => '',
            ),
            'listAllWhere' => array (
                1 => '',
            ),
            'listCountriesAssoc' => array (
                0 => true,
            ),
            'listGroup' => array (
                1 => '',
                2 => true,
            ),
            'listPage' => array (
                2 => '',
            ),
            'listPageWhere' => array (
                3 => '',
            ),
        ),
        'Model_EngineConfig' => array (
            'get' => array (
                1 => 3,
            ),
            'getWhere' => array (
                1 => 3,
            ),
            'listAll' => array (
                0 => '',
            ),
            'listAllWhere' => array (
                1 => '',
            ),
            'listGroup' => array (
                1 => '',
                2 => true,
            ),
            'listPage' => array (
                2 => '',
            ),
            'listPageWhere' => array (
                3 => '',
            ),
        ),
        'Model_FieldType' => array (
            'get' => array (
                1 => 3,
            ),
            'getWhere' => array (
                1 => 3,
            ),
            'listAll' => array (
                0 => '',
            ),
            'listAllWhere' => array (
                1 => '',
            ),
            'listAssoc' => array (
                0 => 'name',
            ),
            'listFieldTypesAssoc' => array (
                0 => false,
            ),
            'listGroup' => array (
                1 => '',
                2 => true,
            ),
            'listPage' => array (
                2 => '',
            ),
            'listPageWhere' => array (
                3 => '',
            ),
        ),
        'Model_Industry' => array (
            'get' => array (
                1 => 3,
            ),
            'getWhere' => array (
                1 => 3,
            ),
            'listAbbreviationsAssoc' => array (
                0 => true,
            ),
            'listAll' => array (
                0 => '',
            ),
            'listAllWhere' => array (
                1 => '',
            ),
            'listAssoc' => array (
                0 => true,
            ),
            'listGroup' => array (
                1 => '',
                2 => true,
            ),
            'listPage' => array (
                2 => '',
            ),
            'listPageWhere' => array (
                3 => '',
            ),
        ),
        'Model_Partner' => array (
            'copyPartner' => array (
                4 => false,
                5 => false,
            ),
            'get' => array (
                1 => 3,
            ),
            'getWhere' => array (
                1 => 3,
            ),
            'listAll' => array (
                0 => '',
            ),
            'listAllPartnerNamesAssoc' => array (
                0 => false,
            ),
            'listAllWhere' => array (
                1 => '',
            ),
            'listGroup' => array (
                1 => '',
                2 => true,
            ),
            'listPage' => array (
                2 => '',
            ),
            'listPageForUser' => array (
                3 => 'name',
            ),
            'listPageWhere' => array (
                3 => '',
            ),
        ),
        'Model_PartnerField' => array (
            'get' => array (
                1 => 3,
            ),
            'getWhere' => array (
                1 => 3,
            ),
            'listAll' => array (
                0 => '',
            ),
            'listAllForPartner' => array (
                1 => 'id',
            ),
            'listAllWhere' => array (
                1 => '',
            ),
            'listGroup' => array (
                1 => '',
                2 => true,
            ),
            'listPage' => array (
                2 => '',
            ),
            'listPageWhere' => array (
                3 => '',
            ),
        ),
        'Model_PartnerFilter' => array (
            'get' => array (
                1 => 3,
            ),
            'getWhere' => array (
                1 => 3,
            ),
            'listAll' => array (
                0 => '',
            ),
            'listAllForPartner' => array (
                1 => 'id',
            ),
            'listAllWhere' => array (
                1 => '',
            ),
            'listAssocForPartnerGroup' => array (
                1 => false,
            ),
            'listGroup' => array (
                1 => '',
                2 => true,
            ),
            'listPage' => array (
                2 => '',
            ),
            'listPageWhere' => array (
                3 => '',
            ),
        ),
        'Model_PartnerSettings' => array (
            'get' => array (
                1 => 3,
            ),
            'getWhere' => array (
                1 => 3,
            ),
            'listAll' => array (
                0 => '',
            ),
            'listAllWhere' => array (
                1 => '',
            ),
            'listGroup' => array (
                1 => '',
                2 => true,
            ),
            'listPage' => array (
                2 => '',
            ),
            'listPageWhere' => array (
                3 => '',
            ),
        ),
        'Model_PartnerTemplate' => array (
            'get' => array (
                1 => 3,
            ),
            'getWhere' => array (
                1 => 3,
            ),
            'listAll' => array (
                0 => '',
            ),
            'listAllWhere' => array (
                1 => '',
            ),
            'listGroup' => array (
                1 => '',
                2 => true,
            ),
            'listPage' => array (
                2 => '',
            ),
            'listPageWhere' => array (
                3 => '',
            ),
            'listTemplateNamesAssocForIndustries' => array (
                0 =>  array(),
            ),
            'markAsTemplate' => array (
                2 =>  array(),
            ),
            'renameTemplate' => array (
                2 =>  array(),
            ),
        ),
        'Model_User' => array (
            'get' => array (
                1 => 3,
            ),
            'getWhere' => array (
                1 => 3,
            ),
            'listAll' => array (
                0 => '',
            ),
            'listAllWhere' => array (
                1 => '',
            ),
            'listGroup' => array (
                1 => '',
                2 => true,
            ),
            'listPage' => array (
                2 => '',
            ),
            'listPageWhere' => array (
                3 => '',
            ),
            'listUsernamesAssoc' => array (
                0 => false,
            ),
            'makeSureWordpressUsersExistInPingtreeUsersTable' => array (
                0 => false,
            ),
        ),
    );


    public function __construct(CrudModelCaching $model) {
        $this->model = $model;
        $this->name  = get_class($model);
    }
    //--------------------------------------------------------------------------


    public function __call($name, $arguments) {

        // Add values of default arguments for the function being called
        // if values are not already present in $arguments array

        if (!empty(self::$defaultArguments[$this->name][$name])) {
            foreach (self::$defaultArguments[$this->name][$name] as $k => $v) {
                if (!array_key_exists($k, $arguments)) {
                    $arguments[$k] = $v;
                }
            }
        }

        // If caching is turned on (DB::$cache is not null) we wrap the call through
        // caching, otherwise just call the function.
        if (is_callable(array($this->model, $name))) {
            if(isset(self::$cachedFunctions[$this->name][$name])) {
                if (DB::$cache) {
                    return DB::$cache->getWithCallback($this->model, $name, $arguments);
                }
            }
            $callback = array($this->model, $name);
            return call_user_func_array($callback, $arguments);
        }
        trigger_error("Missing function in class inherited from CrudModelCaching " . get_class($this->model) . '::' . $name, E_ERROR);
    }
    //--------------------------------------------------------------------------


    public function attachSettingsForGroup(array & $list) {
        return $this->model->attachSettingsForGroup($list);
    }
    //--------------------------------------------------------------------------
}