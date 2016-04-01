<?php

class Controller_Admin_Scavenge extends Controller {

    public function indexAction() {
        $hs = new Helper_Scavenge();
        $hs->getPartnersDeliveryAddrAssoc();
        $fqdns = $hs->extractFQDNs();
        $this->content = Lib::var_export($fqdns, true);
    }
    //--------------------------------------------------------------------------

}
