<?php

class Controller_Admin_Fieldtypes extends CrudControllerWithCM {

    /**
     * @var Model_FieldType
     */
    protected $model;


    public function __construct() {

        $this->model = SingletonRegistry::getSingleInstance('Model_FieldType');

        parent::__construct();
        $this->defaultOrderBy = 'name';
        $this->itemName       = 'Field Type';
    }
    //--------------------------------------------------------------------------


    public function indexAction() {
        /* @var $mci Model_Industry */
        $mci = SingletonRegistry::getSingleInstance('Model_Industry');
        $this->out['industries'] = $mci->listAssoc();

        $this->out['validationTypes'] = $this->model->listValidationTypesAssoc();
        parent::indexAction();
    }
    //--------------------------------------------------------------------------


    protected function addFormFields($action = '') {
        /* @var $mci Model_Industry */
        $mci = SingletonRegistry::getSingleInstance('Model_Industry');
        $this->out['industries'] = $industries = $mci->listAssoc();

        $this->form->add('text',     'name',            'Name',            array('* trim' => 1, '* required' => 1, 'maxlength' => 60));
        $this->form->add('select',   'validation_type', 'Validation',      array('* items' => $this->model->listValidationTypesAssoc()));
        $this->form->add('text',     'validation_data', 'Validation Data', array('* trim' => 1, '* hint' => 'Used only for regex type'));
        $this->form->add('checkbox', 'force_uppercase', 'Force to UPPER CASE');
        $this->form->add('html',     '', '&nbsp;');
        $this->form->add('select',   'industries',      'Industries',      array('* items' => $industries, 'multiple' => 'multiple'));
        $this->form->add('text',     'description',     'Description',     array('* trim' => 1, '* hint' => 'Used on API specs page', 'maxlength' => 255));

        $this->pageFragmentForm->tableAttributes['width']    = 550;
        $this->pageFragmentForm->typeData['text']['class']   = 'full_width';
        $this->pageFragmentForm->typeData['select']['class'] = 'full_width';
    }
    //--------------------------------------------------------------------------


    protected function validateFormImport($action) {
        if (!preg_match('/^[\\w]{1,}$/', $this->form->name)) {
            $this->form->addError('name', 'Name must consist of letters, numbers and underscores');
        }
        if ($this->model->getExistingFieldValuesForKey($this->id, array('name' => $this->form->name))) {
            $this->form->addError('name', "Name already used");
        }
    }
    //--------------------------------------------------------------------------


    protected function getDataRow($id) {
        $row = $this->cmTable ? $this->model->get($this->cmTable, $id) : $this->model->get($id);
        $row['industries'] = $this->model->listIndustries($id);
        return $row;
    }
    //--------------------------------------------------------------------------


    protected function insertRow() {
        $ind = $this->data['industries'];
        unset($this->data['industries']);
        parent::insertRow();
        if ($this->id) {
            $this->model->setIndustries($this->id, is_array($ind) ? $ind : array());
        }
    }
    //--------------------------------------------------------------------------


    protected function updateRow() {
        $ind = $this->data['industries'];
        unset($this->data['industries']);
        parent::updateRow();
        if ($this->id) {
            $this->model->setIndustries($this->id, is_array($ind) ? $ind : array());
        }
    }
    //--------------------------------------------------------------------------


    protected function getDataList($orderBy) {
        $rows = parent::getDataList($orderBy);
        $ids  = array();
        foreach ($rows as & $row) {
            $row['industries'] = array();
            $ids[$row[$this->model->getPrimaryKey()]] = & $row;
        }
        unset($row);
        $list = $this->model->listIndustriesGroup(array_keys($ids));
        foreach ($list as $id => $arr) {
            $ids[$id]['industries'] = $arr;
        }
        return $rows;
    }
    //--------------------------------------------------------------------------
}
