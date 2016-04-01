<?php

class Controller_Admin_Export extends Controller {

    /**
     * @var Model_ExportFeed
     */
    private $model;

    /**
     * @var Form_Data
     */
    private $form;

    public static $allFeedFields = array();


    public function indexAction() {
        $page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 20;
        $count   = $this->model->count();
        $list    = $this->model->listPage($page, $perPage, 'name');

        $this->out['page']    = $page;
        $this->out['perPage'] = $perPage;
        $this->out['count']   = $count;
        $this->out['list']    = $list;
    }
    //--------------------------------------------------------------------------


    public function addAction() {
        $form = $this->form('Add Export Feed', 'Add Export Feed');
        $this->out['sources'] = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validate(0);
            $data = $this->form->getValues();
            $s = '';
            $letters = '0123456789abcdefghijklmnopqrstuvwxyz';
            for ($i = 0; $i < 64; $i++) {
                $s .= $letters[rand(0, strlen($letters) - 1)];
            }
            $data['key'] = $s;

            $id = $this->model->insert($data);
            if ($id) {
                App::getFrontController()->redirectToCP($this);
            }
        } else {
            $form->is_active    = 1;
            $form->default_days = 7;
            $form->system_fields = 'lead_id,email,remote_ip,http_referer,timestamp';
            $form->country_default_include = 1;
            $form->field_types = '*';
        }
    }
    //--------------------------------------------------------------------------


    public function editAction() {
        $id = isset($_GET['id']) ? max(0, (int)$_GET['id']) : 0;
        if (!$id) {
            throw new EExplainableError("Invalid id");
        }
        $row = $this->model->get($id, MYSQL_ASSOC);
        if (!$row) {
            throw new EExplainableError("Invalid id");
        }
        $this->form('Edit Export Feed', 'Update Export Feed');
        $this->out['data'] = $row;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validate($id);
            $data = $this->form->getValues();
            $data['is_active'] = $data['is_active'] ? '1' : '0';
            $this->model->updateDiff($id, $data, $row);
            App::getFrontController()->redirectToCP($this);
        } else {
            $this->form->importFromArray($row, false);
            $this->form->is_active = !empty($row['is_active']) ? 1 : 0;

            $this->out['sources'] = $row['sources'];
        }
    }
    //--------------------------------------------------------------------------


    public function ajaxDeleteAction() {
        try {
            $id = isset($_POST['id']) ? max(0, (int)$_POST['id']) : 0;
            if (!$id) {
                throw new EExplainableError("Invalid id");
            }
            if (!$this->model->get($id, MYSQL_ASSOC)) {
                throw new EExplainableError("Invalid id");
            }
            $this->model->delete($id);
            die('OK');
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    //--------------------------------------------------------------------------


    private function validate($id = 0) {
        $this->form->importFromArray($_POST, true);
        if ($this->form->tld_list) {
            $arr = array();
            foreach (explode("\n", trim($this->form->tld_list)) as $s) {
                $s = strtolower(trim($s));
                $arr[] = $s;
            }
            $arr = array_unique($arr);
            $this->form->tld_list = implode("\n", $arr);
        }
        $this->form->throwDoneIfErrors();
        $this->form->checkNumeric('default_days', 1);

        $conflicts = $this->model->getExistingFieldValuesForKey($id, array('name' => $this->form->name, 'key' => $this->form->key));
        if (isset($conflicts['name'])) {
            $this->form->addError('name', 'Name already exists');
        }

        $this->form->throwDoneIfErrors();
    }
    //--------------------------------------------------------------------------


    /**
     * @return Form_Data
     */
    private function form($title, $buttonTitle) {
        $form = new Form_Data();
        $form->add('text',     'name',                'Name',   array('maxlength' => 128, 'class' => 'f', 'id' => 'export-feed-name', '* required' => true, '* trim' => true));
        $form->add('checkbox', 'is_active',           'Active');
        $form->add('text',     'default_days',        'Days To Show', array('size' => '3'));
        $form->add('textarea', 'notes',               'Notes',        array('rows' => 6));
        $form->add('select',   'tld_default_include', 'TLDs', array ('id' => 'tld-default-include', '* items' => array('0' => 'Only these TLDs', '1' => 'Exclude these TLDs')));
        $form->add('textarea', 'tld_list',                '', array ('rows' => 6, '* hint' => 'One TLD per line'));

        $form->add('hidden',   'sources',    '', array('id' => 'ef-sources'));

        $form->add('hidden',   'system_fields',           '', array ('id' => 'system-fields'));
        $form->add('hidden',   'field_types',             '', array ('id' => 'field-types'));
        $form->add('hidden',   'country_default_include', '', array ('id' => 'country-default-include'));
        $form->add('hidden',   'countries',               '', array ('id' => 'countries-list'));

        $form->add('button', '', $buttonTitle, array('type' => 'submit'));


        $pf = new PageFragment_FormAuto($this, $form);
        $pf->title = $title;
        $pf->buttonTitle = $buttonTitle;

        $this->form = $form;
        $this->out['pf'] = $pf;

        $this->out['campaignsAssoc'] = SingletonRegistry::getModelCampaign()->listCampaigsAssocPerUser();
        $this->out['usernamesAssoc'] = SingletonRegistry::getModelUser()->listUsernamesAssoc();
        $this->out['fieldTypes']     = SingletonRegistry::getModelCampaignField()->listFieldTypesAssoc();
        $this->out['countriesAssoc'] = SingletonRegistry::getModelCountry()->listCountriesAssoc();

        return $form;
    }
    //--------------------------------------------------------------------------


    public function preRun() {
        $this->model = $this->model = SingletonRegistry::getSingleInstance('Model_ExportFeed');
    }
    //--------------------------------------------------------------------------
}
