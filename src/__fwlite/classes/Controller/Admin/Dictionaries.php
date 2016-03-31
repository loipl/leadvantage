<?php

class Controller_Admin_Dictionaries extends CrudControllerWithCM {

    /**
     * @var Model_Dictionary
     */
    protected $model;

    /**
     * @var Model_DictionaryValue
     */
    protected $modelValue;

    /**
     * @var Model_DictionaryColumn
     */
    protected $modelColumn;


    public function __construct() {

        $this->model       = SingletonRegistry::getSingleInstance('Model_Dictionary');
        $this->modelValue  = SingletonRegistry::getSingleInstance('Model_DictionaryValue');
        $this->modelColumn = SingletonRegistry::getSingleInstance('Model_DictionaryColumn');

        parent::__construct();
        $this->defaultOrderBy = 'name';
        $this->itemName       = 'Dictionary';
    }
    //--------------------------------------------------------------------------


    public function columnsAction() {
        $this->getRowFromDb('');
        $this->out['columns'] = $this->modelColumn->listColumnsForDictionary($this->id);
    }
    //--------------------------------------------------------------------------


    protected function insertRow() {
        $this->id = $this->model->insert($this->data);
        if ($this->id) {
            $this->modelColumn->insert(array('dictionary_id' => $this->id, 'name' => 'content'));
        }
    }
    //--------------------------------------------------------------------------


    public function addColumnAction() {
        $this->getRowFromDb('');
        $form = $this->columnForm('Add Column', 'Add Column');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $form->importPostRequestAndThrowEDoneOnError();
            $errors = $this->modelColumn->getExistingFieldValuesForKey(array('id' => 0, 'dictionary_id' => $this->id), array('name' => $form->name));
            if ($errors) {
                $form->addErrorAndThrowEDone('name', "Name already taken");
            }
            $data = $form->getValues();
            $data['dictionary_id'] = $this->id;
            $this->modelColumn->insert($data);
            App::getFrontController()->redirectToCP($this, array('action' => 'columns'), array('id' => $this->id));
        }
    }
    //--------------------------------------------------------------------------


    public function editColumnAction() {
        $this->getRowFromDb('');
        $colId = isset($_GET['col_id']) ? (int)$_GET['col_id'] : 0;
        $data = $this->modelColumn->get($colId);
        if (!$data) {
            throw new ERequestError('invalid col_id');
        }

        $form = $this->columnForm('Edit Column', 'Update Column');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $form->importPostRequestAndThrowEDoneOnError();
            $errors = $this->modelColumn->getExistingFieldValuesForKey(array('id' => $colId, 'dictionary_id' => $this->id), array('name' => $form->name));
            if ($errors) {
                $form->addErrorAndThrowEDone('name', "Name already taken");
            }
            $newData = $form->getValues();
            $this->modelColumn->updateDiff($colId, $newData, $data);
            App::getFrontController()->redirectToCP($this, array('action' => 'columns'), array('id' => $this->id));
        } else {
            $form->importFromArray($data, false);
        }
    }
    //--------------------------------------------------------------------------


    public function deleteColumnAction() {
        $this->getRowFromDb('');
        $columns = $this->modelColumn->listColumnsForDictionary($this->id);
        if (sizeof($columns) <= 1) {
            throw new ERequestError('Cannot delete last columns');
        }
        $colId = isset($_GET['col_id']) ? (int)$_GET['col_id'] : 0;
        $data = $this->modelColumn->get($colId);
        if (!$data) {
            throw new ERequestError('invalid col_id');
        }

        $form = new Form_Data();
        $form->add('button', '', 'Delete Column', array('type' => 'submit'));
        $pf = new PageFragment_FormAuto($this, $form, true);
        $pf->title = 'Delete Column "' . escapeHtml($data['name']) . '"?';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->modelColumn->delete($colId);
            App::getFrontController()->redirectToCP($this, array('action' => 'columns'), array('id' => $this->id));
        }
    }
    //--------------------------------------------------------------------------


    public function valuesAction() {
        $this->getRowFromDb('');
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $this->out['perPage'] = 25;
        $list = $this->modelValue->listForDictionary($this->id, $page, 25);
        $this->out['list'] = $list;
        $this->out['columns'] = $this->modelColumn->listColumnsForDictionary($this->id);
        $this->out['count']   = $this->modelValue->countWhere(array('dictionary_id' => $this->id));
    }
    //--------------------------------------------------------------------------


    public function addValueAction() {
        $this->getRowFromDb('');
        $columns = $this->modelColumn->listColumnsAssoc($this->id);
        $form = $this->valueForm('Add Value', 'Add Value', $columns);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $form->importPostRequestAndThrowEDoneOnError();
            $key = $form->key;
            if ($this->modelValue->getExistingFieldValuesForKey(array('dictionary_id' => $this->id, 'id' => 0), array('key_value' => $key))) {
                $form->addErrorAndThrowEDone('key', 'Key already exists');
            }
            $data = array('dictionary_id' => $this->id, 'key_value' => $key);
            $valueId = $this->modelValue->insert($data);
            if (!$valueId) {
                throw new ERequestError("Unable to add row");
            }
            $valuesAssoc = array();
            foreach ($columns as $cid => $v) {
                $valuesAssoc[$cid] = $form->{'value_' . $cid};
            }
            $this->modelValue->writeValues($valueId, $valuesAssoc);
            App::getFrontController()->redirectToCP($this, array('action' => 'values'), array('id' => $this->id));
        }
    }
    //--------------------------------------------------------------------------


    public function editValueAction() {
        $this->getRowFromDb('');
        $valId = isset($_GET['val_id']) ? (int)$_GET['val_id'] : 0;

        $data    = $this->modelValue->get($valId);
        if (!$data) {
            throw new ERequestError('invalid val_id');
        }
        $columns = $this->modelColumn->listColumnsAssoc($this->id);

        $form = $this->valueForm('Edit Value', 'Update Value', $columns);
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $form->importPostRequestAndThrowEDoneOnError();
            $key = $form->key;
            if ($this->modelValue->getExistingFieldValuesForKey(array('dictionary_id' => $this->id, 'id' => $valId), array('key_value' => $key))) {
                $form->addErrorAndThrowEDone('key', 'Key already exists');
            }
            $newData = array('id' => $valId, 'dictionary_id' => $data['dictionary_id'], 'key_value' => $form->key);
            $this->modelValue->updateDiff($valId, $newData, $data);
            $valuesAssoc = array();
            foreach ($columns as $cid => $v) {
                $valuesAssoc[$cid] = $form->{'value_' . $cid};
            }
            $this->modelValue->writeValues($valId, $valuesAssoc);
            App::getFrontController()->redirectToCP($this, array('action' => 'values'), array('id' => $this->id));
        } else {
            $form->key = $data['key_value'];
            foreach ($columns as $id => $v) {
                $form->{'value_' . $id} = isset($data['values'][$id]) ? $data['values'][$id] : '';
            }
        }
    }
    //--------------------------------------------------------------------------


    public function deleteValueAction() {
        $this->getRowFromDb('');
        $valId = isset($_GET['val_id']) ? (int)$_GET['val_id'] : 0;

        $data    = $this->modelValue->get($valId);
        if (!$data) {
            throw new ERequestError('invalid val_id');
        }

        $form = new Form_Data();
        $form->add('button', '', 'Delete Value', array('type' => 'submit'));
        $pf = new PageFragment_FormAuto($this, $form, true);
        $pf->title = 'Delete Value "' . escapeHtml($data['key_value']) . '"?';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->modelValue->delete($valId);
            App::getFrontController()->redirectToCP($this, array('action' => 'values'), array('id' => $this->id));
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @return Form_Data
     */
    protected function valueForm($title, $buttonTitle, array $columns) {
        $form = new Form_Data();
        $form->add('text', 'key', 'Key', array('* trim' => true, '* required' => true));

        foreach ($columns as $id => $colName) {
            $form->add('text', 'value_' . $id, $colName, array('maxlength' => 255));
        }
        $form->add('button', '',   $buttonTitle, array('type' => 'submit'));
        $pf = new PageFragment_FormAuto($this, $form, true);
        $pf->title = $title;
        $pf->tableAttributes['width'] = 700;

        $this->prependToContent = '
<style type="text/css">
.form_table input {
	width: 100%;
}
</style>
        ';

        return $form;
    }
    //--------------------------------------------------------------------------


    /**
     * @return Form_Data
     */
    protected function columnForm($title, $buttonTitle) {
        $form = new Form_Data();
        $form->add('text', 'name', 'Name', array('* trim' => true, '* required' => true));
        $form->add('button', '',   $buttonTitle, array('type' => 'submit'));
        $pf = new PageFragment_FormAuto($this, $form, true);
        $pf->title = $title;

        return $form;
    }
    //--------------------------------------------------------------------------


    protected function addFormFields($action = '') {
        $this->form->add('text', 'name',     'Name',     array('* trim' => 1, '* required' => 1, 'maxlength' => 60));
    }
    //--------------------------------------------------------------------------


    protected function validateFormImport($action) {
        if ($this->model->getExistingFieldValuesForKey($this->id, array('name' => $this->form->name))) {
            $this->form->addError('name', "Name already used");
        }
    }
    //--------------------------------------------------------------------------
}
