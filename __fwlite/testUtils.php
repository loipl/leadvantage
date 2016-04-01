<?php

require_once dirname(__FILE__) . '/testing/SetupTestData.php';


function importTestData() {
    $data = include dirname(__FILE__) . '/testing/testPartnersExport.inc';
    SetupTestData::recreateTestPartners($data);

    $data = include dirname(__FILE__) . '/testing/testCampaignsExport.inc';
    SetupTestData::recreateTestCampaigns($data);
}

function exportTestData() {
    $data = SetupTestData::exportAllCampaigns();
    file_put_contents(dirname(__FILE__) . '/testing/testCampaignsExport.inc', "<?php\n\nreturn " . var_export($data, true) . ";\n\n");

    $data = SetupTestData::exportAllPartners();
    file_put_contents(dirname(__FILE__) . '/testing/testPartnersExport.inc', "<?php\n\nreturn " . var_export($data, true) . ";\n\n");
}

