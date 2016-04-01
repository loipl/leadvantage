<?php

class Controller_Admin_Validation extends Controller {

    private $validators;
    private $fieldTypesAssocLowerCase;
    private $fieldTypesAssoc;
    private $validatorOptions = array();


    public function indexAction() {
        $form = new Form_Data();
        $this->validators = Validator_Registry::listValidators();

        /* @var $mft Model_FieldType */
        $mft = SingletonRegistry::getSingleInstance('Model_FieldType');
        $this->fieldTypesAssoc = $mft->listFieldTypesAssoc();
        $this->fieldTypesAssocLowerCase = $mft->listFieldTypesAssoc(true);

        $this->validatorOptions = array();

        foreach ($this->validators as $validatorName => /* @var $validator Validator_Base */ $validator) {
            foreach ($validator->listSupportedFTypes() as $arr) {
                $ftNames = array();
                foreach ($arr as $ftId) {
                    $ftNames[] = $this->fieldTypesAssoc[$ftId];
                }
                $optionText  = $validator->name() . ': ' . implode(', ', $ftNames);
                $optionValue = $validatorName . '_' . implode('-', $arr);
                $this->validatorOptions[$optionValue] = $optionText;
            }
        }

        $examplesText = "Example 1:<br><br>
Email<br>
one@emai.com<br>
another@email.com<br><br>
Example 2:<br><br>
Email,Phone<br>
one@emai.com,13245912347<br>
another@email.com,2349576234<br><br>
";
        $form->add('select',   'validators',   'Validators', array('* items' => $this->validatorOptions, 'multiple' => 'multiple', 'size' => max(5, sizeof($this->validatorOptions)), '* required' => true));
        $form->add('textarea', 'csv_data',     'Data (CSV)', array('rows' => 20, 'class' => "full_width", '* required' => true, '* trim' => true, '* hint' => $examplesText));
        $form->add('button',   '',             'Validate',   array('type' => 'submit'));

        $reportViewFile = self::getViewFileFor($this, array('action' => 'report'));

        $pf = new PageFragment_FormAuto($this, $form, true);
        $pf->tableAttributes['width'] = 700;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $form->importPostRequestAndThrowEDoneOnError();
            $this->appendToContentFile = $reportViewFile;

            $this->createReport($form, is_array($form->validators) ? $form->validators : array(), $form->csv_data);
            $form->throwDoneIfErrors();
        } else {
            $form->validators = array('dv_2');
            $form->csv_data = "Email,Phone
joe@yahoo.com,1-718-234-3455";
        }
    }
    //--------------------------------------------------------------------------


    private function getFieldTypesFromFirstLine(Form_Data $form, $csvData) {
        if (trim($csvData) == '') {
            $form->addErrorAndThrowEDone('csv_data', 'First line must have comma-separated field types - Email, Phone etc');
        }
        $lines = explode("\n", $csvData);
        $firstLine = trim($lines[0]);
        $fieldTypes = array();
        $error = false;
        foreach (explode(',', $firstLine) as $ftName) {
            $ftIndex = array_search(trim(strtolower($ftName)), $this->fieldTypesAssocLowerCase);
            if ($ftIndex === false) {
                $fieldTypes = array();
                break;
            }
            $fieldTypes[] = (int)$ftIndex;
        }
        if (!$fieldTypes) {
            $form->addErrorAndThrowEDone('csv_data', 'First line must have comma-separated field types - Email, Phone etc');
        }
        return $fieldTypes;
    }
    //--------------------------------------------------------------------------


    private function createReport(Form_Data $form, array $chosenValidators, $csvData) {
        $ftIds = $this->getFieldTypesFromFirstLine($form, $form->csv_data);
        $validatorsList = $this->getValidatorsList($form, $ftIds, $chosenValidators);

        $this->out['ftIds'] = $ftIds;
        $lines = explode("\n", $csvData);
        $toValidate = array();

        for($i = 1; $i < sizeof($lines); $i++) {
            $line = trim($lines[$i]);
            if (!$line) {
                continue;
            }
            $arr = str_getcsv($line);
            $oneRow = array();
            foreach ($arr as $index => $val) {
                if ($val != '') {
                    $oneRow[$ftIds[$index]] = $val;
                }
            }
            $toValidate[] = $oneRow;
        }

        $this->out['results']      = $toValidate ? $this->validate($toValidate, $validatorsList) : array();
    }
    //--------------------------------------------------------------------------


    private function getValidatorsList(Form_Data $form, array $ftIds, array $chosenValidators) {
        $validatorsList = array();
        foreach ($chosenValidators as $optionString) {
            list($name, $ftIdsString) = explode('_', $optionString, 2);
            $optionFtIds = array();
            foreach (explode('-', $ftIdsString) as $id) {
                $optionFtIds[] = (int)$id;
            }
            $missing = array_diff($optionFtIds, $ftIds);
            if ($missing) {
                $displayName = $this->validatorOptions[$optionString];
                $form->addError('validators', "Your CSV input doesn't have all field types needed for '$displayName' validator");
            }
            $validatorsList[$optionString] = array($this->validators[$name], $optionFtIds);
        }
        $form->throwDoneIfErrors();
        return $validatorsList;
    }
    //--------------------------------------------------------------------------


    private function validate(array $toValidate, array $validatorsList) {
        $results = array();
        foreach ($toValidate as $oneRow) {
            $reportRows = array();
            foreach ($validatorsList as $optionString => $pair) {
                /* @var $validator Validator_Base */
                $validator = $pair[0];
                $ftIds = $pair[1];
                if (!array_intersect($ftIds, array_keys($oneRow))) {
                    continue;
                }
                $displayName = $this->validatorOptions[$optionString];
                $subArray = array();
                foreach ($ftIds as $oneFtId) {
                    $subArray[$oneFtId] = $oneRow[$oneFtId];
                }
                $valResult = $validator->validateFTypesValuesAssoc($subArray);

                $result = array();
                $apiRes = array();
                $errMsg = array();
                foreach ($valResult as $vrFtId => $vrRow) {
                    $result[] = $this->fieldTypesAssoc[$vrFtId] . ': ' . $vrRow[1];
                    $apiRes[] = $this->fieldTypesAssoc[$vrFtId] . ': ' . escapeHtml($vrRow[2]);
                    $errMsg[] = $this->fieldTypesAssoc[$vrFtId] . ': ' . escapeHtml($vrRow[3]);
                }
                $reportRows[] = array(
                    'name'   => $displayName,
                    'result' => implode("<br>\n", $result),
                    'apiRes' => implode("<br>\n", $apiRes),
                    'errMsg' => implode("<br>\n", $errMsg),
                );
            }
            $params = array();
            foreach ($oneRow as $oneFtId => $value) {
                $params[] = $this->fieldTypesAssoc[$oneFtId] . ': ' . escapeHtml($value);
            }
            $results[] = array('row' => $params, 'reportRows' => $reportRows);
        }
        return $results;
    }
    //--------------------------------------------------------------------------
}
