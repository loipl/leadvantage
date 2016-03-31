<?php

class Controller_Admin_Engine extends Controller {

    /**
     * @var Model_EngineConfig
     */
    private $model;

    /**
     * @var Form_Data
     */
    protected $form;

    protected $nodes = array();
    protected $nodesAssoc = array();


    protected $descriptions = array(
        Model_EngineConfig::CRON_TIMEOUT          => "How long will the cron process run. Set to zero to disable",
        Model_EngineConfig::DELIVERY_TIMEOUT      => "Timeout (seconds) for partner response",
        Model_EngineConfig::SUBMISSION_TIMEOUT    => "Timeout (seconds) for processing one incoming submission",
        Model_EngineConfig::REPOST_QUEUE_MEM_SIZE => "Size of repost queue subset - preselected subset of most urgent repost queue entries",
        Model_EngineConfig::ONE_REPOST_BATCH_SIZE => "How many entries are processed in one repost cycle",
    );


    public function __construct() {
        parent::__construct();

        $this->model      = SingletonRegistry::getModelEngineConfig();

        $this->nodes      = $this->model->listNodes();
        $this->nodesAssoc = $this->model->listNodesAssoc();
    }
    //--------------------------------------------------------------------------


    public function indexAction() {
        $this->createFormForSettings();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->form->importPostRequestAndThrowEDoneOnError();
            $this->writeFormDataToDb();
            App::getFrontController()->redirectToCP($this);
        } else {
            $this->dbDataToForm();
        }
    }
    //--------------------------------------------------------------------------


    private function writeFormDataToDb() {
        $settings = array();
        foreach (Model_EngineConfig::$universalSettings as $name) {
            $settings[$name] = $this->form->$name;
        }

        foreach (Model_EngineConfig::$perDomainSettings as $name) {
            $arr = array($this->form->$name);
            foreach ($this->nodes as $ip) {
                $v = $this->form->{$name . '_' . str_replace('.', '_', $ip)};
                if (is_numeric($v) && $v >= 0) {
                    $arr[] = $ip . '=' . $v;
                }
            }
            $settings[$name] = implode("\n", $arr);
        }

        $this->model->writeConfigSettings($settings);
    }
    //--------------------------------------------------------------------------


    /**
     * @return Form_Data
     */
    private function createFormForSettings() {
        $form = new Form_Data();

        foreach (Model_EngineConfig::$universalSettings as $name) {
            $form->add('text', $name, str_replace('_', ' ', $name), array('* hint' => $this->descriptions[$name], 'size' => '10'));
        }

        $arr = array('');
        foreach ($this->nodes as $s) {
            $arr[] = $s;
        }
        foreach (Model_EngineConfig::$perDomainSettings as $name) {
            $form->add('text', $name, '', array('size' => '10', '* hint' => $this->descriptions[$name]));

            foreach ($this->nodes as $ip) {
                $form->add('text', $name . '_' . str_replace('.', '_', $ip), '', array('size' => '10'));
            }
        }

        $form->add('button', '', 'Submit', array('type' => 'submit'));

        $this->form = $form;
    }
    //--------------------------------------------------------------------------


    private function dbDataToForm() {
        $settings = $this->model->listAllSettings();
        $defaultConfig = Engine_Settings::$settings;

        if (!isset($settings[Model_EngineConfig::CRON_TIMEOUT][''])) {
            $settings[Model_EngineConfig::CRON_TIMEOUT][''] = $defaultConfig->cronTimeout;
        }
        if (!isset($settings[Model_EngineConfig::DELIVERY_TIMEOUT][''])) {
            $settings[Model_EngineConfig::DELIVERY_TIMEOUT][''] = $defaultConfig->deliveryTimeout;
        }
        if (!isset($settings[Model_EngineConfig::SUBMISSION_TIMEOUT][''])) {
            $settings[Model_EngineConfig::SUBMISSION_TIMEOUT][''] = $defaultConfig->processingTimeout;
        }
        if (!isset($settings[Model_EngineConfig::REPOST_QUEUE_MEM_SIZE][''])) {
            $settings[Model_EngineConfig::REPOST_QUEUE_MEM_SIZE][''] = $defaultConfig->repostQueueMemSize;
        }
        if (!isset($settings[Model_EngineConfig::ONE_REPOST_BATCH_SIZE][''])) {
            $settings[Model_EngineConfig::ONE_REPOST_BATCH_SIZE][''] = $defaultConfig->oneRepostBatchSize;
        }

        foreach (Model_EngineConfig::$universalSettings as $name) {
            $this->form->$name = isset($settings[$name]['']) ? $settings[$name][''] : '';
        }

        foreach (Model_EngineConfig::$perDomainSettings as $name) {
            $this->form->$name = isset($settings[$name]['']) ? $settings[$name][''] : '';
            foreach ($this->nodes as $ip) {
                $this->form->{$name . '_' . str_replace('.', '_', $ip)} = isset($settings[$name][$ip]) ? $settings[$name][$ip] : '';
            }
        }
    }
    //--------------------------------------------------------------------------


    public function nodesAction() {
        $this->createFormForNodes();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->form->importPostRequestAndThrowEDoneOnError();
            $nodes = $this->getNodesListFromPost();
            $this->form->throwDoneIfErrors();

            $this->model->writeNodes($nodes);
            App::getFrontController()->redirectToCP($this);

        } else {
            $arr = array();
            foreach ($this->nodesAssoc as $ip => $name) {
                if ($ip) {
                    $arr[] = $ip . ($name ? ", $name" : '');
                }
            }
            $this->form->nodes = trim(implode("\n", $arr));
        }
    }
    //--------------------------------------------------------------------------


    private function createFormForNodes() {
        $this->prependToContent = '<a href="' . App::getFrontController()->urlFor($this) . '">Settings</a>';
        $form = new Form_Data();
        $form->add('textarea', 'nodes', 'Nodes', array('class' => 'full_width', 'rows' => 8, '* hint' => 'XXX.XXX.XXX.XXX, Node Name'));
        $form->add('button',   '',      'Submit',      array('type' => 'submit'));

        $pf = new PageFragment_FormAuto($this, $form, true);
        $pf->tableAttributes['width'] = '400';
        $pf->title = "Servers / Nodes";
        $pf->subTitle = "Entier one IP address per line";

        $this->form = $form;
    }
    //--------------------------------------------------------------------------


    private function getNodesListFromPost() {
        $nodes = array();
        foreach (explode("\n", $this->form->nodes) as $s) {
            $s = trim($s);
            if (strlen($s) == 0) {
                continue;
            }
            $arr = explode(",", $s, 2);
            $b = filter_var(trim($arr[0]), FILTER_VALIDATE_IP);
            if ($b === false) {
                $this->form->addError('nodes', "Invalid IP address " . escapeHtml($s));
            }
            $nodes[trim($arr[0])] = isset($arr[1]) ? trim($arr[1]) : '';
        }

        return $nodes;
    }
    //--------------------------------------------------------------------------


    public function preRun() {
        $this->pageTitle = 'Engine Control Panel';
        parent::preRun();
    }
    //--------------------------------------------------------------------------
}
