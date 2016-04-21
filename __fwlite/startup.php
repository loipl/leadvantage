<?php
/**
 * @desc This file should be included from index.php or from unit tests and command-line cron jobs.
 * It does not parse the request_uri, that is done in index.php
 */

if (!defined('CURLE_OPERATION_TIMEDOUT')) {
    define('CURLE_OPERATION_TIMEDOUT', 28);
}

define('FWLITE_START_TIME',  microtime(true));
define('FWLITE_INCLUDED', 1);

define('CFG_ROOT_DIR',       ensureTrailingSlash(dirname(dirname(__FILE__))));
define('CFG_FWLITE_HOME',    ensureTrailingSlash(dirname(__FILE__)));
define('CFG_COMMON_HOME',    CFG_FWLITE_HOME . 'common/');
define('CFG_CLASSES_HOME',   CFG_FWLITE_HOME . 'classes/');
define('CFG_PAGE_TEMPLATES', CFG_FWLITE_HOME . 'page_templates/');

define('TEST_USER_ID',  2);

ini_set('session.name', '__pingtree__');

require_once CFG_COMMON_HOME . '_core.php';
require_once CFG_COMMON_HOME . 'jsonpath-0.8.1.php';
require_once CFG_COMMON_HOME . 'parallelcurl.php';


class App extends App_Base {
    public  static $sqlLog = '';
    public  static $wordpressIncluded = false;
    private static $isLogging = false;

    public static $classMap = array(
        'Auth'                          => 'classes/Auth.php',
        'AuthBase'                      => 'common/AuthBase.php',
        'Auth_MultiAdmin'               => 'common/Auth/MultiAdmin.php',
        'Auth_SingleAdmin'              => 'common/Auth/SingleAdmin.php',
        'Browser'                       => 'classes/Browser.php',
        'CM__Table'                     => 'common/CrudModelMulti.php',
        'Campaign'                      => 'engine/Campaign.php',
        'Controller_Admin'              => 'classes/Controller/Admin.php',
        'Controller_Admin_Apierrors'    => 'classes/Controller/Admin/Apierrors.php',
        'Controller_Admin_Catchup'      => 'classes/Controller/Admin/Catchup.php',
        'Controller_Admin_Dashboard'    => 'classes/Controller/Admin/Dashboard.php',
        'Controller_Admin_Dictionaries' => 'classes/Controller/Admin/Dictionaries.php',
        'Controller_Admin_Engine'       => 'classes/Controller/Admin/Engine.php',
        'Controller_Admin_Export'       => 'classes/Controller/Admin/Export.php',
        'Controller_Admin_Fieldtypes'   => 'classes/Controller/Admin/Fieldtypes.php',
        'Controller_Admin_Industries'   => 'classes/Controller/Admin/Industries.php',
        'Controller_Admin_Messages'     => 'classes/Controller/Admin/Messages.php',
        'Controller_Admin_Reposting'    => 'classes/Controller/Admin/Reposting.php',
        'Controller_Admin_Response'     => 'classes/Controller/Admin/Response.php',
        'Controller_Admin_Scavenge'     => 'classes/Controller/Admin/Scavenge.php',
        'Controller_Admin_Tips'         => 'classes/Controller/Admin/Tips.php',
        'Controller_Admin_Userlevels'   => 'classes/Controller/Admin/Userlevels.php',
        'Controller_Admin_Validation'   => 'classes/Controller/Admin/Validation.php',
        'Controller_Api'                => 'classes/Controller/Api.php',
        'Controller_Application'        => 'classes/Controller/Application.php',
        'Controller_Campaigns'          => 'classes/Controller/Campaigns.php',
        'Controller_Dashboard'          => 'classes/Controller/Dashboard.php',
        'Controller_Exporting'          => 'classes/Controller/Exporting.php',
        'Controller_Partners'           => 'classes/Controller/Partners.php',
        'Controller_Postback'           => 'classes/Controller/Postback.php',
        'Controller_Reporting'          => 'classes/Controller/Reporting.php',
        'CrudController'                => 'common/CrudController.php',
        'CrudControllerWithCM'          => 'common/CrudControllerWithCM.php',
        'CrudModel'                     => 'common/CrudModel.php',
        'CrudModelCaching'              => 'classes/CrudModelCaching.php',
        'CrudModelMulti'                => 'common/CrudModelMulti.php',
        'DbCache'                       => 'classes/DbCache.php',
        'DbCache_Combined'              => 'classes/DbCache/Combined.php',
        'DbCache_Wrapper'               => 'classes/DbCache/Wrapper.php',
        'DerivedField_Base'             => 'classes/DerivedField/Base.php',
        'DerivedField_BirthYear'        => 'classes/DerivedField/BirthYear.php',
        'DerivedField_External'         => 'classes/DerivedField/External.php',
        'DerivedField_Gender'           => 'classes/DerivedField/Gender.php',
        'DerivedField_IPAddress'        => 'classes/DerivedField/IPAddress.php',
        'DerivedField_Postal'           => 'classes/DerivedField/Postal.php',
        'DerivedField_Registry'         => 'classes/DerivedField/Registry.php',
        'DerivedField_Username'         => 'classes/DerivedField/Username.php',
        'Engine'                        => 'engine/Engine.php',
        'Engine_Data'                   => 'engine/Engine/Data.php',
        'Engine_Delivery'               => 'engine/Engine/Delivery.php',
        'Engine_DeliveryCurlHelper'     => 'engine/Engine/DeliveryCurlHelper.php',
        'Engine_Flags'                  => 'engine/Engine/Flags.php',
        'Engine_IncomingData'           => 'engine/Engine/IncomingData.php',
        'Engine_IncomingLogger'         => 'engine/Engine/IncomingLogger.php',
        'Engine_Job'                    => 'engine/Engine/Job.php',
        'Engine_OneFieldValidator'      => 'engine/Engine/OneFieldValidator.php',
        'Engine_PartnerFieldHelper'     => 'engine/Engine/PartnerFieldHelper.php',
        'Engine_Repost'                 => 'engine/Engine/Repost.php',
        'Engine_Submission'             => 'engine/Engine/Submission.php',
        'Engine_Utilities'              => 'engine/Engine/Utilities.php',
        'Engine_Validator'              => 'engine/Engine/Validator.php',
        'ExternalLookup_Base'           => 'classes/ExternalLookup/Base.php',
        'ExternalLookup_RapLeaf'        => 'classes/ExternalLookup/RapLeaf.php',
        'ExternalLookup_Registry'       => 'classes/ExternalLookup/Registry.php',
        'Form_Data'                     => 'common/Form/Data.php',
        'Helper_Archiver'               => 'classes/Helper/Archiver.php',
        'Helper_Export'                 => 'classes/Helper/Export.php',
        'Helper_GeoipUpdater'           => 'classes/Helper/GeoipUpdater.php',
        'Helper_MailWarning'            => 'classes/Helper/MailWarning.php',
        'Helper_Postback'               => 'classes/Helper/Postback.php',
        'Helper_Purge'                  => 'classes/Helper/Purge.php',
        'Helper_Report'                 => 'classes/Helper/Report.php',
        'Helper_Repost'                 => 'classes/Helper/Repost.php',
        'Helper_RepostSubset'           => 'classes/Helper/RepostSubset.php',
        'Helper_SQLLog'                 => 'classes/Helper/SQLLog.php',
        'Helper_Scavenge'               => 'classes/Helper/Scavenge.php',
        'Helper_Tooltip'                => 'classes/Helper/Tooltip.php',
        'Helper_XML'                    => 'classes/Helper/XmlHelper.php',
        'CurlHelper'                    => 'classes/Helper/CurlHelper.php',
        'Lib'                           => 'common/Lib.php',
        'Model_AdminTip'                => 'classes/Model/AdminTip.php',
        'Model_Campaign'                => 'classes/Model/Campaign.php',
        'Model_CampaignField'           => 'classes/Model/CampaignField.php',
        'Model_CampaignFilter'          => 'classes/Model/CampaignFilter.php',
        'Model_CampaignSettings'        => 'classes/Model/CampaignSettings.php',
        'Model_CampaignTemplate'        => 'classes/Model/CampaignTemplate.php',
        'Model_ConfigPostsPerLevel'     => 'classes/Model/ConfigPostsPerLevel.php',
        'Model_Conversion'              => 'classes/Model/Conversion.php',
        'Model_Country'                 => 'classes/Model/Country.php',
        'Model_Dictionary'              => 'classes/Model/Dictionary.php',
        'Model_DictionaryColumn'        => 'classes/Model/DictionaryColumn.php',
        'Model_DictionaryValue'         => 'classes/Model/DictionaryValue.php',
        'Model_EmailSendLog'            => 'classes/Model/EmailSendLog.php',
        'Model_EngineConfig'            => 'classes/Model/EngineConfig.php',
        'Model_ExportFeed'              => 'classes/Model/ExportFeed.php',
        'Model_ExternalLookupCache'     => 'classes/Model/ExternalLookupCache.php',
        'Model_FieldType'               => 'classes/Model/FieldType.php',
        'Model_GeoipLocation'           => 'classes/Model/GeoipLocation.php',
        'Model_Industry'                => 'classes/Model/Industry.php',
        'Model_LogApiErrors'            => 'classes/Model/LogApiErrors.php',
        'Model_LogDelivery'             => 'classes/Model/LogDelivery.php',
        'Model_LogIncoming'             => 'classes/Model/LogIncoming.php',
        'Model_LogIncomingDuplication'  => 'classes/Model/LogIncomingDuplication.php',
        'Model_LogIncomingRepost'       => 'classes/Model/LogIncomingRepost.php',
        'Model_Notification'            => 'classes/Model/Notification.php',
        'Model_NotificationKeyed'       => 'classes/Model/NotificationKeyed.php',
        'Model_Partner'                 => 'classes/Model/Partner.php',
        'Model_PartnerField'            => 'classes/Model/PartnerField.php',
        'Model_DataList'                => 'classes/Model/DataList.php',
        'Model_DataListValue'           => 'classes/Model/DataListValue.php',
        'Model_PartnerFilter'           => 'classes/Model/PartnerFilter.php',
        'Model_PartnerSettings'         => 'classes/Model/PartnerSettings.php',
        'Model_PartnerTemplate'         => 'classes/Model/PartnerTemplate.php',
        'Model_Profile'                 => 'classes/Model/Profile.php',
        'Model_SubmissionLog'           => 'classes/Model/SubmissionLog.php',
        'Model_SystemMessage'           => 'classes/Model/SystemMessage.php',
        'Model_UsState'                 => 'classes/Model/UsState.php',
        'Model_UsZipCode'               => 'classes/Model/UsZipCode.php',
        'Model_User'                    => 'classes/Model/User.php',
        'Model_ValidationCache'         => 'classes/Model/ValidationCache.php',
        'Model_ValidationLog'           => 'classes/Model/ValidationLog.php',
        'Model_Visitor'                 => 'classes/Model/Visitor.php',
        'Model_LogPing'                 => 'classes/Model/LogPing.php',
        'Model_PartnerCap'              => 'classes/Model/PartnerCap.php',
        'Model_SubAccount'              => 'classes/Model/SubAccount.php',
        'MySQLLocker'                   => 'common/MySQLLocker.php',
        'PageFragment_FormAuto'         => 'common/PageFragment/FormAuto.php',
        'PageFragment_Pager'            => 'common/PageFragment/Pager.php',
        'PageFragment_PagerX'           => 'classes/PageFragment/PagerX.php',
        'Pager'                         => 'classes/Pager.php',
        'Partner'                       => 'engine/Partner.php',
        'Util_HeaderLinks'              => 'common/Util/HeaderLinks.php',
        'Validator_Base'                => 'classes/Validator/Base.php',
        'Validator_Brite'               => 'classes/Validator/Brite.php',
        'Validator_DV'                  => 'classes/Validator/DV.php',
        'Validator_LeadSpend'           => 'classes/Validator/LeadSpend.php',
        'Validator_Registry'            => 'classes/Validator/Registry.php',
        'Validator_XVerify'             => 'classes/Validator/XVerify.php',
        'X000004_MailConfig'            => 'plugins/X000004/MailConfig.php',
        'X000004_PHPMailer'             => 'plugins/X000004/PHPMailer.php',
        'X000004_SMTP'                  => 'plugins/X000004/SMTP.php',
        'X000004_Util_Mailer'           => 'plugins/X000004/Util/Mailer.php',
        'X000006_Auth'                  => 'plugins/X000006/Auth.php',
        'X000006_Ext_Login'             => 'plugins/X000006/Ext/Login.php',
        'X000006_Ext_LoginEx'           => 'plugins/X000006/Ext/LoginEx.php',
        'X000006_Ext_Users'             => 'plugins/X000006/Ext/Users.php',
        'X000006_Model_User'            => 'plugins/X000006/Model/User.php',
        'phpmailerException'            => 'plugins/X000004/PHPMailer.php',
    );

    const API_HASH_PARAM = '* ha\tsh *';

    const SQL_LOG_COOKIE        = '12#$56&*90!@34%^78()';
    const DEBUG_REDIRECT_COOKIE = '__fwlite_debug_redirect__SDFERT$%^&DXSFRTYE#$%^YHDTRFGHDERFTGH__';


    public static function init() {
        if (self::$initialized) {
            return;
        }
        parent::init();
        Engine_Settings::$settings = new Engine_Settings;
        if (!Config::$logSQL) {
            Config::$logSQL = !empty($_COOKIE[self::SQL_LOG_COOKIE]);
        }
        if (array_key_exists(self::DEBUG_REDIRECT_COOKIE, $_COOKIE)) {
            Config::$debugRedirect = !empty($_COOKIE[self::DEBUG_REDIRECT_COOKIE]);
        }

        $_SERVER['* REMOTE_ADDR'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Missing';
        Lib::getRealIP();

        if ((strpos($_SERVER['REQUEST_URI'], '/api/') === 0) || (strpos($_SERVER['REQUEST_URI'], '/postback/') === 0)) {
            Config::$checkNoncePost = false;
        } elseif(strpos($_SERVER['REQUEST_URI'], '/cron/') !== 0) {
            Session::getSession();
        }

        self::$classPaths[]= CFG_FWLITE_HOME . 'plugins/';
        self::$classPaths[]= CFG_FWLITE_HOME . 'engine/';

        self::$frontController->getMapper()->addGetMapping('/api/', array(self::API_HASH_PARAM));
        self::$frontController->getMapper()->addGetMapping('/postback/', array(self::API_HASH_PARAM));
        if (Config::$logSQL) {
            DB::addGlobalListener('App::sqlListener', false);
        }
        DB::addGlobalListener('App::sqlListenerForLogging', true);
    }
    //--------------------------------------------------------------------------


    public static function autoload($className) {
        if (isset(self::$classMap[$className])) {
            include_once CFG_FWLITE_HOME . self::$classMap[$className];
            if (class_exists($className, false)) {
                return true;
            };
        }
        $fileName = isset(self::$classesFileNames[$className]) ? self::$classesFileNames[$className] : parent::getPathForClass($className);
        if ($fileName === false) {
            return false;
        } else {
            include_once $fileName;
            return true;
        }
    }
    //----------------------------------------------------------------------------


    public static function getPathForClass($className) {
        if (isset(self::$classMap[$className])) {
            return CFG_FWLITE_HOME . self::$classMap[$className];
        } else {
            return parent::getPathForClass($className);
        }
    }
    //----------------------------------------------------------------------------


    public static function sqlListener($sql, array $params, $sqlToRun, DB $db, $sqlErr, $sqlErrNo, $queryTime) {
        $bgColor = $sqlErr ? '#faa;' : '#eee';
        App::$sqlLog .= "<pre style=\"padding: 2px 9px; margin: 0; background-color: $bgColor\"><b>/* " . number_format($queryTime, 4) . "s */</b> ";
        App::$sqlLog .= escapeHtml($sqlToRun) . ";";
        if ($sqlErr) {
            App::$sqlLog .= "\n<span style=\"font-weight: bold;\">/* $sqlErrNo: " . escapeHtml(wordwrap($sqlErr, 100)) . " */</span>";
        }

        if (!empty($_COOKIE[self::SQL_LOG_COOKIE]) && (stripos($_COOKIE[self::SQL_LOG_COOKIE], 'L') !== false)) {
            ob_start();
            debug_print_backtrace();
            App::$sqlLog .= "\n<span style=\"color: #888; font-size: 75%;\">" . escapeHtml(ob_get_clean());
            App::$sqlLog .= "</span>";
        }

        App::$sqlLog .= "</pre>\n\n";
        if (Config::$devEnvironment && $sqlErr) {
            Config::$debugRedirect = true;
        }
    }
    //--------------------------------------------------------------------------


    public static function sqlListenerForLogging($sql, array $params, $sqlToRun, DB $db, $sqlErr, $sqlErrNo, $queryTime) {
        if (self::$isLogging) {
            return;
        }
        self::$isLogging = true;
        $params = array($sqlToRun, $sqlErr, $sqlErrNo, serialize(debug_backtrace()));
        $sess = isset($_SESSION) ? $_SESSION : array();

        $params[] =
        "\$_SERVER  = " . var_export($_SERVER, true) . ";\n\n" .
                "\$_GET     = " . var_export($_GET, true) . ";\n\n" .
                "\$_POST    = " . var_export($_POST, true) . ";\n\n" .
                "\$_COOKIE  = " . var_export($_COOKIE, true) . ";\n\n" .
                "\$_SESSION = " . var_export($sess, true) . ";\n\n" .
                "\$_ENV     = " . var_export($_ENV, true) . ";";

        try {
            DB::$db->query("INSERT INTO `sql_log` (`req_time`, `query`, `error`, `error_nr`, `call_stack`, `tracking_data`) VALUES (NOW(), ?, ?, ?, ?, ?)", $params);
            self::$isLogging = false;
        } catch (Exception $e) {
            self::$isLogging = false;

        }
    }
    //--------------------------------------------------------------------------


    public static function getClassMap() {
        return self::$classMap;
    }
    //--------------------------------------------------------------------------
}

/**
 * @desc Configuration values for Engine. Will be updated from DB at start of processing,
 * or periodically from cron job as it's running.
 */
class Engine_Settings {
    /**
     * @desc Timeout for processing one incoming submission
     */
    public $processingTimeout = 30;

    /**
     * @desc Timeout for partner response
     */
    public $deliveryTimeout = 10;

    /**
     * @desc How long will the cron process run
     */
    public $cronTimeout = 2940;

    /**
     * @desc Size of repost queue subset - preselected subset of most urgent repost queue entries
     */
    public $repostQueueMemSize = 5000;

    /**
     * @desc How many entries are processed in one repost cycle
     */
    public $oneRepostBatchSize = 10;

    /**
     * @var Engine_Settings
     */
    public static $settings;
}


class Config extends Config_Base {
    public static $logSQL         = false;
    public static $useApc         = false;

    public static $smtpHost       = '';
    public static $smtpAuth       = false;
    public static $smtpPassword   = '';
    public static $smtpSecure     = '';
    public static $smtpUsername   = '';
    public static $smtpFromName   = '';
    public static $smtpFromEmail  = '';

    public static $wordpressPath  = '';
    public static $wordpressUrl   = '';

    public static $errorLogDir    = false;
    public static $sha1Salt       = '3*bG/~]]as##gt';
    public static $publicPages    = array('login', 'logout', 'sendPasswordResetMail', 'resetPassword', 'resetSent', 'signup', 'verify', 'resendVerificationEmail', 'mailSent');

    public static $smtpDebug      = false;
    public static $rapLeafApiKey  = '';
    public static $visitorCookie  = '_ptvid';
    public static $botScoutKey    = '';

    public static $disableRapLeaf = true;
    
    public static $validatorKeys  = array();

    public static $nonceErrorText = 'Nonce error - please press back button in your browser and reload the page';
    public static $timeZone       = 'America/Los_Angeles';

    public static $keepDelivery   = 90;
    public static $keepApiReport  = 120;
    public static $keepIncoming   = 365;

    public static $validatorCache = true;
    public static $validCacheHrs  = 24;

    public static $quotaPercentageWarning = 80;

    public static $useSummaryTablesForDashboard = false;

    public static $codeCoverageOutputFile = false;

    public static $repostThreadLogging = false;

    public static $repostThreadId = 0;

    public static $lockPrefix = 'pingtree.';

    public static $logDbConnectivityIssuesToFileName = false;

    public static $pathToPhpIniFile = false;

    public static $testDbLw = array();

    public static $userIdForReassigningDeletedCampaigns = 59;

    /**
     * @desc Array of IP addresses we do not want to write to DB
     */
    public static $serverIPAddresses = array();


    public static $requiredLevelsForExternalLookupTokens = array (
        'age' => array (
            'level'  => Auth::L_S2_LEVEL7,
            'ftypes' => array(Model_CampaignField::FIELD_TYPE_AGE, Model_CampaignField::FIELD_TYPE_YEAR)
        ),
        'gender' => array (
            'level'  => Auth::L_S2_LEVEL7,
            'ftypes' => array(Model_CampaignField::FIELD_TYPE_GENDER)
        ),
        'education' => array (
            'level'  => Auth::L_S2_LEVEL7,
            'ftypes' => array(Model_CampaignField::FIELD_TYPE_EDUCATION)
        ),
        'home_owner' => array (
            'level'  => Auth::L_S2_LEVEL7,
            'ftypes' => array(Model_CampaignField::FIELD_TYPE_HOME_OWNER)
        ),
        'years_at_address' => array (
            'level'  => Auth::L_S2_LEVEL7,
            'ftypes' => array(Model_CampaignField::FIELD_TYPE_YEARS_AT_ADDRESS, Model_CampaignField::FIELD_TYPE_MONTHS_AT_ADDRESS)
        ),
        'months_at_address' => array (
            'level'  => Auth::L_S2_LEVEL7,
            'ftypes' => array(Model_CampaignField::FIELD_TYPE_MONTHS_AT_ADDRESS, Model_CampaignField::FIELD_TYPE_YEARS_AT_ADDRESS)
        ),
        'married' => array (
            'level'  => Auth::L_S2_LEVEL7,
            'ftypes' => array(Model_CampaignField::FIELD_TYPE_MARRIED)
        ),
        'occupation' => array (
            'level'  => Auth::L_S2_LEVEL7,
            'ftypes' => array(Model_CampaignField::FIELD_TYPE_OCCUPATION)
        ),
        'children' => array (
            'level'  => Auth::L_S2_LEVEL7,
            'ftypes' => array(Model_CampaignField::FIELD_TYPE_CHILDREN)
        ),
        'home_value' => array (
            'level'  => Auth::L_S2_LEVEL7,
            'ftypes' => array(Model_CampaignField::FIELD_TYPE_HOME_VALUE)
        ),
        'annual_income' => array (
            'level'  => Auth::L_S2_LEVEL7,
            'ftypes' => array(Model_CampaignField::FIELD_TYPE_ANNUAL_INCOME, Model_CampaignField::FIELD_TYPE_MONTHLY_INCOME)
        ),
        'monthly_income' => array (
            'level'  => Auth::L_S2_LEVEL7,
            'ftypes' => array(Model_CampaignField::FIELD_TYPE_MONTHLY_INCOME, Model_CampaignField::FIELD_TYPE_ANNUAL_INCOME)
        ),
        'postal_code' => array (
            'level'  => Auth::L_S2_LEVEL7,
            'ftypes' => array(Model_CampaignField::FIELD_TYPE_POSTAL_CODE, Model_CampaignField::FIELD_TYPE_STATE, Model_CampaignField::FIELD_TYPE_STATE_CODE, Model_CampaignField::FIELD_TYPE_CITY, Model_CampaignField::FIELD_TYPE_COUNTRY, Model_CampaignField::FIELD_TYPE_COUNTRY_CODE)
        ),
        'interests' => array (
            'level'  => Auth::L_S2_LEVEL7,
            'ftypes' => array(Model_CampaignField::FIELD_TYPE_INTERESTS)
        ),
        'high_net_worth' => array (
            'level'  => Auth::L_S2_LEVEL7,
            'ftypes' => array(Model_CampaignField::FIELD_TYPE_HIGH_NET_WORTH)
        ),
        'city' => array (
            'level'  => Auth::L_S2_LEVEL7,
            'ftypes' => array(Model_CampaignField::FIELD_TYPE_CITY)
        ),
        'state_or_region' => array (
            'level'  => Auth::L_S2_LEVEL7,
            'ftypes' => array(Model_CampaignField::FIELD_TYPE_STATE)
        ),
        'state_or_region_code' => array (
            'level'  => Auth::L_S2_LEVEL7,
            'ftypes' => array(Model_CampaignField::FIELD_TYPE_STATE_CODE)
        ),
    );


    public static function initExternalConfig($key, $object = null) {
        if ($key == 'X000004_MailConfig') {
            X000004_MailConfig::$smtpHost      = self::$smtpHost;
            X000004_MailConfig::$smtpAuth      = self::$smtpAuth;
            X000004_MailConfig::$smtpPassword  = self::$smtpPassword;
            X000004_MailConfig::$smtpSecure    = self::$smtpSecure;
            X000004_MailConfig::$smtpUsername  = self::$smtpUsername;
            X000004_MailConfig::$smtpFromName  = self::$smtpFromName;
            X000004_MailConfig::$smtpFromEmail = self::$smtpFromEmail;

            X000004_MailConfig::$smtpDebug     = self::$smtpDebug;
        } elseif ($object instanceof PageFragment_FormAuto) {
            $object->tableAttributes['class'] = 'form_table';
        }
    }
    //--------------------------------------------------------------------------
}


abstract class Controller extends Controller_Base {
    //

    public function postRun() {
        // Deliberately left empty - you can override this function in your controller classes and add custom behavior
    }
    //--------------------------------------------------------------------------


    public function postMortem() {
        $this->postRun();
    }
    //--------------------------------------------------------------------------
}


class DB extends DB_Base {
    /**
     * @var DB
     */
    public static $wpDb = null;

    /**
     * @var DbCache
     */
    public static $cache;

    const DEADLOCK_RETRY_COUNT = 5;


    public function query($sql, array $params = array()) {
        $this->dbLink or $this->connect();
        $sqlToRun = $params ? $this->processParams($sql, $params) : $sql;

        // Retry the query in case mysql error number is 1213, ie "Deadlock found when trying to get lock; try restarting transaction"
        for($i = 0; $i < self::DEADLOCK_RETRY_COUNT; $i++) {
            $startTime = microtime(true);
            $result    = mysql_query($sqlToRun, $this->dbLink);
            $queryTime = microtime(true) - $startTime;
            if (($result === false) && (mysql_errno($this->dbLink) == 1213)) {
                continue;
            }
            break;
        }
        $this->queryCount++;
        self::$globalQueryCount++;
        $this->queryTime       += $queryTime;
        self::$globalQueryTime += $queryTime;
        $error = mysql_error($this->dbLink);
        $errNo = mysql_errno($this->dbLink);

        foreach (self::$globalListeners['all'] as $callback) {
            if (is_callable($callback)) {
                call_user_func($callback, $sql, $params, $sqlToRun, $this, $error, $errNo, $queryTime);
            }
        }
        foreach ($this->listeners['all'] as $callback) {
            if (is_callable($callback)) {
                call_user_func($callback, $sql, $params, $sqlToRun, $this, $error, $errNo, $queryTime);
            }
        }

        if ($result === false) {
            foreach (self::$globalListeners['error'] as $callback) {
                if (is_callable($callback)) {
                    call_user_func($callback, $sql, $params, $sqlToRun, $this, $error, $errNo, $queryTime);
                }
            }
            foreach ($this->listeners['error'] as $callback) {
                if (is_callable($callback)) {
                    call_user_func($callback, $sql, $params, $sqlToRun, $this, $error, $errNo, $queryTime);
                }
            }
        }

        return $result;
    }
    //--------------------------------------------------------------------------
}


class FrontController extends FrontController_Base {
    public $requireJQuery   = false;
    public $requireJQueryUI = false;

    public $extraCss = array();

    public $messages = array();

    public $lastMessageId = 0;

    const SECTION_DASHBOARD     = 'Dashboard';
    const SECTION_CAMPAIGN      = 'Campaigns';
    const SECTION_PARTNERS      = 'Partners';
    const SECTION_REPORTING     = 'Reporting';
    const SECTION_ADMIN         = 'Admin';
    const SECTION_SUB_ACCOUNT   = 'SubAccount';

    public $sections        = array(self::SECTION_DASHBOARD, self::SECTION_CAMPAIGN, self::SECTION_PARTNERS, self::SECTION_REPORTING);

    public $activeSection = '';


    public function __construct() {
        parent::__construct();
        $this->mapper->controllerRegexReplacements = array();
        $this->errorTemplate = 'outer_template';
        $this->defaultPageTemplate = 'new_template';
    }
    //--------------------------------------------------------------------------


    public function preRun($class, array $params) {
        if (($class == 'Controller_Api') || ($class == 'Controller_Cron') || ($class == 'Controller_Exporting') || ($class == 'Controller_Postback')) {
            return;
        }
        $auth = App::$wordpressIncluded ? Auth::getInstance() : null;
        if ($auth && $auth->isAdmin()) {
            $this->sections[] = self::SECTION_ADMIN;
        }
        if (strpos($class, 'Controller_Admin') === 0) {
            if ($auth && !$auth->isAdmin()) {
                if ($auth->isLoggedIn()) {
                    throw new EAccessDenied();
                } else {
                    Session::getSession()->returnUrl = App::getFrontController()->getMapper()->originalUrl;

                    App::getFrontController()->redirectToUrl(Config::$wordpressUrl . 'login/?redirect_to=' . urlencode(Session::getSession()->returnUrl));
                }
            } else {
                $this->activeSection = self::SECTION_ADMIN;
            }
        }
        if ($auth && !$auth->isLoggedIn() && !($class == 'Controller_Application' && isset($params['action']) && in_array($params['action'], Config::$publicPages))) {
            Session::getSession()->returnUrl = App::getFrontController()->getMapper()->originalUrl;
            App::getFrontController()->redirectToUrl(Config::$wordpressUrl . 'login/?redirect_to=' . urlencode(Session::getSession()->returnUrl));
        }
        
        $userModel = SingletonRegistry::getSingleInstance('Model_User');
        $configPerLevelModel = SingletonRegistry::getSingleInstance('Model_ConfigPostsPerLevel');
        $userCap = $userModel->listUserCaps($auth->getUserId());
        if (isset($userCap[0])) {
            $subAccountLimit = $configPerLevelModel->getMaxSubAccountLimit($userCap[0]);
        }
        if (isset($subAccountLimit[0]) && $subAccountLimit[0] > 0) {
            $this->sections[] = self::SECTION_SUB_ACCOUNT;
        }
        
        $userId = $auth->getUserId();
        $subAccountModel = SingletonRegistry::getModelSubAccount();
        $subAccountInfo = $subAccountModel->checkIfUserIsSubAccount($userId);
        $isRevokeReportingAccess = $subAccountModel->checkIfRevokeReportingAccess($userId);
        if (!empty($subAccountInfo) && $isRevokeReportingAccess) {
            if(($key = array_search(self::SECTION_REPORTING, $this->sections)) !== false) {
                unset($this->sections[$key]);
            }
        }
    }
    //--------------------------------------------------------------------------


    protected function handleRedirect(ERedirectException $e) {
        if (Config::$unitTestMode) {
            // We're inside a unit test, just re-throw the exception
            throw $e;
        }
        $url = $e->getMessage();

        if (Config::$debugRedirect) {
            echo <<< END
        <html>
        <head>
        <title>Debug Redirecting</title>
        </head>
        <body><h2 style="text-align: center; margin: 40px;">Debug redirection is turned on.<br /> Script is redirecting you to:<br /><a href="$url">$url</a></h2></body>
END;
            if (Config::$devEnvironment && App::$sqlLog) {
                echo '
            <h3>SQL Log:<br /></h3>';
                echo App::$sqlLog;
                list($count, $time) = DB::getGlobalStats();
                echo "Total of $count queries in {$time}s";
            }
            echo "\n</html>";
        } else {
            header("Location: $url");
            echo <<< END
        <html>
        <head>
        <title>Redirecting...</title>
        <meta http-equiv="refresh" content="1;url=$url">
        </head>
        <body>
        Redirecting. If your browser isn't automatically redirected,
        please click <a href="$url">here</a>.
  </body>
</html>
END;
        }
        die;
    }
    //--------------------------------------------------------------------------


    protected function handleException(Exception $e) {
        $this->controller = new ErrorController($e, array($this, 'content'));

        $pageTemplate = CFG_PAGE_TEMPLATES . $this->errorTemplate . '.php';
        if (is_readable($pageTemplate)) {
            include $pageTemplate;
        }
    }
    //--------------------------------------------------------------------------


    public function content(Exception $e) {
        $s = "<pre style=\"border: 2px dashed red; padding: 20px; color: red; margin: 20px;\">There was an error: " . $e->getMessage();
        if (Config::$devEnvironment) {
            $s .= "\n" . $e->getFile() . ":" . $e->getLine();
            $s .= "\n\n" . $e->getTraceAsString();
        }
        $s .= "</pre>";

        return $s;
    }
    //--------------------------------------------------------------------------


    public function checkCampaignsWithNoPartners() {
        $auth = App::$wordpressIncluded ? Auth::getInstance() : null;
        if (!$auth) {
            return array();
        }
        $mc = SingletonRegistry::getModelCampaign();
        return $mc->listCampaignsWithNoDeliverySettings($auth->getUserId());
    }
    //--------------------------------------------------------------------------
    
    public function checkPartnersWithInvalidTemplate() {
        $auth = App::$wordpressIncluded ? Auth::getInstance() : null;
        if (!$auth) {
            return array();
        }
        $mc = SingletonRegistry::getModelPartner();
        return $mc->listPartnersWithInvalidTemplate($auth->getUserId());
    }
    //--------------------------------------------------------------------------
    
    public static function checkIfUserHasNoPartner($getPingPostPartner = null) {
        $auth = App::$wordpressIncluded ? Auth::getInstance() : null;
        if (!$auth) {
            return false;
        }
        $mp = SingletonRegistry::getModelPartner();
        $whereUserId = 'user_id = ' . $auth->getUserId();
        $subAccountPartnerIds = SingletonRegistry::getModelSubAccount()->listAllPartnerIds($auth->getUserId());
        $subAccountPartnerList = SingletonRegistry::getModelPartner()->listPartnersWithIDs($subAccountPartnerIds, $getPingPostPartner);
        
        $count = $mp->countForUser($whereUserId, $getPingPostPartner) + count($subAccountPartnerList);
        return ($count == '0');
    }
    //--------------------------------------------------------------------------

    public static function checkIfUserHasNoCampaignRule() {
        $auth = App::$wordpressIncluded ? Auth::getInstance() : null;
        if (!$auth) {
            return false;
        }
        if (!$auth->isAdminLoggedIn()) {
            return false;
        }
        $mcr = SingletonRegistry::getModelCampaignFilter();
        $count = $mcr->countForUser($auth->getUserId());
        return ($count == '0');
    }
    //--------------------------------------------------------------------------

    protected function getMessagesToArray() {
        $auth = App::$wordpressIncluded ? Auth::getInstance() : null;
        if (!$auth) {
            return array();
        }

        /* @var $messages Model_SystemMessage */
        $messages = SingletonRegistry::getSingleInstance('Model_SystemMessage');
        $this->out['__nonce'] = Session::getSession()->getNonce();

        $msgList = $messages->listMessagesAfterId($auth->lastMessageId(), $auth->getUserId());
        App::getFrontController()->messages = $msgList;

        if ($msgList) {
            App::getFrontController()->lastMessageId = (int)$msgList[0]['id'];
        } else {
            App::getFrontController()->lastMessageId = 0;
        }
    }
    //--------------------------------------------------------------------------


    public function getNotifications() {
        $auth = App::$wordpressIncluded ? Auth::getInstance() : null;
        if (!$auth) {
            return array();
        }

        $arr = SingletonRegistry::getModelNotification()->listAndDelete($auth->getUserId());
        return $arr;
    }
    //--------------------------------------------------------------------------


    public function getOverQuotaNotifications() {
        $auth = App::$wordpressIncluded ? Auth::getInstance() : null;
        if (!$auth) {
            return array();
        }
        if (!$auth->isAdmin()) {
            /* @var $mul Model_ConfigPostsPerLevel */
            $mul = SingletonRegistry::getSingleInstance('Model_ConfigPostsPerLevel');
            
            /* @var $subaccountModel Model_SubAccount */
            $subaccountModel = SingletonRegistry::getSingleInstance('Model_SubAccount');
            $subAccountInfo = $subaccountModel->checkIfUserIsSubAccount($auth->getUserId());
            if (!empty($subAccountInfo)) {
                return array();
            }
            
            $levelsAssoc = $mul->listLevelsAssoc();

            $mu = SingletonRegistry::getModelUser();
            $caps = $mu->listUserCaps($auth->getUserId());
            $max = max(0, Engine_IncomingData::getMaxDeliveriesForUserLevel($levelsAssoc, $caps));
            $arr = array();
            if ($max > 0) {
                $li = SingletonRegistry::getModelLogIncoming();
                $count = $li->getTotalSuccessfulCount($auth->getUserId());

                if ($count >= ($max * Config::$quotaPercentageWarning / 100)) {
                    $percentage = (int)($count / $max * 100);
                    $arr[] = "You are at $percentage% of your $max monthly lead quota";
                }
            }
            return $arr;
        }
        return array();
    }
    //--------------------------------------------------------------------------
}


class Model extends Model_Base {
    //
}


abstract class PageFragment extends PageFragment_Base {
    //
}


class Session extends Session_Base {
    public $impersonate = '';

    public $reportColumns = array();

    public $wasLoggedIn = false;

    public $timeZone = '';


    /**
     * @return Session
     */
    public static function getSession() {
        static $started = false;
        if (!$started) {
            ini_set('session.use_cookies',      1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_trans_sid',    0);

            session_start();
            $started = true;
        }
        if (isset($_SESSION[self::ENTRY_NAME]) && $_SESSION[self::ENTRY_NAME] instanceof Session) {
            /* @var $sess Session_Base */
            $sess = $_SESSION[self::ENTRY_NAME];
            if ($sess->checkBrowserVars) {
                $sess->checkBrowserVars();
            }
        }
        if (empty($_SESSION[self::ENTRY_NAME]) || !($_SESSION[self::ENTRY_NAME] instanceof Session)) {
            $_SESSION[self::ENTRY_NAME] = new Session;
        }
        return $_SESSION[self::ENTRY_NAME];
    }
    //--------------------------------------------------------------------------
}


class SingletonRegistry extends SingletonRegistry_Base {


    /**
     * @desc Returns singleton instance of $className,
     * creates one using default constructor or $callback if it does not exist.
     *
     * <p>For classes inherited from CrudModelCaching it will wrap them in DbCache_Wrapper if caching
     * is turned on.
     */
    public static function getSingleInstance($className, $callback = false, array $callBackParams = array()) {
        if (!self::contains($className)) {
            $object = $callback ? call_user_func_array($callback, $callBackParams) : new $className();

            // We only want to wrap Model if caching is turned on, it is an instance of CrudModelCaching and it has
            // some functions that need to be cached.
            if (DB::$cache && $object instanceof CrudModelCaching && !empty(DbCache_Wrapper::$cachedFunctions[$className])) {
                self::set(new DbCache_Wrapper($object), $className);
            } else {
                self::set($object);
            }
        }
        return self::get($className);
    }
    //--------------------------------------------------------------------------


    /**
     * @return Model_User
     */
    public static function getModelUser() {
        return self::getSingleInstance('Model_User');
    }
    //--------------------------------------------------------------------------


    /**
     * @return Model_UsState
     */
    public static function getModelUsState() {
        return self::getSingleInstance('Model_UsState');
    }
    //--------------------------------------------------------------------------


    /**
     * @return Model_Campaign
     */
    public static function getModelCampaign() {
        return self::getSingleInstance('Model_Campaign');
    }
    //--------------------------------------------------------------------------


    /**
     * @return Model_Campaign
     */
    public static function getModelLogPing() {
        return self::getSingleInstance('Model_LogPing');
    }
    //--------------------------------------------------------------------------



    /**
     * @return Model_Conversion
     */
    public static function getModelConversion() {
        return self::getSingleInstance('Model_Conversion');
    }
    //--------------------------------------------------------------------------


    /**
     * @return Model_CampaignField
     */
    public static function getModelCampaignField() {
        return self::getSingleInstance('Model_CampaignField');
    }
    //--------------------------------------------------------------------------

    
    /**
     * @return Model_CampaignFilter
     */
    public static function getModelCampaignFilter() {
        return self::getSingleInstance('Model_CampaignFilter');
    }
    //--------------------------------------------------------------------------
    
    
    /**
     * @return Model_SubAccount
     */
    public static function getModelSubAccount() {
        return self::getSingleInstance('Model_SubAccount');
    }
    //--------------------------------------------------------------------------
    

    /**
     * @return Model_Country
     */
    public static function getModelCountry() {
        return self::getSingleInstance('Model_Country');
    }
    //--------------------------------------------------------------------------


    /**
     * @return Model_Partner
     */
    public static function getModelPartner() {
        return self::getSingleInstance('Model_Partner');
    }
    //--------------------------------------------------------------------------


    /**
     * @return Model_PartnerField
     */
    public static function getModelPartnerField() {
        return self::getSingleInstance('Model_PartnerField');
    }
    //--------------------------------------------------------------------------

    
    /**
     * @return Model_PartnerField
     */
    public static function getModelDataList() {
        return self::getSingleInstance('Model_DataList');
    }
    //--------------------------------------------------------------------------
    
    
    /**
     * @return Model_PartnerField
     */
    public static function getModelDataListValue() {
        return self::getSingleInstance('Model_DataListValue');
    }
    //--------------------------------------------------------------------------
    

    /**
     * @return Model_PartnerFilter
     */
    public static function getModelPartnerFilter() {
        return self::getSingleInstance('Model_PartnerFilter');
    }
    //--------------------------------------------------------------------------
    
    /**
     * @return Model_PartnerCap
     */
    public static function getModelPartnerCap() {
        return self::getSingleInstance('Model_PartnerCap');
    }
    //--------------------------------------------------------------------------


    /**
     * @return Model_Notification
     */
    public static function getModelNotification() {
        return self::getSingleInstance('Model_Notification');
    }
    //--------------------------------------------------------------------------


    /**
     * @return Model_LogIncoming
     */
    public static function getModelLogIncoming() {
        return self::getSingleInstance('Model_LogIncoming');
    }
    //--------------------------------------------------------------------------
    
    
    /**
     * @return Model_LogIncomingDuplication
     */
    public static function getModelLogIncomingDuplication() {
        return self::getSingleInstance('Model_LogIncomingDuplication');
    }
    //--------------------------------------------------------------------------


    /**
     * @return Model_LogIncomingRepost
     */
    public static function getModelLogIncomingRepost() {
        return self::getSingleInstance('Model_LogIncomingRepost');
    }
    //--------------------------------------------------------------------------


    /**
     * @return Model_LogDelivery
     */
    public static function getModelLogDelivery() {
        return self::getSingleInstance('Model_LogDelivery');
    }
    //--------------------------------------------------------------------------


    /**
     * @return Model_GeoipLocation
     */
    public static function getModelGeoipLocation() {
        return self::getSingleInstance('Model_GeoipLocation');
    }
    //--------------------------------------------------------------------------


    /**
     * @return Model_UsZipCode
     */
    public static function getModelUsZipCode() {
        return self::getSingleInstance('Model_UsZipCode');
    }
    //--------------------------------------------------------------------------


    /**
     * @return Model_EngineConfig
     */
    public static function getModelEngineConfig() {
        return self::getSingleInstance('Model_EngineConfig');
    }
    //--------------------------------------------------------------------------
}


function ensureTrailingSlash($path) {
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/') {
        $path .= '/';
    }
    return $path;
}
//----------------------------------------------------------------------------
