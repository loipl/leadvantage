<?php

abstract class CrudControllerWithCM extends CrudController {

    /**
     * @var CrudModel
     */
    protected $model;

    /**
     * @var CM__Table
     */
    protected $cmTable = null;


    public function __construct() {
        parent::__construct();
    }
    //--------------------------------------------------------------------------


    protected function getDataRow($id) {
        return $this->cmTable ? $this->model->get($this->cmTable, $id) : $this->model->get($id);
    }
    //--------------------------------------------------------------------------


    protected function insertRow() {
        $this->id = $this->cmTable ? $this->model->insert($this->cmTable, $this->data) : $this->model->insert($this->data);
    }
    //--------------------------------------------------------------------------


    protected function updateRow() {
        $this->cmTable ? $this->model->updateDiff($this->cmTable, $this->id, $this->data, $this->origData) : $this->model->updateDiff($this->id, $this->data, $this->origData);
    }
    //--------------------------------------------------------------------------


    protected function deleteRow() {
        $this->cmTable ? $this->model->delete($this->cmTable, $this->id) : $this->model->delete($this->id);
    }
    //--------------------------------------------------------------------------


    protected function getDataList($orderBy) {
        return $this->cmTable ? $this->model->listPage($this->cmTable, $this->page, $this->perPage, $orderBy) : $this->model->listPage($this->page, $this->perPage, $orderBy);
    }
    //--------------------------------------------------------------------------


    protected function getListSize() {
        return $this->cmTable ? $this->model->count($this->cmTable) : $this->model->count();
    }
    //--------------------------------------------------------------------------
}
