<?php

class Controller_Admin_Industries extends CrudControllerWithCM {

    /**
     * @var Model_Industry
     */
    protected $model;


    public function __construct() {

        $this->model = SingletonRegistry::getSingleInstance('Model_Industry');

        parent::__construct();
        $this->defaultOrderBy = 'name';
        $this->itemName       = 'Industry';
    }
    //--------------------------------------------------------------------------


    protected function addFormFields($action = '') {
        $this->form->add('text',     'name',            'Name',            array('* trim' => 1, '* required' => 1, 'maxlength' => 64));
        $this->form->add('text',     'abbreviation',    'Abbreviation',    array('* trim' => 1, '* required' => 1, 'maxlength' => 3));
        
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
        if ($this->model->getExistingFieldValuesForKey($this->id, array('name' => $this->form->name))) {
            $this->form->addError('name', "Name already used");
        }
        $this->form->abbreviation = strtoupper($this->form->abbreviation);
        if ($this->model->getExistingFieldValuesForKey($this->id, array('abbreviation' => $this->form->abbreviation))) {
            $this->form->addError('abbreviation', "Abbreviation already used");
        }
    }
    //--------------------------------------------------------------------------
}