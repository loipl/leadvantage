<?php

/**
 * @desc This controller will process /api/ requests, ie incoming form submissions.
 * Most of actual processing is done in Engine classes.
 *
 */
class Controller_Api extends Controller {

    /**
     * @var Model_Campaign
     */
    protected $model;


    public function run() {

        $this->setPageTemplate('api_template');
        App::getFrontController()->setErrorTemplate('api_template');

        $hash = isset($this->params[App::API_HASH_PARAM]) ? $this->params[App::API_HASH_PARAM] : '';
        if (!$hash) {
            $this->brandVisitor();
            throw new EError404();
        }
        unset($_GET[App::API_HASH_PARAM]);

        $this->brandVisitor();
        $this->model or $this->model = SingletonRegistry::getModelCampaign();

        $campaign = $this->model->getCampaignFromHash($hash);
        if (!$campaign) {
            throw new EError404();
        }

        if (empty($campaign['is_active'])) {
            if (Model_Campaign::RESPONSE_TYPE_API_KEY == $campaign['response_type']) {
                die('FAIL|Inactive Campaign');
            } else {
                throw new ERedirectException($campaign['failure_url']);
            } 
        }

        SingletonRegistry::getModelEngineConfig()->initializeEngineConfigObject(Engine_Settings::$settings, gethostbyname(gethostname()));
        $engine = new Engine_Submission();
        try {
            $engine->processIncomingFormSubmission($campaign);
        } catch (EDoneException $e) {
            die($e->getMessage());
        }
    }
    //--------------------------------------------------------------------------


    private function brandVisitor() {
        if (!isset($_COOKIE[Config::$visitorCookie])) {
            /* @var $mv Model_Visitor */
            $mv = SingletonRegistry::getSingleInstance('Model_Visitor');
            $visitorId = $mv->nextVisitorId();
            setcookie(Config::$visitorCookie, $visitorId, time() + 365 * 24 * 3600, '/');
            $_COOKIE[Config::$visitorCookie] = $visitorId;
        }
    }
    //--------------------------------------------------------------------------
}
