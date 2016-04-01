<?php

class Controller_Admin_Tips extends CrudControllerWithCM {

    /**
     * @var Model_AdminTip
     */
    protected $model;


    public function __construct() {

        $this->model = SingletonRegistry::getSingleInstance('Model_AdminTip');

        parent::__construct();
        $this->defaultOrderBy = 'content';
        $this->itemName       = 'Tip';
    }
    //--------------------------------------------------------------------------


    protected function addFormFields($action = '') {
        $this->form->add('text',     'content',         'Name',            array('* trim' => 1, '* required' => 1, 'maxlength' => 255));

        $this->pageFragmentForm->tableAttributes['width'] = 450;
        $this->prependToContent = '
<style type="text/css">
.form_table input {
	width: 100%;
}
</style>';
    }
    //--------------------------------------------------------------------------


    protected function validateFormImport($action) {
        if ($this->model->getExistingFieldValuesForKey($this->id, array('content' => $this->form->content))) {
            $this->form->addError('content', "Already have that one");
        }
    }
    //--------------------------------------------------------------------------
}
