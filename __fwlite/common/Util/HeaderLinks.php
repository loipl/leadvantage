<?php


/**
 * @desc Creates header links for sortable tables
 */
class Util_HeaderLinks {


    /**
     * @desc Base URL links will point to
     *
     * @var string
     */
    protected $baseUrl = "";

    /**
     * @desc Additional $_GET arguments in links
     *
     * @var array
     */
    protected $arguments = array();

    /**
     * @desc If true allows bidirectional sorting on each link
     *
     * @var bool
     */
    protected $alternateAscDesc = false;

    /**
     * @desc Images for displaying up/down arrows as sort indicators
     *
     * @var array
     */
    // protected $sortIndicators = array('/img/desc_order.gif', '/img/asc_order.gif');
    // protected $sortIndicators = array();

    /**
     * @desc Array of assoc arrays for clickable links, title is text that is
     *   displayed as column header link, sort_key is value indicating table
     *   sorting from DB, ie array('title' => 'Date', 'sort_key' => 'sbm_date')
     *
     * @var assoc array
     */
    protected $columnNames = array();

    /**
     * @desc Name of get argument for sorting column name
     *
     * @var string
     */
    protected $sortByArg = "sortBy";

    /**
     * @desc Name of optional argument that defines sorting direction ASC/DESC
     *
     * @var mixed
     */
    protected $sortDirection = false;

    /**
     * @desc Map of sort_keys to fields
     *
     * @var array
     */
    protected $fieldMap = array();

    /**
     * @desc Sort key to use if none is specified
     *
     * @param string
     */
    protected $defaultSortKey = false;


    /**
     * @desc Constructor
     *
     * @param string $base_url
     * @param bool $alternateAscDesc
     */
    public function __construct($baseUrl, $alternateAscDesc = false, $directionGetArg = false, $defaultSortKey = false){
        $this->baseUrl          = $baseUrl;
        $this->alternateAscDesc = $alternateAscDesc;
        $this->defaultSortKey   = $defaultSortKey;
        $this->sortDirection    = $directionGetArg;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Adds column with given name and sort key
     *
     * @param string $columnTitle
     * @param string $field
     * @param string $defaultSort
     * @param string $cssClass
     * @internal param string $sortKey
     */
    public function addColumn ($columnTitle, $field = '', $defaultSort = 'ASC', $cssClass = "") {
        $sortKey = $field ? (sizeof($this->columnNames) + 1) : '';
        $this->columnNames[] = array(
            'title'     => $columnTitle, 
            'sort_key'  => $sortKey, 
            'css_class' => $cssClass,
            'field'     => $field,
            'sort'      => strtoupper($defaultSort)
        );
        if (strlen($sortKey) > 0) {
            $this->fieldMap[$sortKey] = $field;
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Returns links for column headers, in same order as they were added
     *   If params are not specified, function tries to figure them out from $_GET
     *   array
     *
     * @param string $alreadySelected
     * @param string $existingSortDirection
     *
     * @return array Array of links for header
     */
    public function getColumnLinks ($alreadySelected = "", $existingSortDirection = "") {
        if (strlen($alreadySelected) == 0) {
            $alreadySelected = isset($_GET[$this->sortByArg]) ? $_GET[$this->sortByArg] : '';
        };
        if ((strlen($existingSortDirection) == 0) && ($this->sortDirection != false)) {
            $existingSortDirection = isset($_GET[$this->sortDirection]) ? $_GET[$this->sortDirection] : '';
            $existingSortDirection = trim(strtoupper($existingSortDirection));
            if (!in_array($existingSortDirection, array('ASC', 'DESC'))) {
                $existingSortDirection = '';
            }
        };
        if ((strlen($alreadySelected) == 0) && ($this->defaultSortKey !== false)) {
            $alreadySelected = $this->defaultSortKey;
        };
        $result = array();
        foreach ($this->columnNames as $column) {
            $result[] = $this->getLinkForColumn($column, $alreadySelected, $existingSortDirection);
        }
        return $result;
    }
    //--------------------------------------------------------------------------


    /**
     * @param array  $column
     * @param string $alreadySelected
     * @param string $existingSortDirection
     *
     * @return string Single link for column name
     */
    public function getLinkForColumn(array $column, $alreadySelected = "", $existingSortDirection = "") {
        $columnTitle = $column['title'];
        $sortKey = $column['sort_key'];
        $cssClass = $column['css_class'] > '' ? " class=\"{$column['css_class']}\"" : '';
        if ($sortKey == '') {
            if ($cssClass == '') {
                return $columnTitle;
            }
            return "<span$cssClass>$columnTitle</span>";
        };
        if ((!$this->alternateAscDesc) && ($alreadySelected == $sortKey)) {
            return $columnTitle;
        }
        $arr = array();
        foreach ($this->arguments as $key => $value) $arr[] = $key . '=' . urlencode($value);
        $arr[] = $this->sortByArg . "=" . urlencode($sortKey);
        if ($alreadySelected == $sortKey) {
            if ($existingSortDirection != "DESC") {
                $arr[] = "$this->sortDirection=DESC";
                if (isset($this->sortIndicators[0])) $columnTitle .= "&nbsp;<img src=\"{$this->sortIndicators[0]}\" border=\"0\">";
            } else {
                if (isset($this->sortIndicators[1])) $columnTitle .= "&nbsp;<img src=\"{$this->sortIndicators[1]}\" border=\"0\">";
            }
        }
        $d = implode("&", $arr);

        $s = "<a$cssClass href=\"{$this->baseUrl}?$d\">$columnTitle</a>";
        return $s;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc returns name of field in DB to sort by
     *
     * @param string $default
     */
    function selectedOrderByField($default = '') {
        if (!isset($_GET[$this->sortByArg])) {
            return $this->fullOrderByName($default, $this->defaultSortKey);
        };

        $sortKey = $_GET[$this->sortByArg];
        if (isset($this->fieldMap[$sortKey])) {
            return $this->fullOrderByName($this->fieldMap[$sortKey], $sortKey);
        };
        if ($this->defaultSortKey != false) {
            if (isset($this->fieldMap[$this->defaultSortKey])) {
                return $this->fullOrderByName($this->fieldMap[$this->defaultSortKey], $this->defaultSortKey);
            };
        };
        return $default;
    }
    //--------------------------------------------------------------------------


    public function selectedOrderByViaSortKey($defaultSortKey) {
        $sortKey = isset($_GET[$this->sortByArg]) ? $_GET[$this->sortByArg] : $defaultSortKey;
        $var = $this->fieldMap[$sortKey];
        if (is_string($var)) {
            return $this->selectedOrderByField($var);
        }
        $s = 'ASC';
        if ($this->sortDirection && isset($_GET[$this->sortDirection])) {
            $s = trim(strtoupper($_GET[$this->sortDirection]));
            if (($s != 'ASC') && ($s != 'DESC')) $s = 'ASC';
        };
        return $var[$s];
    }
    //--------------------------------------------------------------------------


    protected function fullOrderByName($field, $sortKey) {
        if (strlen($field) == 0) {
            if (isset($this->fieldMap[$sortKey])) return $this->fieldMap[$sortKey];
        };
        $s = 'ASC';
        if (($this->sortDirection != false) && isset($_GET[$this->sortDirection])) {
            $s = trim(strtoupper($_GET[$this->sortDirection]));
        };
        if (in_array($s, array('ASC', 'DESC'))) return "$field $s";
        foreach ($this->columnNames as $row) {
            if ($row['sort_key'] == $sortKey) return $field . ' ' . $row['sort'];
        };
        return $field;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Returns first row for HTML table
     *
     * @param string $trAttrs Text that gets inserted in TR tag
     * @param array  $tdAttrs Array with texts that get inserted in TD tags
     *
     * @return string
     */
    function tableHeader($trAttrs = '', $tdAttrs = array()) {
        $s = "  <tr$trAttrs>\n";
        $arr = $this->getColumnLinks();
        for ($i = 0; $i < sizeof($arr); $i++) {
            $td = isset($tdAttrs[$i]) ? $tdAttrs[$i] : '';
            $s .= "    <th$td>{$arr[$i]}</th>\n";
        };
        $s .= "  </tr>\n";
        return $s;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Takes all $_GET vars and adds them to values that will be added to
     *   each link
     *
     * @return void
     */
    public function acquireGetVars() {
        $arr = $_GET;
        unset($arr[$this->sortByArg]);
        if ($this->sortDirection != false) {
            unset($arr[$this->sortDirection]);
        }
        foreach ($arr as $k => $v) {
            if (!isset($this->arguments[$k])) {
                $this->arguments[$k] = $v;
            }
        }
    }
    //--------------------------------------------------------------------------
}
