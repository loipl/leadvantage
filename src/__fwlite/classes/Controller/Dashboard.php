<?php

defined('FWLITE_INCLUDED') or die('Access Denied');

class Controller_Dashboard extends Controller {


    public function indexAction() {
        App::getFrontController()->extraCss[] = 'dashboard.css';
        $this->pageTitle = 'Dashboard';

        $logIncoming = SingletonRegistry::getModelLogIncoming();

        $auth = Auth::getInstance();
        $subAccountCampaignIds = SingletonRegistry::getModelSubAccount()->listAllCampaignIds($auth->getUserId());
        $whereUserId = 'user_id = ' . $auth->getUserId();
        if (!empty($subAccountCampaignIds)) {
            $whereUserId = $whereUserId . ' OR `id` IN (' . implode(',', $subAccountCampaignIds) . ')';
        }
        $campaigns = SingletonRegistry::getModelCampaign()->listAllWhere(array('is_active' => '1', $whereUserId), 'name');
        $this->out['campaigns'] = $campaigns;
        $cids = array();
        $cass = array();
        foreach ($campaigns as $campaign) {
            $cids[] = $campaign['id'];
            $cass[$campaign['id']] = $campaign;
        }
        $cStats = $logIncoming->dashboardStats(Auth::getInstance()->getUserId(), $cids, Session::getSession()->timeZone, $cass);
        $this->out['cStats'] = $cStats;

        $subAccountPartnerIds = SingletonRegistry::getModelSubAccount()->listAllPartnerIds($auth->getUserId());
        $pnAss = SingletonRegistry::getModelPartner()->listPartnerNamesAssoc(Auth::getInstance()->getUserId(), $subAccountPartnerIds);
        $this->out['partners'] = $pnAss;

        /* @var $mat Model_AdminTip */
        $mat = SingletonRegistry::getSingleInstance('Model_AdminTip');
        $this->out['adminTip'] = $mat->getRandomTip();

        $scids = array();
        foreach ($cids as $index => $cid) {
            $scids[$index] = !empty($cass[$cid]['shadow_of']) ? (int)$cass[$cid]['shadow_of'] : $cid;
        }

        $graphStats = $logIncoming->listHourlyReportLast24Hours($cids, Session::getSession()->timeZone, $scids);
        
        $this->out['graphStats'] = $graphStats;
    }
    //--------------------------------------------------------------------------


    public function logoutAction() {
        Session::getSession()->clear();
        App::getFrontController()->redirectToUrl(Config::$wordpressUrl . 'login/?action=logout');
    }
    //--------------------------------------------------------------------------


    public function ajaxDismissMessageAction() {
        $id = isset($_POST['id']) ? max(0, (int)$_POST['id']) : 0;
        if (!$id) {
            die('Error');
        }
        $mu = SingletonRegistry::getModelUser();
        $userId = Auth::getInstance()->getUserId();
        $row = $oldRow = $mu->get($userId);
        $row['last_message_id'] = $id;
        $mu->updateDiff($userId, $row, $oldRow);
        die('OK');
    }
    //--------------------------------------------------------------------------


    public function ajaxDeletePerUserMessageAction() {
        $id = isset($_POST['id']) ? max(0, (int)$_POST['id']) : 0;
        if (!$id) {
            die('Error');
        }
        $mu = SingletonRegistry::getModelUser();
        $userId = Auth::getInstance()->getUserId();

        /* @var $messages Model_SystemMessage */
        $messages = SingletonRegistry::getSingleInstance('Model_SystemMessage');
        $msg = $messages->get($id);
        
        if (empty($msg['user_id'])) {
            die('Error');
        }
        
        if ($msg['user_id'] == $userId) {
            $messages->delete($id);
        }

        die('OK');
    }
    //--------------------------------------------------------------------------


    public static function listTimeZones() {
        $zonelist = array (
            array('America/Los_Angeles' , '(GMT-08:00) Pacific Time (US &amp; Canada)'),
            array('America/Denver' , '(GMT-07:00) Mountain Time (US &amp; Canada)'),
            array('America/Chicago' , '(GMT-06:00) Central Time (US &amp; Canada)'),
            array('America/New_York' , '(GMT-05:00) Eastern Time (US &amp; Canada)'),
            '-',
            array('Kwajalein' , '(GMT-12:00) International Date Line West'),
            array('Pacific/Midway' , '(GMT-11:00) Midway Island'),
            array('Pacific/Samoa' , '(GMT-11:00) Samoa'),
            array('Pacific/Honolulu' , '(GMT-10:00) Hawaii'),
            array('America/Anchorage' , '(GMT-09:00) Alaska'),
            array('America/Los_Angeles' , '(GMT-08:00) Pacific Time (US &amp; Canada)'),
            array('America/Tijuana' , '(GMT-08:00) Tijuana, Baja California'),
            array('America/Denver' , '(GMT-07:00) Mountain Time (US &amp; Canada)'),
            array('America/Chihuahua' , '(GMT-07:00) Chihuahua'),
            array('America/Mazatlan' , '(GMT-07:00) Mazatlan'),
            array('America/Phoenix' , '(GMT-07:00) Arizona'),
            array('America/Regina' , '(GMT-06:00) Saskatchewan'),
            array('America/Tegucigalpa' , '(GMT-06:00) Central America'),
            array('America/Chicago' , '(GMT-06:00) Central Time (US &amp; Canada)'),
            array('America/Mexico_City' , '(GMT-06:00) Mexico City'),
            array('America/Monterrey' , '(GMT-06:00) Monterrey'),
            array('America/New_York' , '(GMT-05:00) Eastern Time (US &amp; Canada)'),
            array('America/Bogota' , '(GMT-05:00) Bogota'),
            array('America/Lima' , '(GMT-05:00) Lima'),
            array('America/Rio_Branco' , '(GMT-05:00) Rio Branco'),
            array('America/Indiana/Indianapolis' , '(GMT-05:00) Indiana (East)'),
            array('America/Caracas' , '(GMT-04:30) Caracas'),
            array('America/Halifax' , '(GMT-04:00) Atlantic Time (Canada)'),
            array('America/Manaus' , '(GMT-04:00) Manaus'),
            array('America/Santiago' , '(GMT-04:00) Santiago'),
            array('America/La_Paz' , '(GMT-04:00) La Paz'),
            array('America/St_Johns' , '(GMT-03:30) Newfoundland'),
            array('America/Argentina/Buenos_Aires' , '(GMT-03:00) Georgetown'),
            array('America/Sao_Paulo' , '(GMT-03:00) Brasilia'),
            array('America/Godthab' , '(GMT-03:00) Greenland'),
            array('America/Montevideo' , '(GMT-03:00) Montevideo'),
            array('Atlantic/South_Georgia' , '(GMT-02:00) Mid-Atlantic'),
            array('Atlantic/Azores' , '(GMT-01:00) Azores'),
            array('Atlantic/Cape_Verde' , '(GMT-01:00) Cape Verde Is.'),
            array('Europe/Dublin' , '(GMT) Dublin'),
            array('Europe/Lisbon' , '(GMT) Lisbon'),
            array('Europe/London' , '(GMT) London'),
            array('Africa/Monrovia' , '(GMT) Monrovia'),
            array('Atlantic/Reykjavik' , '(GMT) Reykjavik'),
            array('Africa/Casablanca' , '(GMT) Casablanca'),
            array('Europe/Belgrade' , '(GMT+01:00) Belgrade'),
            array('Europe/Bratislava' , '(GMT+01:00) Bratislava'),
            array('Europe/Budapest' , '(GMT+01:00) Budapest'),
            array('Europe/Ljubljana' , '(GMT+01:00) Ljubljana'),
            array('Europe/Prague' , '(GMT+01:00) Prague'),
            array('Europe/Sarajevo' , '(GMT+01:00) Sarajevo'),
            array('Europe/Skopje' , '(GMT+01:00) Skopje'),
            array('Europe/Warsaw' , '(GMT+01:00) Warsaw'),
            array('Europe/Zagreb' , '(GMT+01:00) Zagreb'),
            array('Europe/Brussels' , '(GMT+01:00) Brussels'),
            array('Europe/Copenhagen' , '(GMT+01:00) Copenhagen'),
            array('Europe/Madrid' , '(GMT+01:00) Madrid'),
            array('Europe/Paris' , '(GMT+01:00) Paris'),
            array('Africa/Algiers' , '(GMT+01:00) West Central Africa'),
            array('Europe/Amsterdam' , '(GMT+01:00) Amsterdam'),
            array('Europe/Berlin' , '(GMT+01:00) Berlin'),
            array('Europe/Rome' , '(GMT+01:00) Rome'),
            array('Europe/Stockholm' , '(GMT+01:00) Stockholm'),
            array('Europe/Vienna' , '(GMT+01:00) Vienna'),
            array('Europe/Minsk' , '(GMT+02:00) Minsk'),
            array('Africa/Cairo' , '(GMT+02:00) Cairo'),
            array('Europe/Helsinki' , '(GMT+02:00) Helsinki'),
            array('Europe/Riga' , '(GMT+02:00) Riga'),
            array('Europe/Sofia' , '(GMT+02:00) Sofia'),
            array('Europe/Tallinn' , '(GMT+02:00) Tallinn'),
            array('Europe/Vilnius' , '(GMT+02:00) Vilnius'),
            array('Europe/Athens' , '(GMT+02:00) Athens'),
            array('Europe/Bucharest' , '(GMT+02:00) Bucharest'),
            array('Europe/Istanbul' , '(GMT+02:00) Istanbul'),
            array('Asia/Jerusalem' , '(GMT+02:00) Jerusalem'),
            array('Asia/Amman' , '(GMT+02:00) Amman'),
            array('Asia/Beirut' , '(GMT+02:00) Beirut'),
            array('Africa/Windhoek' , '(GMT+02:00) Windhoek'),
            array('Africa/Harare' , '(GMT+02:00) Harare'),
            array('Asia/Kuwait' , '(GMT+03:00) Kuwait'),
            array('Asia/Riyadh' , '(GMT+03:00) Riyadh'),
            array('Asia/Baghdad' , '(GMT+03:00) Baghdad'),
            array('Africa/Nairobi' , '(GMT+03:00) Nairobi'),
            array('Asia/Tbilisi' , '(GMT+03:00) Tbilisi'),
            array('Europe/Moscow' , '(GMT+03:00) Moscow'),
            array('Europe/Volgograd' , '(GMT+03:00) Volgograd'),
            array('Asia/Tehran' , '(GMT+03:30) Tehran'),
            array('Asia/Muscat' , '(GMT+04:00) Muscat'),
            array('Asia/Baku' , '(GMT+04:00) Baku'),
            array('Asia/Yerevan' , '(GMT+04:00) Yerevan'),
            array('Asia/Yekaterinburg' , '(GMT+05:00) Ekaterinburg'),
            array('Asia/Karachi' , '(GMT+05:00) Karachi'),
            array('Asia/Tashkent' , '(GMT+05:00) Tashkent'),
            array('Asia/Kolkata' , '(GMT+05:30) Calcutta'),
            array('Asia/Colombo' , '(GMT+05:30) Sri Jayawardenepura'),
            array('Asia/Katmandu' , '(GMT+05:45) Kathmandu'),
            array('Asia/Dhaka' , '(GMT+06:00) Dhaka'),
            array('Asia/Almaty' , '(GMT+06:00) Almaty'),
            array('Asia/Novosibirsk' , '(GMT+06:00) Novosibirsk'),
            array('Asia/Rangoon' , '(GMT+06:30) Yangon (Rangoon)'),
            array('Asia/Krasnoyarsk' , '(GMT+07:00) Krasnoyarsk'),
            array('Asia/Bangkok' , '(GMT+07:00) Bangkok'),
            array('Asia/Jakarta' , '(GMT+07:00) Jakarta'),
            array('Asia/Brunei' , '(GMT+08:00) Beijing'),
            array('Asia/Chongqing' , '(GMT+08:00) Chongqing'),
            array('Asia/Hong_Kong' , '(GMT+08:00) Hong Kong'),
            array('Asia/Urumqi' , '(GMT+08:00) Urumqi'),
            array('Asia/Irkutsk' , '(GMT+08:00) Irkutsk'),
            array('Asia/Ulaanbaatar' , '(GMT+08:00) Ulaan Bataar'),
            array('Asia/Kuala_Lumpur' , '(GMT+08:00) Kuala Lumpur'),
            array('Asia/Singapore' , '(GMT+08:00) Singapore'),
            array('Asia/Taipei' , '(GMT+08:00) Taipei'),
            array('Australia/Perth' , '(GMT+08:00) Perth'),
            array('Asia/Seoul' , '(GMT+09:00) Seoul'),
            array('Asia/Tokyo' , '(GMT+09:00) Tokyo'),
            array('Asia/Yakutsk' , '(GMT+09:00) Yakutsk'),
            array('Australia/Darwin' , '(GMT+09:30) Darwin'),
            array('Australia/Adelaide' , '(GMT+09:30) Adelaide'),
            array('Australia/Canberra' , '(GMT+10:00) Canberra'),
            array('Australia/Melbourne' , '(GMT+10:00) Melbourne'),
            array('Australia/Sydney' , '(GMT+10:00) Sydney'),
            array('Australia/Brisbane' , '(GMT+10:00) Brisbane'),
            array('Australia/Hobart' , '(GMT+10:00) Hobart'),
            array('Asia/Vladivostok' , '(GMT+10:00) Vladivostok'),
            array('Pacific/Guam' , '(GMT+10:00) Guam'),
            array('Pacific/Port_Moresby' , '(GMT+10:00) Port Moresby'),
            array('Asia/Magadan' , '(GMT+11:00) Magadan'),
            array('Pacific/Fiji' , '(GMT+12:00) Fiji'),
            array('Asia/Kamchatka' , '(GMT+12:00) Kamchatka'),
            array('Pacific/Auckland' , '(GMT+12:00) Auckland'),
            array('Pacific/Tongatapu' , '(GMT+13:00) Nukualofa')
        );
        return $zonelist;
    }
    //--------------------------------------------------------------------------


    public function stopImpersonateAction() {
        Session::getSession()->impersonate = '';
        App::getFrontController()->redirectToCP($this);
    }
    //--------------------------------------------------------------------------


    public function preRun() {
        parent::preRun();
        $auth = Auth::getInstance();
        $action = isset($this->params['action']) ? $this->params['action'] : 'index';
        $isPublicPage = in_array($action, Config::$publicPages);
        if ($isPublicPage) {
            $this->pageTemplate = 'outer_template';
        }

        if (!$auth->isLoggedIn()) {
            App::getFrontController()->redirectToUrl(Config::$wordpressUrl . 'login/');
        }
        App::getFrontController()->activeSection = FrontController::SECTION_DASHBOARD;
    }
    //--------------------------------------------------------------------------
}
