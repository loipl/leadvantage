<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestCountryOverriding extends MyUT {
    
    public function setUp(){
        date_default_timezone_set(Config::$timeZone);
    }
    
    public function testGetDayStart(){
        $currentTime = strtotime('2014-10-27 10:11:12');
        $dayStart = Model_Partner::getDayStart($currentTime);
        $this->assertEqual($dayStart, '2014-10-27 00:00:00');
    }
    
    public function testGetDayStartWithTimeZone(){
        $currentTime = strtotime('2014-10-27 05:11:12');
        $timezone = 'Asia/Ho_Chi_Minh'; //GMT +7
        $dayStart = Model_Partner::getDayStart($currentTime, $timezone);
        $this->assertEqual($dayStart, '2014-10-26 10:00:00'); // day start 7 hours earlier
    }
    
    public function testGetWeekStart() {
        $currentTime = strtotime('2014-10-27 10:11:12'); // Monday
        $weekStart = Model_Partner::getWeekStart($currentTime); // last Sunday.
        $this->assertEqual($weekStart, '2014-10-26 00:00:00');
    }
    
    public function testGetWeekStartWithTimeZone() {
        $currentTime = strtotime('2014-10-25 20:11:12'); // Sarturday in Server
        $timezone = 'Asia/Ho_Chi_Minh'; //GMT +7, currently Sunday
        $weekStart = Model_Partner::getWeekStart($currentTime, $timezone); // last Sunday.
        $this->assertEqual($weekStart, '2014-10-25 10:00:00');
    }
    
    public function testGetMonthStart(){
        $currentTime = strtotime('2014-10-27 10:11:12');
        $monthStart = Model_Partner::getMonthStart($currentTime);
        $this->assertEqual($monthStart, '2014-10-01 00:00:00');
    }
    
    public function testGetMonthStartWithTimeZone(){
        $currentTime = strtotime('2014-10-01 01:11:12'); //Oct in Server
        $timezone = 'America/Adak'; //GMT -9, currently Sep
        $monthStart = Model_Partner::getMonthStart($currentTime, $timezone);
        $this->assertEqual($monthStart, '2014-09-01 02:00:00');
    }
    
}

