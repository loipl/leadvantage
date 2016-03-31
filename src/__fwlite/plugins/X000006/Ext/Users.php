<?php

/**
 * @desc Basic users administration
 *
 * @author Boban
 */
class X000006_Ext_Users extends CrudControllerWithCM {

    /**
     * @var X000006_Model_User
     */
    protected $model;

    protected $secretSalt = '';


    public function __construct() {

        // Check if $this->model already exists, in case constructor is called from
        // an inherited class
        if (!$this->model) {
            $this->model = new X000006_Model_User;
        }
        parent::__construct();
        $this->defaultOrderBy = 'username';
        $this->itemName       = 'User';
    }
    //--------------------------------------------------------------------------


    protected function addFormFields($action = '') {
        $this->form->add('text', 'username', 'Username', array('* trim' => 1, '* required' => 1, 'maxlength' => 32));
        $this->form->add('text', 'name',     'Name',     array('* trim' => 1, '* required' => 1, 'maxlength' => 60));
        $this->form->add('text', 'email',    'Email',    array('* trim' => 1, '* required' => 1, 'maxlength' => 90, '* filter' => FILTER_VALIDATE_EMAIL, '* filter_error' => 'Invalid Email'));
        if ($action == 'edit') {
            $this->form->add('password', 'new_pass',     'Password');
            $this->form->add('checkbox', 'change_pwd',   'Change Password');
            $this->form->add('blank');
        } elseif ($action == 'add') {
            $this->form->add('password', 'pass_sha1', 'Password');
        }

        $this->form->add('checkbox', 'is_admin',       'Is Admin');
        $this->form->add('checkbox', 'email_verified', 'Email Is Verified');
        $this->form->add('checkbox', 'enabled',        'Enabled');
    }
    //--------------------------------------------------------------------------


    protected function initHeaderLinks() {
        $hl = new Util_HeaderLinks('', true, 'sortDir', '1');

        $hl->addColumn('Username',       'username');
        $hl->addColumn('Email',          'email');
        $hl->addColumn('Name',           'name');
        $hl->addColumn('Admin');
        $hl->addColumn('Join Time',      'created_at');
        $hl->addColumn('Email Verified');
        $hl->addColumn('Enabled');
        $hl->addColumn('Actions');

        $this->headerLinks = $hl;
    }
    //--------------------------------------------------------------------------


    protected function getFormValuesForWritingToDb($action = '') {
        $values = $this->form->getValues();
        if (isset($values['pass_sha1'])) {
            $values['pass_sha1'] = sha1($this->secretSalt . $values['pass_sha1']);
        } elseif (!empty($values['change_pwd'])) {
            $values['pass_sha1'] = sha1($this->secretSalt . $values['new_pass']);
        }
        unset($values['change_pwd']);
        unset($values['new_pass']);
        return $values;
    }
    //--------------------------------------------------------------------------
}
