<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestExportFeeds extends MyUT {

    private $campaignId = 0;
    private $campaign   = array();

    /**
     * @var Model_Campaign
     */
    private $modelCampaign;

    /**
     * @var Model_ExportFeed
     */
    private $modelExportFeed;

    private $systemFields = '';

    private $countryDefaultInclude = true;

    private $countries = '';

    private $fieldTypes = array();

    private $tldDefaultInclude = true;

    private $tldList = '';



    public function __construct() {
        $this->modelCampaign   = SingletonRegistry::getModelCampaign();
        $this->modelExportFeed = SingletonRegistry::getSingleInstance('Model_ExportFeed');
        $this->campaignId      = SetupTestData::$cids['UTC24'];
    }
    //--------------------------------------------------------------------------


    public function setUp() {
        $this->campaign = $this->modelCampaign->get($this->campaignId);
        SetupTestData::deleteEverythingForTestUser();

        $this->systemFields          = 'remote_ip,country_code';
        $this->countryDefaultInclude = true;
        $this->countries             = '';
        $this->fieldTypes            = array(Model_CampaignField::FIELD_TYPE_EMAIL);

        $this->tldDefaultInclude     = true;
        $this->tldList               = '';

        $this->createTestDataOneRow('joe@yahoo.com',   '69.64.34.113');
        $this->createTestDataOneRow('ribicb@test.com', '85.222.231.34');
    }
    //--------------------------------------------------------------------------


    public function testBasicExport() {
        $arr = $this->runFeed();
        $firstRow = array_flip(array_shift($arr));
        $this->assertEqual(3, sizeof($firstRow));

        $this->assertEqual(2, sizeof($arr));
        $this->assertEqual('69.64.34.113',    $arr[0][$firstRow['remote_ip']]);
        $this->assertEqual('US',              $arr[0][$firstRow['country_code']]);
        $this->assertEqual('joe@yahoo.com',   $arr[0][$firstRow['Email']]);

        $this->assertEqual('85.222.231.34',   $arr[1][$firstRow['remote_ip']]);
        $this->assertEqual('RS',              $arr[1][$firstRow['country_code']]);
        $this->assertEqual('ribicb@test.com', $arr[1][$firstRow['Email']]);
    }
    //--------------------------------------------------------------------------


    public function testFilterOnlyUs() {
        $this->countryDefaultInclude = false;
        $this->countries = 'US';
        $arr = $this->runFeed();
        $firstRow = array_flip(array_shift($arr));
        $this->assertEqual(3, sizeof($firstRow));

        $this->assertEqual(1, sizeof($arr));
        $this->assertEqual('69.64.34.113',    $arr[0][$firstRow['remote_ip']]);
        $this->assertEqual('US',              $arr[0][$firstRow['country_code']]);
        $this->assertEqual('joe@yahoo.com',   $arr[0][$firstRow['Email']]);
    }
    //--------------------------------------------------------------------------


    public function testFilterExcludeUs() {
        $this->countryDefaultInclude = true;
        $this->countries = 'US';
        $arr = $this->runFeed();
        $firstRow = array_flip(array_shift($arr));
        $this->assertEqual(3, sizeof($firstRow));

        $this->assertEqual(1, sizeof($arr));
        $this->assertEqual('85.222.231.34',   $arr[0][$firstRow['remote_ip']]);
        $this->assertEqual('RS',              $arr[0][$firstRow['country_code']]);
        $this->assertEqual('ribicb@test.com', $arr[0][$firstRow['Email']]);
    }
    //--------------------------------------------------------------------------


    public function testFilterOnlyYahooCom() {
        $this->tldDefaultInclude = false;
        $this->tldList = "yahoo.com\nzazoo.com";
        $arr = $this->runFeed();
        $firstRow = array_flip(array_shift($arr));

        $this->assertEqual(1, sizeof($arr));
        $this->assertEqual('joe@yahoo.com',   $arr[0][$firstRow['Email']]);
    }
    //--------------------------------------------------------------------------


    public function testFilterExcludeYahooCom() {
        $this->tldDefaultInclude = true;
        $this->tldList = "yahoo.com\nzazoo.com";
        $arr = $this->runFeed();
        $firstRow = array_flip(array_shift($arr));

        $this->assertEqual(1, sizeof($arr));
        $this->assertEqual('ribicb@test.com',   $arr[0][$firstRow['Email']]);
    }
    //--------------------------------------------------------------------------


    private function runFeed() {

        $key     = rand(0, 999999999) . rand(0, 999999999) . rand(0, 999999999) . rand(0, 999999999);
        $sources = TEST_USER_ID . '-' . $this->campaignId . '-0-' . Model_CampaignField::FIELD_TYPE_IP_ADDRESS . '-0';
        $data    = array(
            'name'                    => 'UTC24-export-feed-' . rand(0, 1000000000),
            'key'                     => $key,
            'is_active'               => 1,
            'default_days'            => 7,
            'notes'                   => 'Created by a unit test',
            'sources'                 => $sources,
            'system_fields'           => $this->systemFields,
            'field_types'             => implode(',', $this->fieldTypes),
            'country_default_include' => $this->countryDefaultInclude ? '1' : '0',
            'countries'               => $this->countries,
            'tld_default_include'     => $this->tldDefaultInclude ? '1' : '0',
            'tld_list'                => $this->tldList,
        );

        $id = $this->modelExportFeed->insert($data);
        if ($id) {
            $hx = new Helper_Export();
            $hx->justGatherData = true;
            $hx->doExport(array('key' => $key));
            $this->modelExportFeed->delete($id);
            return $hx->getGeneratedData();
        }
        return '';
    }
    //--------------------------------------------------------------------------


    private function createTestDataOneRow($email, $ip) {
        $engine = SetupTestData::debugEngineSubmission();
        $engine->setTestIncomingData(array('email' => $email, 'ip_override' => $ip));
        try {
            $engine->processIncomingFormSubmission($this->campaign);
            $this->fail("Should have thrown EDoneException");
        } catch (EDoneException $e) {
            //
        }
    }
    //--------------------------------------------------------------------------
}
