<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestMisc extends MyUT {


    public function testAuthLevels() {
        $maxCap = Auth::maxCap(array(Auth::L_ADMIN, Auth::L_S2_LEVEL5));
        $this->assertEqual(Auth::L_ADMIN, $maxCap);

        $maxCap = Auth::maxCap(array(Auth::L_SUBSCRIBER, Auth::L_S2_LEVEL5));
        $this->assertEqual(Auth::L_S2_LEVEL5, $maxCap);

        $maxCap = Auth::maxCap(array(Auth::L_S2_LEVEL2, Auth::L_S2_LEVEL4));
        $this->assertEqual(Auth::L_S2_LEVEL4, $maxCap);
    }
    //--------------------------------------------------------------------------


    public function testAuthIsAtLeast() {
        $muAssoc = SingletonRegistry::getModelUser()->listUserCapsAssoc();
        $maxCapsAssoc = array();
        $caps2Users = array();
        foreach ($muAssoc as $userId => $caps) {
            $maxCap = Auth::maxCap($caps);
            $maxCapsAssoc[$userId] = $maxCap;
            $caps2Users[$maxCap][] = $userId;
        }

        if (!empty($caps2Users[Auth::L_SUBSCRIBER])) {
            $userId = $caps2Users[Auth::L_SUBSCRIBER][0];
            $b = Auth::isUserIdAtLeast($userId, $muAssoc[$userId], Auth::L_SUBSCRIBER);
            $this->assertTrue($b);

            $b = Auth::isUserIdAtLeast($userId, $muAssoc[$userId], Auth::L_ADMIN);
            $this->assertFalse($b);

            $b = Auth::isUserIdAtLeast($userId, $muAssoc[$userId], Auth::L_S2_LEVEL1);
            $this->assertFalse($b);

            $b = Auth::isUserIdAtLeast($userId, $muAssoc[$userId], Auth::L_S2_LEVEL5);
            $this->assertFalse($b);
        } else {
            // $this->fail("There are no users with subscriber level");
        }

        if (!empty($caps2Users[Auth::L_ADMIN])) {
            $userId = $caps2Users[Auth::L_ADMIN][0];
            $b = Auth::isUserIdAtLeast($userId, $muAssoc[$userId], Auth::L_SUBSCRIBER);
            $this->assertTrue($b);

            $b = Auth::isUserIdAtLeast($userId, $muAssoc[$userId], Auth::L_ADMIN);
            $this->assertTrue($b);

            $b = Auth::isUserIdAtLeast($userId, $muAssoc[$userId], Auth::L_S2_LEVEL1);
            $this->assertTrue($b);

            $b = Auth::isUserIdAtLeast($userId, $muAssoc[$userId], Auth::L_S2_LEVEL5);
            $this->assertTrue($b);
        } else {
            // $this->fail("There are no users with administrator level");
        }

        if (!empty($caps2Users[Auth::L_S2_LEVEL5])) {
            $userId = $caps2Users[Auth::L_S2_LEVEL5][0];
            $b = Auth::isUserIdAtLeast($userId, $muAssoc[$userId], Auth::L_SUBSCRIBER);
            $this->assertTrue($b);

            $b = Auth::isUserIdAtLeast($userId, $muAssoc[$userId], Auth::L_ADMIN);
            $this->assertFalse($b);

            $b = Auth::isUserIdAtLeast($userId, $muAssoc[$userId], Auth::L_S2_LEVEL1);
            $this->assertTrue($b);

            $b = Auth::isUserIdAtLeast($userId, $muAssoc[$userId], Auth::L_S2_LEVEL5);
            $this->assertTrue($b);
        } else {
            // $this->fail("There are no users with s2_level_5 level");
        }
    }
    //--------------------------------------------------------------------------


    public function testFQDN() {
        $this->assertEqual('www.xyz.com',   Engine_Utilities::toFQDN('xyz.com'));
        $this->assertEqual('www.xyz.com',   Engine_Utilities::toFQDN('www.xyz.com'));

        $this->assertEqual('www.xyz.co-uk', Engine_Utilities::toFQDN('xyz.co.uk'));
        $this->assertEqual('www.xyz.co-uk', Engine_Utilities::toFQDN('www.xyz.co.uk'));

        $this->assertEqual('abc.xyz.com',   Engine_Utilities::toFQDN('abc.xyz.com'));
        $this->assertEqual('abc.xyz.com',   Engine_Utilities::toFQDN('def.abc.xyz.com'));
    }
    //--------------------------------------------------------------------------


    public function testEngineDeliveryMatchers() {
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_EQUALS, 'xxx', 'xxx'));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_EQUALS, 'xxx', 'xXx'));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_EQUALS, ' xxx', 'xxx '));

        $this->assertTrue(Engine_Utilities::checkMatching(-Model_PartnerFilter::FILTER_MATCH_EQUALS, 'xxx', 'abc'));

        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_STARTS_WITH, 'xxx', 'xx'));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_STARTS_WITH, ' xxx ', ' xx '));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_STARTS_WITH, ' xxx 33456', ' xx '));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_STARTS_WITH, ' xxx 33456', ' xxx 33'));

        $this->assertTrue(Engine_Utilities::checkMatching(-Model_PartnerFilter::FILTER_MATCH_STARTS_WITH, ' xxx 33456', ' xx 33'));

        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_ENDS_WITH, 'xxx', 'xx'));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_ENDS_WITH, 'xxx', 'x'));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_ENDS_WITH, '123456', '56'));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_ENDS_WITH, '123456', '3456'));

        $this->assertTrue(Engine_Utilities::checkMatching(-Model_PartnerFilter::FILTER_MATCH_ENDS_WITH, '123456', '45'));
        $this->assertTrue(Engine_Utilities::checkMatching(-Model_PartnerFilter::FILTER_MATCH_ENDS_WITH, '123456', 'xa'));

        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_CONTAINS, 'xxx', 'xx'));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_CONTAINS, 'xxx', 'x'));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_CONTAINS, ' xxx ', ' x '));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_CONTAINS, ' xx x ', ' xx '));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_CONTAINS, '123456', '23'));

        $this->assertTrue(Engine_Utilities::checkMatching(-Model_PartnerFilter::FILTER_MATCH_CONTAINS, '123456', '32'));

        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_GREATER_THAN, '123', '13'));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_GREATER_THAN, 'abcdef', 'abb'));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_GREATER_THAN, 'XYZ', 'ABC'));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_GREATER_THAN, 'ABCDEF', 'ABB'));

        $this->assertTrue(Engine_Utilities::checkMatching(-Model_PartnerFilter::FILTER_MATCH_GREATER_THAN, 'x123', 'x13'));

        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_LESS_THAN, '13', '123'));
        $this->assertTrue(Engine_Utilities::checkMatching(-Model_PartnerFilter::FILTER_MATCH_LESS_THAN, '123', '13'));

        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_STRLEN, 'x123', '3'));
        $this->assertTrue(Engine_Utilities::checkMatching(-Model_PartnerFilter::FILTER_MATCH_STRLEN, 'x123', '5'));
    }
    //--------------------------------------------------------------------------


    public function testEngineDeliveryMatchersMultiple() {
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_EQUALS, 'xxx', '4325|xxx'));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_EQUALS, 'xxx', 'xXx|xx|3456'));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_EQUALS, 'xxx', 'xXx |xx|3456'));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_EQUALS, ' xxx', 'x|xx|xxx '));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_EQUALS, ' xxx', 'x| xxx |xx'));

        $this->assertTrue(Engine_Utilities::checkMatching(-Model_PartnerFilter::FILTER_MATCH_EQUALS, ' xxx', 'x|xx|xax '));

        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_STARTS_WITH, 'xxx', 'a|az|xx'));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_STARTS_WITH, '  xxx ', 'a|az| xx '));

        $this->assertTrue(Engine_Utilities::checkMatching(-Model_PartnerFilter::FILTER_MATCH_STARTS_WITH, 'xxx', '|az'));

        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_ENDS_WITH, '123456', 'zz|56'));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_ENDS_WITH, '123456', '23|56'));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_ENDS_WITH, '123456', '23456|56'));

        $this->assertTrue(Engine_Utilities::checkMatching(-Model_PartnerFilter::FILTER_MATCH_ENDS_WITH, '123456', 'zz|ee'));

        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_CONTAINS, '123456', '23|zz'));
        $this->assertTrue(Engine_Utilities::checkMatching(Model_PartnerFilter::FILTER_MATCH_CONTAINS, '123456', '23 |zz'));
    }
    //--------------------------------------------------------------------------
}
