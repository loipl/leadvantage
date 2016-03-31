<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestEmailWarningHelper extends UnitTestCase {


    public function testListing() {
        $helper = new Helper_MailWarning();
        $helper->fakeEmail = true;
        $quotas = $helper->getQuotasForUserAssoc();
        $helper->sendWarningsToUsersOver(0);

        $li = new Model_LogIncoming();
        $li->getSuccessfulCount(4);
    }
    //--------------------------------------------------------------------------
}
