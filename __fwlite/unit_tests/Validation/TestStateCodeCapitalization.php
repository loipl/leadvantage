<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../../runAllTests.php';
}

class TestStateCodeCapitalization extends MyUT {


    public function test() {
        SetupTestData::resetCampaign('UTC00');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_STATE_CODE, 'state_code');

        $partnerId = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($partnerId);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $partnerId, 'name' => 'sc', 'value' => '[State_Code]'));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        $campaign = SingletonRegistry::getModelCampaign()->get(SetupTestData::$cids['UTC00']);

        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe@yahoo.com', 'state_code' => 'ca'), $this->cbFinish, Engine::ACTION_GATHERED);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual('CA', $engine->getData()->inputData[Model_CampaignField::FIELD_TYPE_STATE_CODE]);
        }
    }
    //--------------------------------------------------------------------------

}
