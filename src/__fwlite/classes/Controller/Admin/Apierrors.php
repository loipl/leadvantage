<?php

class Controller_Admin_Apierrors extends CrudControllerWithCM {

    /**
     * @var Model_LogApiErrors
     */
    protected $model;


    public function __construct() {
        $this->model = SingletonRegistry::getSingleInstance('Model_LogApiErrors');

        parent::__construct();
        $this->defaultOrderBy = 'request_time DESC';
        $this->itemName       = 'Apierror';
        $this->perPage        = 20;
    }
    //--------------------------------------------------------------------------


    protected function addFormFields($action = '') {
        $this->form->add('textarea', 'text', 'Text', array('* trim' => 1, '* required' => 1, 'rows' => 12, 'cols' => 80));

        $this->pageFragmentForm->tableAttributes['width'] = 650;
        $this->prependToContent = '
<style type="text/css">
.form_table textarea {
	width: 100%;
}
</style>';
    }
    //--------------------------------------------------------------------------
}
