<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestValidators extends UnitTestCase {
    private $oldCV = false;

    public function testRegistry() {
        $arr = Validator_Registry::listValidators();
        foreach ($arr as $v) {
            $this->assertIsA($v, 'Validator_Base');
        }
    }
    //--------------------------------------------------------------------------


    public function setUp() {
        $this->oldCV = Config::$validatorCache;
        Config::$validatorCache = false;
    }
    //--------------------------------------------------------------------------


    public function tearDown() {
        Config::$validatorCache = $this->oldCV;
    }
    //--------------------------------------------------------------------------


//    public function testGetValidatorDV() {
//        $validatorDV = Validator_Registry::getByName('DV');
//        $this->assertIsA($validatorDV, 'Validator_DV');
//    }
    //--------------------------------------------------------------------------


    public function testGetValidatorBrite() {
        $validatorBrite = Validator_Registry::getByName('Brite');
        $this->assertIsA($validatorBrite, 'Validator_Brite');
    }
    //--------------------------------------------------------------------------


    public function testGetValidatorXVerify() {
        $validatorXVerify = Validator_Registry::getByName('XVerify');
        $this->assertIsA($validatorXVerify, 'Validator_XVerify');
    }
    //--------------------------------------------------------------------------


    public function testValidateWithDV() {
        if (!SetupTestData::$runValidationTests) {
            return;
        }

        /* @var $validatorDV Validator_DV */
        $validatorDV = Validator_Registry::getByName('DV');

        $error = '';
        $result = $validatorDV->validate('ribicb@yahoo.comasd', $error);
        if (($validatorDV->getLastHttpResponseCode() == 403) || ($validatorDV->getLastHttpResponseCode() == 0)) {
            return;
        }
        $this->assertFalse($result);

        $result = $validatorDV->validate('ribicb@yahoo.com', $error);
        $this->assertTrue($result);
    }
    //--------------------------------------------------------------------------


    public function testValidateWithBrite() {
        if (!SetupTestData::$runValidationTests) {
            return;
        }
        /* @var $validatorBrite Validator_Brite */
        $validatorBrite = Validator_Registry::getByName('Brite');

        $error = '';
        $result = $validatorBrite->validate('sssasdfkhasdfkhwer@yahoo.com', $error);
        $this->assertFalse($result);

        $error = '';
        $result = $validatorBrite->validate('ribicb@yahoo.comasd', $error);
        $this->assertFalse($result);

        $result = $validatorBrite->validate('ribicb@yahoo.com', $error);
        $this->assertTrue($result);

        $result = $validatorBrite->validate('4086464677', $error, 'phone');
        $this->assertTrue($result);
    }
    //--------------------------------------------------------------------------


    public function testDV2BriteFallThrough() {
        if (!SetupTestData::$runValidationTests) {
            return;
        }

        $error = '';
        Validator_Registry::validate('ribicb@yahoo.com', Model_CampaignField::FIELD_TYPE_EMAIL, $error);
        $codes = Validator_Registry::getHttpCodesOnLastRun();
        if (($codes[0] == 403) || ($codes[0] == 0)) {
            return;
        }
        $triedValidators = Validator_Registry::getTriedValidatorsOnLastRun();
        $this->assertEqual(array('DV'), $triedValidators);

        Validator_Registry::validate('ribicb@yahoo.com', Model_CampaignField::FIELD_TYPE_EMAIL, $error, array('DV_ambiguous' => 1));
        $triedValidators = Validator_Registry::getTriedValidatorsOnLastRun();
        $this->assertEqual(array('DV', 'Brite'), $triedValidators);
    }
    //--------------------------------------------------------------------------


    public function testCaching() {
        if (!SetupTestData::$runValidationTests) {
            return;
        }
        Config::$validatorCache = true;

        // Dummy var to be passed by reference
        $error = $message = '';
        $res = Validator_Registry::validate('ribicb@yahoo.com', Model_CampaignField::FIELD_TYPE_EMAIL, $error);
        $this->assertTrue($res);

        /* @var $validationCache Model_ValidationCache */
        $validationCache = SingletonRegistry::getSingleInstance('Model_ValidationCache');

        // Dummy var to be passed by reference
        $isValid = false;
        $isCached = $validationCache->isCached(Model_CampaignField::FIELD_TYPE_EMAIL, 'ribicb@yahoo.com', $isValid, $message, 24);
        $this->assertTrue($isCached);

        $isCached = $validationCache->isCached(Model_CampaignField::FIELD_TYPE_EMAIL, 'ribicb@yahoo.com', $isValid, $message, 0);
        $this->assertFalse($isCached);
    }
    //--------------------------------------------------------------------------


    public function testNoCachingForNegativeResult() {
        if (!SetupTestData::$runValidationTests) {
            return;
        }
        Config::$validatorCache = true;

        // Dummy var to be passed by reference
        $error = $message = '';

        $isValid = Validator_Registry::validate('ribicb@yahoo.comasd', Model_CampaignField::FIELD_TYPE_EMAIL, $error);
        $this->assertFalse($isValid);

        /* @var $validationCache Model_ValidationCache */
        $validationCache = SingletonRegistry::getSingleInstance('Model_ValidationCache');

        $isCached = $validationCache->isCached(Model_CampaignField::FIELD_TYPE_EMAIL, 'ribicb@yahoo.comasd', $isValid, $message, 24);
        $this->assertFalse($isCached);
    }
    //--------------------------------------------------------------------------


    public function testRapleafAgeDeduction() {
        $this->assertEqual(25, ExternalLookup_RapLeaf::getAgeFromResponse('25'));
        $this->assertEqual(25, ExternalLookup_RapLeaf::getAgeFromResponse('24-26'));
        $this->assertEqual(40, ExternalLookup_RapLeaf::getAgeFromResponse('35-44'));
    }
    //--------------------------------------------------------------------------
}
