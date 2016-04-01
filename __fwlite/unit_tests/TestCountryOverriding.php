<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestCountryOverriding extends MyUT {

    public function test() {
        SetupTestData::resetCampaign('UTC00');

        $campaignId = SetupTestData::$cids['UTC00'];
        $campaign = SingletonRegistry::getModelCampaign()->get($campaignId, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_POSTAL_CODE, 'zip');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_COUNTRY,     'country');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_IP_ADDRESS,  'ip');

        $partnerId = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($partnerId);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $partnerId, 'name' => 'sys_country', 'value' => '[:country_code_3chars:]'));

        try {
            $testData = array(
                'email'   => 'TestCountryOverriding@test.com',
                'zip'     => '90210',
                'country' => 'United States',
                'ip'      => '67.193.123.194',
            );

            $this->newEngineSubmission($testData, $this->cbGatherData, Engine::ACTION_SENT_VALS)->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual('USA', $this->gathered[0]['sys_country']);
        }
    }
    //--------------------------------------------------------------------------

}
