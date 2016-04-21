<?php

class Controller_Admin_DataList extends CrudControllerWithCM {

    /**
     * @var Model_DataList
     */
    protected $model;


    public function __construct() {
        $this->model = SingletonRegistry::getSingleInstance('Model_DataList');

        parent::__construct();
        $this->defaultOrderBy = 'content';
        $this->itemName       = 'List';
    }
    //--------------------------------------------------------------------------

    public function indexAction() {
        $dataLists = $this->model->listAll();
        $this->out['dataLists'] = $dataLists;
        parent::indexAction();
    }

    protected function addFormFields($action = '') {
        $this->form->add('text', 'name', 'Name', array('* trim' => 1, '* required' => 1, 'maxlength' => 255));

        $this->pageFragmentForm->tableAttributes['width'] = 450;
    }
    //--------------------------------------------------------------------------


    protected function validateFormImport($action) {
        if ($this->model->getExistingFieldValuesForKey($this->id, array('content' => $this->form->content))) {
            $this->form->addError('content', "Already have that one");
        }
    }
    //--------------------------------------------------------------------------
}
