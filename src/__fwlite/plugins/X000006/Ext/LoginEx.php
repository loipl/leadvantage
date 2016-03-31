<?php
/**
 * @desc pages that need to be viewable without logging in:<br>
 * array('login', 'logout', 'sendPasswordResetMail', 'resetPassword', 'resetSent', 'signup', 'verify', 'resendVerificationEmail', 'mailSent');
 */
class X000006_Ext_LoginEx extends X000006_Ext_Login {

    /**
     * @var X000006_Model_User
     */
    protected $model;


    public function signupAction() {
        if (Auth::getInstance()->isLoggedIn()) {
            App::getFrontController()->redirectToCP($this->parentController);
        }
        $this->pageTitle = 'Sign Up';
        $this->model or $this->getModel();
        $form = $this->getSignupForm();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $form->importPostRequestAndThrowEDoneOnError();
            $this->validateSignupForm($form);

            $data = $form->getValues();
            $data['pass_sha1'] = sha1(Config::$sha1Salt . $data['password1']);
            unset($data['password1']);
            unset($data['password2']);
            $data['enabled'] = '1';

            $id = $this->model->insert($data);
            if ($id) {
                $this->sendVerificationMail($id);
                App::getFrontController()->redirectToCP($this->parentController, array('action' => 'mailSent'));
            }
        }
    }
    //--------------------------------------------------------------------------


    public function mailSentAction() {
        $this->content = "A verification mail was sent to you, please click on the link inside the email to activate your accout";
    }
    //--------------------------------------------------------------------------


    public function verifyAction() {
        $userId = $this->checkToken();

        $this->model or $this->getModel();
        $this->model->verifyEmail($userId);
        App::getFrontController()->redirectToCP($this->parentController);
    }
    //--------------------------------------------------------------------------


    public function sendPasswordResetMailAction() {
        $auth = Auth::getInstance();
        if ($auth->isLoggedIn()) {
            App::getFrontController()->redirectToCP($this->parentController);
        }

        $this->pageTitle = 'Password Reset';
        $form = new Form_Data();
        $form->add('text', 'email', 'Email', array('* trim' => 1, '* required' => 1, 'style' => 'width: 100%;'));
        $form->add('button', '', 'Send Password Reset Mail', array('type' => 'submit'));
        $pf = new PageFragment_FormAuto($this, $form, true);
        $pf->title = 'Request Password Reset';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $form->importPostRequestAndThrowEDoneOnError();

            $this->model or $this->getModel();
            $id = $this->model->getUserIdByEmail($form->email);
            if (!$id) {
                $form->addErrorAndThrowEDone('email', 'Unknown email');
            }

            $this->sendPasswordResetMail($id);
            App::getFrontController()->redirectToCP($this->parentController, array('action' => 'resetSent'));
        }
    }
    //--------------------------------------------------------------------------


    public function resetPasswordAction() {
        $userId = $this->checkToken();

        $form = new Form_Data();
        $form->add('password', 'new_pass1', 'Enter New Password',   array('style' => 'width: 100%'));
        $form->add('password', 'new_pass2', 'Confirm New Password', array('style' => 'width: 100%'));
        $form->add('button',   '',          'Set New Password',     array('type' => 'submit'));

        $pf = new PageFragment_FormAuto($this, $form, true);
        $pf->title = 'Reset Your Password';
    }
    //--------------------------------------------------------------------------


    public function resetSentAction() {
        $this->content = "A mail was sent to you, please click on the link inside the email to go to password reet page";
    }
    //--------------------------------------------------------------------------


    public function resendVerificationEmailAction() {
        $auth = Auth::getInstance();
        if ($auth->isLoggedIn()) {
            App::getFrontController()->redirectToCP($this->parentController);
        }

        $this->pageTitle = 'Resend Verification Email';
        $form = new Form_Data();
        $form->add('text', 'email', 'Email', array('* trim' => 1, '* required' => 1, 'style' => 'width: 100%;'));
        $form->add('button', '', 'Resend Verification Emai', array('type' => 'submit'));
        $pf = new PageFragment_FormAuto($this, $form, true);
        $pf->title = 'Resend Verification Email';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $form->importPostRequestAndThrowEDoneOnError();

            $this->model or $this->getModel();
            $id = $this->model->getUserIdByEmail($form->email);
            if (!$id) {
                $form->addErrorAndThrowEDone('email', 'Unknown email');
            }

            $this->sendVerificationMail($id);
            App::getFrontController()->redirectToCP($this->parentController, array('action' => 'mailSent'));
        } else {
            if (isset($_GET['email'])) {
                $form->email = $_GET['email'];
            }
        }
    }
    //--------------------------------------------------------------------------


    protected function validateSignupForm(Form_Data $form) {
        $this->model or $this->getModel();

        foreach ($this->model->getExistingFieldValuesForKey(0, array('username' => $form->username, 'name' => $form->name)) as $k => $v) {
            $form->addError($k, "Value '" . escapeHtml($v) . "' already taken");
        }

        $passwordErr = $this->getErrorForPasswordFormat($form->password1);
        if ($passwordErr) {
            $form->addError('password1', $passwordErr);
        }
        if ($form->password2 != $form->password1) {
            $form->addError('password2', 'Please enter same password again');
        }

        if ($form->hasErrors()) {
            throw new EDoneException;
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @return Form_Data
     */
    protected function getSignupForm() {
        $form = new Form_Data;
        $form->add('text',     'email',     'Email',            array('* required' => true, '* trim' => true, '* filter' => FILTER_VALIDATE_EMAIL, '* filter_error' => 'Invalid Email'));
        $form->add('text',     'name',      'Full Name',        array('* required' => true, '* trim' => true));
        $form->add('blank');
        $form->add('text',     'username',  'Desired Username', array('* required' => true, '* trim' => true));
        $form->add('password', 'password1', 'Password',         array('* required' => true));
        $form->add('password', 'password2', 'Confirm Password', array('* required' => true));
        $form->add('button',   '',          'Sign Up',          array('type' => 'submit'));
        $this->out['form'] = $form;

        $pf = new PageFragment_FormAuto($this, $form, true);
        $pf->title = 'Sign Up';
        $pf->subTitle = 'Already have an account? ' . App::linkFor($this->parentController, 'log in', array('action' => 'login'));

        $this->appendToContent = <<<END
<style type="text/css">
  input {width: 95%}
</style>
END;

        return $form;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Returns error for given password
     */
    protected function getErrorForPasswordFormat($password) {
        if (empty($password) || (strlen(trim($password)) < 6)) {
            return 'Password must be at least 6 characters long';
        }
        if (trim($password) != $password) {
            return 'password cannot start or end with a space';
        }
        if (!preg_match('/[0-9]{1,}/', $password) || !preg_match('/[a-z]{1,}/i', $password) || !preg_match('/[\\W]{1,}/', $password)) {
            return 'password must contain at least one letter, one number and a special character (anything except letters & numbers)';
        }
        return '';
    }
    //--------------------------------------------------------------------------


    protected function checkToken() {
        if (empty($_GET['t']) || !is_string($_GET['t'])) {
            throw new ERequestError("Missing or invalid t parameter");
        }
        $token = $_GET['t'];
        $row = explode('e', $token, 2);
        if (sizeof($row) != 2) {
            throw new ERequestError("Missing or invalid t parameter");
        }
        $this->model or $this->getModel();
        $user = $this->model->get((int)$row[0]);
        if (!$user) {
            throw new ERequestError("Your signup has expired - you did not verify your email within 24 hours. Please " .
            App::linkFor($this->parentController, 'Sign Up', array('action' => 'signup')) . " again.");
        }
        if (!$this->model->isValidToken((int)$row[0], $row[1])) {
            throw new ERequestError("Missing or invalid t parameter");
        }
        return (int)$row[0];
    }
    //--------------------------------------------------------------------------


    protected function sendPasswordResetMail($id) {
        $this->model or $this->getModel();
        $user = $this->model->get($id);
        if (!$user) {
            throw new EServerError("At this point user row must already exist");
        }
        $token   = $this->model->getOrGenerateEmailToken((int)$user['id']);

        $subject = $_SERVER['HTTP_HOST'] . ' Password Reset';
        $body    = 'Click here to <a href="http://' . $_SERVER['HTTP_HOST'] .
        App::getFrontController()->urlFor($this->parentController, array('action' => 'resetPassword'), array('t' => $id . 'e' . $token['token'])) .
        '">reset your password</a>';

        X000004_Util_Mailer::sendHtmlMail($subject, $body, $user['email'], $user['name']);
    }
    //--------------------------------------------------------------------------


    protected function sendVerificationMail($id) {
        $this->model or $this->getModel();
        $user = $this->model->get($id);
        if (!$user) {
            throw new EServerError("At this point user row must already exist");
        }
        $row = $this->model->getOrGenerateEmailToken($id);

        $subject = 'Please verify your email address';

        $params = array('t' => $id . 'e' . $row['token']);
        $link = "http://{$_SERVER['HTTP_HOST']}" . App::getFrontController()->urlFor($this->parentController, array('action' => 'verify'), $params);
        $body = "Please click on this link to verify your email address:<br /><br />\n" .
        "<a href=\"$link\">$link</a>";

        X000004_Util_Mailer::sendHtmlMail($subject, $body, $user['email'], $user['name']);
    }
    //--------------------------------------------------------------------------


    /**
     * @return X000006_Model_User
     */
    protected function getModel() {
        return $this->model = SingletonRegistry::getSingleInstance('X000006_Model_User');
    }
    //--------------------------------------------------------------------------


    protected function getForm() {
        $form = new Form_Data();
        $form->add('text',     'username', 'Username');
        $form->add('password', 'password', 'Password');
        $form->add('button',   '',         'Log In', array('type' => 'submit'));

        $pf = new PageFragment_FormAuto($this, $form, true);
        $pf->title    = 'Login';
        $this->pf = $pf;
        $pf->subTitle = 'Don\'t have an account? ' . App::linkFor($this->parentController, 'Sign up', array('action' => 'signup')) . '!<br>' .
        App::linkFor($this->parentController, 'Forgot password?', array('action' => 'sendPasswordResetMail'));

        $this->appendToContent = <<<END
<style type="text/css">
  input {width: 95%}
</style>
END;

        return $form;
    }
    //--------------------------------------------------------------------------
}