<?php

$skipOtherTests = 1;
defined('RUN_ALL_TESTS') or require_once '../../runAllTests.php';

class TestHelperPostback__Test extends Helper_Postback {
    public function getLeadId() {
        return $this->leadId;
    }
    //--------------------------------------------------------------------------
}


class TestHelperPostback extends C11UnitTests {

    /**
     * @var TestHelperPostback__Test
     */
    private $helper;


    public function setUp() {
        parent::setUp();
        $this->helper = new TestHelperPostback__Test();
    }
    //--------------------------------------------------------------------------


    public function testParsingWithoutHashCode() {
        try {
            $this->helper->run('/postback/');
            $this->fail('should have thrown a 404');
        } catch (EError404 $e) {
            $this->assertEqual(1, $e->getCode());
        }
    }
    //--------------------------------------------------------------------------


    public function testParsingInvalidHashCode() {
        try {
            $this->helper->run('/postback/xxYY');
            $this->fail('should have thrown a 404');
        } catch (EError404 $e) {
            $this->assertEqual(1, $e->getCode());
        }
    }
    //--------------------------------------------------------------------------


    public function testParsingValidHashCodeButNoLeadId() {
        try {
            $this->helper->run('/postback/' . Auth::userIdToPostbackHash(2) . '/');
            $this->fail('should have thrown a 404');
        } catch (EError404 $e) {
            $this->assertEqual(2, $e->getCode());
        }

        try {
            $this->helper->run('/postback/' . Auth::userIdToPostbackHash(2) . '/?lead_id=0');
            $this->fail('should have thrown a 404');
        } catch (EError404 $e) {
            $this->assertEqual(2, $e->getCode());
        }
    }
    //--------------------------------------------------------------------------


    public function testParsingWithoutActionParameter() {
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-y-n', '3-y-n'), array(1, 2), 2);
        $iid = $this->engine->getIncomingId();

        $arr = $this->logIncoming->listRepostQueuesForIncomingId($iid);
        $this->assertEqual(1, sizeof($arr));


        try {
            $this->helper->run('/postback/' . Auth::userIdToPostbackHash(2) . '/?lead_id=' . $iid);
            $this->fail('should have thrown a 404');
        } catch (EError404 $e) {
            $this->assertEqual(3, $e->getCode());
        }
    }
    //--------------------------------------------------------------------------


    public function testFindAndDeleteLeadId() {
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-y-n', '3-y-n'), array(1, 2), 2);
        $iid = $this->engine->getIncomingId();

        $arr = $this->logIncoming->listRepostQueuesForIncomingId($iid);
        $this->assertEqual(1, sizeof($arr));

        $this->helper->run('/postback/' . Auth::userIdToPostbackHash(2) . '/?action=' . Helper_Postback::ACTION_STOP_DELIVERY . '&lead_id=' . $iid);
        $this->assertEqual($iid, $this->helper->getLeadId());

        $arr = $this->logIncoming->listRepostQueuesForIncomingId($iid);
        $this->assertEqual(0, sizeof($arr));
    }
    //--------------------------------------------------------------------------


    public function testFindAndDeleteLeadId_WithCURL() {
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-y-n', '3-y-n'), array(1, 2), 2);
        $iid = $this->engine->getIncomingId();

        $arr = $this->logIncoming->listRepostQueuesForIncomingId($iid);
        $this->assertEqual(1, sizeof($arr));

        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/postback/' . Auth::userIdToPostbackHash(2) . '/?action=' . Helper_Postback::ACTION_STOP_DELIVERY . '&lead_id=' . $iid;
        file_get_contents($url);

        $arr = $this->logIncoming->listRepostQueuesForIncomingId($iid);
        $this->assertEqual(0, sizeof($arr));
    }
    //--------------------------------------------------------------------------
}
