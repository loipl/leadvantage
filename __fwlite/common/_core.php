<?php

defined('FWLITE_INCLUDED') or die('Access Denied');


abstract class App_Base {

    public static $classPaths = array(CFG_COMMON_HOME, CFG_CLASSES_HOME);

    public static $route = '__route__';

    public static $classesFileNames = array();

    protected static $initialized = false;

    /**
     * @var FrontController
     */
    protected static $frontController = false;


    public static function processRequest() {
        if (!self::$initialized) {
            App::init();
        }
        if (empty(self::$frontController)) {
            die("Front Controller is not set");
        }
        self::$frontController->run();
    }
    //--------------------------------------------------------------------------


    /**
     * @return FrontController
     */
    public static function getFrontController() {
        return self::$frontController;
    }
    //--------------------------------------------------------------------------


    public static function init() {
        if (self::$initialized) {
            return;
        }
        if (isset($_SERVER['HTTP_HOST']) && htmlentities($_SERVER['HTTP_HOST'], ENT_QUOTES) != $_SERVER['HTTP_HOST']) {
            die;
        }
        self::$initialized = true;

        if (function_exists('get_magic_quotes_gpc') && (get_magic_quotes_gpc() == 1)) {
            $input = array(& $_GET, & $_POST, & $_COOKIE, & $_ENV, & $_SERVER);
            self::unquoteArray($input);
        }

        if (ini_get('register_globals')) {
            foreach (array_diff(array_keys($GLOBALS), array('_GET', '_POST', '_REQUEST',
                '_COOKIE', '_SESSION',  '_SERVER', '_ENV', '_FILES', 'GLOBALS',
                'HTTP_GET_VARS', 'HTTP_POST_VARS', 'HTTP_COOKIE_VARS', 'HTTP_SERVER_VARS',
                'HTTP_ENV_VARS', 'HTTP_POST_FILES')) as $v)
            {
                unset($GLOBALS[$v]);
            };
        }

        spl_autoload_register(array('App', 'autoload'));
        $configFile = Config::findFileInFolder(CFG_FWLITE_HOME . 'config/');
        include $configFile;

        if (empty(self::$frontController)) {
            self::$frontController = new FrontController();
        }
        unset($_GET[self::$route]);
    }
    //--------------------------------------------------------------------------


    public static function unquoteArray(& $input) {
        for ($k = 0; $k < sizeof($input); $k++) {
            foreach ($input[$k] as $key => $val) {
                if (!is_array($val)) {
                    $input[$k][$key] = stripslashes($val);
                    continue;
                }
                $input[] = & $input[$k][$key];
            }
        }
    }
    //--------------------------------------------------------------------------


    public static function getUrlParts() {
        if (!empty($_GET[self::$route])) {
            $url = $_GET[self::$route];
            unset($_GET[self::$route]);
        } else {
            $url = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : '/';
            $p = strpos($url, '?');
            if ($p !== false) {
                $url = substr(0, $p);
            }
        }
        if (strpos($url, '/') !== 0) {
            $url = '/' . $url;
        }
        $urlParts = explode('/', substr($url, 1));
        return $urlParts;
    }
    //--------------------------------------------------------------------------


    public static function linkFor($class, $text, array $params = array(), array $get = array(), $att = '') {
        return '<a href="' . self::$frontController->urlFor($class, $params, $get) . "\" $att>$text</a>";
    }
    //--------------------------------------------------------------------------


    public static function autoload($className) {
        $fileName = isset(self::$classesFileNames[$className]) ? self::$classesFileNames[$className] : self::getPathForClass($className);
        if ($fileName === false) {
            return false;
        } else {
            include_once $fileName;
            return true;
        }
    }
    //----------------------------------------------------------------------------


    public static function getPathForClass($className) {
        if (isset(self::$classesFileNames[$className])) {
            return self::$classesFileNames[$className];
        } elseif(Config::$useApc) {
            $success = false;
            $s = apc_fetch(__FILE__ . '/App::$classesFileNames/' . $className, $success);
            if ($success) {
                return self::$classesFileNames[$className] = $s;
            }
        }
        $classFileName = str_replace('_', '/', $className) . '.php';
        foreach (self::$classPaths as $homeFolder) {
            $fileName = $homeFolder . $classFileName;
            if (is_readable($fileName)) {
                if (Config::$useApc) {
                    apc_store(__FILE__ . '/App::$classesFileNames/' . $className, $fileName);
                } else {
                    self::$classesFileNames[$className] = $fileName;
                }
                return $fileName;
            }
        }
        return false;
    }
    //----------------------------------------------------------------------------
}


abstract class Session_Base {
    const ENTRY_NAME = ' __Session_Base_obj__ ';

    public $returnUrl;

    protected $nonce;

    protected $authData = array();

    protected $userAgent      = '';

    protected $checkBrowserVars = true;


    protected function __construct() {
        $this->nonce = sha1(mt_rand() . rand() . microtime(true));

        $this->userAgent      = isset($_SERVER['HTTP_USER_AGENT'])      ? $_SERVER['HTTP_USER_AGENT'] : '';
    }
    //--------------------------------------------------------------------------


    public function getNonce() {
        return $this->nonce;
    }
    //--------------------------------------------------------------------------


    public function getAuthData() {
        return $this->authData;
    }
    //--------------------------------------------------------------------------


    public function setAuthData(array $authData) {
        $this->authData = $authData;
    }
    //--------------------------------------------------------------------------


    public function getCheckBrowserVars() {
        return $this->checkBrowserVars;
    }
    //--------------------------------------------------------------------------


    public function setCheckBrowserVars($checkBrowserVars = true) {
        $this->checkBrowserVars = $checkBrowserVars;
    }
    //--------------------------------------------------------------------------


    public function checkBrowserVars() {
        $condition = ($this->userAgent == (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''));
        if (!$condition) {
            unset($_SESSION[self::ENTRY_NAME]);
            session_destroy();
        }
    }
    //--------------------------------------------------------------------------


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


    public function clear() {
        unset($_SESSION[self::ENTRY_NAME]);
        session_destroy();
    }
    //--------------------------------------------------------------------------
}


abstract class Url_Mapper {
    public $originalUrl = false;
    public $urlParts = array();
    public $urlBase = '/';
    public $preferredExtension = '.html';
    public $classAbbreviations = array('a' => 'Application');
    public $urlReplacementMaps = array();
    public $controllerRegexReplacements = array('[A-Z][\\d]{6,6}');
    protected $getMaps = array();


    abstract public function getCPForUrl(& $class, & $params, $url = false);

    abstract public function getUrlForCP($class, array $params = array(), array $get = array());


    public function translateClass($class) {
        if ($class instanceof Controller) {
            $pc = $class->getParentController();
            $result = get_class($pc ? $pc : $class);
        } else {
            if ((strpos($class, '://') !== false) || (strpos($class, '..') !== false)) {
                throw new EError404();
            }
            $class = isset($this->classAbbreviations[$class]) ? $this->classAbbreviations[$class] : $class;
            $result = (strpos($class, 'Controller_') !== 0) ? "Controller_$class" : $class;
        }
        foreach ($this->controllerRegexReplacements as $replacement) {
            if (preg_match("/(?P<replacement>$replacement)_Controller(?P<name>[\\w]{1,})/", $result, $matches)) {
                $result = "Controller_{$matches['replacement']}" . (isset($matches['name']) ? $matches['name'] : '');
                break;
            }
        }
        return $result;
    }
    //--------------------------------------------------------------------------


    public function addGetMapping($urlPrefix, array $getMap) {
        if ((substr($urlPrefix, 0, 1) != '/') || (substr($urlPrefix, -1) != '/')) {
            throw new EServerError("urlPrefix must start and end with a slash");
        }
        $this->getMaps[$urlPrefix] = $getMap;
    }
    //--------------------------------------------------------------------------
}


/**
 * @desc Maps paths to urls like this:<br />
 * <b>/some/sub/folder/ <=> Controller_Some_Sub::folderAction()</b><br />
 * last part, ie /folder/ will be in $params['action']
 */
class Url_Mapper_Simple extends Url_Mapper {

    public static $rootController = 'Controller_Application';

    public function getCPForUrl(& $class, & $params, $url = false) {
        if (isset($_SERVER["REQUEST_URI"]) && ($_SERVER["REQUEST_URI"] == '/favicon.ico')) {
            die;
        }
        if ($url === false) {
            $start = substr($_SERVER['PHP_SELF'], 0, -strlen('/index.php'));
            $this->urlBase = Config::$urlBase = "$start/";

            $url = substr($_SERVER["REQUEST_URI"], strlen($start));
            if (substr($url, 0, 10) == '/index.php') {
                header('Location: /', 1, 301);
                exit;
            }
        }

        $this->originalUrl = $url;
        foreach ($this->urlReplacementMaps as $reqUrl => $mappedUrl) {
            if (strpos($url, $mappedUrl) === 0) {
                throw new EError404();
            }
            if (strpos($url, $reqUrl) === 0) {
                $url = $mappedUrl . substr($url, strlen($reqUrl));
                break;
            }
        }
        $parts = parse_url($url);
        $path = isset($parts['path']) ? $parts['path'] : '/';
        if (isset($parts['query'])) {
            parse_str($parts['query'], $get);
        } else {
            $get = array();
        }
        foreach ($this->getMaps as $urlPrefix => $getArr) {
            $s = (substr($urlPrefix, 0, 1) == '/') ? $urlPrefix : "/$urlPrefix";
            $s = (substr($urlPrefix, -1)   == '/') ? $urlPrefix : "$urlPrefix/";
            if (strpos($path, $s) === 0) {
                $p = substr($path, strlen($s));
                $path = $s;
                $g = explode('/', $p);
                foreach ($g as $index => $arg) {
                    if (!isset($getArr[$index])) {
                        continue;
                    }
                    $get[$getArr[$index]] = $arg;
                    $params[$getArr[$index]] = $arg;
                }
            }
        }

        if (strpos($url, '/') !== 0) {
            $url = '/' . $url;
        }
        $urlParts = explode('/', substr($path, 1));
        $this->urlParts = $urlParts;
        $size = sizeof($urlParts);

        if ($size == 1) {
            $class = self::$rootController;
        } else {
            $class = 'Controller';
            for ($i = 0; $i < $size - 1; $i++) {
                $class .= '_' . ucfirst($urlParts[$i]);
            }
        }
        foreach ($this->controllerRegexReplacements as $replacement) {
            if (preg_match("/Controller_(?P<replacement>$replacement)(?P<name>[\\w]{1,})/", $class, $matches)) {
                $class = "{$matches['replacement']}_Controller" . (isset($matches['name']) ? $matches['name'] : '');
                break;
            }
        }

        if (empty($params['action'])) {
            $action = $urlParts[sizeof($urlParts) - 1];
            if (!$action) {
                $action = 'index';
            }
            $action = str_replace('-', '_', $action);
            if ($this->preferredExtension && (substr($action, -strlen($this->preferredExtension)) == $this->preferredExtension)) {
                $action = substr($action, 0, -strlen($this->preferredExtension));
            }
            $params['action'] = $action;
        }
        $_GET = $get;
    }
    //--------------------------------------------------------------------------


    public function getUrlForCP($class, array $params = array(), array $get = array()) {
        $class = $this->translateClass($class);
        $url = ($class == self::$rootController) ? '' : str_replace('_', '/', strtolower(substr($class, strpos($class, '_') + 1))) . "/";
        foreach ($this->urlReplacementMaps as $reqUrl => $mappedUrl) {
            if (strpos("/$url", $mappedUrl) === 0) {
                $url = substr($reqUrl, 1) . substr($url, strlen($mappedUrl) - 1);
                break;
            }
        }
        foreach ($this->getMaps as $urlPrefix => $getArr) {
            if (strpos("/$url", $urlPrefix) === 0) {
                $pushed = false;
                if (isset($params['action']) && !isset($get['action'])) {
                    $get['action'] = $params['action'];
                    $pushed = $params['action'];
                }
                foreach ($getArr as $arg) {
                    if (isset($get[$arg])) {
                        if ($arg == 'action') {
                            $pushed = false;
                        }
                        $url .= (substr($url, -1) == '/') ? urlencode($get[$arg]) : '/' . urlencode($get[$arg]);
                        unset($get[$arg]);
                        unset($params[$arg]);
                    }
                }
                if ($pushed !== false) {
                    $params['action'] = $pushed;
                    unset($get['action']);
                }
            }
        }
        $action = isset($params['action']) ? $params['action'] : 'index';
        $action = ($action == 'index') ? '' : $action . $this->preferredExtension;
        $action = str_replace('_', '-', $action);
        $url .= $action;
        if (!empty($get)) {
            $url .= '?' . http_build_query($get);
        }
        return $this->urlBase . $url;
    }
    //--------------------------------------------------------------------------
}


abstract class Config_Base {
    public static $debugRedirect  = false;
    public static $devEnvironment = false;
    public static $unitTestMode   = false;
    public static $encoding       = 'UTF-8';
    public static $siteTitle      = '';
    public static $sha1Salt       = '';

    public static $useApc         = false;

    public static $urlBase        = '/';

    public static $publicPages    = array();

    public static $nonceErrorText = "Nonce error - please reload the page and try again";

    /**
     * @desc Whether framework should check __nonce value on every POST request
     */
    public static $checkNoncePost = false;


    public static function findFileInFolder($folder, $extension = 'php', $hostName = '', $defaultHostName = '') {
        if (!$hostName) {
            $hostName = empty($_SERVER['HTTP_HOST']) ? $defaultHostName : $_SERVER['HTTP_HOST'];
        }
        $folder   = ensureTrailingSlash($folder);
        $fileName = $hostName ? "$hostName.config.$extension" : "config.$extension";
        for(;;) {
            if (is_readable($folder . $fileName)) {
                return $folder . $fileName;
            }
            $fileName = substr($fileName, strpos($fileName, '.') + 1);
            if ($fileName == $extension) {
                break;
            }
        }
        throw new EServerError('Missing config file');
    }
    //--------------------------------------------------------------------------


    public static function initExternalConfig($key, $object = null) {
        //
    }
    //--------------------------------------------------------------------------
}


abstract class Controller_Base {
    protected $params = array();

    protected $pageTemplate = false;

    protected $viewFile = false;

    protected $assumeClassName = '';

    protected $content = false;

    protected $out = array();

    protected $pageTitle = '';

    protected $functionPrefix = '';

    protected $appendToContent = '';

    protected $prependToContent = '';

    protected $prependToContentFile = '';

    protected $appendToContentFile = '';

    /**
     * @var Controller
     */
    protected $parentController = null;

    protected $extensionControllers = array();

    /**
     * @var Controller
     */
    protected $handlingController = null;

    protected $jsPrepend = array();

    protected $jsPrependIncluded = false;


    public function __construct() {
        if (is_callable(array($this, 'init'))) {
            $this->init();
        }
    }
    //--------------------------------------------------------------------------


    public function setParams(array $params = array()) {
        $this->params = $params;
    }
    //--------------------------------------------------------------------------


    public function getPageTitle() {
        return ($this->pageTitle ? $this->pageTitle . ' - ' : '') . Config::$siteTitle;
    }
    //--------------------------------------------------------------------------


    public function getPageTemplate() {
        return $this->pageTemplate;
    }
    //--------------------------------------------------------------------------
    
    
    public function getJsPrepend() {
        return $this->jsPrepend;
    }
    //--------------------------------------------------------------------------


    public function setPageTemplate($fileName) {
        $this->pageTemplate = $fileName;
    }
    //--------------------------------------------------------------------------


    public function getViewFile() {
        return $this->viewFile;
    }
    //--------------------------------------------------------------------------


    public function setViewFile($fileName) {
        $this->viewFile = $fileName;
    }
    //--------------------------------------------------------------------------


    public function set($outputVar, $value) {
        $this->out[$outputVar] = $value;
    }
    //--------------------------------------------------------------------------


    public function get($outputVar) {
        return isset($this->out[$outputVar]) ? $this->out[$outputVar] : null;
    }
    //--------------------------------------------------------------------------


    public function setParentController(Controller $parentController = null) {
        $this->parentController = $parentController;
    }
    //--------------------------------------------------------------------------


    /**
     * @return Controller
     */
    public function getParentController() {
        return $this->parentController;
    }
    //--------------------------------------------------------------------------


    public function setFunctionPrefix($functionPrefix) {
        $this->functionPrefix = $functionPrefix;
    }
    //--------------------------------------------------------------------------


    protected function setHandlingController(Controller $controller = null) {
        $this->handlingController = $controller;
    }
    //--------------------------------------------------------------------------


    public function addExtension($className, $actionMatch = '', $actionPrefix = '') {
        $this->extensionControllers[$className] = array(
            'actionRegex'  => $actionMatch,
            'actionPrefix' => $actionPrefix
        );
    }
    //--------------------------------------------------------------------------


    public function run() {
        $originalAction = ((!empty($this->params['action']) && is_string($this->params['action'])) ? $this->params['action'] : '');
        $function = ($originalAction ? $originalAction : 'index') . 'Action';
        $wasInitialized = false;
        if (is_callable(array($this, $function . 'Init'))) {
            $this->{$function . 'Init'}();
            $wasInitialized = true;
        }
        if ((isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] == 'POST')) && is_callable(array($this, $function . 'Post'))) {
            $this->{$function . 'Post'}();
            throw new EDoneException();
        } elseif (is_callable(array($this, $function . 'Get'))) {
            $this->{$function . 'Get'}();
            throw new EDoneException();
        } elseif (is_callable(array($this, $function))) {
            $this->$function();
            throw new EDoneException();
        } else {
            if ($wasInitialized) {
                throw new EDoneException();
            }

            foreach ($this->extensionControllers as $className => $data) {
                if ($data['actionRegex'] && !preg_match($data['actionRegex'], $originalAction)) {
                    continue;
                }
                $params = $this->params;
                if ($data['actionPrefix']) {
                    if (strpos($originalAction, $data['actionPrefix']) !== 0) {
                        continue;
                    }
                    $action = substr($originalAction, strlen($data['actionPrefix']));
                    $params['action'] = strtolower(substr($action, 0, 1)) . substr($action, 1);
                }

                $pc = $this->parentController ? $this->parentController : $this;
                $o = new $className;
                $o->setParentController($pc);
                $o->setFunctionPrefix($data['actionPrefix']);
                $o->setParams($params);

                $pc->setHandlingController($o);
                try {
                    try {
                        $o->preRun();
                        $o->run();
                    } catch (EDoneException $e) {
                        $o->postMortem();
                    }
                    throw new EDoneException();
                } catch (EError404 $e) {
                    if ($this->parentController == null) {
                        throw $e;
                    }
                }
                $pc->setHandlingController(null);
            }

            throw new EError404();
        }
    }
    //--------------------------------------------------------------------------


    public static function getViewFileFor($className, array $params = array()) {
        if (($className instanceof Controller) || ($className instanceof PageFragment)) {
            $className = get_class($className);
        }
        $fileName = App::getPathForClass($className);
        if ($fileName === false) {
            throw new EServerError("Unable to find class file for class '$className'");
        }
        $p = strrpos($className, '_');
        $lastPart = substr($className, $p + 1);
        $action = (isset($params['action']) && is_string($params['action'])) ? $params['action'] : 'index';
        return str_replace('\\', '//', dirname($fileName) . "/{$lastPart}-view/" . "$action.phtml");
    }
    //--------------------------------------------------------------------------


    public function show($return = false) {
        if ($this->handlingController) {
            $this->handlingController->show();
            return;
        }

        if ($this->prependToContentFile) {
            include $this->prependToContentFile;
        }
        echo $this->prependToContent;

        if ($this->content !== false) {
            echo $this->content;
        } else {
            if (empty($this->viewFile)) {
                $this->viewFile = Controller::getViewFileFor($this->assumeClassName ? $this->assumeClassName : get_class($this), $this->params);
            }
            if (is_readable($this->viewFile)) {
                extract($this->out, EXTR_REFS);
                include $this->viewFile;
            }
        }

        echo $this->appendToContent;
        if ($this->appendToContentFile) {
            include $this->appendToContentFile;
        }

        if ($this->viewFile) {
            if (substr($this->viewFile, -6) == '.phtml') {
                $js = substr($this->viewFile, 0, -5) . 'js';
                if (is_readable($js)) {
                    echo "\n" . implode("\n", $this->jsPrepend) . "\n";
                    $this->jsPrependIncluded = true;
                    echo "<script type=\"text/javascript\">\n";
                    include $js;
                    echo "\n</script>";
                }
            }
            $dvf = Controller::getViewFileFor($this->assumeClassName ? $this->assumeClassName : get_class($this), $this->params);
            if (($this->viewFile != $dvf) && (substr($dvf, -6) == '.phtml')) {
                $js = substr($dvf, 0, -5) . 'js';
                if (is_readable($js)) {
                    if (!$this->jsPrependIncluded) {
                        echo "\n" . implode("\n", $this->jsPrepend) . "\n";
                        $this->jsPrependIncluded = true;
                    }
                    echo "<script type=\"text/javascript\">\n";
                    include $js;
                    echo "\n</script>";
                }
            }
        }
    }
    //--------------------------------------------------------------------------


    public function getOutputVars() {
        return $this->out;
    }
    //--------------------------------------------------------------------------


    public function isAjax()  {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }
    //--------------------------------------------------------------------------


    public function ajaxResponse() {
        App::getFrontController()->setUsePageTemplate(false);
        header('Content-Type: application/json');
        echo json_encode($this->out);
    }
    //--------------------------------------------------------------------------


    public function urlFor($class, array $params = array(), array $get = array()) {
        return App::getFrontController()->urlFor($class, $params, $get);
    }
    //--------------------------------------------------------------------------


    public function preRun() {
        //
    }
    //--------------------------------------------------------------------------


    public function postMortem() {
        //
    }
    //--------------------------------------------------------------------------
}


class FrontController_Base {
    /**
     * @var Url_Mapper
     */
    protected $mapper = null;

    /**
     * @var Controller
     */
    protected $controller;

    protected $usePageTemplate = true;

    protected $defaultPageTemplate = 'default_template';

    protected $errorTemplate       = 'default_template';


    public function __construct() {
        $this->mapper = new Url_Mapper_Simple();
    }
    //--------------------------------------------------------------------------


    /**
     * @return Url_Mapper
     */
    public function getMapper() {
        return $this->mapper;
    }
    //--------------------------------------------------------------------------


    public function run($url = false) {
        try {
            if (Config::$checkNoncePost && isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] == 'POST')) {
                if (empty($_POST['__nonce']) || ($_POST['__nonce'] != Session::getSession()->getNonce())) {
                    throw new ERequestError(Config::$nonceErrorText);
                }
            }
            $class = '';
            $params = array();
            $this->mapper->getCPForUrl($class, $params, $url);
            
            if (!class_exists($class, true)) {
                throw new EError404();
            }
            $this->preRun($class, $params);

            $this->controller = new $class;
            $this->controller->setParams($params);
            try {
                $this->controller->preRun();
                $this->controller->run($params);
            } catch (EDoneException $e) {
                $this->controller->postMortem();
            }

            if ($this->usePageTemplate) {
                $pageTemplate = $this->controller->getPageTemplate();
                if (!$pageTemplate) {
                    $pageTemplate = $this->defaultPageTemplate;
                }
                $pageTemplate = CFG_PAGE_TEMPLATES . $pageTemplate . '.php';
                if (is_readable($pageTemplate)) {
                    include $pageTemplate;
                }
            } else {
                $this->controller->show();
            }

        } catch (EError404 $e) {
            header("HTTP/1.0 404 Not Found");
            die("<h1>Error 404 - File not found</h1>");
        } catch (EAccessDenied $e) {
            header("HTTP/1.0 403 Access Forbidden");
            die("<h1>Error 403 - Access Forbidden</h1>");
        } catch (ERedirectException $e) {
            $this->handleRedirect($e);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    //--------------------------------------------------------------------------


    public function preRun($class, array $params) {
        //
    }
    //--------------------------------------------------------------------------


    public function setUsePageTemplate($use) {
        $this->usePageTemplate = $use;
    }
    //--------------------------------------------------------------------------


    public function setErrorTemplate($errorTemplate) {
        $this->errorTemplate = $errorTemplate;
    }
    //--------------------------------------------------------------------------


    public function redirectToCP($class, array $params = array(), array $get = array()) {
        $url = $this->mapper->getUrlForCP($class, $params, $get);
        throw new ERedirectException($url);
    }
    //--------------------------------------------------------------------------


    public function urlFor($class, array $params = array(), array $get = array()) {
        return $this->mapper->getUrlForCP($class, $params, $get);
    }
    //--------------------------------------------------------------------------


    public function redirectToUrl($url) {
        throw new ERedirectException($url);
    }
    //--------------------------------------------------------------------------


    protected function handleException(Exception $e) {
        $this->controller = new ErrorController($e);

        $pageTemplate = CFG_PAGE_TEMPLATES . $this->errorTemplate . '.php';
        if (is_readable($pageTemplate)) {
            include $pageTemplate;
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
</html>
END;
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


    public function addReplacementUrl($reqUrl, $mappedUrl) {
        $this->mapper->urlReplacementMaps[$reqUrl] = $mappedUrl;
    }
    //----------------------------------------------------------------------------


    public function addGetMapping($urlPrefix, array $getArr) {
        $this->mapper->addGetMapping($urlPrefix, $getArr);
    }
    //----------------------------------------------------------------------------


    public function getCPForUrl(& $class, & $params, $url = false) {
        $this->mapper->getCPForUrl($class, $params, $url);
    }
    //----------------------------------------------------------------------------
}


class ErrorController extends Controller_Base {

    public function __construct(Exception $e, $callback = null) {
        parent::__construct();
        if ($callback) {
            $this->content = call_user_func($callback, $e);
        } else {
            $this->content = "<pre style=\"border: 2px dashed red; padding: 20px; color: red;\">There was an error: " . $e->getMessage();
            if (Config::$devEnvironment) {
                $this->content .= "\n\n" . $e->getTraceAsString();
            }
            $this->content .= "</pre>";
        }
    }
    //----------------------------------------------------------------------------
}


abstract class PageFragment_Base {

    protected $viewFile = false;

    protected $content = false;

    protected $out = array();

    protected $return = false;

    /**
     * @var Controller
     */
    protected $controller;


    public function __construct(Controller $controller = null) {
        $this->controller = $controller;
    }
    //--------------------------------------------------------------------------


    public function getViewFile() {
        return $this->viewFile;
    }
    //--------------------------------------------------------------------------


    public function setViewFile($fileName) {
        $this->viewFile = $fileName;
    }
    //--------------------------------------------------------------------------


    public function output($return = false) {
        $this->return = $return;
        unset($return);
        if (!$this->viewFile) {
            $this->viewFile = Controller::getViewFileFor(get_class($this));
        }
        if ($this->content !== false) {
            if ($this->return) {
                return $this->content;
            }
            echo $this->content;
            return;
        }
        if ($this->return) {
            ob_start();
        }
        if (is_readable($this->viewFile)) {
            extract($this->out, EXTR_REFS);
            include $this->viewFile;
        }
        if ($this->return) {
            return ob_get_clean();
        }
    }
    //--------------------------------------------------------------------------


    public static function create(Controller $controller = null) {
        die;
    }
    //--------------------------------------------------------------------------
}


abstract class DB_Base {
    /**
     * @var DB
     */
    public static $db = null;

    /**
     * @desc Number of queries ran across all instances
     */
    protected static $globalQueryCount = 0;
    protected static $globalQueryTime  = 0.0;

    /**
     * @desc All-instances listeners for all queries or just the ones with errors
     */
    protected static $globalListeners = array('all' => array(), 'error' => array());

    protected static $instanceCount = 0;

    /**
     * @desc Number of queries ran on this mysql link
     */
    protected $queryCount = 0;

    protected $queryTime  = 0.0;

    protected $hostName    = false;
    protected $userName    = false;
    protected $password    = false;
    protected $dbName      = false;
    protected $permanent   = false;
    protected $tablePrefix = '';
    protected $dbLink      = false;

    protected $originalTableNames = null;

    protected $instanceNr = 0;

    /**
     * @desc Per-connection listeners for all queries or just the ones with errors
     */
    protected $listeners = array('all' => array(), 'error' => array());


    public function __construct($host, $userName, $password, $db, $permanent = false, $tablePrefix = '') {
        $this->hostName    = $host;
        $this->userName    = $userName;
        $this->password    = $password;
        $this->dbName      = $db;
        $this->permanent   = $permanent;

        $this->instanceNr = ++self::$instanceCount;

        if ($tablePrefix) {
            $this->setTablePrefix($tablePrefix);
        } else {
            $this->tablePrefix = $tablePrefix;
        }
    }
    //--------------------------------------------------------------------------


    public static function getGlobalStats() {
        return array(self::$globalQueryCount, number_format(self::$globalQueryTime, 4));
    }
    //--------------------------------------------------------------------------


    public function getInstanceNr() {
        return $this->instanceNr;
    }
    //--------------------------------------------------------------------------


    public function connect() {
        if ($this->dbLink === false) {
            if ($this->permanent) {
                $this->dbLink = mysql_pconnect($this->hostName, $this->userName, $this->password);
            } else {
                $this->dbLink = mysql_connect($this->hostName,  $this->userName, $this->password, true);
            }

            if (!$this->dbLink) {
                throw new EServerError("DB $this->instanceNr: Cannot connect to MySQL server at '$this->hostName' as user '$this->userName'");
            }
            if (!mysql_query("USE `$this->dbName`")) {
                $this->dbLink = false;
                throw new EServerError("DB $this->instanceNr: Cannot use MySQL DB '$this->dbName'");
            }
        }
    }
    //--------------------------------------------------------------------------


    public function getLink($connect = false) {
        if ($connect && !$this->dbLink) {
            $this->connect();
        }
        return $this->dbLink;
    }
    //--------------------------------------------------------------------------


    public function getTablePrefix() {
        return $this->tablePrefix;
    }
    //--------------------------------------------------------------------------


    public function setTablePrefix($tablePrefix) {
        if (is_null($this->originalTableNames)) {
            $this->originalTableNames = array();
            foreach (get_object_vars($this) as $k => $v) {
                if (strpos($k, 't_') === 0) {
                    $this->originalTableNames[$k] = empty($v) ? substr($k, 2) : $v;
                }
            }
        }
        foreach ($this->originalTableNames as $varName => $tableName) {
            $this->$varName = "$tablePrefix$tableName";
        }
        $this->tablePrefix = $tablePrefix;
    }
    //--------------------------------------------------------------------------


    public function processParams($queryString, array $params = array()) {
        if (sizeof($params) == 0) {
            return $queryString;
        }
        if (substr_count($queryString, '?') != sizeof($params)) {
            throw new EServerError("Missmatch in argument count, $queryString");
        }
        $arr = explode('?', $queryString);
        $current = 0;
        $result  = '';
        foreach ($params as $val) {
            $result .= $arr[$current++];
            if (is_string($val)) {
                if (!$this->dbLink) {
                    $this->connect();
                }
                $result .= "'" . mysql_real_escape_string($val, $this->dbLink) . "'";
            } elseif (is_double($val)) {
                // locales fix so 1.1 does not get converted to 1,1
                $result .= str_replace(',', '.', $val);
            } elseif (is_bool($val)) {
                $result .= $val ? 1 : 0;
            } elseif (is_null($val)) {
                $result .= 'NULL';
            } else {
                $result .= $val;
            }
        };
        if (isset($arr[$current])) {
            $result .= $arr[$current];
        }
        return $result;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Adds a listener for all DB connections
     */
    public static function addGlobalListener($callback, $errorOnly = true) {
        self::$globalListeners[$errorOnly ? 'error' : 'all'][] = $callback;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Adds a listener for this DB instance
     */
    public function addListener($callback, $errorOnly = true) {
        $this->listeners[$errorOnly ? 'error' : 'all'][] = $callback;
    }
    //--------------------------------------------------------------------------


    public function clearInstanceListeners($clearAll = true, $clearError = true) {
        if ($clearAll) {
            $this->listeners['all'] = array();
        }
        if ($clearError) {
            $this->listeners['error'] = array();
        }
    }
    //--------------------------------------------------------------------------


    public static function clearGlobalListeners($clearAll = true, $clearError = true) {
        if ($clearAll) {
            self::$globalListeners['all'] = array();
        }
        if ($clearError) {
            self::$globalListeners['error'] = array();
        }
    }
    //--------------------------------------------------------------------------


    public function query($sql, array $params = array()) {
        $this->dbLink or $this->connect();
        $sqlToRun = $params ? $this->processParams($sql, $params) : $sql;

        $startTime = microtime(true);
        $result    = mysql_query($sqlToRun, $this->dbLink);
        $queryTime = microtime(true) - $startTime;
        $this->queryCount++;
        self::$globalQueryCount++;
        $this->queryTime       += $queryTime;
        self::$globalQueryTime += $queryTime;

        foreach (self::$globalListeners['all'] as $callback) {
            if (is_callable($callback)) {
                call_user_func($callback, $sql, $params, $sqlToRun, $this, mysql_error($this->dbLink), mysql_errno($this->dbLink), $queryTime);
            }
        }
        foreach ($this->listeners['all'] as $callback) {
            if (is_callable($callback)) {
                call_user_func($callback, $sql, $params, $sqlToRun, $this, mysql_error($this->dbLink), mysql_errno($this->dbLink), $queryTime);
            }
        }

        if ($result === false) {
            foreach (self::$globalListeners['error'] as $callback) {
                if (is_callable($callback)) {
                    call_user_func($callback, $sql, $params, $sqlToRun, $this, mysql_error($this->dbLink), mysql_errno($this->dbLink), $queryTime);
                }
            }
            foreach ($this->listeners['error'] as $callback) {
                if (is_callable($callback)) {
                    call_user_func($callback, $sql, $params, $sqlToRun, $this, mysql_error($this->dbLink), mysql_errno($this->dbLink), $queryTime);
                }
            }
        }

        return $result;
    }
    //--------------------------------------------------------------------------


    public function lastInsertId() {
        $this->dbLink or $this->connect();
        return (int)mysql_insert_id($this->dbLink);
    }
    //--------------------------------------------------------------------------


    public function affectedRows() {
        $this->dbLink or $this->connect();
        return (int)mysql_affected_rows($this->dbLink);
    }
    //--------------------------------------------------------------------------


    public function getArray($queryString, array $params = array(), $mysql_mode = MYSQL_BOTH) {
        $res = $this->query($queryString, $params);
        if ($res === false) {
            return array();
        }
        for($result = array(), $row = mysql_fetch_array($res, $mysql_mode); !empty($row); $row = mysql_fetch_array($res, $mysql_mode)) {
            $result[] = $row;
        };
        mysql_free_result($res);
        return $result;
    }
    //--------------------------------------------------------------------------


    public function getArray1v($queryString, array $params = array()) {
        $res = $this->query($queryString, $params);
        if ($res === false) {
            return array();
        }
        for($result = array(), $row = mysql_fetch_array($res, MYSQL_NUM); !empty($row); $row = mysql_fetch_array($res, MYSQL_NUM)) {
            $result[] = $row[0];
        };
        mysql_free_result($res);
        return $result;
    }
    //--------------------------------------------------------------------------


    public function getArrayAssoc($queryString, array $params = array()) {
        $res = $this->query($queryString, $params);
        if ($res === false) {
            return array();
        }
        $result = array();
        if (!($row = mysql_fetch_row($res))) {
            mysql_free_result($res);
            return array();
        }
        if (sizeof($row) == 2) {
            do {
                $result[$row[0]] = $row[1];
            } while($row = mysql_fetch_row($res));
        } elseif (sizeof($row) == 3) {
            do {
                $result[$row[0]][$row[1]] = $row[2];
            } while($row = mysql_fetch_row($res));
        } else {
            mysql_free_result($res);
            throw new EServerError("getArrayAssoc expects two- or three-column results");
        }
        mysql_free_result($res);
        return $result;
    }
    //--------------------------------------------------------------------------


    public function getTopArray($queryString, array $params = array(), $mysql_mode = MYSQL_BOTH) {
        $res = $this->query($queryString, $params);
        if ($res === false) {
            return array();
        }
        $row = mysql_fetch_array($res, $mysql_mode);
        mysql_free_result($res);
        return empty($row) ? array() : $row;
    }
    //--------------------------------------------------------------------------


    public function getTopLeft($queryString, array $params = array()) {
        $res = $this->query($queryString, $params);
        if ($res === false) {
            return false;
        }
        $arr = mysql_fetch_row($res);
        mysql_free_result($res);
        return isset($arr[0]) ? $arr[0] : false;
    }
    //--------------------------------------------------------------------------


    public function getTopLeftInt($queryString, array $params = array()) {
        $res = $this->query($queryString, $params);
        if ($res === false) {
            return false;
        }
        $arr = mysql_fetch_row($res);
        mysql_free_result($res);
        return isset($arr[0]) ? (int)$arr[0] : 0;
    }
    //--------------------------------------------------------------------------


    public function getArrayIndexed($queryString, array $params = array(), $indexBy = 0, $mysql_mode = MYSQL_BOTH) {
        $res = $this->query($queryString, $params);
        if ($res === false) {
            return array();
        }
        $result = array();
        while ($row = mysql_fetch_array($res, $mysql_mode)) {
            $result[$row[$indexBy]] = $row;
        }
        mysql_free_result($res);
        return $result;
    }
    //--------------------------------------------------------------------------


    public function getArrayGrouped($queryString, array $params = array(), $groupBy = 0, $mysql_mode = MYSQL_BOTH) {
        $res = $this->query($queryString, $params);
        if ($res === false) {
            return array();
        }
        $result = array();
        while ($row = mysql_fetch_array($res, $mysql_mode)) {
            $result[$row[$groupBy]][] = $row;
        }
        mysql_free_result($res);
        return $result;
    }
    //--------------------------------------------------------------------------
}


class Model_Base {
    protected $tablePrefix = '';
    protected $originalTableNames = null;


    public function getTablePrefix() {
        return $this->tablePrefix;
    }
    //--------------------------------------------------------------------------


    public function setTablePrefix($tablePrefix) {
        if (is_null($this->originalTableNames)) {
            $this->originalTableNames = array();
            foreach (get_object_vars($this) as $k => $v) {
                if (strpos($k, 't_') === 0) {
                    $this->originalTableNames[$k] = empty($v) ? substr($k, 2) : $v;
                }
            }
        }
        foreach ($this->originalTableNames as $varName => $tableName) {
            $this->$varName = "$tablePrefix$tableName";
        }
        $this->tablePrefix = $tablePrefix;
    }
    //--------------------------------------------------------------------------


    protected function limit($page, $perPage) {
        return ' LIMIT ' . (((int)$page  - 1) * (int)$perPage) . ', ' . (int)$perPage;
    }
    //--------------------------------------------------------------------------


    protected function order($orderBy) {
        $arr = explode(",", $orderBy); $ord = array();
        foreach ($arr as $s) {
            $s = preg_replace('/[\\s]{2,}/', ' ', $s);
            $a2 = explode(' ', trim($s));
            if (sizeof($a2) > 2) {
                return '';
            }
            if (!preg_match('/^[\\w]{1,}$/', $a2[0])) {
                return '';
            }
            $dir = isset($a2[1]) ? strtoupper($a2[1]) : '';
            $dir = strtoupper($dir);
            if ($dir && ($dir != 'ASC') && ($dir != 'DESC')) {
                return '';
            }
            if ($dir == 'ASC') {
                $dir = '';
            }
            $ord[] = "`{$a2[0]}`" . ($dir ? " $dir" : '');
        }
        return " ORDER BY " . implode(', ', $ord);
    }
    //--------------------------------------------------------------------------


    protected function orderLimit($orderBy, $page, $perPage) {
        return $this->order($orderBy) . $this->limit($page, $perPage);
    }
    //--------------------------------------------------------------------------
}


class SingletonRegistry_Base {
    protected static $objects = array();


    public static function get($key) {
        return isset(self::$objects[$key]) ? self::$objects[$key] : null;
    }
    //--------------------------------------------------------------------------


    public static function set($object, $name = null) {
        self::$objects[$name ? $name : get_class($object)] = $object;
    }
    //--------------------------------------------------------------------------


    public static function contains($key) {
        return isset(self::$objects[is_object($key) ? get_class($key) : $key]);
    }
    //--------------------------------------------------------------------------


    public static function clear($key = '') {
        if ($key) {
            unset(self::$objects[is_object($key) ? get_class($key) : $key]);
        } else {
            self::$objects = array();
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Returns singleton instance of $className,
     * creates one using default constructor or $callback if it does not exist
     */
    public static function getSingleInstance($className, $callback = false, array $callBackParams = array()) {
        if (!self::contains($className)) {
            self::set($callback ? call_user_func_array($callback, $callBackParams) : new $className());
        }
        return self::get($className);
    }
    //--------------------------------------------------------------------------
}


/**
 * @desc Base class for all exception classes defined in FW
 */
class EFWException extends Exception {
    public function __construct($message = '', $code = 0) {
        parent::__construct($message, $code);
    }
}
//--------------------------------------------------------------------------


/**
 * @desc Exception thrown when we wish to redirect user to another url.
 * It will be caught by FrontController
 */
class ERedirectException extends EFWException {
    public function __construct($message = '', $code = 0) {
        parent::__construct($message, $code);
    }
}
//--------------------------------------------------------------------------


/**
 * @desc Exception thrown when we're done processing the request
 * It will be caught by FrontController or parentController
 */
class EDoneException extends EFWException {
    public function __construct($message = '', $code = 0) {
        parent::__construct($message, $code);
    }
}
//--------------------------------------------------------------------------


/**
 * @desc Error in user's request, like missing or invalid parameter, access denied, 404 etc
 */
class ERequestError extends EFWException {
    public function __construct($message = '', $code = 0) {
        parent::__construct($message, $code);
    }
}
//--------------------------------------------------------------------------


/**
 * @desc Request error that can be explained to user
 */
class EExplainableError extends ERequestError {
    public function __construct($message = '', $code = 1) {
        parent::__construct($message, $code);
    }
}
//--------------------------------------------------------------------------


class EAccessDenied extends ERequestError {
    public function __construct($message = 'Access denied', $code = 2) {
        parent::__construct($message, $code);
    }
}
//--------------------------------------------------------------------------


class EAccessRestricted extends EAccessDenied {
    public function __construct($message = 'admin', $code = 3) {
        parent::__construct($message, $code);
    }
}
//--------------------------------------------------------------------------


class EError404 extends ERequestError {
    public function __construct($message = 'Access Forbidden', $code = 4) {
        parent::__construct($message, $code);
    }
}
//--------------------------------------------------------------------------


/**
 * @desc Error when validating a form
 */
class EValidationError extends EExplainableError {
    public function __construct($message = '', $code = 5) {
        parent::__construct($message, $code);
    }
}
//--------------------------------------------------------------------------


/**
 * @desc Error on server side, should not explain it to user unless we are on
 * dev environment.
 */
class EServerError extends EFWException {
    public function __construct($message = '', $code = 6) {
        parent::__construct($message, $code);
    }
}
//--------------------------------------------------------------------------

require_once CFG_COMMON_HOME . '_util_functions.php';
