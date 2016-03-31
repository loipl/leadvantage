<?php

class Controller_Admin_Userlevels extends CrudControllerWithCM {

    /**
     * @var Model_ConfigPostsPerLevel
     */
    protected $model;

    protected $prettyNames = array();


    public function __construct() {

        $this->model = SingletonRegistry::getSingleInstance('Model_ConfigPostsPerLevel');

        parent::__construct();
        $this->defaultOrderBy = 'max_posts DESC';
        $this->itemName       = 'User Level';
    }
    //--------------------------------------------------------------------------


    public function indexAction() {
        $this->prettyNames = $this->model->listPrettyLevelNames();
        parent::indexAction();
    }
    //--------------------------------------------------------------------------


    protected function addFormFields($action = '') {
        $this->form->add('text',     'level',     'Level',     array('* trim' => 1, '* required' => 1, 'class' => 'full_width', 'maxlength' => 40));
        $this->form->add('text',     'max_posts', 'Max Posts', array('* trim' => 1, '* required' => 1, 'class' => 'full_width', 'maxlength' => 10, '* hint' => '0 means unlimited'));
        $this->form->add('text',     'max_sub_accounts', 'Max Sub-accounts', array('* trim' => 1, '* required' => 1, 'class' => 'full_width', 'maxlength' => 10, '* hint' => '0 means no sub-account'));
        $this->form->add('checkbox', 'can_validate_email', 'Can Validate Email');
        $this->form->add('checkbox', 'can_validate_phone', 'Can Validate Phone');

        $this->pageFragmentForm->tableAttributes['width'] = 650;
        if (0) $this->prependToContent = '
<style type="text/css">
.form_table input {
	width: 100%;
}
</style>';
    }
    //--------------------------------------------------------------------------


    protected function validateFormImport($action) {
        if (!preg_match('/^[\\w]{1,}$/', $this->form->level)) {
            $this->form->addError('level', 'Name must consist of letters, numbers and underscores');
        }
        if ($this->model->getExistingFieldValuesForKey($this->id, array('level' => $this->form->level))) {
            $this->form->addError('level', "Level already used");
        }
        if (!(is_numeric($this->form->max_posts) && ($this->form->max_posts >= 0))) {
            $this->form->addError('max_posts', 'Must be whole number, zero or more');
        }
        if (!(is_numeric($this->form->max_sub_accounts) && ($this->form->max_sub_accounts >= 0))) {
            $this->form->addError('max_sub_accounts', 'Must be whole number, zero or more');
        }
    }
    //--------------------------------------------------------------------------
}
