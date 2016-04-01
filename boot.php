<?php

defined('PINGTREE_STARTED') or die('Access Denied');

error_reporting(E_ERROR || E_WARNING);
ini_set('display_errors', 1);

if (empty($_SERVER['REQUEST_METHOD'])) {
    $_SERVER['REQUEST_METHOD'] = 'GET';
}

function pingtree_exception_handler(Exception $exc) {
    trigger_error(get_class($exc) . ', ' . " - " . $exc->getMessage());
}

function pingtree_errh($errno, $errstr, $errfile, $errline) {
    static $wpPath = false;
    static $myPath = false;
    if (!$wpPath) {
        $wpPath = realpath(dirname(__FILE__))  . '/wp';
        $myPath = realpath(dirname(__FILE__) . '/');
    }
    $wpPath  = str_replace('\\', '/', $wpPath);
    $myPath  = str_replace('\\', '/', $myPath);
    $errfile = str_replace('\\', '/', $errfile);
    if (strpos($errfile, $wpPath) === 0) {
        // do not handle wordpress files
        return false;
    }
    $errNames = array(
    1     => 'ERROR',
    2     => 'WARNING',
    4     => 'PARSE',
    8     => 'NOTICE',
    16    => 'CORE_ERROR',
    32    => 'CORE_WARNING',
    64    => 'COMPILE_ERROR',
    128   => 'COMPILE_WARNING',
    256   => 'USER_ERROR',
    512   => 'USER_WARNING',
    1024  => 'USER_NOTICE',
    2048  => 'STRICT',
    4096  => 'RECOVERABLE_ERROR',
    8192  => 'DEPRECATED',
    16384 => 'USER_DEPRECATED',
    );
    $errfile = substr($errfile, strlen($myPath));
    $_SERVER['* errors'][] = date('Y-m-d H:i:s') . " $errfile:$errline " . (isset($errNames[$errno]) ? "[{$errNames[$errno]}]" : "[$errno]") . " $errstr";
    $key = "$errfile:$errline\n" . (isset($errNames[$errno]) ? "[{$errNames[$errno]}]" : "[$errno]") . " $errstr";
    $_SERVER['* err4db'][$key] = 1 + (isset($_SERVER['* err4db'][$key]) ? $_SERVER['* err4db'][$key] : 0);
    return false;
};

function pingtree_shutdown() {
    if (empty($_SERVER['* errors']) && empty($_SERVER['* err4db'])) {
        return;
    }

    if (class_exists('Config', false) && !empty(Config::$errorLogDir) && is_dir(Config::$errorLogDir)) {
        $dir = Config::$errorLogDir;
        $fp = fopen($dir . 'php-error-log.txt', 'at');
        if ($fp) {
            fputs($fp, date('Y-m-d H:i:s') . ' ' . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['HTTP_HOST'] . '/' . $_SERVER['REQUEST_URI'] . "\n---------------------------------------------\n");
            foreach($_SERVER['* errors'] as $err) {
                fputs($fp, $err . "\n");
            }
            fputs($fp, "\n");
            fclose($fp);
        }
    }
    
    DB::$db->query("ROLLBACK");

    foreach ($_SERVER['* err4db'] as $key => $count) {
        $arr = explode("\n", $key);
        $sql = "INSERT INTO `php_log` (`file_line`, `text`, `last_occurred_time`) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE counter = counter + ?, last_occurred_time = NOW()";
        $params = array(trim($arr[0]), trim($arr[1]), (int)$count);
        try {
            DB::$db->query($sql, $params);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}

$_SERVER['* errors'] = array();
$_SERVER['* err4db'] = array();
set_error_handler('pingtree_errh');
set_exception_handler('pingtree_exception_handler');
register_shutdown_function('pingtree_shutdown');

function isPingtree() {
    $s = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $isPingtree =
    ((strpos($s, '/dashboard/') === 0) ||
    (strpos($s, '/admin/') === 0) ||
    (strpos($s, '/campaigns/') === 0) ||
    (strpos($s, '/partners/') === 0) ||
    (strpos($s, '/subaccount/') === 0) ||
    (strpos($s, '/postback/') === 0) ||
    (strpos($s, '/reporting/') === 0) ||
    (strpos($s, '/api/') === 0) ||
    (strpos($s, '/exporting/') === 0) ||
    (strpos($s, '/cron/') === 0)) &&
    ($s != '/');
    return $isPingtree;
}

if (!isPingtree()) {
    class App {
        public static function processRequest() {}
    };
    define('WP_USE_THEMES', true);
    require dirname(__FILE__) . '/wp-blog-header.php';
    return;
};

require_once dirname(__FILE__) . '/__fwlite/startup.php';


App::init();

// This has to be done from global space, cannot be closed into a function
// because wordpress sets global vars

$includeWP = true; $refreshUserRightsCache = false;
if (defined('NO_WORDPRESS') || (strpos((isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/'), '/api/') === 0)) {
    $includeWP = false;
    $modelUser = SingletonRegistry::getModelUser();
    if (!$modelUser->countOfUserCapCacheEntries()) {
        $includeWP = true;
        $refreshUserRightsCache = true;
    }
}

if ($includeWP) {
    require_once Config::$wordpressPath . 'wp-config.php';
    is_user_logged_in();

    // Wordpress will apparently addslashes() to get, post etc, so we need to undo that
    $input = array(& $_GET, & $_POST, & $_REQUEST);
    App::unquoteArray($input);
    unset($input);
    App::$wordpressIncluded = true;
    error_reporting(E_ERROR || E_WARNING);
    ini_set('display_errors', 1);
}

if ($refreshUserRightsCache && $modelUser) {
    $modelUser->recreateUserCapCache();
}

if (Config::$timeZone) {
    date_default_timezone_set(Config::$timeZone);
}

if ((PHP_SAPI != 'cli') && isset($_SERVER['REQUEST_URI']) && (strpos($_SERVER['REQUEST_URI'], '/api/') === 0)) {
    // For now only use APC caching in /api/ url calls
    DB::$cache = DbCache::getCache();
}
