<?php

$skipOtherTests = 1;
defined('RUN_ALL_TESTS') or require_once '../../runAllTests.php';


class TestHelperPostbackConversion__Test extends Helper_Postback {
    public function getLeadId() {
        return $this->leadId;
    }
    //--------------------------------------------------------------------------
}

class TestHelperPostbackConversion extends C11UnitTests {

    /**
     * @var TestHelperPostbackConversion__Test
     */
    private $helper;


    public function setUp() {
        parent::setUp();
        $this->helper = new TestHelperPostbackConversion__Test();
    }
    //--------------------------------------------------------------------------


    public function testMissingLeadId() {
        $this->checkUrlThrowsExpectedCode('', 2);
    }
    //--------------------------------------------------------------------------


    public function testMissingPartnerId() {
        $iid = $this->createTestSubmission();
        $this->checkUrlThrowsExpectedCode('&lead_id=' . $iid, 5);
    }
    //--------------------------------------------------------------------------


    public function testInvalidPartnerId() {
        $iid = $this->createTestSubmission();
        $this->checkUrlThrowsExpectedCode('&partner_id=0&lead_id=' . $iid, 5);
    }
    //--------------------------------------------------------------------------


    public function testPartnerIdForDifferentUser() {
        $iid = $this->createTestSubmission();

        $partnerId = SetupTestData::$pids['C11-P01'];
        $mp        = SingletonRegistry::getModelPartner();
        $partner   = $mp->get($partnerId, MYSQL_ASSOC);
        $partner['user_id'] = 16;
        $mp->update($partnerId, $partner);

        $this->checkUrlThrowsExpectedCode('&partner_id=' . SetupTestData::$pids['C11-P01'] . '&lead_id=' . $iid, 6);

        $partner['user_id'] = TEST_USER_ID;
        $mp->update($partnerId, $partner);
    }
    //--------------------------------------------------------------------------


    public function testSuccessWithoutValue_AndNoType() {
        $partnerId = SetupTestData::$pids['C11-P01'];

        $iid = $this->createTestSubmission();
        $url = '&lead_id=' . $iid . '&partner_id=' . $partnerId;
        $this->helper->run('/postback/' . Auth::userIdToPostbackHash(2) . '/?action=' . Helper_Postback::ACTION_CONVERSION . $url);

        $lastConversion = SingletonRegistry::getModelConversion()->getLast();
        $this->assertEqual($lastConversion['incoming_id'], $iid);
        $this->assertEqual($lastConversion['partner_id'],  $partnerId);
        $this->assertEqual($lastConversion['type'],        '');
        $this->assertEqual($lastConversion['value'],       0);
    }
    //--------------------------------------------------------------------------


    public function testSuccessWithoutValue() {
        $partnerId = SetupTestData::$pids['C11-P01'];

        $iid = $this->createTestSubmission();
        $url = '&type=upsell&lead_id=' . $iid . '&partner_id=' . $partnerId;
        $this->helper->run('/postback/' . Auth::userIdToPostbackHash(2) . '/?action=' . Helper_Postback::ACTION_CONVERSION . $url);

        $lastConversion = SingletonRegistry::getModelConversion()->getLast();
        $this->assertEqual($lastConversion['incoming_id'], $iid);
        $this->assertEqual($lastConversion['partner_id'],  $partnerId);
        $this->assertEqual($lastConversion['type'],        'upsell');
        $this->assertEqual($lastConversion['value'],       0);
    }
    //--------------------------------------------------------------------------


    public function testSuccessWithoutValue_Email() {
        SetupTestData::deleteEverythingForTestUser();
        $partnerId = SetupTestData::$pids['C11-P01'];

        $iid = $this->createTestSubmission();
        $url = '&type=upsell&email=c11@campaign.com&partner_id=' . $partnerId;
        $this->helper->run('/postback/' . Auth::userIdToPostbackHash(2) . '/?action=' . Helper_Postback::ACTION_CONVERSION . $url);

        $lastConversion = SingletonRegistry::getModelConversion()->getLast();
        $this->assertEqual($lastConversion['incoming_id'], $iid);
        $this->assertEqual($lastConversion['partner_id'],  $partnerId);
        $this->assertEqual($lastConversion['type'],        'upsell');
        $this->assertEqual($lastConversion['value'],       0);
    }
    //--------------------------------------------------------------------------


    public function testSuccessWithValue() {
        $partnerId = SetupTestData::$pids['C11-P01'];

        $iid = $this->createTestSubmission();
        $url = '&type=upsell&value=1.23&lead_id=' . $iid . '&partner_id=' . $partnerId;
        $this->helper->run('/postback/' . Auth::userIdToPostbackHash(2) . '/?action=' . Helper_Postback::ACTION_CONVERSION . $url);

        $lastConversion = SingletonRegistry::getModelConversion()->getLast();
        $this->assertEqual($lastConversion['incoming_id'], $iid);
        $this->assertEqual($lastConversion['partner_id'],  $partnerId);
        $this->assertEqual($lastConversion['type'],        'upsell');
        $this->assertEqual($lastConversion['value'],       1.23);
    }
    //--------------------------------------------------------------------------


    public function testConversionAndStopDeliveryInSameUrl() {
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-y-n', '3-y-n'), array(1, 2), 2);
        $iid = $this->createTestSubmission();
        $partnerId = SetupTestData::$pids['C11-P01'];

        $arr = $this->logIncoming->listRepostQueuesForIncomingId($iid);
        $this->assertEqual(1, sizeof($arr));

        $url = '/?action[]=' . Helper_Postback::ACTION_STOP_DELIVERY . '&action[]=' . Helper_Postback::ACTION_CONVERSION . '&lead_id=' . $iid;
        $url .= '&value=1.23&partner_id=' . $partnerId . '&type=1';
        $this->helper->run('/postback/' . Auth::userIdToPostbackHash(2) . $url);
        $this->assertEqual($iid, $this->helper->getLeadId());

        $arr = $this->logIncoming->listRepostQueuesForIncomingId($iid);
        $this->assertEqual(0, sizeof($arr));
    }
    //--------------------------------------------------------------------------


    private function checkUrlThrowsExpectedCode($url, $expectedCode) {
        try {
            $this->helper->run('/postback/' . Auth::userIdToPostbackHash(2) . '/?action=' . Helper_Postback::ACTION_CONVERSION . $url);
            $this->fail('Should have thrown an EError404 with code ' . $expectedCode);
        } catch (EError404 $e) {
            $this->assertEqual($expectedCode, $e->getCode());
        }
    }
    //--------------------------------------------------------------------------


    private function createTestSubmission() {
        $this->logRepost = SingletonRegistry::getModelLogIncomingRepost();
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-y-n', '3-y-n'), array(1, 2), 2);
        $iid = $this->engine->getIncomingId();

        $arr = $this->logIncoming->listRepostQueuesForIncomingId($iid);
        $this->assertEqual(1, sizeof($arr));

        return $iid;
    }
    //--------------------------------------------------------------------------
}
