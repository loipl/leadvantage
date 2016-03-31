<?php

$skipOtherTests = 1;
defined('RUN_ALL_TESTS') or require_once '../../runAllTests.php';

class TestSystemSubmitDateFields extends C11UnitTests {

    /**
     * @desc tests [:submit_date:] and [:submit_time:]
     */
    public function testSubmitDateAndSubmitTime() {
        $campaign = SingletonRegistry::getModelCampaign()->get(SetupTestData::$cids['UTC09']);

        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe@yahoo.com', 'sd' => '08/21/2010'), $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($this->lastData['sd'], '2010-08-21');
            $this->assertEqual($this->lastData['st'], '00:00:01');
        }

        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe@yahoo.com', 'sd' => '08/21/2010 12:34:56'), $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($this->lastData['sd'], '2010-08-21');
            $this->assertEqual($this->lastData['st'], '12:34:56');
        }

        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe@yahoo.com'), $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($this->lastData['sd'], date('Y-m-d'));
            $this->assertTrue((strtotime(date('H:i:s')) - strtotime($this->lastData['st'])) < 30);
        }
    }
    //--------------------------------------------------------------------------


    public function testWithSubmitDateFieldType() {
        SetupTestData::resetCampaign('UTC00');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_SUBMIT_DATE, 'sd');

        $partnerId = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($partnerId);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $partnerId, 'name' => 'sc', 'value' => '[:submit_day:]-[:submit_month:]-[:submit_year:]'));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        $campaign = SingletonRegistry::getModelCampaign()->get(SetupTestData::$cids['UTC00']);

        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe@yahoo.com', 'sd' => '02/28/1992'), $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual('28-02-1992', $this->lastData['sc']);
        }
    }
    //--------------------------------------------------------------------------


    public function testWithoutSubmitDateFieldType() {
        SetupTestData::resetCampaign('UTC00');

        $partnerId = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($partnerId);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $partnerId, 'name' => 'sc', 'value' => '[:submit_day:]-[:submit_month:]-[:submit_year:]'));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        $campaign = SingletonRegistry::getModelCampaign()->get(SetupTestData::$cids['UTC00']);

        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe@yahoo.com'), $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual(date('d-m-Y'), $this->lastData['sc']);
        }
    }
    //--------------------------------------------------------------------------


    public function testRepostUsesOriginalSubmitTime() {
        SetupTestData::deleteEverythingForTestUser();
        SetupTestData::resetCampaign('UTC00');
        $mc = SingletonRegistry::getModelCampaign();
        $campaign = $mc->get(SetupTestData::$cids['UTC00']);
        $campaign['is_oob'] = '1';
        $mc->update($campaign['id'], $campaign);

        $partnerId = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($partnerId);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $partnerId, 'name' => 'sc', 'value' => '[:submit_day:]-[:submit_month:]-[:submit_year:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $partnerId, 'name' => 'sd', 'value' => '[:submit_date:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $partnerId, 'name' => 'st', 'value' => '[:submit_time:]'));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));
        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe@yahoo.com'), $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EDoneException $e) {
            $this->assertEqual('SUCCESS', $e->getMessage());
            $iid = $engine->getIncomingId();
        }

        $ts = date('Y-m-d H:i:s', time() - 90 * 3600);

        DB::$db->query("UPDATE `log_incoming` SET `request_time` = '$ts' WHERE `id` = $iid");

        try {
            $this->repostAllForTestUser($this->cbFinish);
            $this->waitAction = Engine::ACTION_SENT_VALS;
        } catch (EFWUT $e) {
            $this->assertEqual(date('d-m-Y', strtotime($ts)), $this->lastData['sc']);
            $this->assertEqual(date('Y-m-d', strtotime($ts)), $this->lastData['sd']);
            $this->assertEqual(date('H:i:s', strtotime($ts)), $this->lastData['st']);
        }

        $campaign['is_oob'] = '0';
        $mc->update($campaign['id'], $campaign);
    }
    //--------------------------------------------------------------------------

}
