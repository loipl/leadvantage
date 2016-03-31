<?php

class Controller_Admin_Messages extends CrudControllerWithCM {

    /**
     * @var Model_SystemMessage
     */
    protected $model;


    public function __construct() {
        $this->model = SingletonRegistry::getSingleInstance('Model_SystemMessage');

        parent::__construct();
        $this->defaultOrderBy = 'msg_time';
        $this->itemName       = 'Message';
        $this->perPage        = 20;

        $mu = SingletonRegistry::getModelUser();
        $this->out['usernames'] = $mu->listUsernamesAssoc();
    }
    //--------------------------------------------------------------------------


    protected function addFormFields($action = '') {
        $this->form->add('textarea', 'text', 'Text', array('* trim' => 1, '* required' => 1, 'rows' => 12, 'cols' => 80));
        $mu = SingletonRegistry::getModelUser();
        $usernames = array(0 => '* All *');
        foreach ($mu->listUsernamesAssoc() as $userId => $name) {
            $usernames[$userId] = $name;
        }
        $this->form->add('select', 'user_id', 'Recipient', array('* items' => $usernames));

        $this->pageFragmentForm->tableAttributes['width'] = 650;
        $this->prependToContent = '
<style type="text/css">
.form_table textarea {
	width: 100%;
}
</style>';
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Here we can override the form values before they are written to DB
     */
    protected function getFormValuesForWritingToDb($action = '') {
        $data = $this->form->getValues();
        if ($data['user_id'] == 0) {
            $data['user_id'] = null;
        }
        return $data;
    }
    //--------------------------------------------------------------------------
}
