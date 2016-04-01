<?php

$skipOtherTests = 1;
defined('RUN_ALL_TESTS') or require_once '../../runAllTests.php';

class TestHelperScavenge extends UnitTestCase {

    public function test() {
        $hs = new Helper_Scavenge();
        $hs->getPartnersDeliveryAddrAssoc();
        $fqdns = $hs->extractFQDNs();
        $this->content = Lib::var_export($fqdns, true);
    }
    //--------------------------------------------------------------------------

}
