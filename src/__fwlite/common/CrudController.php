<?php

abstract class CrudController extends Controller {

    protected $itemName   = 'Thing';

    protected $sortByArg  = 'sortBy';

    protected $sortDirArg = 'sortDir';

    protected $alternateSortDir = false;

    protected $pageArgument = 'page';

    protected $defaultOrderBy = '';

    protected $getForRedirect = array();

    protected $paramsForRedirect = array();

    /**
     * @var Form_Data
     */
    protected $form;

    /**
     * @var PageFragment_FormAuto
     */
    protected $pageFragmentForm;

    /**
     * @var Util_HeaderLinks
     */
    protected $headerLinks;


    protected $id       = 0;

    protected $data     = array();

    protected $origData = array();

    protected $page     = 1;

    protected $perPage  = 25;


    public function indexAction() {
        $this->page = isset($_GET[$this->pageArgument]) ? max(1, (int)$_GET[$this->pageArgument]) : 1;
        $this->initHeaderLinks();
        $orderBy = $this->defaultOrderBy;
        if ($this->headerLinks) {
            $this->out['headerLinks'] = $this->headerLinks;
            $this->headerLinks->acquireGetVars();
            $orderBy = $this->headerLinks->selectedOrderByField();
        }
        $list = $this->getDataList($orderBy);

        $this->out['page']    = $this->page;
        $this->out['perPage'] = $this->perPage;
        $this->out['list']    = $list;
        $this->out['count']   = $this->getListSize();
    }
    //--------------------------------------------------------------------------


    public function editActionInit() {
        $this->getRowFromDb('edit');
        $this->form($this->titleForEditForm(), $this->buttonTextForEditForm(), 'edit');
    }
    //--------------------------------------------------------------------------


    public function editActionGet() {
        $this->form->importFromArray($this->data, false);
    }
    //--------------------------------------------------------------------------


    public function editActionPost($update = true, $redirect = true) {
        $this->form->importFromArray($_POST, true);
        if ($this->form->hasErrors()) {
            return;
        }
        $this->validateFormImport('edit');
        if ($this->form->hasErrors()) {
            return;
        }
        $this->data = $this->getFormValuesForWritingToDb('edit');
        if ($update) {
            $this->updateRow();
        }
        if ($redirect) {
            App::getFrontController()->redirectToCP($this, $this->paramsForRedirect, $this->getForRedirect);
        }
    }
    //--------------------------------------------------------------------------


    public function addAction($insert = true, $redirect = true) {
        $this->form($this->titleForAddForm(), $this->buttonTextForAddForm(), 'add');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->form->importFromArray($_POST, true);
            if ($this->form->hasErrors()) {
                return;
            }
            $this->validateFormImport('add');
            if ($this->form->hasErrors()) {
                return;
            }
            $this->data = $this->getFormValuesForWritingToDb('add');
            $this->insertDefaultValues();
            if ($insert) {
                $this->insertRow();
            }
            if ($redirect) {
                App::getFrontController()->redirectToCP($this, $this->paramsForRedirect, $this->getForRedirect);
            }
        } else {
            $this->putInitialValuesToForm();
        }
    }
    //--------------------------------------------------------------------------


    public function deleteAction($delete = true, $redirect = true) {
        $this->getRowFromDb('delete');
        $this->form = new Form_Data;
        $this->form->add('button', '', 'Delete', array('type' => 'submit'));

        $frm = new PageFragment_FormAuto($this, $this->form, true);
        $frm->title = $this->titleForDeleteForm();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($delete) {
                $this->deleteRow();
            }
            if ($redirect) {
                App::getFrontController()->redirectToCP($this, $this->paramsForRedirect, $this->getForRedirect);
            }
        }
    }
    //--------------------------------------------------------------------------


    protected function getRowFromDb($action) {
        $this->id = isset($_GET['id']) ? max((int)$_GET['id'], 0) : 0;
        if ($this->id) {
            $this->data = $this->origData = $this->getDataRow($this->id);
            if ($this->data) {
                $this->checkRights($action);
                $this->out['id']   = $this->id;
                $this->out['data'] = $this->data;
                return;
            }
        }
        throw new ERequestError("Invalid id parameter");
    }
    //--------------------------------------------------------------------------


    protected function form($title, $buttonTitle, $action = '') {
        $this->form = new Form_Data;
        $frm = new PageFragment_FormAuto($this, $this->form, true);
        $this->pageFragmentForm = $frm;
        $this->addFormFields($action);
        $this->form->add('button', '', $buttonTitle, array('type' => 'submit'));

        $frm->title = $title;
    }
    //--------------------------------------------------------------------------


    protected function titleForEditForm() {
        return "Edit $this->itemName";
    }
    //--------------------------------------------------------------------------


    protected function titleForAddForm() {
        return "Add New $this->itemName";
    }
    //--------------------------------------------------------------------------


    protected function titleForDeleteForm() {
        return "Delete $this->itemName";
    }
    //--------------------------------------------------------------------------


    protected function buttonTextForEditForm() {
        return "Update $this->itemName";
    }
    //--------------------------------------------------------------------------


    protected function buttonTextForAddForm() {
        return "Add New $this->itemName";
    }
    //--------------------------------------------------------------------------


    protected function initHeaderLinks() {
        //
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Here we can override the form values before they are written to DB
     */
    protected function getFormValuesForWritingToDb($action = '') {
        return $this->form->getValues();
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Check if user has the right to access this item. Should throw an
     * exception, not return a value
     */
    protected function checkRights($action) {
        //
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Will be called from addAction, set default values like timestamp<br><br>
     *
     * $this->data['timestamp'] = date('Y-m-d H:i:s');
     */
    protected function insertDefaultValues() {
        //
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Should add errors to form, ie $this->form->addError('fieldname', 'Error Message');
     */
    protected function validateFormImport($action) {
        //
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Called from addAction() in case it is not a POST request, when the form is just shown
     */
    protected function putInitialValuesToForm() {
        //
    }
    //--------------------------------------------------------------------------


    /**
     * @desc $this->form->add('text', 'fieldName', 'Label'); // ....
     */
    abstract protected function addFormFields($action = '');
    //--------------------------------------------------------------------------


    /**
     * @desc return $this>model->insert($this->data);
     */
    abstract protected function insertRow();
    //--------------------------------------------------------------------------


    /**
     * @desc $this>model->update($this->id, $this->data);
     */
    abstract protected function updateRow();
    //--------------------------------------------------------------------------


    /**
     * @desc $this>model->delete($this->id);
     */
    abstract protected function deleteRow();
    //--------------------------------------------------------------------------


    /**
     * @desc return $this->model->get($id);
     */
    abstract protected function getDataRow($id);
    //--------------------------------------------------------------------------


    /**
     * @desc return $this->model->list($this->page, $this->perPage, $orderBy);
     */
    abstract protected function getDataList($orderBy);
    //--------------------------------------------------------------------------


    /**
     * @desc return $this->model->getSize();
     */
    abstract protected function getListSize();
    //--------------------------------------------------------------------------
}