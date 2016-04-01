<?php

require_once dirname(__FILE__) . '/cron.inc';

// Model Partner
$mp = SingletonRegistry::getModelPartner();
$partners = $mp->listPartnersWithExistingCaps();

// Model_PartnerCap
$mpc = SingletonRegistry::getModelPartnerCap();

if (!empty($partners)) {
    foreach ($partners as $row) {
        if (array_key_exists((int)$row['delivery_ctype'], $mpc->getIntervals()) && !$mpc->checkOldCapExists($row)) {
            $mpc->addPartnerCap($row);
        }
    }
} else {
    echo "No Partners with Existing Cap";
}
