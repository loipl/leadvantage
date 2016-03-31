<?php

class X000006_Ext_Login extends Controller {

    /**
     * @var PageFragment_FormAuto
     */
    protected $pf;

    public function loginAction() {
        $form = $this->getForm();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $form->importPostRequestAndThrowEDoneOnError();
            try {
                Auth::getInstance()->tryToLogIn($form->username, $form->password);
            } catch (Exception $e) {
                if ($e->getCode() == 1234) {
                    $this->pf->subTitle .= "<br>" . App::linkFor($this->parentController, 'Resend verification mail?', array('action' => 'resendVerificationEmail'));
                }
                $form->addErrorAndThrowEDone('username', $e->getMessage());
            }

            session_regenerate_id(true);
            $returnUrl = Session::getSession()->returnUrl;
            if ($returnUrl) {
                Session::getSession()->returnUrl = '';
            } else {
                $returnUrl = App::getFrontController()->urlFor('Application');
            }
            throw new ERedirectException($returnUrl);
        }
    }
    //--------------------------------------------------------------------------


    public function logoutAction() {
        Auth::getInstance()->logOut();
        Session::getSession()->clear();
        App::getFrontController()->redirectToCP('Application');
    }
    //--------------------------------------------------------------------------


    protected function getForm() {
        $form = new Form_Data();
        $form->add('text',     'username', 'Username');
        $form->add('password', 'password', 'Password');
        $form->add('button',   '',         'Log In', array('type' => 'submit'));

        $pf = new PageFragment_FormAuto($this, $form, true);
        $pf->title = 'Login';
        $this->pf = $pf;

        return $form;
    }
    //--------------------------------------------------------------------------


    public function preRun() {
        $pt = $this->parentController->getPageTemplate();
        if ($pt) {
            $this->pageTemplate = $pt;
        }
    }
    //--------------------------------------------------------------------------
}

