<?php

/**
 * @desc Renders the form in a "standard" way
 */
class PageFragment_FormAuto extends PageFragment {

    public $xhtml = false;

    public $typeData = array();

    public $elemCallbackMap = array();

    public $tableAttributes = array('cellspacing' => "0");

    public $title = '';

    public $subTitle = '';

    /**
     * @var Form_Data
     */
    protected $form = null;


    public function __construct(Controller $controller = null, Form_Data $form = null, $override = false) {
        parent::__construct($controller);
        $this->form = $form;
        if ($override && ($controller instanceof Controller)) {
            $controller->setViewFile(Controller::getViewFileFor(get_class($this), array('action' => 'forController')));
            $controller->set('____PageFragment_FormAuto', $this);
        }
        Config::initExternalConfig(__CLASS__, $this);
    }
    //--------------------------------------------------------------------------


    /**
     * @param Controller $controller
     *
     * @return PageFragment_Box
     */
    public static function create(Controller $controller = null) {
        die;
    }
    //--------------------------------------------------------------------------


    public function setForm(Form_Data $form) {
        $this->form = $form;
    }
    //--------------------------------------------------------------------------


    public function getForm() {
        return $this->form;
    }
    //--------------------------------------------------------------------------


    public function getHtmlFor(array $attributes) {
        $type = strtolower($attributes['* type']);
        if (!empty($this->typeData[$type])) {
            $attributes = array_merge($this->typeData[$type], $attributes);
        }

        $func = 'getHtmlFor' . ucfirst($type);
        if (isset($this->elemCallbackMap[$type])) {
            return call_user_func($this->elemCallbackMap[$type], $attributes, $this);
        } elseif (is_callable(array($this, $func))) {
            return $this->$func($attributes);
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    public function getHtmlForNamedElement($name, array $mergeAttributes = array()) {
        $elem = $this->form->getElementByName($name);
        if ($elem) {
            foreach ($mergeAttributes as $k => $v) {
                $elem[$k] = empty($elem[$k]) ? $v : "$k $v";
            }
            return $this->getHtmlFor($elem);
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    public function getHtmlForNamedElementFull($name, array $mergeAttributes = array()) {
        $elem = $this->form->getElementByName($name);
        if (!$elem) {
            return '';
        }
        foreach ($mergeAttributes as $k => $v) {
            $elem[$k] = empty($elem[$k]) ? $v : $elem[$k] . " $v";
        }
        $out = $this->getHtmlFor($elem);
        if (!empty($elem['* hint'])) {
            $out .= "<div class=\"form_hint\">{$elem['* hint']}</div>\n";
        }
        $s = $this->form->getFieldErrorsImploded($name, "<br>");
        if ($s) {
            $out .= "<span class=\"form_error\">$s</span>\n";
        }
        return $out;
    }
    //--------------------------------------------------------------------------


    public function getHtmlForText(array $elem) {
        $output = '<input type="text" value="' . escapeAttrVal($this->form->{$elem['name']}) . '"';
        $output .= $this->glueAttributes($elem);
        $output .= $this->xhtml ? ' />' : '>';

        return $output;
    }
    //--------------------------------------------------------------------------


    public function getHtmlForPassword(array $elem) {
        $output = '<input type="password" value="' . escapeAttrVal($this->form->{$elem['name']}) . '"';
        $output .= $this->glueAttributes($elem);
        $output .= $this->xhtml ? ' />' : '>';

        return $output;
    }
    //--------------------------------------------------------------------------


    public function getHtmlForSubmit(array $elem) {
        $output = '<input type="submit" value="' . escapeAttrVal(isset($elem['* label']) ? $elem['* label'] : 'Submit') . '"';
        $output .= $this->glueAttributes($elem);
        $output .= $this->xhtml ? ' />' : '>';

        return $output;
    }
    //--------------------------------------------------------------------------


    public function getHtmlForFile(array $elem) {
        $output = '<input type="file"' . $this->glueAttributes($elem);
        $output .= $this->xhtml ? ' />' : '>';

        return $output;
    }
    //--------------------------------------------------------------------------


    public function getHtmlForCheckbox(array $elem) {
        $output = '<label><input type="checkbox" value="1"';
        if (!empty($this->form->{$elem['name']})) {
            $output .= ' checked="checked"';
        }
        $output .= $this->glueAttributes($elem);
        $output .= $this->xhtml ? '/>' : '>';
        $output .= $elem['* label'] . "</label>";

        return $output;
    }
    //--------------------------------------------------------------------------


    public function getHtmlForRadio(array $elem) {
        $output = '<input type="radio" value="1"';
        if (!empty($this->form->{$elem['name']})) {
            $output .= ' checked="checked"';
        }
        $output .= $this->glueAttributes($elem);
        $output .= $this->xhtml ? '/>' : '>';

        return $output;
    }
    //--------------------------------------------------------------------------


    public function getHtmlForTextarea(array $elem) {
        $output = '<textarea ' . $this->glueAttributes($elem) . ">\n";
        $output .= escapeAttrVal($this->form->{$elem['name']});
        $output .= '</textarea>';
        return $output;
    }
    //--------------------------------------------------------------------------
    
    
    public function getHtmlForButton(array $elem) {
        $output = '<button' . $this->glueAttributes($elem, array('value')) . '>';
        $output .= $elem['* label'];
        $output .= '</button>';
        return $output;
    }
    //--------------------------------------------------------------------------


    public function getHtmlForSelect(array $elem) {
        $isMulti = !empty($elem['multiple']);
        $output = '<select';
        if (!empty($elem['name'])) {
            $output .= ' name="' . escapeAttrVal($elem['name']) . ($isMulti ? '[]' : '') . '"';
        }
        $output .= $this->glueAttributes($elem, array('type', 'value', 'name')) . ">\n";
        if (isset($elem['* items'])) {
            $selected = $this->form->{$elem['name']};
            if($selected && !$isMulti) {
                $selected = (string)$selected;
            }
            foreach ($elem['* items'] as $index => $text) {
                $output .= '<option value="' . escapeAttrVal($index) . '"';
                if ($isMulti) {
                    if (is_array($selected) && (array_search($index, $selected) !== false)) {
                        $output .= ' selected="selected"';
                    }
                } else {
                    if (!is_null($selected) && ((string)$index == (string)$selected)) {
                        $output .= ' selected="selected"';
                    }
                }
                $output .= '>' . escapeHtml($text) . "</option>\n";
            }
        }
        $output .= "</select>\n";

        return $output;
    }
    //--------------------------------------------------------------------------


    public function getHtmlForHidden(array $elem) {
        $output = '<input type="hidden"';
        $output .= $this->glueAttributes($elem, array('type'));
        $output .= ' value="' . escapeAttrVal($this->form->{$elem['name']}) . '"';
        $output .= $this->xhtml ? ' />' : '>';

        return $output;
    }
    //--------------------------------------------------------------------------


    public function glueAttributes(array $elem, array $skip = array('type', 'value')) {
        $out = '';
        foreach ($elem as $attrName => $attrValue) {
            if (in_array(strtolower($attrName), $skip) || (strpos($attrName, '* ') === 0)) {
                continue;
            }
            $out .= " $attrName=\"" . escapeAttrVal($attrValue) . '"';
        }
        return $out;
    }
    //--------------------------------------------------------------------------
}