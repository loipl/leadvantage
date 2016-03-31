<?php

/**
 * @desc Data in sources field is space-delimited list of 5 dash-delimited numbers.<br>
 * userId-campaignId-delay-ipOverrideFType-refererOverrideFType
 */
class Helper_Export {

    /**
     * @var Model_ExportFeed
     */
    private $modelExport;

    /**
     * @var Model_LogIncoming
     */
    private $logIncoming;

    /**
     * @var Model_GeoipLocation
     */
    private $geoip;

    /**
     * @var Model_Profile
     */
    private $profile;

    private $skipGeoip = false;

    private $ftAssoc = array();

    private $userSettings = array();

    private $campaignSettings = array();

    private $job = array();

    private $sources;

    private $days;

    private $tldInclude = false;

    private $tldList = array();

    private $needsTldCheck = false;

    private $countryInclude = false;

    private $countryList = array();

    private $needsCountryCheck = false;

    private $needsFilterCheck = false;

    private $generatedData = array();

    public $justGatherData = false;


    public static $allSysFeedColumns = array('lead_id', 'email', 'remote_ip', 'http_referer', 'timestamp', 'country_code', 'user_id', 'campaign_id');

    const S_USER_ID      = 0;
    const S_CAMPAIGN_ID  = 1;
    const S_DELAY        = 2;
    const S_IP_OVERRIDE  = 3;
    const S_REF_OVERRIDE = 4;


    public function doExport(array $get) {
        $this->getJob($get);

        $this->logIncoming = SingletonRegistry::getModelLogIncoming();

        $this->profile = SingletonRegistry::getSingleInstance('Model_Profile');

        $this->skipGeoip = !empty($get['nogeoip']);
        if (!$this->skipGeoip) {
            $this->geoip = SingletonRegistry::getModelGeoipLocation();
        }

        if (!$this->justGatherData) {
            header("Content-Type: Text/CSV");
            header("Content-Disposition: attachment;filename=export-{$this->job['id']}.csv");
        }


        $this->exportToCsv();
    }
    //--------------------------------------------------------------------------


    private function getJob(array $get) {
        if (isset($get['key']) && is_string($get['key'])) {
            $this->modelExport = SingletonRegistry::getSingleInstance('Model_ExportFeed');
            $this->job = $this->modelExport->getWhere(array('key' => $get['key']));
        }

        if (empty($this->job)) {
            throw new EExplainableError("Missing or invalid key parameter");
        }

        if (empty($this->job['is_active'])) {
            throw new EExplainableError("Feed is not active");
        }

        $this->sources = $this->job['sources'];
        if (empty($this->sources)) {
            throw new EExplainableError("Feed is not configured correctly, please inform admin");
        }

        if (isset($get['days']) && is_numeric($get['days'])) {
            $this->days = (int)$get['days'];
        } else {
            $this->days = $this->job['default_days'];
        }

    }
    //--------------------------------------------------------------------------


    /**
     * @return array
     */
    public function getGeneratedData() {
        return $this->generatedData;
    }
    //--------------------------------------------------------------------------


    public function exportToCsv() {
        $campaigns = $this->extractCampaignIDsFromSources();
        $frontRow  = $this->initFieldTypesUsedForJobAndGetFrontRow();

        if ($this->justGatherData) {
            $this->generatedData[] = $frontRow;
        } else {
            $columnNamesLine = $this->outputCSV($frontRow);
            echo $columnNamesLine;
        }

        $this->prepareFilterVars();
        foreach ($campaigns as $campaignId) {
            $this->exportCampaignToCSV($campaignId);
        }
    }
    //--------------------------------------------------------------------------


    private function extractCampaignIDsFromSources() {
        $this->userSettings     = array();
        $this->campaignSettings = array();

        if (!$this->sources) {
            return array();
        }

        $campaignIDs = $this->getStandaloneCampaignsAndWriteSettings();
        $campaignIDs = $this->appendAllCampaignsForUserIDs($campaignIDs);

        $campaignIDs = array_unique($campaignIDs);
        sort($campaignIDs);
        return $campaignIDs;
    }
    //--------------------------------------------------------------------------


    private function getStandaloneCampaignsAndWriteSettings() {
        $campaignIDs = array();
        foreach (explode(' ', $this->sources) as $sourceSetting) {
            $row        = explode('-', $sourceSetting);
            $campaignId = (int)$row[self::S_CAMPAIGN_ID];

            // Campaign id wil be zero if we want all campaigns for the user
            if ($campaignId) {
                $campaignIDs[] = $campaignId;
                $this->campaignSettings[$campaignId] = $sourceSetting;
            }
        }
        return $campaignIDs;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Gets IDs of users for which we are exporting all campaigns
     */
    private function getUserIdsWhichExportAllCampaignsAndWriteSettings() {
        $userIDs = array();
        foreach (explode(' ', $this->sources) as $sourceSetting) {
            $row        = explode('-', $sourceSetting);
            $userId     = (int)$row[self::S_USER_ID];
            $campaignId = (int)$row[self::S_CAMPAIGN_ID];

            // Campaign id wil be zero if we want all campaigns for the user
            if ($campaignId == 0) {
                $userIDs[] = $userId;
                $this->userSettings[$userId] = $sourceSetting;
            }
        }
        return array_unique($userIDs);
    }
    //--------------------------------------------------------------------------


    private function appendAllCampaignsForUserIDs(array $campaignIDs) {
        $userIDs = $this->getUserIdsWhichExportAllCampaignsAndWriteSettings();

        if ($userIDs) {
            $campaignsAssoc = SingletonRegistry::getModelCampaign()->listCampaigsAssocPerUser();
            foreach ($userIDs as $userId) {
                if (isset($campaignsAssoc[$userId])) {
                    foreach ($campaignsAssoc[$userId] as $campaign) {
                        $campaignIDs[] = (int)$campaign['id'];
                    }
                }
            }
        }
        return $campaignIDs;
    }
    //--------------------------------------------------------------------------


    private function initFieldTypesUsedForJobAndGetFrontRow() {

        $systemFields = explode(',', $this->job['system_fields']);
        $frontRow = array();
        foreach (self::$allSysFeedColumns as $columnName) {
            if (in_array($columnName, $systemFields)) {
                $frontRow[] = $columnName;
                $this->ftAssoc[$columnName] = sizeof($this->ftAssoc);
            }
        }

        $ftAssoc = SingletonRegistry::getModelCampaignField()->listFieldTypesAssoc();
        if ($this->job['field_types'] == '*') {
            $jobFieldTypes = array_keys($ftAssoc);
        } else {
            $jobFieldTypes = explode(',', $this->job['field_types']);
        }

        foreach ($ftAssoc as $fieldTypeId => $fieldTypeName) {
            if (in_array($fieldTypeId, $jobFieldTypes)) {
                $frontRow[] = $fieldTypeName;
                $this->ftAssoc[$fieldTypeId] = sizeof($this->ftAssoc);
            }
        }

        return $frontRow;
    }
    //--------------------------------------------------------------------------


    public function exportCampaignToCSV($campaignId) {
        $campaign = SingletonRegistry::getModelCampaign()->get($campaignId, MYSQL_ASSOC);
        $userId   = (int)$campaign['user_id'];

        $exportSettings = $this->getExportSettings($campaignId, $userId);
        if (!$exportSettings) {
            return;
        }

        $mysqlResource = $this->modelExport->listIncomingRowsFor_MysqlRes($campaignId, $this->days, $exportSettings[self::S_DELAY]);
        $systemFields = explode(',', $this->job['system_fields']);
        $countOfSystemFields = sizeof($systemFields);

        while (($row = mysql_fetch_assoc($mysqlResource)) !== false) {
            $profileFTValues = $this->profile->getProfileDataForEmail($row['email']);
            $this->overrideIpAndReferer($row, $exportSettings, $profileFTValues);

            $location = $this->skipGeoip ? array('country' => '') : $this->geoip->getLocationForIP($row['remote_ip']);
            if (!$this->checkRowFilters($row, $location)) {
                continue;
            }

            $data = $this->systemFields($row, $systemFields, $location, $userId, $campaignId);

            // Add empty columns for all field types
            for ($i = $countOfSystemFields; $i < sizeof($this->ftAssoc); $i++) {
                $data[] = '';
            }

            // Then fill in the ones for which we have values
            foreach ($profileFTValues as $ftId => $val) {
                if (isset($this->ftAssoc[$ftId])) {
                    $data[$this->ftAssoc[$ftId]] = $val;
                }
            }

            if ($this->justGatherData) {
                $this->generatedData[] = $data;
            } else {
                echo "\n" . $this->outputCSV($data);
            }
        }
    }
    //--------------------------------------------------------------------------


    private function systemFields(array $row, array $systemFields, array $location, $userId, $campaignId) {
        $data = array();
        if (in_array('lead_id', $systemFields)) {
            $data[] = $row['id'];
        }
        if (in_array('email', $systemFields)) {
            $data[] = $row['email'];
        }
        if (in_array('remote_ip', $systemFields)) {
            $data[] = $row['remote_ip'];
        }
        if (in_array('http_referer', $systemFields)) {
            $data[] = $row['http_referer'];
        }
        if (in_array('timestamp', $systemFields)) {
            $data[] = $row['request_time'];
        }
        if (in_array('country_code', $systemFields)) {
            $data[] = empty($location['country']) ? '??' : $location['country'];
        }
        if (in_array('user_id', $systemFields)) {
            $data[] = $userId;
        }
        if (in_array('campaign_id', $systemFields)) {
            $data[] = $campaignId;
        }
        return $data;
    }
    //--------------------------------------------------------------------------


    private function getExportSettings($campaignId, $userId) {
        if (isset($this->campaignSettings[$campaignId])) {
            return explode('-', $this->campaignSettings[$campaignId]);
        } elseif (isset($this->userSettings[$userId])) {
            return explode('-', $this->userSettings[$userId]);
        } else {
            return false;
        }
    }
    //--------------------------------------------------------------------------


    private function overrideIpAndReferer(array & $row, array $exportSettings, array $profileFTValues) {
        if (!empty($exportSettings[self::S_IP_OVERRIDE]) && !empty($profileFTValues[$exportSettings[self::S_IP_OVERRIDE]])) {
            $row['remote_ip'] = $profileFTValues[$exportSettings[self::S_IP_OVERRIDE]];
        }
        if (!empty($exportSettings[self::S_REF_OVERRIDE]) && !empty($profileFTValues[$exportSettings[self::S_REF_OVERRIDE]])) {
            $row['http_referer'] = $profileFTValues[$exportSettings[self::S_REF_OVERRIDE]];
        }
    }
    //--------------------------------------------------------------------------


    private function prepareFilterVars() {
        $this->tldInclude = ($this->job['tld_default_include'] == '1');
        if (trim($this->job['tld_list']) != '') {
            $this->tldList = explode("\n", strtolower($this->job['tld_list']));
            foreach (array_keys($this->tldList) as $k) {
                $this->tldList[$k] = trim($this->tldList[$k]);
            }
        } else {
            $this->tldList = array();
        }
        $this->needsTldCheck = !$this->tldInclude || !empty($this->tldList);


        $this->countryInclude = ($this->job['country_default_include'] == '1');
        if (trim($this->job['countries']) != '') {
            $this->countryList = explode("-", strtoupper(trim($this->job['countries'])));
        } else {
            $this->countryList = array();
        }

        // If skipGeoIp is on then we don't filter by countries
        $this->needsCountryCheck = !$this->skipGeoip && (!$this->countryInclude || !empty($this->countryList));

        $this->needsFilterCheck = $this->needsTldCheck || $this->needsCountryCheck;
    }
    //--------------------------------------------------------------------------


    private function checkRowFilters(array $row, array $loc) {
        if (!$this->needsFilterCheck) {
            return true;
        }
        if ($this->needsCountryCheck) {
            $country = strtoupper($loc['country']);
            if ($this->countryInclude) {
                // countryInclude = true means we are including all countries except the ones in the list
                // so if a country is found in the list row should be filtered out
                return !in_array($country, $this->countryList);
            } else {
                return in_array($country, $this->countryList);
            }
        }

        if ($this->needsTldCheck) {
            $tld = strtolower(substr(strstr($row['email'], '@'), 1));
            if ($this->tldInclude) {
                // tldInclude = true means we are including all TLDs except the ones in the list
                // so if a TLD is found in the list row should be filtered out
                return !in_array($tld, $this->tldList);
            } else {
                return in_array($tld, $this->tldList);
            }
        }

        return true;
    }
    //--------------------------------------------------------------------------


    public function outputCSV(array $data) {
        $out = array();
        foreach ($data as $s) {
            $surround = false;
            if (strpos($s, '"') !== false) {
                $s = str_replace('"', '"""', $s);
                $surround = true;
            }
            if (strpos($s, ',') !== false) {
                $surround = true;
            }
            if ($surround) {
                $s = '"' . $s . '"';
            }
            $out[] = $s;
        }
        return implode(",", $out);
    }
    //--------------------------------------------------------------------------
}
