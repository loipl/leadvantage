<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestsWithSTBrowser
extends UnitTestCase
{
    /**
     * @var Model_Campaign
     */
    protected $modelCampaign;

    /**
     * @var Model_Partner
     */
    protected $modelPartner;

    /**
     * @var Model_Profile
     */
    private $modelProfile;

    protected $campaign;

    /**
     * @var SimpleBrowser
     */
    protected $browser;

    private $url;


    public function setUp() {
        $this->modelCampaign or $this->modelCampaign = SingletonRegistry::getModelCampaign();
        $this->modelPartner  or $this->modelPartner  = SingletonRegistry::getModelPartner();
        $this->modelProfile  or $this->modelProfile  = SingletonRegistry::getSingleInstance('Model_Profile');

        $this->campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC13']);
        $this->url      = 'http://' . $_SERVER['HTTP_HOST'] . '/api/' . $this->modelCampaign->getCampaignHashCode((int) $this->campaign['user_id'], (int) $this->campaign['id']) . '/';
    }
    //--------------------------------------------------------------------------


    public function skip_testBenchmarkUrl() {
        $ts = microtime(true);
        for($i = 0; $i < 100; $i++) {
            $this->testInvalidInputDataUrlAutoDecode();
        }
        echo "\n\n";
        echo microtime(true) - $ts;
        echo "\n\n";
    }
    //--------------------------------------------------------------------------


    public function testInactiveCampaignIsRedirectedToFailureUrl() {
        $this->ensureActiveState($this->campaign, false);

        $browser = $this->getBrowser();
        $text = $browser->get($this->url);
        $this->assertEqual(302, $browser->getResponseCode());
        $this->assertEqual($this->campaign['failure_url'], $this->getRedirect());
    }
    //--------------------------------------------------------------------------


    private function ensureActiveState(array $campaign, $activeState) {
        // Make sure campaign is not active
        if ($campaign['is_active'] != ($activeState ? '1' : '0')) {
            $copy = $campaign;
            $copy['is_active'] = $activeState ? '1' : '0';
            $this->modelCampaign->updateDiff($campaign['id'], $copy,  $campaign);
            // There's a scheme with APC caching of campaign data, which functions with one second delay
            // From unit tests we have to flush the cash after every change to DB
            $this->flushApcCache();
        }
    }
    //--------------------------------------------------------------------------


    public function testInvalidInputDataEmail() {
        $this->ensureActiveState($this->campaign, true);

        $browser = $this->getBrowser();
        $content = $browser->post($this->url, array('email' => 'xxyy'));

        // We were not redirected, this is an error page
        $this->assertEqual(200, $browser->getResponseCode());
        $this->assertTrue(strpos($content, ' is not a valid email') !== false);
    }
    //--------------------------------------------------------------------------


    public function testInvalidInputDataUrl() {
        $this->ensureActiveState($this->campaign, true);

        // Delete this from profiles table so the script won't pickup old data
        $this->modelProfile->deleteWhere(array('email' => 'xxyy@yahoo.com'));

        $browser = $this->getBrowser();
        $content = $browser->post($this->url, array('email' => 'xxyy@yahoo.com', 'url' => 'wer'));

        // We were not redirected, this is an error page
        $this->assertEqual(200, $browser->getResponseCode());
        $this->assertTrue(strpos($content, ' is not a valid URL') !== false);
    }
    //--------------------------------------------------------------------------


    public function testInvalidInputDataUrlAutoDecode() {
        $this->ensureActiveState($this->campaign, true);

        $browser = $this->getBrowser();
        // Delete this from profiles table so the script won't pickup old data
        $this->modelProfile->deleteWhere(array('email' => 'xxyy@yahoo.com'));
        $content = $browser->post($this->url, array('email' => 'xxyy@yahoo.com', 'url' => 'http://www.xxx.com/'));

        // Redirection
        $this->assertEqual(302, $browser->getResponseCode());

        // Delete this from profiles table so the script won't pickup old data
        $this->modelProfile->deleteWhere(array('email' => 'xxyy@yahoo.com'));
        $content = $browser->post($this->url, array('email' => 'xxyy@yahoo.com', 'url' => urlencode('http://www.xxx.com/')));

        // We were not redirected, this is an error page
        $this->assertEqual(302, $browser->getResponseCode());
    }
    //--------------------------------------------------------------------------


    public function testInvalidInputDateField() {
        $this->ensureActiveState($this->campaign, true);

        $browser = $this->getBrowser();
        // Delete this from profiles table so the script won't pickup old data
        $this->modelProfile->deleteWhere(array('email' => 'xxyy@yahoo.com'));
        $content = $browser->post($this->url, array('email' => 'xxyy@yahoo.com', 'date' => 'wer'));

        // We were not redirected, this is an error page
        $this->assertEqual(200, $browser->getResponseCode());
        $this->assertTrue(strpos($content, 'Invalid date: wer') !== false);
    }
    //--------------------------------------------------------------------------


    public function testRedirectingToPartnerSuccessUrl() {
        $this->ensureActiveState($this->campaign, true);

        $browser = $this->getBrowser();
        // Delete this from profiles table so the script won't pickup old data
        $this->modelProfile->deleteWhere(array('email' => 'testSTB@tests.com'));
        $content = $browser->post($this->url, array('email' => 'testSTB@tests.com'));

        $p = $this->modelPartner->get(SetupTestData::$pids['C13-P1']);

        $this->assertEqual(302, $browser->getResponseCode());
        $this->assertEqual($p['success_url'], $this->getRedirect());
    }
    //--------------------------------------------------------------------------


    public function testOOBCampaignResponse() {
        $cn = $this->modelCampaign->get(SetupTestData::$cids['UTC15']);
        $this->ensureActiveState($cn, true);

        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/api/' . $this->modelCampaign->getCampaignHashCode((int) $cn['user_id'], (int) $cn['id']) . '/';
        $browser = $this->getBrowser();
        $content = $browser->post($url, array('email' => 'invmail@'));

        $this->assertEqual($content, 'FAIL: invmail@ is not a valid email, First_Name is a mandatory field');

        $browser = $this->getBrowser();
        $content = $browser->post($url, array('email' => 'invmail@', 'first_name' => 'Joe'));
        $this->assertEqual($content, 'FAIL: Duplicate Submission');
    }
    //--------------------------------------------------------------------------


    private function getRedirect() {
        $arr = explode("\n", $this->browser->getHeaders());
        foreach ($arr as $s) {
            $s = trim($s);
            if (strpos($s, 'Location: ') === 0) {
                return trim(substr($s, strlen('Location: ')));
            }
        }
        return '';
    }
    //--------------------------------------------------------------------------


    /**
     * @return SimpleBrowser
     */
    private function getBrowser() {
        $browser = new SimpleBrowser();
        $browser->setCookie(App::DEBUG_REDIRECT_COOKIE, '0');
        $browser->setMaximumRedirects(0);

        $this->browser = $browser;

        return $browser;
    }
    //--------------------------------------------------------------------------


    private function flushApcCache() {
        $url = 'http://w1.localhost/flushUserCache.php';
        file_get_contents($url);
    }
    //--------------------------------------------------------------------------
}
