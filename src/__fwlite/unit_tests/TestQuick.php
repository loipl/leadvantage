<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestQuick extends MyUT {

    public function d_testMate1() {
        $url = 'http://api.mate1.com/reg-01/createProfile';
        $ch = curl_init();
        $data = array(
            'campaignId'      => '6582',
            'companyId'       => '750',
            'companyPassword' => md5('LW750m1'),
            'gender'          => 'M',
            'lookingGender'   => 'F',
            //'lookingMinAge'   => '19',
            //'lookingMaxAge'   => '23',
            'country'         => 'US',
            'city'            => "Beverly Hills",
            'postalCode'      => '90210',
            'nickName'        => 'asdfq w3er',
            'dobMonth'        => '01',
            'dobDay'          => '02',
            'dobYear'         => '1990',
            'email'           => 'ribicb@@yahoo.com',
            'password'        => 'asdfasdf'
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        echo curl_error($ch);
    }
    //--------------------------------------------------------------------------

}
