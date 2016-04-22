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
    
    public function listValueAction() {
        $dataListModel = SingletonRegistry::getSingleInstance('Model_DataListValue');
        $id = filter_input(INPUT_GET, 'id');
        
        $dataList = $this->model->get($id);
        $dataListValues = $dataListModel->listAllWhere(array('data_list_id' => $id));
        
        $this->out['dataListValues'] = $dataListValues;
        $this->out['dataList'] = $dataList;
        
    }
    //--------------------------------------------------------------------------
    
    public function uploadCsvAction() {
        App::getFrontController()->setUsePageTemplate(false);
        $response = array('status' => '0');
        if ( isset($_FILES["csv_file"])) {
            if ($_FILES["csv_file"]["error"] > 0) {
                $response['message'] = "File error, number " . $_FILES["file"]["error"];
            } else {
                $tmpName   = $_FILES["csv_file"]["tmp_name"];
                $filename = $_FILES["csv_file"]["name"];
                $destination = CFG_ROOT_DIR . 'uploads';
                $filenameWithPath = $destination . DIRECTORY_SEPARATOR . $filename;
                
                move_uploaded_file($tmpName, $filenameWithPath);
                $this->changeToCsvStandardFormat($filenameWithPath);
                
                $file   = fopen($filenameWithPath, "r");
                $header = fgetcsv($file);
                fclose($file);
                
                $response['status'] = 1;
                $response['filePath'] = $filenameWithPath;
                $response['columns'] = $header;
            }
        } else {
            $response['message'] = "No file selected";
        }
        
        echo json_encode($response);
    }
    
    public function changeToCsvStandardFormat($filename) {
        try {
            $content            = file_get_contents($filename);
//            $content            = str_replace(array('"', '\''), array('', ''), $content);
            $content            = preg_replace('/\t/', ',', $content);
            $content            = preg_replace("/\r\n?/", "\r\n", $content);
            file_put_contents($filename, $content);
        } catch (Exception $ex) {
            
        }
        
    }
    
    public function pickColumnAndSaveAction() {
        $dataListValueModel = SingletonRegistry::getSingleInstance('Model_DataListValue');
        
//        $filename = "C:/xampp/htdocs/leadvantage/uploads/zipcode.csv";
//        $column = "zipcode";
        $filename = filter_input(INPUT_POST, 'filePath');
        $column = filter_input(INPUT_POST, 'column');
        $id = filter_input(INPUT_GET, 'id');
        
        $file   = fopen($filename, "r");
        $header = fgetcsv($file);
        $valueList = array();
        $columnIndex = array_search($column, $header);

        while(!feof($file)) {
            $row = fgetcsv($file);
            if (isset($row[$columnIndex])) {
                $valueList[] = $row[$columnIndex];
            }
        }       
                
        $valueList = array_unique($valueList);
        fclose($file);
        $dataListValueModel->insertMultiple($id, $valueList);

//        $filename = filter_input(INPUT_POST, 'filePath');
//        $column = filter_input(INPUT_POST, 'column');

        App::getFrontController()->redirectToCP($this, array('action' =>  'listValue'), array('id' => $id));
    }
}
