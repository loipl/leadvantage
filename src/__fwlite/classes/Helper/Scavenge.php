<?php

class Helper_Scavenge {
    /**
     * @var Model_Partner
     */
    private $modelPartner;

    private $id2Url = array();
    private $fqdn2PartnerIDs = array();


    public function __construct() {
        $this->modelPartner = SingletonRegistry::getModelPartner();
    }
    //--------------------------------------------------------------------------


    public function getPartnersDeliveryAddrAssoc() {
        $fullList = $this->modelPartner->listAll('delivery_addr');
        $this->id2Url = array();
        foreach($fullList as $row) {
            $this->id2Url[$row['id']] = $row['delivery_addr'];
        }
    }
    //--------------------------------------------------------------------------


    public function extractFQDNs() {
        foreach ($this->id2Url as $id => $url) {
            if ($this->isAffCoreg($url)) {
                $fqdn = 'friendfinder.con';
            } else {
                $fqdn = parse_url($url, PHP_URL_HOST);
                if (strpos($fqdn, '.leadwrench.com') !== false) {
                    continue;
                }
                if ($fqdn == 'w1.localhost' || $fqdn == 'www.rbisoftware.com' || $fqdn == 'www.yourdomain.com') {
                    continue;
                }
            }
            $this->fqdn2PartnerIDs[$fqdn][] = $id;
        }
        return $this->fqdn2PartnerIDs;
    }
    //--------------------------------------------------------------------------


    private function isAffCoreg($url) {
        $query = parse_url($url, PHP_URL_PATH);
        return strpos($query, '/COREG/') === 0;
    }
    //--------------------------------------------------------------------------
}
